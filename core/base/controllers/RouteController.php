<?php

namespace core\base\controllers;

use core\base\exceptions\RouteException;
use core\base\settings\Settings;
use core\base\settings\ShopSettings;

class RouteController
{
    static private $_instance;

    protected $routes;

    protected $controller;
    protected $inputMethod;
    protected $outputMethod;
    protected $parameters;

    private function __construct()
    {
        $address_str = $_SERVER['REQUEST_URI'];

        // избавляемся от дублей страниц (/ в конце адресной строки)
        $slash_pos = strrpos($address_str, '/');
        if ($slash_pos === strlen($address_str) - 1 and $slash_pos !== 0) {
            $this->redirect(rtrim($address_str, '/'));
        }

        // проверка на расположение index.php в корне
        $index_path = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'index.php'));
        if ($index_path === PATH) {
            $this->routes = Settings::get('routes');

            if (!$this->routes) {
                throw new RouteException("Routes are not available!!!");
            }

            $admin_alias = $this->routes['admin']['alias'];
            if (strpos($address_str, $admin_alias) === strlen(PATH)) {
                // админка

                $useful_url = substr($address_str, strlen(PATH) + strlen($admin_alias) + 1);
                $url = explode('/', $useful_url);

                $plugin_path = $_SERVER['DOCUMENT_ROOT'] . PATH . $this->routes['plugins']['path'] . $url[0];
                if ($url[0] and is_dir($plugin_path)) {
                    $plugin = array_shift($url);

                    $pluginSettings = $this->routes['settings']['path'] . ucfirst($plugin) . 'Settings';
                    $pluginSettings_path = $_SERVER['DOCUMENT_ROOT'] . PATH . $pluginSettings . 'php';

                    if (file_exists($pluginSettings_path)) {
                        $pluginSettings = str_replace('/', '\\', $pluginSettings);
                        $this->routes = $pluginSettings::get('routes');
                    }

                    $dir = $this->routes['plugins']['dir'] ? '/' . $this->routes['plugins']['dir'] . '/' : '/';
                    $dir = str_replace('//', '/', $dir);

                    $this->controller = $this->routes['plugins']['path'] . $plugin . $dir;

                    $hrUrl = $this->routes['plugins']['hrUrl'];

                    $route = 'plugins';
                } else {
                    $this->controller = $this->routes['admin']['path'];

                    $hrUrl = $this->routes['admin']['hrUrl'];

                    $route = 'admin';
                }
            } else {
                $url = explode('/', substr($address_str, strlen(PATH)));

                $hrUrl = $this->routes['user']['hrUrl'];

                $this->controller = $this->routes['user']['path'];

                $route = 'user';
            }

            $this->createRoute($route, $url);

            if ($url[1]) {
                $count = count($url);
                $key = '';

                if (!$hrUrl) {
                    $i = 1;
                } else {
                    $this->parameters['alias'] = $url[1];
                    $i = 2;
                }

                for (; $i < $count; $i++) {
                    if (!$key) {
                        $key = $url[$i];
                        $this->parameters[$key] = '';
                    } else {
                        $this->parameters[$key] = $url[$i];
                        $key = '';
                    }
                }
            }

            exit();
        } else {
            try {
                throw new \Exception("Некорректная директория сайта!");
            } catch (\Exception $e) {
                exit($e->getMessage());
            }
        }
    }

    private function createRoute($var, $arr)
    {
        $route = [];

        if (!empty($arr[0])) {
            if ($this->routes[$var]['routes'][$arr[0]]) {
                $route = explode('/', $this->routes[$var]['routes'][$arr[0]]);

                $this->controller .= ucfirst($route[0] . 'Controller');
            } else {
                $this->controller .= ucfirst($arr[0] . 'Controller');
            }
        } else {
            $this->controller .= $this->routes['default']['controller'];
        }

        $this->inputMethod = $route[1] ? $route[1] : $this->routes['default']['inputMethod'];
        $this->outputMethod = $route[2] ? $route[2] : $this->routes['default']['outputMethod'];

        return;
    }

    private function __clone()
    {
    }

    static public function getInstance()
    {
        if (self::$_instance instanceof self) {
            return self::$_instance;
        }

        return self::$_instance = new self();
    }

    public function route()
    {
    }

    public function redirect($address)
    {
    }
}
