<?php
namespace HXC\Api;

use GuzzleHttp\Client;

trait base
{
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
     * 获取url参数
     * @return string
     * @throws \Exception
     */
    public static function getUrlParmas()
    {
        $admin_data = self::getAdminData();
        $random = self::getRandom();
        return "?usersig={$admin_data['usersig']}&identifier={$admin_data['identifier']}&sdkappid={$admin_data['appid']}&random={$random}&contenttype=json";
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
        self::$url .= "v4/im_open_login_svc/multiaccount_import";
        self::$postData = ["Accounts" => $accounts];
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
        self::$url .= "v4/im_open_login_svc/account_import";
        self::$postData = $account_arr;
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
        $url = self::$url.(self::getUrlParmas());
        $response = $client->request('POST',$url , [
            'body' => json_encode(self::$postData)
        ]);
        return $response->getBody()->getContents();
    }

}