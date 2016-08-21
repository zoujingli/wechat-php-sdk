# WECHAT-PHP-SDK
---
### 运行环境说明

* 此SDK运行最底要求PHP版本5.3.3, 建议在PHP7运行以获取最佳性能。
* 微信的部分接口需要缓存数据在本地，因此对目录需要有写权限。

###初始化动作 

##### A. 使用 Composer 安装，符合PSR-4标准。
>1. 不需要引入include.php文件，所有文件都可以自动加载。
>2. 可使用Wechat\Loader::set_config()来配置全局参数。

```shell
composer require zoujingli/wechat-php-sdk
```
##### B. 普通文件加载（需要引用 include.php）
>1. 引入SDK，可在include中配置全局微信参数。

```php
    include include.php
```
### 准备配置参数 

```php
$options = array(
    'token'             =>  'tokenaccesskey',       //填写你设定的key
    'appid'             =>  'wxdk1234567890',       //填写高级调用功能的app id, 请在微信开发模式后台查询
    'appsecret'         =>  'xxxxxxxxxxxxxxxxxxx'   //填写高级调用功能的密钥
    'encodingaeskey'    =>  'encodingaeskey',       //填写加密用的EncodingAESKey（可选，接口传输选择加密时必需）
    'mch_id'            =>  '',                     //微信支付，商户ID（可选）
    'partnerkey'        =>  '',                     //微信支付，密钥（可选）
    'ssl_cer'           =>  '',                     //微信支付，双向证书（可选，操作退款或打款时必需）
    'ssl_key'           =>  '',                     //微信支付，双向证书（可选，操作退款或打款时必需）
);
```

### 实例SDK对象

* 微信支付操作

```php
$pay = & load_wechat('Pay',$options);
//TODO：调用支付实例方法
```

* 微信菜单操作

```php
$menu = & load_wechat('Menu',$options);
//TODO：调用微信菜实例方法
```

### SDK文件说明
微信公众平台php开发包，细化各项接口操作，支持链式调用，欢迎Fork此项目！

* WechatCustom.php 微信多客服接口
* WechatDevice.php 微信周边设备接口
* WechatExtends.php 微信其它工具接口
* WechatMedia.php 微信媒体素材接口
* WechatMenu.php 微信菜单操作接口
* WechatOauth.php 微信网页授权接口
* WechatPay.php 微信支付相关接口
* WechatReceive.php 微信被动消息处理SDK
* WechatScript.php 微信网页脚本工具
* WechatUser.php 微信粉丝操作接口

### 使用详解
> 使用前需先打开微信帐号的开发模式，详细步骤请查看微信公众平台接口使用说明：  
* 微信公众平台： http://mp.weixin.qq.com/wiki/
* 微信企业平台： http://qydev.weixin.qq.com/wiki/
* 微信开放平台：https://open.weixin.qq.com/
* 微信支付接入文档：https://mp.weixin.qq.com/cgi-bin/readtemplate?t=business/course2_tmpl&lang=zh_CN
* 微信多客服：http://dkf.qq.com

### 微信常用接口主要功能

* 接入验证 （初级权限）
* 自动回复（文本、图片、语音、视频、音乐、图文） （初级权限）
* 菜单操作（查询、创建、删除） （菜单权限）
* 客服消息（文本、图片、语音、视频、音乐、图文） （认证权限）
* 二维码（创建临时、永久二维码，获取二维码URL） （服务号、认证权限）
* 长链接转短链接接口 （服务号、认证权限）
* 标签操作（查询、创建、修改、移动用户到标签） （认证权限）
* 网页授权（基本授权，用户信息授权） （服务号、认证权限）
* 用户信息（查询用户基本信息、获取关注者列表） （认证权限）
* 多客服功能（客服管理、获取客服记录、客服会话管理） （认证权限）
* 媒体文件（上传、获取） （认证权限）
* 高级群发 （认证权限）
* 模板消息（设置所属行业、添加模板、发送模板消息） （服务号、认证权限）
* 卡券管理（创建、修改、删除、发放、门店管理等） （认证权限）
* 语义理解 （服务号、认证权限）
* 获取微信服务器IP列表 （初级权限）
* 微信JSAPI授权(获取ticket、获取签名) （初级权限）
* 数据统计(用户、图文、消息、接口分析数据) （认证权限）
* 微信支付（网页支付、扫码支付、交易退款、给粉丝打款）（认证服务号并开通支付）

#### 备注：
* 初级权限：基本权限，任何正常的公众号都有此权限
* 菜单权限：正常的服务号、认证后的订阅号拥有此权限
* 认证权限：分为订阅号、服务号认证，如前缀服务号则仅认证的服务号有此权限，否则为认证后的订阅号、服务号都有此权限
* 支付权限：仅认证后的服务号可以申请此权限