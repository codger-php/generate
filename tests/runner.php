<?php

use Gentry\Gentry\Wrapper;

$inout = Wrapper::createObject(Codger\Generate\FakeInOut::class);
Codger\Generate\Recipe::setInOut($inout);

/** Testsuite for Codger\Generate\Runner */
return function () : Generator {
    $runner = Wrapper::createObject(Codger\Generate\Runner::class, 'chef');

    /** Arguments method strips the -w flag */
    yield function () use ($runner) {
        $GLOBALS['argv'] = ['', '', '-w'];
        $result = $runner->arguments();
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

    /** defaults yields $result === null */
    yield function () use ($runner) {
        $runner->setOptions([]);
        $runner->defaults('blarps');
        $result = $runner->hasOption('blarps');
        assert($result === true);
    };
};

