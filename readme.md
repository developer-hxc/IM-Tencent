# 腾讯云通信+ThinkPHP5.0

## 安装方法
`composer require hxc/im`

## 使用方法

1. 在`application/config.php`中增加配置信息：

   ```php
   'im' => [
     'appid'  => '云通信appid',
     'admin'  => '帐号管理员用户名',
     'secret'   => '云通信密钥',
     'domain' => 'https://console.tim.qq.com',//请求的域名，无特殊情况不用修改
     'fun_arr' => [
       //格式举例：
       //'account_import'           => '/v4/im_open_login_svc/account_import',//单个帐号导入
     ],//请求接口数组，如果遇到报错为：暂未封装此方法，可根据文档自己封装。（文档：https://cloud.tencent.com/document/product/269/1519），可在此处添加
   ],
   ```

2. 调用

   ```php
   //获取配置
   $config = Config::get('im');
   //账号导入
   $res = json_decode(IM::TX($config)->fun('account_import', [
     "Identifier" => '用户名，长度不超过32字节',
     "Nick" => '用户昵称',
     "FaceUrl" => '用户头像 URL'
   ])->post(), true);
   if ($res['ActionStatus'] !== 'OK' || $res['ErrorCode'] !== 0) {
     $this->returnFail('登录失败:通讯连接失败');
   }
   
   //生成签名
   IM::TX($config)->user($data['account'])->genSig();
   ```

