<?php

use Wechat\Loader;

/**
 * 微信SDK引用文件（非Composer模式加载）
 * 
 * @author Anyon <zoujingli@qq.com>
 * @date 2016-08-21 11:26
 */
if (!class_exists('Wechat\Loader', FALSE)) {

    /** 注册自动加载函数 */
    spl_autoload_register(function($class) {
        if (stripos($class, 'Wechat\\') === 0) {
            require __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 7)) . '.php';
        }
    });

    /**
     * 请设置微信配置信息
     * (token,appid,appsecret,encodingaeskey,mch_id,partnerkey,ssl_cer,ssl_key,qrc_img)
     */
    $options = array(
        'token'          => '',
        'appid'          => '',
        'appsecret'      => '',
        'encodingaeskey' => '',
        'mch_id'         => '',
        'partnerkey'     => 'partnerkey',
        'ssl_cer'        => '',
        'ssl_key'        => '',
    );

    Loader::setConfig($options);
}


