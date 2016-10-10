<?php

/**
 * 非Composer模式注册SDK加载函数
 * 
 * @author Anyon <zoujingli@qq.com>
 * @date 2016-08-21 11:26
 */
if (!class_exists('Wechat\Loader', FALSE)) {
    /**
     * 注册自动加载函数
     */
    spl_autoload_register(function($class) {
        if (0 === stripos($class, 'Wechat\\')) {
            $filename = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
            file_exists($filename) && require($filename);
        }
    });
}

