<?php
    require 'include.php';

    $url = '';
    $appid = '';


    $wxa = new \Wechat\WechatApplet([
        'component_encodingaeskey'=>'component_encodingaeskey',
        'component_verify_ticket'=>'',
        'component_appsecret'=>'component_appsecret',
        'component_token'=>'component_token',
        'component_appid'=>'component_appid',
        'authorizer_appid' => 'authorizer_appid',
    ]);
    $redirect_url = $wxa->getAuthRedirect('https://ceshi.xinzhibang168.com/open/auth_code.php');

    $token = $wxa->getComponentAccessToken();
    if(!$token){
        echo $wxa->errCode.'.'.$wxa->errMsg;
        exit('failt to get component access token!');
    }

    $pre_auth_code = $wxa->getPreauthCode();
    if(!$pre_auth_code){
        echo $wxa->errCode.'.'.$wxa->errMsg;
        exit('failt to get component access token!');
    }





    echo '<html>
    <body>
    <a href="'.$redirect_url.'">
        这里!
    </a>
    </body>
</html>'

    ?>

