<?php

namespace Wechat\Lib;

use CURLFile;

/**
 * 微信接口通用类
 * 
 * @category WechatSDK
 * @subpackage library
 * @author Anyon <zoujingli@qq.com>
 * @date 2016/05/28 11:55
 */
abstract class WechatBasic {

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
        $params = array();
        foreach ($arrdata as $key => $value) {
            $params[] = "{$key}={$value}";
        }
        return $method(join('&', $params));
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
     * XML编码
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $attr 根节点属性
     * @param string $id   数字索引子节点key转换的属性名
     * @return string
     */
    public function array2xml($data, $root = 'xml', $item = 'item', $id = 'id') {
        return "<{$root}>" . self::_data_to_xml($data, $item, $id) . "</{$root}>";
    }

    /**
     * 数据XML编码
     * @param type $data    数据
     * @param type $item    数字索引的子节点名
     * @param type $id      数字索引子节点key转换的属性名
     * @param type $content XML内容
     * @return type
     */
    protected static function _data_to_xml($data, $item = 'item', $id = 'id', $content = '') {
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = "{$item} {$id}=\"{$key}\"";
            $content .= "<{$key}>";
            if (is_array($val) || is_object($val)) {
                $content.=self::_data_to_xml($val);
            } elseif (is_numeric($val)) {
                $content.=$val;
            } else {
                $content.= '<![CDATA[' . preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/", '', $val) . ']]>';
            }
            list($_key, ) = explode(' ', $key . ' ');
            $content .= "</$_key>";
        }
        return $content;
    }

    /**
     * 将xml转为array
     * @param type $xml
     * @return type
     */
    static public function xml2array($xml) {
        return json_decode(self::json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
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
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1);
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
     * @return boolean
     */
    static public function http_post($url, $postdata) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        if (is_array($postdata)) {
            foreach ($postdata as &$value) {
                if (is_string($value) && stripos($value, '@') === 0 && class_exists('CURLFile', FALSE)) {
                    $value = new CURLFile(realpath(trim($value, '@')));
                }
            }
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $data = curl_exec($ch);
        curl_close($ch);
        if ($data) {
            return $data;
        }
        return false;
    }

    /**
     * 使用证书，以post方式提交xml到对应的接口url
     * @param type $url POST提交的内容
     * @param type $postdata 请求的地址
     * @param type $ssl_cer 证书Cer路径 | 证书内容
     * @param type $ssl_key 证书Key路径 | 证书内容
     * @param type $second 设置请求超时时间
     * @return boolean
     */
    static function http_ssl_post($url, $postdata, $ssl_cer = null, $ssl_key = null, $second = 30) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        /* 要求结果为字符串且输出到屏幕上 */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        /* 设置证书 */
        if (!is_null($ssl_cer) && file_exists($ssl_cer) && is_file($ssl_cer)) {
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, $ssl_cer);
        }
        if (!is_null($ssl_key) && file_exists($ssl_key) && is_file($ssl_key)) {
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, $ssl_key);
        }
        curl_setopt($ch, CURLOPT_POST, true);
        if (is_array($postdata)) {
            foreach ($postdata as &$data) {
                if (is_string($data) && stripos($data, '@') === 0 && class_exists('CURLFile', FALSE)) {
                    $data = new CURLFile(realpath(trim($data, '@')));
                }
            }
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 生成JSON，保留汉字
     * @param type $array
     * @return type
     */
    protected static function json_encode($array) {
        $str = json_encode($array);
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', create_function('$matches', 'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'), $str);
    }

    /**
     * 读取客户端IP
     * @return type
     */
    public function ipAddress() {
        foreach (array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP', 'REMOTE_ADDR') as $header) {
            if (!isset($_SERVER[$header]) || ($spoof = $_SERVER[$header]) === NULL) {
                continue;
            }
            sscanf($spoof, '%[^,]', $spoof);
            if (!filter_var($spoof, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $spoof = NULL;
            } else {
                return $spoof;
            }
        }
        return '0.0.0.0';
    }

    /**
     * 设置缓存，按需重载
     * @param string $cachename
     * @param mixed $value
     * @param int $expired
     * @return boolean
     */
    protected function setCache($cachename, $value, $expired) {
        return Cache::set($cachename, $value, $expired);
    }

    /**
     * 获取缓存，按需重载
     * @param string $cachename
     * @return mixed
     */
    protected function getCache($cachename) {
        return Cache::get($cachename);
    }

    /**
     * 清除缓存，按需重载
     * @param string $cachename
     * @return boolean
     */
    protected function removeCache($cachename) {
        return Cache::del($cachename);
    }

}
