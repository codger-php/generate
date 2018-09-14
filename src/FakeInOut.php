<?php

namespace Codger\Generate;

class FakeInOut implements InOut
{
    private $stack = [];

    public function read(string $format = null) : string
    {
        $input = array_shift($this->stack);
        return $input ?? '';
    }

    public function write(string $output) : void
    {
        // noop
    }

    public function error(string $output) : void
    {
        // noop
    }

    public function expect(string $expectation) : FakeInOut
    {
        $this->stack[] = $expectation;
        return $this;
    }
}

