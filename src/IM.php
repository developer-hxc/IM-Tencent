<?php

namespace HXC;

use EasyWeChat\Kernel\Exceptions\Exception;
use HXC\Gateway\Base;
use HXC\Provider\TX;

/**
 * Class IM
 * @package HXC
 * @method static TX TX($config = []) 腾讯im
 */
class IM
{
    private $config;

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $app = new self(...$arguments);
        return $app->make($name);
    }

    /**
     * IM constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        $this->config = $params;
    }

    /**
     * @param $class
     * @return mixed
     */
    public function make($class)
    {
        $class_name = '\HXC\Provider\\'.$class;

        $app = new $class_name($this->config);
        if($app instanceof Base){
            return $app;
        }

        throw new \Exception('网关错误');
    }
}