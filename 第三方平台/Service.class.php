<?php

namespace Library\Util\Api;

/**
 * 微信第三方法公众平台SDK
 * 
 * @author zoujingli <zoujingli@qq.com>
 * @version 1.0
 * @date 2014/11/22 00:35:55
 * 
 * usage:
 *   $options = array(
 *                  'ticket'=>'ticket', //填写你设定的ticket
 *                  'appid'=>'appid', //填写高级调用功能的appid
 *                  'appsecret'=>'appsecret', //填写高级调用功能的appsecret
 * 		);
 * $weObj = new Service($options);
 * $weObj->getAuthorizationInfo(); //获取服务号的授权信息
 * $weObj->refreshAccessToken(); //刷新授权方操作Token
 * $weObj->getWechatInfo(); //获取公众号的帐号信息
 * $weObj->getAuthorizerOption(); //获取公众号的授权项的值
 * $weObj->setAuthorizerOption(); //设置公众号的授权项的值
 * 
 */
class Service {

    const URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin/component';
    // 获取服务access_token
    const COMPONENT_TOKEN_URL = '/api_component_token';
    // 获取（刷新）授权公众号的令牌
    const REFRESH_ACCESS_TOKEN = '/api_authorizer_token';
    // 获取预授权码
    const PREAUTH_CODE_URL = '/api_create_preauthcode';
    // 获取公众号的授权信息
    const QUERY_AUTH_URL = '/api_query_auth';
    // 获取授权方的账户信息
    const GET_AUTHORIZER_INFO_URL = '/api_get_authorizer_info';
    // 刷新授权令牌
    const REFRESH_AUTHORIZER_TOKEN = './api_authorizer_token';
    // 获取授权方的选项设置信息
    const GET_AUTHORIZER_OPTION_URL = '/api_get_authorizer_option';
    // 设置授权方的选项信息
    const SET_AUTHORIZER_OPTION_URL = '/api_set_authorizer_option';

    // 微信后台推送的ticket 每十分钟更新一次
    protected $component_verify_ticket;
    // 服务appid
    protected $component_appid;
    // 服务appsecret
    protected $component_appsecret;
    // 服务令牌
    protected $component_access_token;
    // 授权方appid
    protected $authorizer_appid;
    // 授权方令牌
    protected $authorizer_access_token;
    // 刷新令牌
    protected $authorizer_refresh_token;
    // 预授权码
    protected $pre_auth_code;
    // Wechat对象缓存
    protected $wechat = array();
    // JSON数据
    protected $data;
    public $errCode;
    public $errMsg;

    /**
     * SDK初始化构造方法
     * 
     * @param type $options
     */
    public function __construct($options) {
        $this->component_verify_ticket = isset($options['ticket']) ? $options['ticket'] : S('ComponentVerifyTicket');
        $this->component_appid = isset($options['appid']) ? $options['appid'] : get_sysconfig('wechat_appid');
        $this->component_appsecret = isset($options['appsecret']) ? $options['appsecret'] : get_sysconfig('wechat_appsecret');
    }

    /**
     * 获取或刷新 服务access_token
     * 
     * @return Service
     */
    protected function _accessToken() {
        $cacheKey = 'wechat_component_access_token';
        $this->component_access_token = Common::getCache($cacheKey);
        if (empty($this->component_access_token)) :
            $data = array();
            $data['component_appid'] = $this->component_appid;
            $data['component_appsecret'] = $this->component_appsecret;
            $data['component_verify_ticket'] = $this->component_verify_ticket;
            $url = self::URL_PREFIX . self::COMPONENT_TOKEN_URL;
            $result = $this->http_post($url, $data);
            $this->component_access_token = $this->_decode($result, 'component_access_token');
            Common::setCache($cacheKey, $this->component_access_token, 7200);
        endif;
        return $this;
    }

