<?php

/**
 * 获取微信操作对象
 * @staticvar array $wechat
 * @param type $type    接口类型
 * @param type $config  SDK配置(token,appid,appsecret,encodingaeskey,mch_id,partnerkey,ssl_cer,ssl_key,qrc_img)
 * @return WechatBasic
 */
function &load_wechat($type = '', $config = array()) {
    static $wechat = array();
    if (!isset($wechat[$type])) {
        $className = "\\Wechat\\Wechat" . ucfirst(strtolower($type));
        $wechat[$type] = new $className($config);
    }
    return $wechat[$type];
}

/**
 * 注册自动加载函数
 */
spl_autoload_register(function($class) {
    if (stripos($class, 'Wechat\\') === 0) {
        require __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    }
});
