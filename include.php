<?php

use Wechat\Loader;

/**
 * 微信SDK引用文件（非Composer模式加载）
 * 
 * @author Anyon <zoujingli@qq.com>
 * @date 2016-08-21 11:26
 */
if (!class_exists('Wechat\Loader', FALSE)) {

    /** 加载SKD自动器 */
    require __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Loader.php';

    /** 注册SDK自动加载 */
    Loader::register();
}


