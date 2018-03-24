<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/23
 * Time: 11:34
 */
require 'include.php';
$wxa = new \Wechat\WechatApplet([
    'component_encodingaeskey'=>'component_encodingaeskey',
    'component_verify_ticket'=>'',
    'component_appsecret'=>'component_appsecret',
    'component_token'=>'component_token',
    'component_appid'=>'component_appid',
    'authorizer_appid' => 'authorizer_appid',
]);
$getTicketRst = $wxa->getComonentTicket();
if($getTicketRst){
    echo 'SUCCESS';
    exit();
}
var_dump($getTicketRst);
echo PHP_EOL;
$getAuditRst = $wxa->revAuditResult();
var_dump($getAuditRst);
