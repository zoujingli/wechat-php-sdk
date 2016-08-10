<?php

namespace Wechat;

/**
 * 微信网页端脚本相关
 */
class WechatScript extends WechatCommon {

    private $jsapi_ticket;

    /**
     * 删除JSAPI授权TICKET
     * @param string $appid 用于多个appid时使用
     */
    public function resetJsTicket($appid = '') {
        $this->jsapi_ticket = '';
        $authname = 'wechat_jsapi_ticket_' . empty($appid) ? $this->appid : $appid;
        $this->removeCache($authname);
        return true;
    }

    /**
     * 获取JSAPI授权TICKET
     * @param string $appid 用于多个appid时使用,可空
     * @param string $jsapi_ticket 手动指定jsapi_ticket，非必要情况不建议用
     */
    public function getJsTicket($appid = '', $jsapi_ticket = '') {
        if (!$this->access_token && !$this->checkAuth()) {
            return false;
        }
        if (!$appid) {
            $appid = $this->appid;
        }
        if ($jsapi_ticket) { //手动指定token，优先使用
            $this->jsapi_ticket = $jsapi_ticket;
            return $this->jsapi_ticket;
        }
        $authname = 'wechat_jsapi_ticket_' . $appid;
        $rs = $this->getCache($authname);
        if ($rs) {
            $this->jsapi_ticket = $rs;
            return $rs;
        }
        $result = $this->http_get(self::API_URL_PREFIX . self::GET_TICKET_URL . 'access_token=' . $this->access_token . '&type=jsapi');
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            $this->jsapi_ticket = $json['ticket'];
            $expire = $json['expires_in'] ? intval($json['expires_in']) - 100 : 3600;
            $this->setCache($authname, $this->jsapi_ticket, $expire);
            return $this->jsapi_ticket;
        }
        return false;
    }

    /**
     * 获取JsApi使用签名
     * @param string $url 网页的URL，自动处理#及其后面部分
     * @param string $timestamp 当前时间戳 (为空则自动生成)
     * @param string $noncestr 随机串 (为空则自动生成)
     * @param string $appid 用于多个appid时使用,可空
     * @return array|bool 返回签名字串
     */
    public function getJsSign($url, $timestamp = 0, $noncestr = '', $appid = '') {
        if (!$this->jsapi_ticket && !$this->getJsTicket($appid) || empty($url)) {
            return false;
        }
        $arrdata = array(
            "jsapi_ticket" => $this->jsapi_ticket,
            "timestamp"    => empty($timestamp) ? time() : $timestamp,
            "noncestr"     => '' . empty($noncestr) ? $this->createNoncestr(16) : $noncestr,
            "url"          => trim($url),
        );
        return array(
            // 'debug' => true,
            "appId"     => empty($appid) ? $this->appid : $appid,
            "nonceStr"  => $arrdata['noncestr'],
            "timestamp" => $arrdata['timestamp'],
            "signature" => $this->getSignature($arrdata, 'sha1'),
            'jsApiList' => array(
                'onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareQZone',
                'hideOptionMenu', 'showOptionMenu', 'hideMenuItems', 'showMenuItems', 'hideAllNonBaseMenuItem', 'showAllNonBaseMenuItem',
                'chooseImage', 'previewImage', 'uploadImage', 'downloadImage', 'closeWindow', 'scanQRCode', 'chooseWXPay',
                'translateVoice', 'getNetworkType', 'openLocation', 'getLocation',
            #	'startRecord', 'stopRecord', 'onVoiceRecordEnd', 'playVoice', 'pauseVoice', 'stopVoice', 'onVoicePlayEnd', 'uploadVoice', 'downloadVoice',
            #	'openProductSpecificView', 'addCard', 'chooseCard', 'openCard',
            )
        );
    }

}
