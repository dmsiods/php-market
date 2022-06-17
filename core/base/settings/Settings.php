<?php

namespace core\base\settings;

class Settings
{

    private $routes = [
        'admin' => [
            'alias' => 'admin',
            'path' => 'core/admin/controllers/',
            'hrUrl' => false,
            'routes' => [
                // 'product' => 'goods/getGoods/sale'
            ]
        ],
        'settings' => [
            'path' => 'core/base/settings/'
        ],
        'plugins' => [
            'path' => 'core/plugins/',
            'hrUrl' => false,
            'dir' => false
        ],
        'user' => [
            'path' => 'core/user/controllers/',
            'hrUrl' => true,
            'routes' => [
                // 'article' => 'info/getArticle',
                // 'club' => 'info/getClub'
                // 'catalog' => 'site/hello/by'
                // 'site' => 'index/hello'
            ]
        ],
        'default' => [
            'controller' => 'IndexController',
            'inputMethod' => 'inputData',
            'outputMethod' => 'outputData'
        ]
    ];

    private $templateArr = [
        'text' => ['name', 'phone', 'address'],
        'textarea' => ['content', 'keywords']
    ];

    static private $_instance;

    public function __construct()
    {
    }

    public function __clone()
    {
    }

    static public function get($property)
    {
        return self::instance()->$property;
    }

    static public function instance()
    {
        if (self::$_instance instanceof self) {
            return self::$_instance;
        }
        return self::$_instance = new self();
    }

    public function clueProperties($class)
    {
        $baseProperties = [];

        foreach ($this as $name => $item) {
            $property = property_exists($class, $name) ? $class::get($name) : null;

            if (is_array($property) && is_array($item)) {
                $baseProperties[$name] = $this->arrayMergeRecursive($this->$name, $property);
                continue;
            }

            if (!$property) {
                $baseProperties[$name] = $this->$name;
            }
        }

        return $baseProperties;
    }

    public function arrayMergeRecursive()
    {
        $arrays = func_get_args();

        $base = array_shift($arrays);

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) and array_key_exists($key, $base) and is_array($base[$key])) {
                    $base[$key] = $this->arrayMergeRecursive($base[$key], $value);
                } else {
                    if (is_int($key)) {
                        if (!in_array($value, $base)) {
                            array_push($base, $value);
                        }
                        continue;
                    }
                    $base[$key] = $value;
                }
            }
        }

        return $base;
    }
}
