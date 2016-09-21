# WECHAT-PHP-SDK

[![Downloads](https://img.shields.io/github/downloads/zoujingli/wechat-php-sdk/total.svg)](https://github.com/zoujingli/wechat-php-sdk/releases)
[![Releases](https://img.shields.io/github/release/zoujingli/wechat-php-sdk.svg)](https://github.com/zoujingli/wechat-php-sdk/releases/latest)
[![Releases Downloads](https://img.shields.io/github/downloads/zoujingli/wechat-php-sdk/latest/total.svg)](https://github.com/zoujingli/wechat-php-sdk/releases/latest)
[![Packagist Status](https://img.shields.io/packagist/v/zoujingli/wechat-php-sdk.svg)](https://packagist.org/packages/zoujingli/wechat-php-sdk)
[![Packagist Downloads](https://img.shields.io/packagist/dt/zoujingli/wechat-php-sdk.svg)](https://packagist.org/packages/zoujingli/wechat-php-sdk)

### 运行环境说明

* 此SDK运行最底要求PHP版本5.3.3, 建议在PHP7运行以获取最佳性能。
* 微信的部分接口需要缓存数据在本地，因此对目录需要有写权限。
* SDK已经过数个线上项目验证，可靠性极高，欢迎阅读SDK相关源码。
* 我们鼓励大家使用composer来管理您的第三方库，方便后期更新操作（尤其是接口类）。
* 近期发现access_token经常无故失效，此SDK加入失败状态检测，重新生成access_token并试图再次返回结果.

###初始化动作 

##### A. 使用 Composer 安装，符合PSR-4标准。
>1. 不需要引入include.php文件，所有文件都可以自动加载。
>2. 可使用Wechat\Loader::setConfig()来配置全局参数。

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
    'cachepath'         =>  '',                     //设置SDK缓存目录（可选，默认位置在./src/Cache下，请保证写权限）
);
```

### 实例SDK对象

* 微信支付操作

```php
$pay = & \Wechat\Loader::get_instance('Pay',$options);
//TODO：调用支付实例方法
```

* 微信菜单操作

```php
$menu = & \Wechat\Loader::get_instance('Menu',$options);
//TODO：调用微信菜实例方法
```

#### 可以在项目中放置这样的函数，方便加载SDK对象
> 这个代码是从CI框架中拿出来的，可以根据实际情况修改下哦！

```php

/**
 * 获取微信操作对象
 * @staticvar array $wechat
 * @param type $type
 * @return WechatReceive
 */
function &load_wechat($type = '') {
    static $wechat = array();
    $index = md5(strtolower($type));
    if (!isset($wechat[$index])) {
        $CI = & get_instance();
        $CI->db->reset_query();
        $CI->db->select('token,appid,appsecret,encodingaeskey,mch_id,partnerkey,ssl_cer,ssl_key,qrc_img');
        // 读取SDK动态配置
        $config = $CI->db->get('wechat_config')->first_row('array');
        // 设置SDK缓存路径
        $config['cachepath'] = CACHEPATH . 'data/';
        $wechat[$index] = & \Wechat\Loader::get_instance($type, $config);
    }
    return $wechat[$index];
}
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

### 接口调用

* 获取粉丝列表
```
//加载SDK对象,load_wechat方法参与上面进行定义
$user = & load_wechat('User');
//读取调用接口，读取微信官方粉丝列表
$result = $user->getUserList();
//接口异常的处理
if ($result === FALSE) {
    echo $user->errMsg;
    echo $user->errCode;
} else {
//接口正常的处理
}
```

*读取单个粉丝的信息
```
//加载SDK对象,load_wechat方法参与上面进行定义
$user = & load_wechat('User');
//读取调用接口，读取微信粉丝信息，需要传入粉丝的openid
$result = $user->getUserInfo($openid);
//接口异常的处理
if ($result === FALSE) {
    echo $user->errMsg;
    echo $user->errCode;
} else {
//接口正常的处理
}
```
* 以引类推，调用方法一样。

==============================================================
#### 微信的两种支付封装，基于CI框架
```
/**
 * 通用支付入口
 * 
 * @author Anyon<zoujingli@qq.com>
 * @date 2016-09-21 13:27
 */
class PayData {

    /**
     * 微信支付操作SDK
     * @var type 
     */
    protected $pay;

    /**
     * CI超级对象
     * @var type 
     */
    protected $CI;

    /**
     * 构造函数
     */
    public function __construct() {
        $this->pay = & load_wechat('Pay');
        $this->CI = & get_instance();
    }

    /**
     *  创建微信二维码支付
     * @param type $order_no	系统订单号
     * @param type $fee			支付金额
     * @param type $title		订单标题
     * @return boolean
     */
    public function createQrc($order_no, $fee, $title) {
        if (($prepayid = $this->_createPrepayid(null, $order_no, $fee, $title, 'NATIVE')) === FALSE) {
            return FALSE;
        }
        $fileename = FCPATH . 'static/upload/payqrc/' . join('/', str_split(md5($prepayid), 16)) . '.png';
        $dirname = dirname($fileename);
        !is_dir($dirname) && mkdir($dirname, 0777, true);
        if (!file_exists($fileename)) {
            $qrCode = new QRcode();
            $qrCode->png($prepayid, $fileename, QR_ECLEVEL_L, 8);
        }
        ob_clean();
        flush();
        header("Content-type: image/png");
        exit(readfile($fileename));
    }

    /**
     * 创建微信JSAPI支付签名包
     * @param type $openid		微信用户openid  
     * @param type $order_no	系统订单号
     * @param type $fee			支付金额
     * @param type $title		订单标题
     * @return boolean
     */
    public function createJs($openid, $order_no, $fee, $title) {
        if (($prepayid = $this->_createPrepayid($openid, $order_no, $fee, $title, 'JSAPI')) === FALSE) {
            return FALSE;
        }
        return $this->pay->createMchPay($prepayid);
    }

    /**
     * 微信退款操作
     * @param type $order_no	系统订单号
     * @param type $fee			退款金额
     * @return boolean
     */
    public function refund($order_no, $fee = 0, $refund_no = NULL) {
        $map = array('order_no' => $order_no, 'is_pay' => '1');
        $notify = $this->CI->db->where($map)->get('wechat_pay_prepayid')->first_row('array');
        if (empty($notify)) {
            log_message('error', "内部订单号{$order_no}验证退款失败");
            return FALSE;
        }
        if (FALSE !== $this->pay->refund($notify['out_trade_no'], $notify['transaction_id'], is_null($refund_no) ? "T{$order_no}" : $refund_no, $notify['fee'], empty($fee) ? $notify['fee'] : $fee)) {
            $this->CI->load->library('FormData');
            $data = array('out_trade_no' => $notify['out_trade_no'], 'is_refund' => "1", 'refund_at' => date('Y-m-d H:i:s'), 'expires_in' => time() + 7000);
            if (FALSE !== $this->CI->formdata->save('wechat_pay_prepayid', $data, 'out_trade_no')) {
                return TRUE;
            }
            log_message('error', "内部订单号{$order_no}退款成功，系统更新异常");
            return FALSE;
        }
        log_message('error', "内部订单号{$order_no}退款失败，{$this->pay->errMsg}");
        return FALSE;
    }

    /**
     * 创建微信预支付码
     * @param type $openid		支付者Openid
     * @param type $order_no	实际订单号
     * @param type $fee			实际订单支付费用
     * @param type $title		订单标题
     * @param type $trade_type	付款方式
     * @return boolean
     */
    protected function _createPrepayid($openid, $order_no, $fee, $title, $trade_type = 'JSAPI') {
        /* 预支付ID缓存5000秒 */
        $prepayinfo = $this->CI->db->where('order_no', $order_no)->where('(is_pay', '1')->or_where('expires_in>' . time() . ')')->get('wechat_pay_prepayid')->first_row('array');
        if (empty($prepayinfo) || empty($prepayinfo['prepayid'])) {
            $this->CI->load->library('BaseData');
            $out_trade_no = BaseData::createSequence(18, 'WXPAY-OUTER-NO');
            $prepayid = $this->pay->getPrepayId($openid, $title, $out_trade_no, $fee, site_url('api/notify'), $trade_type);
            if (empty($prepayid)) {
                log_message('error', "内部订单号{$order_no}生成预支付失败，{$this->pay->errMsg}");
                return FALSE;
            }
            $data = array('prepayid' => $prepayid, 'order_no' => $order_no, 'out_trade_no' => $out_trade_no, 'fee' => $fee, 'trade_type' => $trade_type, 'expires_in' => time() + 5000);
            if ($this->CI->db->insert('wechat_pay_prepayid', $data) > 0) {
                log_message('info', "内部订单号{$order_no}生成预支付成功,{$prepayid}");
                return $prepayid;
            }
        }
        return $prepayinfo['prepayid'];
    }

}
```