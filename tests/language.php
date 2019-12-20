<?php

use Codger\Generate\Language;

/** Testsuite for language helpers */
return function () : Generator {
    /** English words are correctly pluralized */
    yield function () {
        assert(Language::pluralize('lady') === 'ladies');
        assert(Language::pluralize('user') === 'users');
    };

    /** English words are correctly singularized */
    yield function () {
        assert(Language::singular('ladies') === 'lady');
        assert(Language::singular('users') === 'user');
    };

    /** We can correctly convert to a PHP namesace (this is the most complex case, currently) */
    yield function () {
        assert(Language::convert('foo/bar/foo-bar', Language::TYPE_PHP_NAMESPACE) === 'Foo\Bar\FooBar');
    };

};

