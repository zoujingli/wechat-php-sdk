<?php

namespace Library\Util\Api;

/**
 * 微信基础工具类
 */
class WechatPayCommon {

    /**
     * 产生随机字符串
     * @param type $length
     * @return type
     */
    static public function createNoncestr($length = 32) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str.= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 格式化参数，签名过程需要使用
     * @param type $option
     * @param type $urlencode
     * @return type
     */
    static private function formatPayOption($option, $urlencode) {
        $buff = "";
        ksort($option);
        foreach ($option as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = null;
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    /**
     * 生成支付签名
     * @param type $option 
     * @param type $partnerKey
     * @return type
     */
    static public function getPaySign($option, $partnerKey) {
        ksort($option);
        $String = self::formatPayOption($option, false);
        return strtoupper(md5("{$String}&key={$partnerKey}"));
    }

    /**
     * array转xml
     * @param type $array
     * @return string
     */
    static function array2xml($array) {
        $xml = "<xml>";
        foreach ($array as $key => $val) {
            if (is_numeric($val)) {
                $xml.="<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml.="<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * 将xml转为array
     * @param type $xml
     * @return type
     */
    static public function xml2array($xml) {
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    /**
     * 	作用：以post方式提交xml到对应的接口url
     */
    static public function post($xml, $url, $second = 30) {
        $ch = curl_init();
        curl_setopt($ch, CURLOP_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        /* post提交方式 */
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $data = curl_exec($ch);
        curl_close($ch);
        if ($data) {
            return $data;
        } else {
            return false;
        }
    }

    /**
     * 使用证书，以post方式提交xml到对应的接口url
     * @param type $data POST提交的内容
     * @param type $url 请求的地址
     * @param type $ssl_cer 证书Cer路径 | 证书内容
     * @param type $ssl_key 证书Key路径 | 证书内容
     * @param type $second 设置请求超时时间
     * @return boolean
     */
    static function ssl_post($data, $url, $ssl_cer = null, $ssl_key = null, $second = 30) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        /* 要求结果为字符串且输出到屏幕上 */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        /* 设置证书 */
        if (!is_null($ssl_cer)) {
            if (is_file($ssl_cer)) {
                $_cer_path = $ssl_cer;
            } else {
                $_cer_path = RUNTIME_PATH . 'Data/client_cer.pem';
                file_put_contents($_cer_path, $ssl_cer);
            }
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, $_cer_path);
        }
        if (!is_null($ssl_key)) {
            if (is_file($ssl_key)) {
                $_key_path = $ssl_key;
            } else {
                $_key_path = RUNTIME_PATH . 'Data/client_key.pem';
                file_put_contents($_key_path, $ssl_key);
            }
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, $_key_path);
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

}
