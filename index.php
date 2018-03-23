<?php
    require 'sdk/include.php';

    $url = 'https://ceshi.xinzhibang168.com/open/auth_code.php';
    $appid = 'wx4708c9d541173cdf';


    $wxa = new \Wechat\WechatApplet([
        'component_encodingaeskey'=>'xoiUaiy5MOWtkZFw5I5tY5Q0wfFYV5QyQTGTuFmuY0A',
        'component_verify_ticket'=>'',
        'component_appsecret'=>'e4c0a98ee2281b66132a23962ce89ffc',
        'component_token'=>'KKAecamB63DEdQc8C872edOK92ECAm6E',
        'component_appid'=>'wx4708c9d541173cdf',
        'authorizer_appid' => 'wx4846b50fa640a081',
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

