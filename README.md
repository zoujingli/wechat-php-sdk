# WECHAT-PHP-SDK

[![Downloads](https://img.shields.io/github/downloads/zoujingli/wechat-php-sdk/total.svg)](https://github.com/zoujingli/wechat-php-sdk/releases)
[![Releases](https://img.shields.io/github/release/zoujingli/wechat-php-sdk.svg)](https://github.com/zoujingli/wechat-php-sdk/releases/latest)
[![Releases Downloads](https://img.shields.io/github/downloads/zoujingli/wechat-php-sdk/latest/total.svg)](https://github.com/zoujingli/wechat-php-sdk/releases/latest)
[![Packagist Status](https://img.shields.io/packagist/v/zoujingli/wechat-php-sdk.svg)](https://packagist.org/packages/zoujingli/wechat-php-sdk)
[![Packagist Downloads](https://img.shields.io/packagist/dt/zoujingli/wechat-php-sdk.svg)](https://packagist.org/packages/zoujingli/wechat-php-sdk)

---

* 此 SDK 运行最底要求 PHP 版本 5.3.3 , 建议在 PHP7 运行以获取最佳性能。
* 微信的部分接口需要缓存数据在本地，因此对目录需要有写权限。
* SDK 已经过数个线上项目验证，可靠性极高，欢迎阅读 SDK 相关源码。
* 我们鼓励大家使用 composer 来管理您的第三方库，方便后期更新操作（尤其是接口类）。
* 近期发现 access_token 经常无故失效，此 SDK 加入失败状态检测，重新生成 access_token 并试图再次返回结果.
---
[PHP微信SDK文档，欢迎阅读！](http://www.kancloud.cn/zoujingli/wechat-php-sdk)
--
http://www.kancloud.cn/zoujingli/wechat-php-sdk
---
官方接口文档链接
--
>使用前需先打开微信帐号的开发模式，详细步骤请查看微信公众平台接口使用说明：  
>1. 微信公众平台： http://mp.weixin.qq.com/wiki/
>2. 微信企业平台： http://qydev.weixin.qq.com/wiki/
>3. 微信开放平台：https://open.weixin.qq.com/
>4. 微信支付接入文档：https://mp.weixin.qq.com/cgi-bin/readtemplate?t=business/course2_tmpl&lang=zh_CN
>5. 微信商户平台：https://pay.weixin.qq.com

SDK 封装对接及功能
--
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

接口权限备注：
--
* 初级权限：基本权限，任何正常的公众号都有此权限
* 菜单权限：正常的服务号、认证后的订阅号拥有此权限
* 认证权限：分为订阅号、服务号认证，如前缀服务号则仅认证的服务号有此权限，否则为认证后的订阅号、服务号都有此权限
* 支付权限：仅认证后的服务号可以申请此权限
