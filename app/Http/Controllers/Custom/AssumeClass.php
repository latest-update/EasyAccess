<?php
namespace App\Http\Controllers\Custom;

use ReflectionClass;

class AssumeClass
{
    public static function getClassName(object $object) : string
    {
        return (new ReflectionClass($object))->getShortName();
    }
}