    /**
     * 获取公众号的AccessToken
     * @param type $appid
     */
    public function getAppidAccessToken($appid) {
        $cacheKey = 'wechat_access_token_' . $appid;
        $this->access_token = Common::getCache($cacheKey);
        if (!empty($this->access_token)) {
            return $this->access_token;
        } else {
            $wechat = M('WechatConfig')->where(array('authorizer_appid' => $appid, 'status' => 2))->find();
            if ($wechat) {
                $newAccessToken = $this->refreshAccessToken($appid, $wechat['authorizer_refresh_token']);
                if ($newAccessToken['authorizer_access_token']) {
                    M('WechatConfig')->where(array('authorizer_appid' => $appid, 'status' => 2))->save(array('authorizer_access_token' => $newAccessToken['authorizer_access_token'], 'authorizer_refresh_token' => $newAccessToken['authorizer_refresh_token']));
                    Common::setCache($cacheKey, $newAccessToken['authorizer_access_token'], 7200);
                    return $newAccessToken['authorizer_access_token'];
                } else {
                    return '请在服务器操作';
                }
            } else {
                P(date('Y/m/d H:i:s') . "\t 获取getAppidAccessToken失败,公众号未授权或者取消授权，请重新授权。原因：" . var_export($newAccessToken, true), false, RUNTIME_PATH . 'AccessToken.log');
                return '公众号未授权或者取消授权，请重新授权';
            }
        }
    }

    /**
     * 获取微信SDK操作对象
     * @return Wechat
     */
    public function getInstanceWechat($appid) {
        $config = $this->getWechatConfig($appid);
        if (isset($this->wechat[$appid]) && $config['expires_in'] > time()) {
            return $this->wechat[$appid];
        } else {
            return $this->wechat[$appid] = new Wechat($config);
        }
    }

    /**
     * 获取公众号的AccessToken
     * @param type $appid
     */
    public function getWechatConfig($appid, $model = 'WechatConfig') {
        $map = array('authorizer_appid' => $appid, 'status' => '2');
        $info = M($model)->where($map)->find();
        if (empty($info)) {
            $this->errMsg = '公众号配置不存在';
            return false;
        }
        if ($info['expires_in'] < time()) {
            $newAccessToken = $this->refreshAccessToken($info['authorizer_appid'], $info['authorizer_refresh_token']);
            if ($newAccessToken === false) {
                $this->errMsg = '刷新AcessToken失败';
                P(date('Y/m/d H:i:s') . "\t 刷新AcessToken失败。原因：" . var_export($newAccessToken, true), false, RUNTIME_PATH . 'AccessToken.log');
                return false;
            }
            $newAccessToken['expires_in'] = (int) $newAccessToken['expires_in'] + time();
            $result = M($model)->where($map)->save($newAccessToken);
            if ($result === false) {
                $this->errMsg = '更新AccessToken失败';
                P(date('Y/m/d H:i:s') . "\t 更新AccessToken失败。原因：" . var_export($result, true), false, RUNTIME_PATH . 'AccessToken.log');
                return false;
            }
            $info = M($model)->where($map)->find();
        }
        return array(
            'appid' => $info['authorizer_appid'],
            'access_token' => $info['authorizer_access_token'],
            'token' => get_sysconfig('wechat_token'),
            'encodingaeskey' => get_sysconfig('wechat_encodingaeskey'),
        );
    }

    /**
     * 获取签名包
     * @return type
     */
    public function getSignPackage($appid, $url) {
        $wechat = $this->getWechatConfig($appid);
        if (empty($wechat)) {
            $this->errMsg = '获取AccessToken失败';
            P(date('Y/m/d H:i:s') . "\t 获取AccessToken失败。原因：" . var_export($wechat, true), false, RUNTIME_PATH . 'AccessToken.log');
            return false;
        }
        $jsapiTicket = $this->getJsApiTicket($appid, $wechat['access_token']);
        $timestamp = time();
        $nonceStr = Common::createNoncestr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket={$jsapiTicket}&noncestr={$nonceStr}&timestamp={$timestamp}&url={$url}";
        return array(
            "appId" => $appid,
            "timestamp" => $timestamp,
            "nonceStr" => $nonceStr,
            "url" => $url,
            "signature" => sha1($string),
            "rawString" => $string
        );
    }

