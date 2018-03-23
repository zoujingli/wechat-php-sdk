<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/23
 * Time: 11:48
 */

if(isset($_GET['auth_code']) && isset($_GET['expires_in']) ){
    echo $_GET['auth_code'].' | '.$_GET['expires_in'];

    require 'sdk/include.php';
    $wxa = new \Wechat\WechatApplet([
        'component_encodingaeskey'=>'xoiUaiy5MOWtkZFw5I5tY5Q0wfFYV5QyQTGTuFmuY0A',
        'component_verify_ticket'=>'',
        'component_appsecret'=>'e4c0a98ee2281b66132a23962ce89ffc',
        'component_token'=>'KKAecamB63DEdQc8C872edOK92ECAm6E',
        'component_appid'=>'wx4708c9d541173cdf',
        'authorizer_appid' => 'wx4846b50fa640a081',
    ]);
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