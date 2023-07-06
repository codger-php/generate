<?php

namespace Codger\Generate;

use Twig\Environment;
use StdClass;
use Monolyth\Cliff;
use ReflectionObject;
use ReflectionProperty;
use Closure;

/**
 * Base Recipe class all other recipes should extend.
 */
abstract class Recipe extends Cliff\Command
{
    use InOutTrait;
    use DefaultOptions;

    protected string $_template;

    protected stdClass $_variables;

    protected array $_delegated = [];

    protected Closure $_output;

    private Environment $_twig;

    /**
     * Constructor.
     *
     * @param array|null $arguments
     * @return void
     */
    public function __construct(array $arguments = null)
    {
        parent::__construct($arguments);
        $this->_variables = new StdClass;
        self::initInOut();
    }

    /**
     * Set the Twig environment to be used.
     *
     * @param Twig\Environment $_twig
     * @return void
     */
    protected function setTwigEnvironment(Environment $_twig) : void
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
            if (isset($this->{$property->name})) {
                $this->set($property->name, $property->getValue($this));
            }
        }
    }

    /**
     * Set a variable to later be passed to Twig.
     *
     * @param string $name
     * @param mixed $value
     * @return Codger\Generate\Recipe Itself for chaining.
     */
    public function set(string $name, $value) : self
    {
        $this->_variables->$name = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name) : mixed
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
        $variables = (array)$this->_variables;
        array_walk($variables, [$this, 'preProcess']);
        return $this->_twig->render($this->_template, $variables);
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
    public function ask(string $question, callable $callback) : self
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
    public function options(string $question, array $options, callable $callback) : self
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
    public function output(string $filename) : self
    {
        $this->_output = Closure::fromCallable(function () use ($filename) : void {
            $output = $this->render();
            if (!isset($this->outputDir)) {
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
                    if (file_exists("{$this->outputDir}/$filename") && !$this->replace) {
                        $this->options(
                            "{$this->outputDir}/$filename already exists, overwrite or dump to screen?",
                            ['o' => 'Overwrite', 'd' => 'Dump', 's' => 'Skip'],
                            function ($answer) use (&$overwrite, &$dump) {
                                $overwrite = $answer == 'o';
                                $dump = $answer == 'd';
                            }
                        );
                    }
                    if (!file_exists("{$this->outputDir}/$filename") || $overwrite) {
                        file_put_contents("{$this->outputDir}/$filename", $output);
                    } elseif ($dump) {
                        self::$inout->write("\n{$this->outputDir}/$filename:\n$output\n");
                    }
                }
            }
        });
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
            $recipe->execute();
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
    public function delegate(string $recipe, array $arguments = null) : self
    {
        $recipeClass = class_exists($recipe) ? $recipe : self::toClassName($recipe);
        try {
            $recipe = new $recipeClass($arguments);
        } catch (Error $e) {
            throw new RecipeNotFoundException($recipe);
        }
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
    public function info(string $info) : self
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
    public function error(string $error) : self
    {
        self::$inout->error("\n$error\n");
        return $this;
    }

    /**
     * Execute and process the recipe.
     *
     * @return void
     */
    public function execute() : void
    {
        parent::execute();
        $this->process();
    }

    /**
     * Convert the CLI recipe into a fully qualified classname.
     *
     * @param string $recipe
     * @return string
     */
    public static function toClassName(string $recipe) : string
    {
        return preg_replace('@\\\\Command$@', '', self::toPhpName($recipe));
    }

    /**
     * Internal helper to recursively render passed variables.
     *
     * @param mixed &$element
     * @return void
     */
    private function preProcess(&$element) : void
    {
        if (is_object($element) && method_exists($element, 'render')) {
            $element = $element->render();
        }
        if (is_array($element)) {
            array_walk($element, [$this, 'preProcess']);
        }
    }
}

