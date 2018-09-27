<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/27
 * Time: 16:38
 */

namespace HXC;

use Tencent\TLSSigAPI;

define('BASE_PATH',str_replace( '\\' , '/' , realpath(dirname(__FILE__).'/../../../../../')).'/');

class IM
{
    static private $identifier;


    /**
     * 用户
     * @param $identifier
     * @return IM
     */
    public static function identifier($identifier)
    {
        try{
            if($identifier){
                self::$identifier = $identifier;
                return new IM();
            }else{
                throw new \Error('$identifier不能为空');
            }
        }catch (\Error $e){
            echo $e;
        }
    }



    public function genSig()
    {
        $cache = S([
            'prefix'=>'usersig',
            'expire'=>15550000
        ]);
        $identifier = self::$identifier;
        $usersig = $cache->$identifier;
        if(!$usersig){
            $config = C('im');
            $private_key = file_get_contents(BASE_PATH.$config['path'].'/private_key');
            $public_key = file_get_contents(BASE_PATH.$config['path'].'/public_key');
            $api = new TLSSigAPI();
            $api->SetAppid($config['appid']);//设置在腾讯云申请的appid
            $api->SetPrivateKey($private_key);//生成usersig需要先设置私钥
            $api->SetPublicKey($public_key);//校验usersig需要先设置公钥
            $sig = $api->genSig($identifier);//生成usersig
            $cache->$identifier = $sig;
            return $sig;
        }
        return $usersig;
    }

    /**
     * 校验usersig
     */
    public function verifySig($sig)
    {
        $api = new TLSSigAPI();
        $result = $api->verifySig($sig, self::$identifier, $init_time, $expire_time, $error_msg);//校验usersig
        var_dump($result);
        var_dump($init_time);
        var_dump($expire_time);
        var_dump($error_msg);
    }




}