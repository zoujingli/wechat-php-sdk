<?php

namespace Wechat;

use Wechat\Lib\WechatBasic;
 
/**
 * 微信支付SDK
 * @author zoujingli <zoujingli@qq.com>
 * @date 2015/05/13 12:12:00
 */
class WechatPay extends WechatBasic {

    /** 支付接口基础地址 */
    const MCH_BASE_URL = 'https://api.mch.weixin.qq.com';

    /** 公众号appid */
    protected $appid;

    /** 商户身份ID */
    protected $mch_id;

    /** 商户支付密钥Key */
    protected $partnerKey;

    /** 证书路径 */
    protected $ssl_cer;
    protected $ssl_key;

    /** 执行错误消息及代码 */
    public $errMsg;
    public $errCode;

    /**
     * 支付SDK构造函数
     * @param type $options
     */
    public function __construct($options) {
        $this->appid = isset($options['appid']) ? $options['appid'] : '';
        $this->mch_id = isset($options['mch_id']) ? $options['mch_id'] : '';
        $this->partnerKey = isset($options['partnerkey']) ? $options['partnerkey'] : '';
        $this->ssl_cer = isset($options['ssl_cer']) ? $options['ssl_cer'] : '';
        $this->ssl_key = isset($options['ssl_key']) ? $options['ssl_key'] : '';
    }

    /**
     * 设置标配的请求参数，生成签名，生成接口参数xml
     * @param type $data
     * @return type
     */
    protected function createXml($data) {
        if (!isset($data['wxappid']) && !isset($data['mch_appid']) && !isset($data['appid'])) {
            $data['appid'] = $this->appid;
        }
        if (!isset($data['mchid']) && !isset($data['mch_id'])) {
            $data['mch_id'] = $this->mch_id;
        }
        isset($data['nonce_str']) || $data['nonce_str'] = self::createNoncestr();
        $data["sign"] = self::getPaySign($data, $this->partnerKey);
        return self::arr2xml($data);
    }

    /**
     * POST提交XML
     * @param type $data
     * @param type $url
     * @return type
     */
    public function postXml($data, $url) {
        return self::http_post($url, $this->createXml($data));
    }

    /**
     * 使用证书post请求XML
     * @param type $data
     * @param type $url
     * @return type
     */
    function postXmlSSL($data, $url) {
        return self::http_ssl_post($url, $this->createXml($data), $this->ssl_cer, $this->ssl_key);
    }

    /**
     * POST提交获取Array结果
     * @param type $data 需要提交的数据
     * @param type $url
     * @param type $method
     * @return type
     */
    public function getArrayResult($data, $url, $method = 'postXml') {
        return self::xml2arr($this->$method($data, $url));
    }

    /**
     * 解析返回的结果
     * @param type $result
     * @return boolean
     */
    protected function _parseResult($result) {
        if (empty($result)) {
            $this->errCode = 'result error';
            $this->errMsg = '解析返回结果失败';
            return false;
        }
        if ($result['return_code'] !== 'SUCCESS') {
            $this->errCode = $result['return_code'];
            $this->errMsg = $result['return_msg'];
            return false;
        }
        if (isset($result['err_code'])) {
            $this->errMsg = $result['err_code_des'];
            $this->errCode = $result['err_code'];
            return false;
        }
        return $result;
    }

    /**
     * 获取预支付ID
     * @param type $openid          用户openid，JSAPI必填
     * @param type $body            商品标题
     * @param type $out_trade_no    第三方订单号
     * @param type $total_fee       订单总价
     * @param type $notify_url      支付成功回调地址
     * @param type $trade_type      支付类型JSAPI|NATIVE|APP
     * @return boolean
     */
    public function getPrepayId($openid, $body, $out_trade_no, $total_fee, $notify_url, $trade_type = "JSAPI") {
        $postdata = array(
            "body"             => $body,
            "out_trade_no"     => $out_trade_no,
            "total_fee"        => $total_fee,
            "notify_url"       => $notify_url,
            "trade_type"       => $trade_type,
            "spbill_create_ip" => $this->ipAddress(),
        );
        empty($openid) || $postdata['openid'] = $openid;
        $result = $this->getArrayResult($postdata, self::MCH_BASE_URL . '/pay/unifiedorder');
        if (false === $this->_parseResult($result)) {
            return false;
        }
        return $trade_type == 'JSAPI' ? $result['prepay_id'] : $result['code_url'];
    }

