# codger/generate
CODe GEneratoR, base framework

In any software project adhering to some form of standards (be it your own or
those "mandated" by a framework) there will be lots of boilerplate code. E.g.
in an MVC setting you'll (pratically) always have models, views and controllers
for each component that in their core are similar. An example would be if you
use Doctrine - the entities are generated based upon your database schema
directly.

Codger aims to offer code generation tools that take this principle a step
further, allowing you to specify so-called _recipes_ for artbitrary code
generation.

Although Codger itself uses PHP and Twig, the generated code can theoretically
be in any language. As an example, a recipe for Chef code is included.

## Installation
```sh
$ composer require --dev codger/generate
```

> Typically you'll install a more specific package like `codger/php`, which has
> `codger/generate` as a dependency.

## Usage
```sh
$ vendor/bin/codger name-of-recipe some additional arguments --or --options
```

The name of the recipe should be resolvable to a PHP class name. The rules for
this are as follows:

- slashes or semicolons are converted to backslashes (namespace separator);
- characters following a hyphen are uppercased;
- other characters are lowercased, barring the first which is uppercased.

Additionally, all recipes are prefixed with the `Codger` namespace. This allows
you to easily group them in a directory outside of your regular source code,
e.g. a `./recipes` folder.

Thus, a recipe `monolyth:some-test` would resolve to the namespace
`Codger\\Monolyth\\SomeTest`.

## Default options
All Codger recipes support 2 default options as defined in the
`Codger\Generate\DefaultOptions` trait:

- `--output-dir=/some/path`. Supply this to actually attempt to write generated
  files to disk; the default is to dump to screen for manual inspection.
- `--replace`. If this flag is set, existing files will be overwritten without
  warning (the default is to prompt for overwrite, dump or skip).

Whether or not shorthand flags exist depends on your recipe's other options.

## Writing recipes
Recipes are expected to be stored in a `recipes` folder in the root of your
project (i.e., next to the `vendor` folder Composer creates).

> Actually, they're expected in `cwd`, or the current working directory. If for
> whatever reason you want or need to place your `recipes` directory elsewhere,
> simply run `codger` from there, e.g. `$ /path/to/vendor/bin/codger recipe`.

Each recipe needs at least a `Recipe.php` main file. This _must_ return a lambda
in turn returning an instance of a class extending `Codger\Generate\Recipy`. The
arguments to the lambda are the additional command line arguments after the name
of the recipe, in order. Inside the closure, the recipe class can do its thing
in order to correctly generate code. E.g.:

```php
<?php

use Codger\Generate\Recipe;

return function (string $some, string $argument) : Recipe {
    $recipe = new class() extends Recipy {
    };
    // Do stuff...
    return $recipe;
};
```

For a recipe to be useful, it needs to either call
`Codger\Generate\Recipe::output` to specify the intended output file, or
delegate to another recipe (see below).

## Writing output
When the recipe is done, it should call the `output` method (unless it is a
"master recipe" only meant to delegate tasks). It takes a single argument which
should contain the full path to the intended target file.

> Output is only written to disk if one of the _flags_ (see above) has been set;
> the default modus for Codger is to write to STDOUT for inspection.

Typically, the path will be constructed using one or more of the _arguments_
passed to the recipe; a recipe that only ever updates a single file is probably
not too useful.

## Converting arguments
Use the `Codger\Generate\Language` class to convert arguments for various uses.
E.g., a PHP module `Foo\Bar` might be written to `src/Foo/Bar.php`. The
`Language` helper class defines a number of methods to make this easier:

```php
<?php

use Codger\Generate\Language;

echo Language::pluralize('city'); // cities
echo Language::singular('cities'); // city
echo Language::convert('Foo\Bar', Language::TYPE_CSS_IDENTIFIER); // foo-bar

```

The following `TYPE_` constants are currently available:

- `TYPE_PHP_NAMESPACE`: Foo\Bar
- `TYPE_TABLE`: foo_bar
- `TYPE_VARIABLE`: fooBar
- `TYPE_PATH`: Foo/Bar
- `TYPE_URL`: foo/bar
- `TYPE_CSS_IDENTIFIER`: foo-bar
- `TYPE_ANGULAR_MODULE`: foo.bar
- `TYPE_ANGULAR_COMPONENT`: fooBar
- `TYPE_ANGULAR_TAG`: foo-bar

For backwards compatibility, the type `TYPE_NAMESPACE` currently acts as an
alias for `TYPE_PHP_NAMESPACE`, but it is deprecated and will raise a warning
when used. It will be removed in a future release, so it is recommended to use
`TYPE_PHP_NAMESPACE` as of version 0.7.0.

## Delegating tasks
Some recipes will want to make use of other recipes. This way you can "chain"
recipes together to build more complex recipes. Delegating is done by calling
the `Codger\Generate\Recipe::delegate` method.

The first argument is the name of the recipe to delegate to. Any additional
parameters to `delegate` are passed verbatim as arguments to the delegated
recipe.

## User feedback
Via the `Codger\Generate\InOutTrait` recipes provide the `info` and `error`
methods which can be used to offer additional information. This is useful if
(obviously) something wrong-ish happened during recipe execution, but also for
notes about code that cannot be generated directly into a file (e.g. because
your recipe defines additional routes which can't be safely appended to an
existing routing file).

## User input and conditionals
Often a recipe will want to offer various options or ask for user input that you
don't want to or cannot specify on the command line as arguments. Recipes offer
two convenience methods for this: `ask` and `options`.

### `ask`ing questions
The `ask` method is meant for open-ended input, e.g. database credentials. Its
first argument is the question string, the second a callback. The callback is
called with a single argument: the answer string given. It is up to the recipe's
author to validate the answer inside the callback:

```php
<?php

$recipe->ask("What is your name?", function (string $answer) {
    $this->info("Hello, $answer.");
});
```

Note that the callback is bound to the recipe, so inside it you can simply use
`$this` to refer to it.

### Offering `options`
The `options` method is meant for providing the user with a simple list of
options to select from (the most simple case being 'yes/no'). Like `ask` its
first argument is the question. The second argument is an array or hash of
options, and the third is the callback (which works like `ask`).

Unlike `ask`, the `options` method validates the answer given. It should either
be a key in the passed `$options` array, or a full answer present in it.

The answer passed to the callback is _always_ the key in the array.

```php
<?php

$recipe->options("Would you like fries with that?", ['Y' => 'Yes', 'n' => 'no'], function (string $answer) {
    if ($answer == 'Y') {
        $this->info('Yummy!');
    } else {
        $this->info('A very healthy choice.');
    }
});

```

