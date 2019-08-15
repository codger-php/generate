<?php

namespace Codger\Generate;

abstract class Language
{
    use InOutTrait;

    const TYPE_PHP_NAMESPACE = 1;
    const TYPE_NAMESPACE = 99;
    const TYPE_TABLE = 2;
    const TYPE_VARIABLE = 3;
    const TYPE_PATH = 4;
    const TYPE_URL = 5;
    const TYPE_CSS_IDENTIFIER = 6;
    const TYPE_ANGULAR_MODULE = 7;
    const TYPE_ANGULAR_COMPONENT = 8;
    const TYPE_ANGULAR_TAG = 9;

    /**
     * Returns a pluralized version of the specified `$string`.
     *
     * @param string $string
     * @return string
     */
    public static function pluralize(string $string) : string
    {
        switch (substr($string, -1)) {
            case 'y':
                return substr($string, 0, -1).'ies';
            default:
                return "{$string}s";
        }
    }

    /**
     * Returns the singular version of the specified `$string`.
     *
     * @param string $string
     * @return string
     */
    public static function singular(string $string) : string
    {
        if (substr($string, -3) == 'ies') {
            return substr($string, 0, -3).'y';
        }
        return substr($string, 0, -1);
    }

    /**
     * Convert input string to a different "format".
     *
     * @param string $input
     * @param int $to Use one of the `TYPE_` constants defined on
     *  `Codger\Php\Language`.
     * @return string
     */
    public static function convert(string $input, int $to) : string
    {
        $string = self::normalize($input);
        $parts = explode(' ', $string);
        array_walk($parts, function (&$part) {
            $part = ucfirst($part);
        });
        switch ($to) {
            case self::TYPE_NAMESPACE:
                self::initInOut();
                self::$inout->error("`Codger\Generate\Language::TYPE_NAMESPACE` is deprecated.
Use `Codger\Generate\Language::TYPE_PHP_NAMESPACE` instead.");
            case self::TYPE_PHP_NAMESPACE:
                return implode('\\', $parts);
            case self::TYPE_TABLE:
                return strtolower(implode('_', $parts));
            case self::TYPE_VARIABLE:
            case self::TYPE_ANGULAR_COMPONENT:
                return lcfirst(implode('', $parts));
            case self::TYPE_PATH:
                return implode('/', $parts);
            case self::TYPE_URL:
                return strtolower(implode('/', $parts));
            case self::TYPE_CSS_IDENTIFIER:
            case self::TYPE_ANGULAR_TAG:
                return strtolower(implode('-', $parts));
            case self::TYPE_ANGULAR_MODULE:
                return strtolower(implode('.', $parts));
            default:
                throw new DomainException("Please use one of the `TYPE_` constants on the `Codger\Generate\Language` class as 2nd parameter.");
        }
    }

    /**
     * Internal helper to normalize an input string.
     *
     * @param string $string
     * @return string
     */
    private static function normalize(string $string) : string
    {
        return strtolower(str_replace(['\\', '/', '_', '.'], ' ', $string));
    }
}

