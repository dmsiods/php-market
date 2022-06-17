<?php

namespace core\user\controllers;

use core\base\controllers\BaseController;

class IndexController extends BaseController
{
    protected function inputData()
    {
        $name = 'Dima';
        $content = $this->render('', compact('name'));
        $header = $this->render(TEMPLATE . 'header');
        $footer = $this->render(TEMPLATE . 'footer');

        return compact('header', 'content', 'footer');
    }

    // protected function outputData()
    // {
    //     $vars = func_get_arg(0);
    //     $this->page = $this->render(TEMPLATE . 'templater', $vars);
    // }
}
