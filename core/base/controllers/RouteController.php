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
            $this->redirect(rtrim($address_str, '/'), 301);
        }

        // проверка на расположение index.php в корне
        $index_path = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'index.php'));

        if ($index_path === PATH) {

            // проверка, доступны ли руты
            $this->routes = Settings::get('routes');

            if (!$this->routes) {
                throw new RouteException("Routes are not available!!!");
            }

            // проверка на запрос админки
            $admin_alias = $this->routes['admin']['alias'];
            $url_parsed_arr = explode('/', substr($address_str, strlen(PATH)));
            $admin_placeholder = !empty($url_parsed_arr[0]) ? $url_parsed_arr[0] : false;

            if ($admin_placeholder and $admin_placeholder === $admin_alias) {

                array_shift($url_parsed_arr);  // delete "admin"

                // проверка, есть ли плагин
                $plugin_placeholder = !empty($url_parsed_arr[0]) ? $url_parsed_arr[0] : false;
                $plugin_path = $_SERVER['DOCUMENT_ROOT'] . PATH . $this->routes['plugins']['path'] . $plugin_placeholder;

                if ($plugin_placeholder and is_dir($plugin_path)) {

                    $plugin = array_shift($url_parsed_arr);

                    // подгружаем уникальные настройки плагина
                    $pluginSettings = $this->routes['settings']['path'] . ucfirst($plugin) . 'Settings';
                    $pluginSettings_path = $_SERVER['DOCUMENT_ROOT'] . PATH . $pluginSettings . '.php';

                    if (file_exists($pluginSettings_path)) {
                        $pluginSettings = str_replace('/', '\\', $pluginSettings);
                        $this->routes = $pluginSettings::get('routes');
                    }

                    // проверка, может контроллер для плагина в другом месте
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
                $hrUrl = $this->routes['user']['hrUrl'];
                $this->controller = $this->routes['user']['path'];
                $route = 'user';
            }

            $this->createRoute($route, $url_parsed_arr);

            // есть ли параметры
            if (!empty($url_parsed_arr[1])) {
                $count = count($url_parsed_arr);
                $key = '';

                // проверка на наличие алиаса (читабельность, чпу(человекоподобное))
                if (!$hrUrl) {
                    $i = 1;
                } else {
                    $this->parameters['alias'] = $url_parsed_arr[1];
                    $i = 2;
                }

                // раскидываем параметры по ключам и значениям
                for (; $i < $count; $i++) {
                    if (!$key) {
                        $key = $url_parsed_arr[$i];
                        $this->parameters[$key] = '';
                    } else {
                        $this->parameters[$key] = $url_parsed_arr[$i];
                        $key = '';
                    }
                }
            }
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
            if (array_key_exists($arr[0], $this->routes[$var]['routes'])) {
                $route = explode('/', $this->routes[$var]['routes'][$arr[0]]);

                $this->controller .= ucfirst($route[0] . 'Controller');
            } else {
                $this->controller .= ucfirst($arr[0] . 'Controller');
            }
        } else {
            $this->controller .= $this->routes['default']['controller'];
        }

        $this->inputMethod = array_key_exists(1, $route) ? $route[1] : $this->routes['default']['inputMethod'];
        $this->outputMethod = array_key_exists(2, $route) ? $route[2] : $this->routes['default']['outputMethod'];

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

    public function redirect($address, $code)
    {
    }
}
