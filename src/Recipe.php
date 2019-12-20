<?php

namespace Codger\Generate;

use Twig_Environment;
use StdClass;
use Monolyth\Cliff\Command;

/**
 * Base Recipe class all other recipes should extend.
 */
abstract class Recipe extends Command
{
    use InOutTrait;

    /** @var Twig_Environment */
    protected $_twig;
    /** @var StdClass */
    protected $_variables;
    /** @var bool */
    protected $_delegated = false;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct(array $arguments = null, bool $strict = true)
    {
        parent::__construct($arguments, $strict);
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
        return $this->_twig->render($this->template, (array)$this->_variables);
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
     * Like `Codger\Recipe::ask`, except supplying a list of valid options to
     * choose from. The _index_ of the selected option is passed to `$callback`.
     * If the index is a string, it is a shortcut for the full answer.
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
        $this->output = function () use ($filename) : void {
            $output = $this->render();
            if (getenv("CODGER_DRY")) {
                $output = "\n$filename:\n$output\n";
                self::$inout->write($output);
            } else {
                $dir = dirname($filename);
                if (file_exists($dir) && !is_dir($dir)) {
                    self::$inout->error("$dir already exists, but is not a directory!");
                } else {
                    if (!file_exists($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    $overwrite = (bool)getenv("CODGER_OVERWRITE");
                    $dump = (bool)getenv("CODGER_DUMP");
                    $skip = (bool)getenv("CODGER_SKIP");
                    if (file_exists($filename) && !($overwrite || $dump || $skip)) {
                        $this->options(
                            "$filename already exists, overwrite or dump to screen?",
                            ['o' => 'Overwrite', 'd' => 'Dump', 's' => 'Skip'],
                            function ($answer) use (&$overwrite) {
                                $overwrite = $answer == 'o';
                            }
                        );
                    }
                    if (!file_exists($filename) || $overwrite) {
                        file_put_contents($filename, $output);
                    } elseif ($dump) {
                        self::$inout->write("\n$filename:\n$output\n");
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
        if (isset($this->output)) {
            $this->output->call($this);
        } elseif (!$this->_delegated) {
            self::$inout->error("Recipe is missing a call to `output` and did not delegate anything, not very useful probably...\n");
        }
    }

    /**
     * Delegate to a sub-recipe.
     *
     * @param string $recipe The name of the recipe to delegate to.
     * @param mixed ...$args Extra arguments.
     * @return Codger\Generate\Recipe Itself for chaining.
     */
    public function delegate(string $recipe, ...$args) : Recipe
    {
        $recipeClass = self::toClassName($recipe);
        $recipe = new $recipeClass($args);
        $arguments = $recipe->getOperands();
        array_shift($arguments); // script name
        $recipe(...$arguments);
        $this->_delegated = true;
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

    public static function toClassName(string $recipe) : string
    {
        return 'Codger\\'.preg_replace('@\\\\Command$@', '\Recipe', self::toPhpName($recipe));
    }
}

