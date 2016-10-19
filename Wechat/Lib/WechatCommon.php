<?php

namespace Wechat\Lib;

use Prpcrypt;
use Wechat\Loader;
 
/**
 * 微信SDK基础类
 * 
 * @category WechatSDK
 * @subpackage library
 * @author Anyon <zoujingli@qq.com>
 * @date 2016/05/28 11:55
 */
class WechatCommon extends WechatBasic {

    public $token;
    public $encodingAesKey;
    public $encrypt_type;
    public $appid;
    public $appsecret;
    public $access_token;
    public $postxml;
    public $_msg;
    public $_funcflag = 1;
    public $debug = false;
    public $errCode = 40001;
    public $errMsg = "no access";
    public $config = array();
    private $_retry = FALSE;

    /** API接口URL需要使用此前缀 */
    const API_BASE_URL_PREFIX = 'https://api.weixin.qq.com';
    const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin';
    const GET_TICKET_URL = '/ticket/getticket?';
    const AUTH_URL = '/token?grant_type=client_credential&';

    /**
     * 构造方法
     * @param type $options
     */
    public function __construct($options) {
        $options = Loader::config($options);
        $this->token = isset($options['token']) ? $options['token'] : '';
        $this->appid = isset($options['appid']) ? $options['appid'] : '';
        $this->appsecret = isset($options['appsecret']) ? $options['appsecret'] : '';
        $this->encodingAesKey = isset($options['encodingaeskey']) ? $options['encodingaeskey'] : '';
        isset($options['cachepath']) && Cache::$cachepath = $options['cachepath'];
        $this->config = $options;
    }

    /**
     * 验证来自微信服务器
     * @param type $str
     * @return boolean
     */
    private function checkSignature($str = '') {
        $signature = isset($_GET["signature"]) ? $_GET["signature"] : '';
        $signature = isset($_GET["msg_signature"]) ? $_GET["msg_signature"] : $signature; //如果存在加密验证则用加密验证段
        $timestamp = isset($_GET["timestamp"]) ? $_GET["timestamp"] : '';
        $nonce = isset($_GET["nonce"]) ? $_GET["nonce"] : '';
        $tmpArr = array($this->token, $timestamp, $nonce, $str);
        sort($tmpArr, SORT_STRING);
        if (sha1(implode($tmpArr)) == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 接口验证
     * @return boolean
     */
    public function valid() {
        $encryptStr = "";
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $postStr = file_get_contents("php://input");
            $array = (array) simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $this->encrypt_type = isset($_GET["encrypt_type"]) ? $_GET["encrypt_type"] : '';
            if ($this->encrypt_type == 'aes') {
                $encryptStr = $array['Encrypt'];
                !class_exists('Prpcrypt', FALSE) && require __DIR__ . '/Prpcrypt.php';
                $pc = new Prpcrypt($this->encodingAesKey);
                $array = $pc->decrypt($encryptStr, $this->appid);
                if (!isset($array[0]) || intval($array[0]) > 0) {
                    $this->errCode = $array[0];
                    $this->errMsg = $array[1];
                    $this->log("Interface Authentication Failed. {$this->errMsg}[{$this->errCode}]", 'ERR');
                    return false;
                }
                $this->postxml = $array[1];
                empty($this->appid) && $this->appid = $array[2];
            } else {
                $this->postxml = $postStr;
            }
        } elseif (isset($_GET["echostr"])) {
            if ($this->checkSignature()) {
                exit($_GET["echostr"]);
            } else {
                return false;
            }
        }
        if (!$this->checkSignature($encryptStr)) {
            $this->errMsg = 'Interface authentication failed, please use the correct method to call.';
            return false;
        }
        return true;
    }

    /**
     * 获取公众号访问 access_token
     * @param string $appid 如在类初始化时已提供，则可为空
     * @param string $appsecret 如在类初始化时已提供，则可为空
     * @param string $token 手动指定access_token，非必要情况不建议用
     */
    public function getAccessToken($appid = '', $appsecret = '', $token = '') {
        if (!$appid || !$appsecret) {
            $appid = $this->appid;
            $appsecret = $this->appsecret;
        }
        if ($token) {
            return $this->access_token = $token;
        }
        $cache = 'wechat_access_token_' . $appid;
        if (($access_token = $this->getCache($cache)) && !empty($access_token)) {
            return $this->access_token = $access_token;
        }
        # 检测事件注册
        if (isset(Loader::$callback[__FUNCTION__])) {
            return $this->access_token = call_user_func_array(Loader::$callback[__FUNCTION__], array(&$this, &$cache));
        }
        $result = $this->http_get(self::API_URL_PREFIX . self::AUTH_URL . 'appid=' . $appid . '&secret=' . $appsecret);
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                $this->log("Get New AccessToken Error. {$this->errMsg}[{$this->errCode}]", 'ERR');
                return false;
            }
            $this->access_token = $json['access_token'];
            $this->log("Get New AccessToken Success.");
            $this->setCache($cache, $this->access_token, 5000);
            return $this->access_token;
        }
        return false;
    }

    /**
     * 删除验证数据
     * @param string $appid
     */
    public function resetAuth($appid = '') {
        $authname = 'wechat_access_token_' . (empty($appid) ? $this->appid : $appid);
        $this->log("Reset Auth And Remove Old AccessToken.");
        $this->access_token = '';
        $this->removeCache($authname);
        return true;
    }

    /**
     * 重试检测
     * @return boolean
     */
    protected function checkRetry($method, $arguments = array()) {
        if (!$this->_retry && in_array($this->errCode, array('40014', '40001', '41001', '42001'))) {
            $this->log("Run {$method} Faild. {$this->errMsg}[{$this->errCode}]", 'ERR');
            ($this->_retry = true) && $this->resetAuth();
            $this->errCode = 40001;
            $this->errMsg = 'no access';
            $this->log("Retry Run {$method} ...");
            return call_user_func_array(array($this, $method), $arguments);
        }
        return false;
    }

}
