<?php
namespace HXC\Provider;

use GuzzleHttp\Client;
use HXC\Gateway\Base;
use Tencent\TLSSigAPI;
use think\Cache;
use think\Exception;


class TX implements Base
{
    protected $userName;//操作用户名称
    protected $config;//配置
    protected $request = [];//请求的参数
    //腾讯云方法
    protected $funArr = [
        'portrait_set'             => '/v4/profile/portrait_set',//设置资料
        'portrait_get'             => '/v4/profile/portrait_get',//拉取资料
        'account_import'           => '/v4/im_open_login_svc/account_import',//单个帐号导入
        'multiaccount_import'      => '/v4/im_open_login_svc/multiaccount_import',//批量帐号导入
        'create_group'             => '/v4/group_open_http_svc/create_group',//创建群组
        'get_appid_group_list'     => '/v4/group_open_http_svc/get_appid_group_list',//获取App中的所有群组
        'destroy_group'            => '/v4/group_open_http_svc/destroy_group',//解散群
        'delete_group_member'      => '/v4/group_open_http_svc/delete_group_member',//删除群组成员
        'add_group_member'         => '/v4/group_open_http_svc/add_group_member',//增加群组成员
        'get_joined_group_list'    => '/v4/group_open_http_svc/get_joined_group_list',//获取用户所加入的群组
        'modify_group_base_info'   => '/v4/group_open_http_svc/modify_group_base_info',//修改群组基础资料
        'get_group_member_info'    => '/v4/group_open_http_svc/get_group_member_info',//获取群组成员详细资料
        'get_group_info'           => '/v4/group_open_http_svc/get_group_info',//获取群组详细资料
        'friend_import'            => '/v4/sns/friend_import',//导入好友
        'account_delete'           => '/v4/im_open_login_svc/account_delete',//账号删除
        'modify_group_member_info' =>'v4/group_open_http_svc/modify_group_member_info',//修改群成员资料
        'change_group_owner'       => 'v4/group_open_http_svc/change_group_owner', //转让群组
        'friend_check'             => '/v4/sns/friend_check',//校验好友
    ];

    /**
     * TX constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * 随机数
     * @param int $length
     * @return int
     */
    private function getRandom($length = 8)
    {
        $min = pow(10 , ($length - 1));
        $max = pow(10, $length) - 1;
        return mt_rand($min, $max);
    }

    /**
     * 操作用户
     * @param $str
     * @return $this
     */
    public function user($str)
    {
        $this->userName = $str;
        return $this;
    }

    /**
     * 生成用户签名
     * @param $identifier
     * @return string
     * @throws \Exception
     */
    public function genSig()
    {
        $sig = Cache::tag('TX-IM')->get($this->userName);
        if(!$sig){//缓存中没有签名
            $config = $this->config;
            $api = new TLSSigAPIv2($this->config['appid'],$this->config['secret']);;
            $sig = $api->genSig($this->userName);//生成usersig
            Cache::tag('TX-IM')->set($this->userName,$sig,15551000);
        }
        return $sig;
    }


    /**
     * @param $fun
     * @param string $data
     * @throws \Exception
     */
    public function fun($fun,$data = '')
    {
        if(!isset($this->funArr[$fun])) throw new \Exception('暂未封装此方法，可根据文档自己封装。（文档：https://cloud.tencent.com/document/product/269/1519）');
        $sig = $this->user($this->config['admin'])->genSig();
        $random = $this->getRandom();
        $this->request = [
            'url' => $this->config['domain'].$this->funArr[$fun]."?usersig={$sig}&identifier={$this->config['admin']}&sdkappid={$this->config['appid']}&random={$random}&contenttype=json",
            'data' => json_encode($data)?:'{}'
        ];
        return $this;
    }

    /**
     * 发起post同步请求
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post()
    {
        $client = new Client();
        $response = $client->request('POST',$this->request['url'] , [
            'body' => $this->request['data']
        ]);
        return $response->getBody()->getContents();
    }

}