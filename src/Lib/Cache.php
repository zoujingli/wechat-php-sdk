<?php

namespace Wechat\Lib;

/**
 * SDK缓存类
 * 
 * @author Anyon <zoujingli@qq.com>
 * @date 2016-08-20 17:50
 */
class Cache {

    /**
     * 缓存位置
     * @var type 
     */
    static public $cachePath;

    /**
     * 设置缓存
     * @param type $name
     * @param type $value
     * @param type $expired
     * @return type
     */
    static public function set($name, $value, $expired = 0) {
        $data = serialize(array('value' => $value, 'expired' => $expired > 0 ? time() + $expired : 0));
        return self::check() && file_put_contents(self::$cachePath . $name, $data);
    }

    /**
     * 读取缓存
     * @param type $name
     * @return type
     */
    static public function get($name) {
        if (self::check() && ($file = self::$cachePath . $name) && file_exists($file) && ($data = file_get_contents($file)) && !empty($data)) {
            $data = unserialize($data);
            if (isset($data['expired']) && ($data['expired'] > time() || $data['expired'] === 0)) {
                return isset($data['value']) ? $data['value'] : null;
            }
        }
        return null;
    }

    /**
     * 删除缓存
     * @param type $name
     * @return type
     */
    static public function del($name) {
        return self::check() && unlink(self::$cachePath . $name);
    }

    /**
     * 检查缓存目录
     * @return boolean
     */
    static protected function check() {
        empty(self::$cachePath) && self::$cachePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Cache' . DIRECTORY_SEPARATOR;
        if (!is_dir(self::$cachePath) && !mkdir(self::$cachePath, 0755, TRUE)) {
            return FALSE;
        }
        return TRUE;
    }

}
