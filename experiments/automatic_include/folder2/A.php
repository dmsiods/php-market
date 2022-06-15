<?php

namespace folder2;

use core\base\exceptions\RouteException;

class A
{
    public function __construct()
    {
        // echo "class A - 2 \n";
        throw new RouteException("ERROR class A - 2 \n");
    }
}
