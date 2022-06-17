<?php

namespace core\base\controllers;

use core\base\exceptions\RouteException;
use core\base\settings\Settings;

abstract class BaseController
{
    use \core\base\controllers\BaseMethods;

    protected $page;
    protected $errors;

    protected $controller;
    protected $inputMethod;
    protected $outputMethod;
    protected $parameters;

    protected $styles;
    protected $scripts;

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

        $data = $this->$inputData();

        if (method_exists($this, $outputData)) {
            $page = $this->$outputData($data);
            if (!empty($page)) {  // upgate $this->page
                $this->page = $page;
            }
        } elseif (!empty($data)) {
            $this->page = $data;
        }

        if ($this->errors) {
            $this->writeLog($this->errors);
        }

        $this->getPage();
    }

    protected function render($path = '', $parameters = [])
    {
        extract($parameters);

        if (empty($path)) { // is_empty
            $class = new \ReflectionClass($this);

            $space = str_replace('\\', '/', $class->getNamespaceName() . '\\');
            $routes = Settings::get('routes');

            if ($space === $routes['user']['path']) {
                $template = TEMPLATE;
            } else {
                $template = ADMIN_TEMPLATE;
            }

            $className = $class->getShortName();  // IndexController => indexcontroller
            $path = $template . explode('controller', strtolower($className))[0];  // indexcontroller => index
        }

        ob_start();

        if (!@include_once $path . '.php') throw new RouteException('Отсутствует шаблон ' . $path);

        return ob_get_clean();
    }

    protected function getPage()
    {
        if (is_array($this->page)) {
            foreach ($this->page as $block) {
                echo $block;
            }
        } else {
            echo $this->page;
        }
    }

    protected function init($admin = false)
    {
        if (!$admin) {
            if (!empty(USER_CSS_JS['styles'])) {
                foreach (USER_CSS_JS['styles'] as $item) {
                    $this->styles[] = PATH . TEMPLATE . trim($item, '/');
                }
            }

            if (!empty(USER_CSS_JS['scripts'])) {
                foreach (USER_CSS_JS['scripts'] as $item) {
                    $this->scripts[] = PATH . TEMPLATE . trim($item, '/');
                }
            }
        } else {
            if (!empty(ADMIN_CSS_JS['styles'])) {
                foreach (USER_CSS_JS['styles'] as $item) {
                    $this->styles[] = PATH . ADMIN_TEMPLATE . trim($item, '/');
                }
            }

            if (!empty(ADMIN_CSS_JS['scripts'])) {
                foreach (USER_CSS_JS['scripts'] as $item) {
                    $this->scripts[] = PATH . ADMIN_TEMPLATE . trim($item, '/');
                }
            }
        }
    }

    public function writeLog()
    {
    }
}
