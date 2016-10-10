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
     * 配置参数
     * @var type 
     */
    static protected $config = array();

    /**
     * 对象缓存
     * @var type 
     */
    static protected $cache = array();

    /**
     * 设置配置参数
     * @param type $config
     */
    static public function setConfig($config) {
        self::$config = $config;
    }

    /**
     * 获取配置参数
     * @return type
     */
    static public function getConfig() {
        return self::$config;
    }

    /**
     * 获取微信SDK接口对象
     * @param type $type 接口类型(Card|Custom|Device|Extends|Media|Menu|Oauth|Pay|Receive|Script|User)
     * @param type $config SDK配置(token,appid,appsecret,encodingaeskey,mch_id,partnerkey,ssl_cer,ssl_key,qrc_img)
     * @return \Wechat\WechatReceive
     */
    static public function & get_instance($type, $config = array()) {
        $index = md5(strtolower($type));
        if (!isset(self::$cache[$index])) {
            $basicName = 'Wechat' . ucfirst(strtolower($type));
            $className = "\\Wechat\\{$basicName}";
            !class_exists($basicName, FALSE) && class_alias($className, $basicName);
            self::$cache[$index] = new $className(empty($config) ? self::$config : $config);
        }
        return self::$cache[$index];
    }

    /**
     * 获取微信SDK接口对象(别名函数)
     * @param type $type 接口类型(Card|Custom|Device|Extends|Media|Menu|Oauth|Pay|Receive|Script|User)
     * @param type $config SDK配置(token,appid,appsecret,encodingaeskey,mch_id,partnerkey,ssl_cer,ssl_key,qrc_img)
     * @return \Wechat\WechatReceive
     */
    static public function & get($type, $config = array()) {
        return self::get_instance($type, $config);
    }

}
