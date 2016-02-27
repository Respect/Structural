<?php

namespace Respect\Structural\Driver;

use Exception as BaseException;

class Exception extends BaseException
{
    public static function extensionNotLoaded($name)
    {
        return new self("The {$name} extension is not loaded");
    }
}
