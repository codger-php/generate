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
$ vendor/bin/codger name-of-recipe some additional arguments
```

To actually write files (Codger defaults to outputting to `STDOUT`) pass the
`-w` flag ("write") as any argument.

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

## Delegating tasks
Some recipes will want to make use of other recipes. This way you can "chain"
recipes together to build more complex recipes. Delegating is done by calling
the `Codger\Generate\Recipe::delegate` method.

The first argument is the name of the recipe to delegate to. The second argument
is the optional path to the child recipe (defaults to `cwd` when `null` is
passed). Note this path should _not_ contain the `recipes` part; the base
location of the package is sufficient. You can optionally include the
vendor/package name to make the runner automatically look in `vendor`, e.g.:
`vendor/bin/codger vendor/package/recipe`.