    /**
     * 创建JSAPI支付参数包
     * @param type $prepay_id
     * @return type
     */
    public function createMchPay($prepay_id) {
        $option = array();
        $option["appId"] = $this->appid;
        $option["timeStamp"] = (string) time();
        $option["nonceStr"] = self::createNoncestr();
        $option["package"] = "prepay_id={$prepay_id}";
        $option["signType"] = "MD5";
        $option["paySign"] = self::getPaySign($option, $this->partnerKey);
        $option['timestamp'] = $option['timeStamp'];
        unset($option["timeStamp"]);
        return $option;
    }

    /**
     * 关闭订单
     * @param type $out_trade_no
     * @return boolean
     */
    public function closeOrder($out_trade_no) {
        $data = array('out_trade_no' => $out_trade_no);
        $result = $this->getArrayResult($data, self::MCH_BASE_URL . '/pay/closeorder');
        if (false === $this->_parseResult($result)) {
            return false;
        }
        return ($result['return_code'] === 'SUCCESS');
    }

    /**
     * 查询订单详情
     * @param type $out_trade_no
     */
    public function queryOrder($out_trade_no) {
        $data = array('out_trade_no' => $out_trade_no);
        $result = $this->getArrayResult($data, self::MCH_BASE_URL . '/pay/orderquery');
        if (false === $this->_parseResult($result)) {
            return false;
        }
        return $result;
    }

    /**
     * 订单退款接口
     * @param type $out_trade_no 商户订单号
     * @param type $transaction_id 微信订单号
     * @param type $out_refund_no 商户退款订单号
     * @param type $total_fee 商户订单总金额
     * @param type $refund_fee 退款金额
     * @param type $op_user_id 操作员ID，默认商户ID
     * @return boolean
     */
    public function refund($out_trade_no, $transaction_id, $out_refund_no, $total_fee, $refund_fee, $op_user_id = null) {
        $data = array();
        $data['out_trade_no'] = $out_trade_no;
        $data['transaction_id'] = $transaction_id;
        $data['out_refund_no'] = $out_refund_no;
        $data['total_fee'] = $total_fee;
        $data['refund_fee'] = $refund_fee;
        $data['op_user_id'] = empty($op_user_id) ? $this->mch_id : $op_user_id;
        $result = $this->getArrayResult($data, self::MCH_BASE_URL . '/secapi/pay/refund', 'postXmlSSL');
        if (false === $this->_parseResult($result)) {
            return false;
        }
        return ($result['return_code'] === 'SUCCESS');
    }

    /**
     * 退款查询接口
     * @param type $out_trade_no
     * @return boolean
     */
    public function refundQuery($out_trade_no) {
        $data = array();
        $data['out_trade_no'] = $out_trade_no;
        $result = $this->getArrayResult($data, self::MCH_BASE_URL . '/pay/refundquery');
        if (false === $this->_parseResult($result)) {
            return false;
        }
        return $result;
    }

    /**
     * 获取对账单
     * @param type $bill_date 账单日期，如 20141110
     * @param type $bill_type ALL|SUCCESS|REFUND|REVOKED
     * @return boolean
     */
    public function getBill($bill_date, $bill_type = 'ALL') {
        $data = array();
        $data['bill_date'] = $bill_date;
        $data['bill_type'] = $bill_type;
        $result = $this->postXml($data, self::MCH_BASE_URL . '/pay/downloadbill');
        $json = self::xml2arr($result);
        if (!empty($json) && false === $this->_parseResult($json)) {
            return false;
        }
        return $json;
    }

