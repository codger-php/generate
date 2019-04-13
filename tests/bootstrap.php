<?php

use Gentry\Gentry\Wrapper;

$inout = Wrapper::createObject(Codger\Generate\FakeInOut::class);
Codger\Generate\Recipe::setInOut($inout);

/** Testsuite for Codger\Generate\Bootstrap */
return function () use ($inout) : Generator {
    $runner = Wrapper::createObject(Codger\Generate\Bootstrap::class, 'chef');

    /** Arguments method strips the -w flag */
    yield function () use ($runner) {
        $GLOBALS['argv'] = ['', '', '-w'];
        $result = $runner->arguments([]);
        assert($result === []);
        putenv("CODGER_DRY=1");
        $GLOBALS['argv'] = ['', ''];
    };

    /** run yields $result === null */
    yield function () use ($runner) {
        $result = $runner->run('Chocolate cake');
        assert($result === null);
    };

    /** hasOption returns true even if the option was negated */
    yield function () use ($runner) {
        $runner->setOptions(['^blarps']);
        $result = $runner->hasOption('blarps');
        assert($result === true);
    };

    /** askedFor returns false if the option was negated */
    yield function () use ($runner) {
        $result = $runner->askedFor('blarps');
        assert($result === false);
    };

    /** an option with a default appears set, even if not specified explicitly */
    yield function () use ($runner) {
        $runner->setOptions([]);
        $runner->defaults('blarps');
        $result = $runner->hasOption('blarps');
        assert($result === true);
    };

    /** we can set aliases */
    yield function () use ($inout) {
        $runner = Wrapper::createObject(Codger\Generate\Bootstrap::class, 'chefspecial', (object)[
            'aliases' => (object)['chefspecial' => ['chef', 'Chocolate mousse']]
        ]);
        $runner->run();
        assert(strpos($inout->flush(), 'Chocolate mousse') !== false);
    };
};