    /**
     * 获取JsApiTicket
     * @param type $appid 公众号AppId
     * @param type $access_token 公众号AccessToken
     * @return type
     */
    private function getJsApiTicket($appid, $access_token) {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $ticket = Common::getCache('wechat_jsapi_ticket_' . $appid);
        if ($ticket) {
            return $ticket;
        } else {
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token={$access_token}";
            $res = json_decode(Common::http_get($url));
            $ticket = $res->ticket;
            if ($ticket) {
                Common::setCache('wechat_jsapi_ticket_' . $appid, $ticket, 7200);
            }
            if (empty($ticket)) {
                p($url);
                P(date('Y/m/d H:i:s') . "\t 获取JsApiAccessTicket失败。原因：" . var_export($res, true), false, RUNTIME_PATH . 'AccessToken.log');
            }
            return $ticket;
        }
    }

    /**
     *  获取卡券签名包
     * @param type $appid
     * @return type
     */
    public function getCardList($appid) {
        $cardApiTicket = $this->getCardApiTicket($appid);
        $timestamp = time();
        $nonceStr = Common::createNoncestr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $sign = array();
        $sign['app_id'] = $appid;
        $sign['api_ticket'] = $cardApiTicket;
        $sign['times_tamp'] = $timestamp;
        $sign['nonce_str'] = $nonceStr;
        $sign['card_type'] = 'GENERAL_COUPON';

        sort($sign, SORT_STRING);
        $tmpStr = implode($sign);
        $signature = sha1($tmpStr);

        $signPackage = array(
            "cardType" => 'GENERAL_COUPON', // 卡券类型
            "timestamp" => $timestamp, // 卡券签名时间戳
            "nonceStr" => $nonceStr, // 卡券签名随机串
            "signType" => 'SHA1', // 签名方式，默认'SHA1'
            "cardSign" => $signature,
            "cardApiTicket" => $cardApiTicket,
            "tmpStr" => $tmpStr,
        );
        return $signPackage;
    }

    /**
     * 添加到卡包
     * @param type $appid
     * @return string
     */
    public function bacthAddCard($appid, $cardid) {
        $cardApiTicket = $this->getCardApiTicket($appid);
        $timestamp = time();
        $tmpArr = array($timestamp, $cardApiTicket, $cardid);
        sort($tmpArr, SORT_STRING);
        $signature = sha1(implode($tmpArr));
        $cardInfo = array(
            "card_id" => $cardid,
            'card_ext' => json_encode(array(
                'timestamp' => time(),
                'signature' => $signature,
            )),
            'sign' => $signature,
        );
        return $cardInfo;
    }

