<?php

namespace Wechat;

/**
 * 微信SDK加载器
 * 
 * @author Anyon <zoujingli@qq.com>
 * @date 2016-08-21 11:06
 */
class Loader {

    /**
     * 表态配置参数
     * @var type 
     */
    static protected $config=array();

    /**
     * 设置配置参数
     * @param type $config
     */
    static public function set_config($config) {
        self::$config;
    }

    /**
     * 获取配置参数
     * @return type
     */
    static public function get_config() {
        return self::$config;
    }

    /**
     * 获取微信SDK接口对象
     * @staticvar array $wechat
     * @param type $type 接口类型(Card|Custom|Device|Extends|Media|Menu|Oauth|Pay|Receive|Script|User)
     * @param type $config SDK配置(token,appid,appsecret,encodingaeskey,mch_id,partnerkey,ssl_cer,ssl_key,qrc_img)
     * @return \Wechat\WechatReceive
     */
    static public function & get_instance($type, $config = array()) {
        static $wechat = array();
        $index = md5(strtolower($type));
        if (!isset($wechat[$index])) {
            $className = "\\Wechat\\Wechat" . ucfirst(strtolower($type));
            $classAlias = 'Wechat' . ucfirst(strtolower($type));
            !class_exists($classAlias, FALSE) && class_alias($className, $classAlias);
            $wechat[$index] = new $className(empty($config)?self::$config:$config);
        }
        return $wechat[$index];
    }

}