    /**
     * 发送现金红包
     * @param type $openid      红包接收者OPENID
     * @param type $amount      红包总金额
     * @param type $billno      商户订单号
     * @param type $sendname    商户名称
     * @param type $wishing     红包祝福语
     * @param type $actname     活动名称
     * @param type $remark      备注信息
     * @return boolean
     * 
     * @link  https://pay.weixin.qq.com/wiki/doc/api/tools/cash_coupon.php?chapter=13_5
     */
    public function sendRedPack($openid, $amount, $billno, $sendname, $wishing, $actname, $remark) {
        $data = array();
        $data['mch_billno'] = $billno; // 商户订单号 mch_id+yyyymmdd+10位一天内不能重复的数字
        $data['wxappid'] = $this->appid;
        $data['send_name'] = $sendname; //商户名称
        $data['re_openid'] = $openid; //红包接收者
        $data['total_amount'] = $amount; //红包金额
        $data['total_num'] = '1'; //发放人数据
        $data['wishing'] = $wishing; //红包祝福语
        $data['client_ip'] = $_SERVER['REMOTE_ADDR']; //调用接口的机器Ip地址
        $data['act_name'] = $actname; //活动名称
        $data['remark'] = $remark; //备注信息
        $result = $this->postXmlSSL($data, self::MCH_BASE_URL . '/mmpaymkttransfers/sendredpack');
        $json = self::xml2arr($result);
        if (!empty($json) && false === $this->_parseResult($json)) {
            return false;
        }
        return $json;
    }

    /**
     * 现金红包状态查询
     * @param type $billno
     * @return boolean
     * 
     * @link https://pay.weixin.qq.com/wiki/doc/api/tools/cash_coupon.php?chapter=13_7&index=6
     */
    public function queryRedPack($billno) {
        $data['mch_billno'] = $billno;
        $data['bill_type'] = 'MCHT';
        $result = $this->postXmlSSL($data, self::MCH_BASE_URL . '/mmpaymkttransfers/gethbinfo');
        $json = self::xml2arr($result);
        if (!empty($json) && false === $this->_parseResult($json)) {
            return false;
        }
        return $json;
    }

    /**
     * 企业付款
     * @param type $openid      红包接收者OPENID
     * @param type $amount      红包总金额
     * @param type $billno      商户订单号
     * @param type $desc        备注信息
     * @return boolean
     * 
     * @link https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2
     */
    public function transfers($openid, $amount, $billno, $desc) {
        $data = array();
        $data['mchid'] = $this->mch_id;
        $data['mch_appid'] = $this->appid;
        $data['partner_trade_no'] = $billno;
        $data['openid'] = $openid;
        $data['amount'] = $amount;
        $data['check_name'] = 'NO_CHECK'; #不验证姓名
        $data['spbill_create_ip'] = $_SERVER['REMOTE_ADDR']; //调用接口的机器Ip地址
        $data['desc'] = $desc; //备注信息
        $result = $this->postXmlSSL($data, self::MCH_BASE_URL . '/mmpaymkttransfers/promotion/transfers');
        $json = self::xml2arr($result);
        if (!empty($json) && false === $this->_parseResult($json)) {
            return false;
        }
        return $json;
    }

    /**
     * 企业付款查询
     * @param type $billno
     * @return boolean
     * 
     * @link https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_3
     */
    public function queryTransfers($billno) {
        $data['appid'] = $this->appid;
        $data['mch_id'] = $this->mch_id;
        $data['partner_trade_no'] = $billno;
        $result = $this->postXmlSSL($data, self::MCH_BASE_URL . '/mmpaymkttransfers/gettransferinfo');
        $json = self::xml2arr($result);
        if (!empty($json) && false === $this->_parseResult($json)) {
            return false;
        }
        return $json;
    }

    /**
     * 二维码链接转成短链接
     * @param type $url
     * @return boolean
     */
    public function shortUrl($url) {
        $data = array();
        $data['long_url'] = $url;
        $result = $this->getArrayResult($data, self::MCH_BASE_URL . '/tools/shorturl');
        if (!$result || $result['return_code'] !== 'SUCCESS') {
            $this->errCode = $result['return_code'];
            $this->errMsg = $result['return_msg'];
            return false;
        }
        if (isset($result['err_code'])) {
            $this->errMsg = $result['err_code_des'];
            $this->errCode = $result['err_code'];
            return false;
        }
        return $result['short_url'];
    }

}
