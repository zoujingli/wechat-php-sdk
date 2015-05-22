<?php

namespace Library\Util\Api;

class Common {

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
     * 获取签名
     * @param array $arrdata 签名数组
     * @param string $method 签名方法
     * @return boolean|string 签名值
     */
    static public function getSignature($arrdata, $method = "sha1") {
        if (!function_exists($method)) {
            return false;
        }
        ksort($arrdata);
        $paramstring = "";
        foreach ($arrdata as $key => $value) {
            if (strlen($paramstring) == 0) {
                $paramstring .= $key . "=" . $value;
            } else {
                $paramstring .= "&" . $key . "=" . $value;
            }
        }
        $Sign = $method($paramstring);
        return $Sign;
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
     * GET 请求
     * @param string $url
     */
    static public function http_get($url) {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

    /**
     * 以post方式提交xml到对应的接口url
     * @param type $url
     * @param type $postdata
     * @param type $second
     * @return boolean
     */
    static public function http_post($url, $postdata, $second = 30) {
        $ch = curl_init();
        curl_setopt($ch, CURLOP_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        /* post提交方式 */
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
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
     * @param type $url POST提交的内容
     * @param type $data 请求的地址
     * @param type $ssl_cer 证书Cer路径 | 证书内容
     * @param type $ssl_key 证书Key路径 | 证书内容
     * @param type $second 设置请求超时时间
     * @return boolean
     */
    static function http_ssl_post($url, $data, $ssl_cer = null, $ssl_key = null, $second = 30) {
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

    /**
     * 设置缓存
     * @param type $key
     * @param type $value
     * @param type $expires_in
     */
    static public function setCache($key, $value, $expires_in = 0) {
        if (function_exists('S')):
            S($key, $value, $expires_in);
        else:
        /** @todo add other cache method */
        endif;
    }

    /**
     * 获取缓存
     * @param type $key
     * @return type
     */
    static public function getCache($key) {
        if (function_exists("S")):
            return S($key);
        else:
        /** @todo add other cache method */
        endif;
    }

    /**
     * 删除缓存
     * @param type $key
     */
    static public function delCache($key) {
        if (function_exists("S")):
            return S($key, null);
        else:
        /** @todo add other cache method */
        endif;
    }

}