    /**
     * 获取CardApiTicket
     * @return type
     */
    private function getCardApiTicket($appid) {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $ticket = Common::getCache('card_api_ticket_' . $appid);
        if ($ticket) {
            return $ticket;
        } else {
            $accessToken = $this->getAppidAccessToken($appid);
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$accessToken}&type=wx_card";
            $res = json_decode(Common::http_get($url));
            $ticket = $res->ticket;
            if ($ticket) {
                Common::setCache('card_api_ticket_' . $appid, $ticket, 7200);
            }
            if (empty($ticket)) {
                P(date('Y/m/d H:i:s') . "\t 获取card_api_ticket_失败。原因：" . var_export($res, true), false, RUNTIME_PATH . 'AccessToken.log');
            }
            return $ticket;
        }
    }

    /**
     * 获取预授权码
     * 
     * @return type
     */
    public function getPreauthCode() {
        empty($this->component_access_token) && $this->_accessToken();
        if (empty($this->component_access_token)) {
            return false;
        }
        $data = array();
        $data['component_appid'] = $this->component_appid;
        $url = self::URL_PREFIX . self::PREAUTH_CODE_URL . "?component_access_token={$this->component_access_token}";
        $result = $this->http_post($url, $data);
        return $this->pre_auth_code = $this->_decode($result, 'pre_auth_code');
    }

    /**
     * 获取（刷新）授权公众号的令牌
     * @注意1. 授权公众号访问access token2小时有效
     * @注意2. 一定保存好新的刷新令牌
     * @param type $authorizer_appid 授权方APPID
     * @param type $authorizer_refresh_token 授权方刷新令牌
     * @return type
     */
    public function refreshAccessToken($authorizer_appid, $authorizer_refresh_token) {
        empty($this->component_access_token) && $this->_accessToken();
        if (empty($this->component_access_token)) {
            return false;
        }
        $data = array();
        $data['component_appid'] = $this->component_appid;
        $data['authorizer_appid'] = $authorizer_appid;
        $data['authorizer_refresh_token'] = $authorizer_refresh_token;
        $url = self::URL_PREFIX . self::REFRESH_ACCESS_TOKEN . "?component_access_token={$this->component_access_token}";
        $result = $this->http_post($url, $data);
        return $this->_decode($result);
    }

    /**
     * 获取公众号的授权信息
     * 
     * @param type $authorization_code
     * @return array
     */
    public function getAuthorizationInfo($authorization_code) {
        empty($this->component_access_token) && $this->_accessToken();
        if (empty($this->component_access_token)) {
            return false;
        }
        $data = array();
        $data['component_appid'] = $this->component_appid;
        $data['authorization_code'] = $authorization_code;
        $url = self::URL_PREFIX . self::QUERY_AUTH_URL . "?component_access_token={$this->component_access_token}";
        $result = $this->http_post($url, $data);
        $authorization_info = $this->_decode($result, 'authorization_info');
        if (empty($authorization_info)) {
            return false;
        }
        $authorization_info['func_info'] = $this->_parseFuncInfo($authorization_info['func_info']);
        return $authorization_info;
    }

    /**
     * 获取授权方的账户信息
     * @param type $authorizer_appid
     * @return boolean
     */
    public function getWechatInfo($authorizer_appid) {
        empty($this->component_access_token) && $this->_accessToken();
        $data = array();
        $data['component_access_token'] = $this->component_access_token;
        $data['component_appid'] = $this->component_appid;
        $data['authorizer_appid'] = $authorizer_appid;
        $url = self::URL_PREFIX . self::GET_AUTHORIZER_INFO_URL . "?component_access_token={$this->component_access_token}";
        $result = $this->http_post($url, $data);
        $authorizer_info = $this->_decode($result, 'authorizer_info');
        if (empty($authorizer_info)) {
            return false;
        } else {
            //合并数据
            $author_data = array_merge($authorizer_info, $this->data['authorization_info']);
            $author_data['service_type_info'] = $author_data['service_type_info']['id'];
            $author_data['verify_type_info'] = $author_data['verify_type_info']['id'];
            $author_data['func_info'] = $this->_parseFuncInfo($author_data['func_info']);
            return $author_data;
        }
    }

    /**
     * 获取授权方的选项设置信息
     * @param type $authorizer_appid
     * @param type $option_name
     * @return boolean
     */
    public function getAuthorizerOption($authorizer_appid, $option_name) {
        empty($this->component_access_token) && $this->_accessToken();
        if (empty($this->authorizer_appid)) {
            return false;
        }
        $data = array();
        $data['component_appid'] = $this->component_appid;
        $data['authorizer_appid'] = $authorizer_appid;
        $data['option_name'] = $option_name;
        $url = self::URL_PREFIX . self::GET_AUTHORIZER_OPTION_URL . "?component_access_token={$this->component_access_token}";
        $result = $this->http_post($url, $data);
        return $this->_decode($result);
    }

    /**
     * 设置授权方的选项信息
     * @param type $authorizer_appid
     * @param type $option_name
     * @param type $option_value
     * @return boolean
     */
    public function setAuthorizerOption($authorizer_appid, $option_name, $option_value) {
        empty($this->component_access_token) && $this->_accessToken();
        if (empty($this->authorizer_appid)) {
            return false;
        }
        $data = array();
        $data['component_appid'] = $this->component_appid;
        $data['authorizer_appid'] = $authorizer_appid;
        $data['option_name'] = $option_name;
        $data['option_value'] = $option_value;
        $url = self::URL_PREFIX . self::SET_AUTHORIZER_OPTION_URL . "?component_access_token={$this->component_access_token}";
        $result = $this->http_post($url, $data);
        return $this->_decode($result);
    }

    /**
     * 获取授权回跳地址
     * @param type $redirect_uri
     * @return boolean
     */
    public function getAuthRedirect($redirect_uri) {
        empty($this->pre_auth_code) && $this->getPreauthCode();
        if (empty($this->pre_auth_code)) {
            return false;
        }
        return "https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid={$this->component_appid}&pre_auth_code={$this->pre_auth_code}&redirect_uri={$redirect_uri}";
    }

    /**
     * 获取错误消息
     * @return type
     */
    public function getErrorMsg() {
        return "{$this->data['errcode']} - {$this->data['errmsg']}";
    }

    /**
     * 解析授权信息，返回以逗号分割的数据
     * @param type $func_info
     * @return type
     */
    private function _parseFuncInfo($func_info) {
        $authorization_list = array();
        foreach ($func_info as $func) {
            foreach ($func as $f) {
                $authorization_list[] = $f['id'];
            }
        }
        return join($authorization_list, ',');
    }

    /**
     * 解析JSON数据

     * @param type $result
     * @param type $field
     * @return type
     */
    private function _decode($result, $field = null) {
        $this->data = json_decode($result, true);
        if ($this->data && !is_null($field)) {
            if (isset($this->data[$field])) {
                return $this->data[$field];
            } else {
                return false;
            }
        }
        return $this->data;
    }

    /**
     * oauth 授权跳转接口
     * @param type $appid
     * @param type $redirect_uri
     * @param type $scope snsapi_userinfo|snsapi_base
     * @return type
     */
    public function getOauthRedirect($appid, $redirect_uri, $scope = 'snsapi_userinfo') {
        return "https://open.weixin.qq.com/connect/oauth2/authorize"
                . "?appid={$appid}"
                . "&redirect_uri=" . urlencode($redirect_uri)
                . "&response_type=code&"
                . "scope={$scope}"
                . "&state={$appid}"
                . "&component_appid={$this->component_appid}"
                . "#wechat_redirect";
    }

    /**
     * 通过code获取Access Token
     * @param type $appid
     * @return boolean
     */
    public function getOauthAccessToken($appid) {
        $code = isset($_GET['code']) ? $_GET['code'] : '';
        if (!$code) :
            return false;
        endif;

        empty($this->component_access_token) && $this->_accessToken();
        if (empty($this->component_access_token)) :
            return false;
        endif;

        $url = "https://api.weixin.qq.com/sns/oauth2/component/access_token?"
                . "appid={$appid}&"
                . "code={$code}&"
                . "grant_type=authorization_code&"
                . "component_appid={$this->component_appid}&"
                . "component_access_token={$this->component_access_token}";
        $result = Common::http_get($url);
        $json = $this->parseJson($result);
        if ($json !== false) :
            //Common::setCache('OauthAccessToken_' . $json['openid'], $json,7200);
            return $json;
        endif;
        return false;
    }

    /**
     * 获取关注者详细信息
     * @param string $openid
     * @param boolen $oauthAccessToken
     * @return array {subscribe,openid,nickname,sex,city,province,country,language,headimgurl,subscribe_time,[unionid]}
     * 注意：unionid字段 只有在用户将公众号绑定到微信开放平台账号后，才会出现。建议调用前用isset()检测一下
     */
    public function getOauthUserInfo($openid, $oauthAccessToken) {
        $url = "https://api.weixin.qq.com/sns/userinfo?"
                . "access_token=$oauthAccessToken&"
                . "openid={$openid}&"
                . "lang=zh_CN";
        $result = Common::http_get($url);
        $json = $this->parseJson($result);
        if ($json !== false) :
            return $json;
        endif;
        return false;
    }

    /**
     * 解析JSON数据
     * @param type $result
     * @return boolean
     */
    protected function parseJson($result) {
        $json = json_decode($result, true);
        if (empty($json) || isset($json['errcode'])):
            $this->errCode = $json['errcode'];
            $this->errMsg = $json['errmsg'];
            return false;
        endif;
        return $json;
    }

    /**
     * POST提交数据
     * @param type $url
     * @param type $data
     * @return type
     */
    protected function http_post($url, $data) {
        return Common::http_post($url, json_encode($data));
    }

}
