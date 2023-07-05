<?php

/** Testsuite for fake in/output */
return function () : Generator {
    $object = new Codger\Generate\FakeInOut;
    /** read yields $result === '0' */
    yield function () use ($object) {
        $result = $object->expect('foo');
        assert($result instanceof Codger\Generate\FakeInOut);
        $result = $object->read('%d');
        assert($result === '0');
    };
    /** read yields $result === 'foo' */
    yield function () use ($object) {
        $object->expect('foo');
        $result = $object->read();
        assert($result === 'foo');
    };

    /** write generates flushable output */
    yield function () use ($object) {
        $object->write('foo');
        $result = $object->flush();
        assert($result === 'foo');
    };

    /** error generates flushable output */
    yield function () use ($object) {
        $object->error('foo');
        $result = $object->flush();
        assert($result === 'foo');
    };

};

