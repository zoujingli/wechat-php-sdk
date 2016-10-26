<?php

namespace Wechat;

use Wechat\Lib\Cache;
use Wechat\WechatReceive;

/**
 * 注册SDK自动加载机制
 * 
 * @author Anyon <zoujingli@qq.com>
 * @date 2016/10/26 10:21
 */
spl_autoload_register(function($class) {
    if (0 === stripos($class, 'Wechat\\')) {
        $filename = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
        file_exists($filename) && require($filename);
    }
});

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
     * 事件注册函数
     * @var type 
     */
    static public $callback = array();

    /**
     * 设置配置参数
     * @param type $config
     * @return type
     */
    static public function config($config = array()) {
        !empty($config) && self::$config = $config;
        if (!empty(self::$config['cachepath'])) {
            Cache::$cachepath = self::$config['cachepath'];
        }
        if (empty(self::$config['component_verify_ticket'])) {
            self::$config['component_verify_ticket'] = Cache::get('component_verify_ticket');
        }
        return self::$config;
    }

    /**
     * 动态注册SDK事件处理函数
     * @param type $event 事件名称（getAccessToken|getJsTicket）
     * @param type $method 处理方法（可以是普通方法或者类中的方法）
     * @param type $class 处理对象（可以直接使用的类实例）
     * @return boolean
     */
    static public function register($event, $method, $class = NULL) {
        if (!empty($class) && class_exists($class, FALSE) && method_exists($class, $method)) {
            self::$callback[$event] = array($class, $method);
        } else {
            self::$callback[$event] = $method;
        }
    }

    /**
     * 获取微信SDK接口对象(别名函数)
     * @param type $type 接口类型(Card|Custom|Device|Extends|Media|Menu|Oauth|Pay|Receive|Script|User)
     * @param type $config SDK配置(token,appid,appsecret,encodingaeskey,mch_id,partnerkey,ssl_cer,ssl_key,qrc_img)
     * @return WechatReceive
     */
    static public function & get_instance($type, $config = array()) {
        return self::get($type, $config);
    }

    /**
     * 获取微信SDK接口对象
     * @param type $type 接口类型(Card|Custom|Device|Extends|Media|Menu|Oauth|Pay|Receive|Script|User)
     * @param type $config SDK配置(token,appid,appsecret,encodingaeskey,mch_id,partnerkey,ssl_cer,ssl_key,qrc_img)
     * @return WechatReceive
     */
    static public function & get($type, $config = array()) {
        $index = md5(strtolower($type) . md5(json_encode(self::$config)));
        if (!isset(self::$cache[$index])) {
            $basicName = 'Wechat' . ucfirst(strtolower($type));
            $className = "\\Wechat\\{$basicName}";
            /* 注册类的无命名空间别名，兼容未带命名空间的老版本SDK */
            !class_exists($basicName, FALSE) && class_alias($className, $basicName);
            self::$cache[$index] = new $className(self::config($config));
        }
        return self::$cache[$index];
    }

}
