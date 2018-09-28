<?php

namespace Codger\Generate;

use Twig_Environment;
use StdClass;

/**
 * Base Recipe class all other recipes should extend.
 */
abstract class Recipe
{
    /** @var Twig_Environment */
    protected $twig;
    /** @var StdClass */
    protected $variables;
    /** @var bool */
    protected $delegated = false;
    /** @var Codger\Generate\InOut */
    protected static $inout;

    /**
     * Constructor. Recipes must be constructed with a user-supplied
     * Twig_Environment, since we can't guess how users would like to configure
     * it (cache dir, loader, debug etc).
     *
     * @param Twig_Environment $twig
     */
    public function __construct(Twig_Environment $twig)
    {
        $this->variables = new StdClass;
        $this->twig = $twig;
        if (!isset(self::$inout)) {
            self::$inout = new StandardInOut;
        }
    }

    /**
     * Set the input/output streams. This is useful for e.g. testing, but also
     * in other scenarios where you need to reroute input/output.
     *
     * @param Codger\Generate\InOut $inout
     */
    public static function setInOut(InOut $inout) : void
    {
        self::$inout = $inout;
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
        $this->variables->$name = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->variables->$name ?? null;
    }

    /**
     * Render the Twig template as a string.
     *
     * @return string
     */
    public function render() : string
    {
        return $this->twig->render($this->template, (array)$this->variables);
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
        if ($question) {
            self::$inout->write("$question\n\n");
        }
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
                    file_put_contents($filename, $output);
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
        } elseif (!$this->delegated) {
            fwrite(STDERR, "Recipe is missing a call to `output` and did not delegate anything, not very useful probably...\n");
        }
    }

    /**
     * Delegate to a sub-recipe.
     *
     * @param string $recipe The name of the recipe to delegate to.
     * @param string|null $path Path to sub-recipe. Defaults to `cwd`.
     * @param string ...$args Extra arguments.
     * @return Codger\Generate\Recipe Itself for chaining.
     */
    public function delegate(string $recipe, string $path = null, string ...$args) : Recipe
    {
        (new Runner($recipe, $path))->run(...$args);
        $this->delegated = true;
        return $this;
    }

    /**
     * Display info to the user at a certain stage. The info is automatically
     * wrapped by newlines.
     *
     * @param string $info
     * @return Codger\Generate\Recipe Itself for chaining.
     */
    public function info(string $info) : Recipe
    {
        self::$inout->write("\n$info\n");
        if (getenv("CODGER_DRY")) {
            $this->ask("\nPress enter to continue", function() {});
        }
        return $this;
    }
}

