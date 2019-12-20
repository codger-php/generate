<?php

$inout = new Codger\Generate\FakeInOut;
Codger\Generate\Recipe::setInOut($inout);

/** Testsuite for Codger\Generate\Command */
return function () use ($inout) : Generator {
    $runner = new Codger\Generate\Command([]);

    /** run yields $result === null */
    yield function () use ($runner) {
        $result = $runner('demo:chef', "'Chocolate cake'");
        assert($result === null);
    };
};

