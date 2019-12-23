<?php

namespace Codger\Generate;

use Twig_Environment;
use StdClass;
use Monolyth\Cliff;
use ReflectionObject;
use ReflectionProperty;

/**
 * Base Recipe class all other recipes should extend.
 */
abstract class Recipe extends Cliff\Command
{
    use InOutTrait;
    use DefaultOptions;

    /** @var string */
    protected $_template;

    /** @var StdClass */
    protected $_variables;

    /** @var array */
    protected $_delegated = [];

    /** @var callable */
    protected $_output;

    /** @var Twig_Environment */
    private $_twig;

    /**
     * Constructor.
     *
     * @param array|null $arguments
     * @param Monolyth\Cliff\Command|null $forwardingCommand
     * @return void
     */
    public function __construct(array $arguments = null, Cliff\Command $forwardingCommand = null)
    {
        parent::__construct($arguments, $forwardingCommand);
        $this->_variables = new StdClass;
        self::initInOut();
    }

    /**
     * Set the Twig environment to be used.
     *
     * @param Twig_Environment $_twig
     * @return void
     */
    protected function setTwigEnvironment(Twig_Environment $_twig)
    {
        $this->_twig = $_twig;
    }

    /**
     * Persist all passed options as Twig variables.
     *
     * @return void
     */
    protected function persistOptionsToTwig() : void
    {
        $reflection = new ReflectionObject($this);
        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC & ~ReflectionProperty::IS_STATIC) as $property) {
            $this->set($property->name, $property->getValue($this));
        }
    }

    /**
     * Set a variable to later be passed to Twig.
     *
     * @param string $name
     * @param mixed $value
     * @return Codger\Generate\Recipe Itself for chaining.
     */
    public function set(string $name, $value) : Recipe
    {
        $this->_variables->$name = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->_variables->$name ?? null;
    }

    /**
     * Render the Twig template as a string.
     *
     * @return string
     */
    public function render() : string
    {
        if (!isset($this->_twig)) {
            throw new TwigEnvironmentNotSetException("Missing Twig environment in ".get_class($this), Command::ERROR_TWIG_ENVIRONMENT_NOT_SET);
        }
        if (!isset($this->_template)) {
            throw new TwigEnvironmentNotSetException("Missing template in ".get_class($this), Command::ERROR_TWIG_ENVIRONMENT_NOT_SET);
        }
        return $this->_twig->render($this->_template, (array)$this->_variables);
    }

    /**
     * Ask the user a free-form question. The answer is passed to `$callback` as
     * an argument. Note that `$callback` is called with the current recipe as
     * its scope (`$this`).
     *
     * @param string $question
     * @param callable $callback
     * @return Codger\Generate\Recipe Itself for chaining.
     */
    public function ask(string $question, callable $callback) : Recipe
    {
        self::$inout->write("$question ");
        $answer = self::$inout->read();
        $callback->call($this, $answer);
        return $this;
    }

    /**
     * Like `Codger\Generate\Recipe::ask`, except supplying a list of valid
     * options to choose from. The _index_ of the selected option is passed to
     * `$callback`. If the index is a string, it is a shortcut for the full
     * answer.
     *
     * @param string $question
     * @param array $options
     * @param callable $callback
     * @return Codger\Generate\Recipe Itself for chaining.
     */
    public function options(string $question, array $options, callable $callback) : Recipe
    {
        self::$inout->write("$question\n\n");
        foreach ($options as $index => $option) {
            self::$inout->write("[$index]: $option\n");
        }
        $answer = trim(self::$inout->read("%s\n"));
        if (!array_key_exists($answer, $options) && !in_array($answer, $options)) {
            self::$inout->write("Please select a valid option:\n");
            return $this->options('', $options, $callback);
        }
        if (!isset($options[$answer])) {
            $answer = array_flip($options)[$answer];
        }
        $callback->call($this, $answer);
        return $this;
    }

    /**
     * Output the rendered template to $filename.
     *
     * @param string $filename
     * @return Codger\Generate\Recipe Itself for chaining.
     */
    public function output(string $filename) : Recipe
    {
        $this->_output = function () use ($filename) : void {
            $output = $this->render();
            if (!strlen($this->outputDir)) {
                $output = "\n$filename:\n$output\n";
                self::$inout->write($output);
            } else {
                $dir = dirname("{$this->outputDir}/$filename");
                if (file_exists($dir) && !is_dir($dir)) {
                    self::$inout->error("$dir already exists, but is not a directory!");
                } else {
                    if (!file_exists($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    $overwrite = (bool)$this->replace;
                    $dump = false;
                    if (file_exists("$dir/$filename") && !$this->replace) {
                        $this->options(
                            "$dir/$filename already exists, overwrite or dump to screen?",
                            ['o' => 'Overwrite', 'd' => 'Dump', 's' => 'Skip'],
                            function ($answer) use (&$overwrite, &$dump) {
                                $overwrite = $answer == 'o';
                                $dump = $answer == 'd';
                            }
                        );
                    }
                    if (!file_exists("$dir/$filename") || $overwrite) {
                        file_put_contents("$dir/$filename", $output);
                    } elseif ($dump) {
                        self::$inout->write("\n$dir/$filename:\n$output\n");
                    }
                }
            }
        };
        return $this;
    }

    /**
     * Process this recipe.
     *
     * @return void
     */
    public function process() : void
    {
        if (isset($this->_output)) {
            $this->_output->call($this);
        } elseif (!$this->_delegated) {
            self::$inout->error("Recipe is missing a call to `output` and did not delegate anything, not very useful probably...\n");
        }
        array_walk($this->_delegated, function ($recipe) {
            self::$inout->write(sprintf(
                "  > Delegating to %s...\n",
                get_class($recipe)
            ));
            $recipe->process();
        });
    }

    /**
     * Delegate to a sub-recipe.
     *
     * @param string $recipe The name of the recipe to delegate to. This can be
     *  the CLI name, or the actual classname.
     * @param array $arguments|null Arguments.
     * @return Codger\Generate\Recipe Itself for chaining.
     * @throws Codger\Generate\RecipeNotFoundException
     */
    public function delegate(string $recipe, array $arguments = null) : Recipe
    {
        $recipeClass = class_exists($recipe) ? $recipe : self::toClassName($recipe);
        try {
            $recipe = new $recipeClass($arguments);
        } catch (Error $e) {
            throw new RecipeNotFoundException($recipe);
        }
        $recipe->execute();
        $this->_delegated[] = $recipe;
        return $this;
    }

    /**
     * Display info to the user at a certain stage. The info is automatically
     * wrapped by newlines.
     *
     * @param string $info
     * @return Codger\Generate\Recipe Itself for chaining.
     * @TODO add some formatting (colours?) so it stands out more.
     */
    public function info(string $info) : Recipe
    {
        self::$inout->write("\n$info\n");
        return $this;
    }

    /**
     * Display an error to the user. The error is automatically wrapped by
     * newlines.
     *
     * @param string $error
     * @return Codger\Generate\Recipe Itself for chaining.
     * @TODO add some formatting (colours?) so it stands out more.
     */
    public function error(string $error) : Recipe
    {
        self::$inout->error("\n$error\n");
        return $this;
    }

    public function execute() : void
    {
        parent::execute();
        $this->process();
    }

    public static function toClassName(string $recipe) : string
    {
        return 'Codger\\'.preg_replace('@\\\\Command$@', '', self::toPhpName($recipe));
    }
}

