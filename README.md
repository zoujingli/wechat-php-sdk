#WECHAT-PHP-SDK

### 运行环境说明
此SDK开发调试是在ThinkPHP3.2的基础进行的，因此带上了命名空间

如果不需要命名空间，可以命名空间去除，再分别引入对应的PHP文体

### SDK文件说明
微信公众平台php开发包,细化各项接口操作,支持链式调用,欢迎Fork此项目  
weixin developer SDK.

Wechat.class.php 微信常用接口

WechatPay.class.php 微信支付接口

Service.class.php 微信开放平台接口

Common.class.php 微信接口基础库

### 常用接口方法
微信公众平台php开发包,细化各项接口操作,支持链式调用,欢迎Fork此项目  
wechat-php-sdk.

#### 使用详解
使用前需先打开微信帐号的开发模式，详细步骤请查看微信公众平台接口使用说明：  

微信公众平台： http://mp.weixin.qq.com/wiki/

微信企业平台： http://qydev.weixin.qq.com/wiki/

微信开放平台：https://open.weixin.qq.com/

微信支付接入文档：
https://mp.weixin.qq.com/cgi-bin/readtemplate?t=business/course2_tmpl&lang=zh_CN

微信多客服：http://dkf.qq.com

### 支付接口方法
支付商户证书

### 微信开放平台

    $options = array(
        'ticket'=>'ticket', //填写你设定的ticket
        'appid'=>'appid', //填写高级调用功能的appid
        'appsecret'=>'appsecret', //填写高级调用功能的appsecret
    );
    $weObj = new Service($options);
    $weObj->getAuthorizationInfo(); //获取服务号的授权信息
    $weObj->refreshAccessToken(); //刷新授权方操作Token
    $weObj->getWechatInfo(); //获取公众号的帐号信息
    $weObj->getAuthorizerOption(); //获取公众号的授权项的值
    $weObj->setAuthorizerOption(); //设置公众号的授权项的值