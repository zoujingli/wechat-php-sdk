<?php

// +----------------------------------------------------------------------
// | wechat-php-sdk
// +----------------------------------------------------------------------
// | 版权所有 2014~2025 ThinkAdmin [ thinkadmin.top ]
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// | 免责声明 ( https://thinkadmin.top/disclaimer )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/wechat-php-sdk
// | github 代码仓库：https://github.com/zoujingli/wechat-php-sdk
// +----------------------------------------------------------------------

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
$wechat = \Wechat\Loader::get('User', $config);
$userlist = $wechat->getUserList();

var_dump($userlist);
var_dump($wechat->errMsg);
var_dump($wechat->errCode);

exit;

// 第三方平台 JSSDK 签名包

$wechat = Db::name('WechatConfig')->where('authorizer_appid', 'wx60a43dd8161666d4')->find();
// 第三方授权获取到的 Access_token
$access_token = $wechat['authorizer_access_token'];
// 参与授权的公众号 APPID
$authorizer_appid = $wechat['authorizer_appid'];
// 当前微信页面URL地址（完整）
$current_url = url('', '', true, true);
// 实例SDK脚本
$script = load_wechat('Script', $authorizer_appid);
// 获取JS签名包
$result = $script->getJsSign($current_url, 0, '', $authorizer_appid, $access_token);

$json = json_encode($result, JSON_PRETTY_PRINT);
echo '<script src="https://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>';
echo "
<script>
    // JSSDK 错误处理
    wx.error(function(){
        //alert(JSON.stringify(arguments));
    });
    // JSSDK 配置参数
    wx.config({$json});
    // JSSDK 初始化成功
    wx.ready(function(){
        alert('初始化成功！');
        wx.hideOptionMenu();
    });
</script>
";