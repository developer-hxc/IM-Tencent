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
     * 拉取用户资料
     * @param $data
     * @return base
     */
    public static function portraitGet($data)
    {
        self::$url .= "v4/profile/portrait_get";
        self::$postData = $data;
        return new self();
    }

    /**
     * 设置用户资料
     * 接受字段请访问查看 https://cloud.tencent.com/document/product/269/1500#.E8.87.AA.E5.AE.9A.E4.B9.89.E8.B5.84.E6.96.99.E5.AD.97.E6.AE.B5
     * @param $data
     * @return base
     */
    public static function portraitSet($data)
    {
        self::$url .= "v4/profile/portrait_set";
        self::$postData = $data;
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