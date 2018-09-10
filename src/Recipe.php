<?php

namespace Codger\Generate;

use Twig_Environment;
use StdClass;

abstract class Recipe
{
    protected $twig;
    protected $variables;

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
        fwrite(STDOUT, $question.' ');
        $answer = trim(fgets(STDIN));
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
            fwrite(STDOUT, "$question\n\n");
        }
        foreach ($options as $index => $option) {
            fwrite(STDOUT, "[$index]: $option\n");
        }
        $answer = fscanf(STDIN, "%s\n")[0];
        if (!array_key_exists($answer, $options) && !in_array($answer, $options)) {
            fwrite(STDOUT, "Please select a valid option:\n");
            return $this->options('', $options, $callback);
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
        $this->output = function () use ($filename) {
            $output = $this->render();
            file_put_contents($filename, $output);
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
        $this->output->call($this);
    }

    /**
     * Delegate to a sub-recipe.
     *
     * @param string $path Path to sub-recipe.
     * @param string ...$args Extra arguments.
     * @return Codger\Generate\Recipe Itself for chaining.
     */
    public function delegate(string $path, string ...$args) : Recipe
    {
        $runner = new Runner($path);
        $runner->run(...$args);
        return $this;
    }
}

