<?php

/**
 * 获取微信操作对象
 * @staticvar array $wechat
 * @param type $type    接口类型
 * @param type $config  SDK配置(token,appid,appsecret,encodingaeskey,mch_id,partnerkey,ssl_cer,ssl_key,qrc_img)
 * @return \className
 */
function &load_wechat($type = '', $config = array()) {
    static $wechat = array();
    if (!isset($wechat[$type])) {
        $className = "Wechat" . ucfirst(strtolower($type));
        if (!class_exists($className, false)) {
            require __DIR__ . "/{$className}.php";
        }
        $wechat[$type] = new $className($config);
    }
    return $wechat[$type];
}
