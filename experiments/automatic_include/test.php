<?php

use core\base\exceptions\RouteException;

function autoloadMainClasses($class_name)
{
    $class_name = str_replace('\\', '/', $class_name);

    if (!@include_once $class_name . '.php') {
        throw new RouteException('Неверное имя файла для подключения - ' . $class_name);
    }
}

spl_autoload_register('autoloadMainClasses');

try {
    $a = new \folder2\A();
} catch (RouteException $e) {
    exit($e->getMessage());
}
