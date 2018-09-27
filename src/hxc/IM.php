<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/27
 * Time: 16:38
 */

namespace HXC;

use Tencent\TLSSigAPI;

define('BASE_PATH',str_replace( '\\' , '/' , realpath(dirname(__FILE__).'/../../')).'/');

class IM
{
    static private $config = [];


    public function aa()
    {
        echo 11;
    }

    /**
     * 初始化
     * @param $config 配置数据
     * $key = [
     *      'appid' => '',
     *      'path' =>
     *  ];
     * @return $this
     */
    public static function init($config)
    {
        try{
            if(is_array($config)){
                self::$config = $config;
                return new IM();
            }else{
                throw new \Error('$config必须为数组');
            }
        }catch (\Error $e){
            echo $e;
        }
    }


    public function genSig()
    {
        $config = self::$config;
        $private_key = file_get_contents(BASE_PATH.$config['path'].'/private_key');
        $public_key = file_get_contents(BASE_PATH.$config['path'].'/public_key');
        $api = new TLSSigAPI();
        $api->SetAppid($config['appid']);//设置在腾讯云申请的appid
        $api->SetPrivateKey($private_key);//生成usersig需要先设置私钥
        $api->SetPublicKey($public_key);//校验usersig需要先设置公钥
        $sig = $api->genSig($config['identifier']);//生成usersig
        echo $sig;
        $sig = 'eJxNjF1vgjAYRv9Lb13M25YOtjtElhFxH0F086ZhUrBTsGkLKmb-fYRotttznvNc0CJOxtlmc2hqy*1ZCfSIAN0NWOaitrKQQvfQCmPxVWRKyZxnllOd-9ubfMcH1TPsAGCHuR67SnFSUgueFXa4w4wxAnBLW6GNPNS9IIAZJhTgT1pZiSGhHtB7wm6XRpY9noefQfQeuCLpAjMdrWfRKF1tg9ly8uw8eFrRNtyXTXq0RXqmX1289KOtH2MPdn5CKvdYf8-bpxejGmOqxespBDfpVh*xWe8nEXsrffTzC*s2V9M_';
        echo '<br /><br /><br />';
        $result = $api->verifySig($sig, 'test1', $init_time, $expire_time, $error_msg);//校验usersig
        var_dump($result);
        var_dump($init_time);
        var_dump($expire_time);
        var_dump($error_msg);

    }




}