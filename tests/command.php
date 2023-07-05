<?php

$inout = new Codger\Generate\FakeInOut;
Codger\Generate\Recipe::setInOut($inout);

/** Testsuite for the main generate command */
return function () use ($inout) : Generator {

    /** We can execute the demo command and get a chocolate cake. */
    yield function () use ($inout) {
        $runner = new Codger\Generate\Command(['codger:demo:chef', "Chocolate cake"]);
        $runner->execute();
        $result = $inout->flush();
        assert(strpos($result, 'Chocolate cake') !== false);
        assert(strpos($result, 'Chocolate sauce') === false);
    };

    /** We can specifically request for sauce. */
    yield function () use ($inout) {
        $runner = new Codger\Generate\Command(['codger:demo:chef', "Chocolate cake", '--sauce']);
        $runner->execute();
        $result = $inout->flush();
        assert(strpos($result, 'Chocolate cake') !== false);
        assert(strpos($result, 'Chocolate sauce') !== false);
    };
};

