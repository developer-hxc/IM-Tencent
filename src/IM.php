<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/27
 * Time: 16:38
 */

namespace HXC;

use GuzzleHttp\Client;
use HXC\Api\base;
use Tencent\TLSSigAPI;

define('BASE_PATH',str_replace( '\\' , '/' , realpath(dirname(__FILE__).'/../../../../')).'/');

class IM
{
    use base;
    
    private static $sig; //用户签名
    private static $identifier; //用户名称
    private static $targetUsersig = '';//目标sig，例如需要校验的usersig
    private static $postData = [];//post请求要发送的数据
    private static $url = 'https://console.tim.qq.com/';//请求的url


    /**
     * @return TLSSigAPI
     * @throws \Exception
     */
    public static function getApi()
    {
        $config = C('im');
        $api = new TLSSigAPI();
        $api->SetAppid($config['appid']);//设置在腾讯云申请的appid
        $private_key = file_get_contents(BASE_PATH.$config['path'].'/private_key');
        $public_key = file_get_contents(BASE_PATH.$config['path'].'/public_key');
        $api->SetPrivateKey($private_key);//生成usersig需要先设置私钥
        $api->SetPublicKey($public_key);//校验usersig需要先设置公钥
        return $api;
    }

    /**
     * 用户
     * @param $identifier
     * @return IM
     */
    public static function identifier($identifier)
    {
        self::$identifier = $identifier;
        self::genSig($identifier);
        return new self();
    }

    /**
     * 管理员
     * @return IM
     */
    public static function admin()
    {
        if(!$admin_name = C('im.admin_name')){
            throw new \Error('请先在TP配置中定义管理员名称');
        }else{
            self::$identifier = $admin_name;
            self::genSig($admin_name);
        }
        return new self();
    }

    /**
     * 获取用户签名
     * @return mixed
     */
    public function get()
    {
        return self::$sig;
    }


    /**
     * 生成用户签名
     * @param $identifier
     * @return string
     * @throws \Exception
     */
    public static function genSig($identifier)
    {
        $config = C('im');
        $api = self::getApi();
        $cache = S([
            'prefix'=>'usersig',
            'expire'=>15552000,
        ]);
        $usersig = $cache->$identifier;
        if(!$usersig){
            $sig = $api->genSig($identifier);//生成usersig
            $cache->$identifier = $sig;
            return self::$sig = $sig;
        }
        return self::$sig = $usersig;
    }

    /**
     * 要验证的usersig
     * @param $usersig
     * @return IM
     */
    public static function usersig($usersig)
    {
        self::$targetUsersig = $targetUsersig;
        return new self();
    }

    /**
     * 校验usersig
     */
    public function verifySig()
    {
        $cache = S([
            'prefix'=>'usersig',
            'expire'=>15552000,
        ]);
        if(self::$targetUsersig){
            $sig = $targetUsersig;
        }else{
            $sig = self::$sig;
        }
        $api = self::getApi();
        $result = $api->verifySig($sig, self::$identifier, $init_time, $expire_time, $error_msg);//校验usersig
        if($result){
            $data['status'] = 1;
            $data['init_time'] = $init_time;
            $data['expire_time'] = $expire_time;
        }else{
            $data['status'] = 0;
            $data['msg'] = $error_msg;
        }
        return $data;
    }


    /**
     * 获取管理员usersig
     * @return string
     * @throws \Exception
     */
    public static function getAdminData()
    {
        $cache = S([
            'prefix'=>'usersig',
            'expire'=>15552000,
        ]);
        $config = C('im');
        $data['usersig'] = self::genSig($config['admin_name']);
        $data['identifier'] = $config['admin_name'];
        $data['appid'] = $config['appid'];
        return $data;
    }

    /**
     * 随机数
     * @param int $length
     * @return int
     */
    public static function getRandom($length = 8)
    {
        $min = pow(10 , ($length - 1));
        $max = pow(10, $length) - 1;
        return mt_rand($min, $max);
    }


    /**
     * 批量导入
     * @param $accounts
     * @return IM
     * @throws \Exception
     */
    public static function multiAccountImport($accounts)
    {
        if(!is_array($accounts)) throw new \Error('$accounts必须为数组');
        $admin_data = self::getAdminData();
        $random = self::getRandom();
        self::$url .= "v4/im_open_login_svc/multiaccount_import?usersig={$admin_data['usersig']}&identifier={$admin_data['identifier']}&sdkappid={$admin_data['appid']}&random={$random}&contenttype=json";
        self::$postData = json_encode(["Accounts" => $accounts]);
        return new self();
    }

    /**
     * 导入账号
     * @param $account_arr
     * @return IM
     * @throws \Exception
     */
    public static function accountImport($account_arr)
    {
        $admin_data = self::getAdminData();
        $random = self::getRandom();
        self::$url .= "v4/im_open_login_svc/account_import?usersig={$admin_data['usersig']}&identifier={$admin_data['identifier']}&sdkappid={$admin_data['appid']}&random={$random}&contenttype=json";
        self::$postData = json_encode($account_arr);
        return new self();
    }

    /**
     * 发起post请求
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post()
    {
        $client = new Client();
        $response = $client->request('POST', self::$url, [
            'body' => self::$postData
        ]);
        return $response->getBody()->getContents();
    }


    //TODO 登录态失效

    //TODO 添加好友



}