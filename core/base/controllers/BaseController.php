<?php

namespace core\base\controllers;

use core\base\exceptions\RouteException;

abstract class BaseController
{
    protected $page;
    protected $errors;

    protected $controller;
    protected $inputMethod;
    protected $outputMethod;
    protected $parameters;

    public function route()
    {
        $controller = str_replace('/', '\\', $this->controller);

        try {
            $object = new \ReflectionMethod($controller, 'request');

            $args = [
                'parameters' => $this->parameters,
                'inputMethod' => $this->inputMethod,
                'outputMethod' => $this->outputMethod
            ];

            $object->invoke(new $controller(), $args);
        } catch (\ReflectionException $e) {
            throw new RouteException($e->getMessage());
        }
    }

    public function request($args)
    {
        $this->parameters = $args['parameters'];

        $inputData = $args['inputMethod'];
        $outputData = $args['outputMethod'];

        $this->$inputData();

        $this->page = $this->$outputData();

        if ($this->errors) {
            $this->writeLog();
        }

        $this->getPage();
    }

    protected function render($path = '', $parameters = [])
    {
        extract($parameters);

        if (empty($path)) { // is_empty
            $className = (new \ReflectionClass($this))->getShortName();  // IndexController => indexcontroller
            $path = TEMPLATE . explode('controller', strtolower($className))[0];  // indexcontroller => index
        }

        ob_start();

        if (!@include_once $path . '.php') throw new RouteException('Отсутствует шаблон ' . $path);

        return ob_get_clean();
    }

    protected function getPage()
    {
        exit($this->page);
    }

    public function writeLog()
    {
    }
}
