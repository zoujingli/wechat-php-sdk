<?php

# 引入文件
require 'include.php';

# 配置参数
$config = array(
    'token'          => '',
    'appid'          => '',
    'appsecret'      => '',
    'encodingaeskey' => '',
);

# 加载对应操作接口
$wechat = & \Wechat\Loader::get('User', $config);
$userlist = $wechat->getUserList();

//var_dump($userlist);
var_dump($wechat->errMsg);
var_dump($wechat->errCode);
