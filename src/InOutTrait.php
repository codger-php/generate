<?php

namespace Codger\Generate;

trait InOutTrait
{
    /** @var Codger\Generate\InOut */
    protected static $inout;

    protected static function initInOut()
    {
        if (!isset(self::$inout)) {
            self::$inout = new StandardInOut;
        }
    }

    /**
     * Set the input/output streams. This is useful for e.g. testing, but also
     * in other scenarios where you need to reroute input/output.
     *
     * @param Codger\Generate\InOut $inout
     */
    public static function setInOut(InOut $inout) : void
    {
        self::$inout = $inout;
    }
}

