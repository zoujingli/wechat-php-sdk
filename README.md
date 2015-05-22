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

## 微信常用接口主要功能

    接入验证 （初级权限）
    自动回复（文本、图片、语音、视频、音乐、图文） （初级权限）
    菜单操作（查询、创建、删除） （菜单权限）
    客服消息（文本、图片、语音、视频、音乐、图文） （认证权限）
    二维码（创建临时、永久二维码，获取二维码URL） （服务号、认证权限）
    长链接转短链接接口 （服务号、认证权限）
    分组操作（查询、创建、修改、移动用户到分组） （认证权限）
    网页授权（基本授权，用户信息授权） （服务号、认证权限）
    用户信息（查询用户基本信息、获取关注者列表） （认证权限）
    多客服功能（客服管理、获取客服记录、客服会话管理） （认证权限）
    媒体文件（上传、获取） （认证权限）
    高级群发 （认证权限）
    模板消息（设置所属行业、添加模板、发送模板消息） （服务号、认证权限）
    卡券管理（创建、修改、删除、发放、门店管理等） （认证权限）
    语义理解 （服务号、认证权限）
    获取微信服务器IP列表 （初级权限）
    微信JSAPI授权(获取ticket、获取签名) （初级权限）
    数据统计(用户、图文、消息、接口分析数据) （认证权限）
    备注：
        > 初级权限：基本权限，任何正常的公众号都有此权限
        > 菜单权限：正常的服务号、认证后的订阅号拥有此权限
        > 认证权限：分为订阅号、服务号认证，如前缀服务号则仅认证的服务号有此权限，否则为认证后的订阅号、服务号都有此权限
        > 支付权限：仅认证后的服务号可以申请此权限

## 支付接口方法
支付商户证书

## 微信开放平台

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