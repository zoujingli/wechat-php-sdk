<?php

/**
 * 获取微信操作对象
 * @staticvar array $wechat
 * @param type $type    接口类型
 * @param type $config  SDK配置(token,appid,appsecret,encodingaeskey,mch_id,partnerkey,ssl_cer,ssl_key,qrc_img)
 * @return WechatBasic
 */
if (!function_exists('load_wechat')) {

    /**
     * 获取微信操作对象
     * @staticvar array $wechat
     * @param type $type 接口类型(Card|Custom|Device|Extends|Media|Menu|Oauth|Pay|Receive|Script|User)
     * @param type $config SDK配置(token,appid,appsecret,encodingaeskey,mch_id,partnerkey,ssl_cer,ssl_key,qrc_img)
     * @return \className
     */
    function &load_wechat($type = '', $config = array()) {
        static $wechat = array();
        $index = md5(strtolower($type));
        if (!isset($wechat[$index])) {
            $className = "\\Wechat\\Wechat" . ucfirst(strtolower($type));
            $wechat[$index] = new $className($config);
        }
        return $wechat[$index];
    }

    /** 注册自动加载函数 */
    spl_autoload_register(function($class) {
        if (stripos($class, 'Wechat\\') === 0) {
            require __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
        }
    });
}

