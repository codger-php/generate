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

## Writing recipes
Recipies are expected to be stored in a `recipes` folder in the root of your
project (i.e., next to the `vendor` folder Composer creates).

> Actually, they're expected in `cwd`, or the current working directory. If for
> whatever reason you want or need to place your `recipes` directory elsewhere,
> simply run `codger` from there, e.g. `$ /path/to/vendor/bin/codger recipe`.

Each recipe needs at least a `Recipy.php` main file. This _must_ return a lambda
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

