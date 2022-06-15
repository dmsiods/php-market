<?php

namespace core\base\settings;

use core\base\settings\Settings;

class ShopSettings
{
    static private $_instance;
    private $baseSettings;

    private $routes = [
        'plugins' => [
            'path' => 'core/plugins/',
            'hrUrl' => false,
            'dir' => '',
            'routes' => []
        ]
    ];

    private $templateArr = [
        'text' => ['price', 'short'],
        'textarea' => ['goods_content']
    ];

    static public function get($property)
    {
        return self::instance()->$property;
    }

    static public function instance()
    {
        if (self::$_instance instanceof self) {
            return self::$_instance;
        }

        self::$_instance = new self();
        self::$_instance->baseSettings = Settings::instance();
        $baseSettings = self::$_instance->baseSettings->clueProperties(get_class());
        self::$_instance->setProperty($baseSettings);

        return self::$_instance;
    }

    protected function setProperty($properties)
    {
        if ($properties) {
            foreach ($properties as $name => $property) {
                $this->$name = $property;
            }
        }
    }

    public function __construct()
    {
    }

    public function __clone()
    {
    }
}
