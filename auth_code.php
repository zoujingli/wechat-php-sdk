<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/23
 * Time: 11:48
 */

if(isset($_GET['auth_code']) && isset($_GET['expires_in']) ){
    echo $_GET['auth_code'].' | '.$_GET['expires_in'];

    require 'include.php';
    $wxa = new \Wechat\WechatApplet([
        'component_encodingaeskey'=>'component_encodingaeskey',
        'component_verify_ticket'=>'',
        'component_appsecret'=>'component_appsecret',
        'component_token'=>'component_token',
        'component_appid'=>'component_appid',
        'authorizer_appid' => 'authorizer_appid',
    ]);
    #用户授权后跳转至此.

    #保存用户授权token
    $wxa->getAuthorizationInfo($_GET['auth_code']);
    $category_rst = $wxa->getCategory();
    var_dump($category_rst);
    $wxa->getLastAuditResult();
    $wxa->getPage();
    $wxa->getSearchStatus();
    $wxa->getTemplateList();

}else{
    echo 'do nothing';
}