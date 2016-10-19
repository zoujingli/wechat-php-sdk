<?php

namespace Wechat;

use Wechat\Lib\WechatCommon; 

/**
 * 微信菜单操作SDK
 * 
 * @author Anyon <zoujingli@qq.com>
 * @date 2016/06/28 11:52
 */
class WechatMenu extends WechatCommon {

    /** 创建自定义菜单 */
    const MENU_ADD_URL = '/menu/create?';
    /* 获取自定义菜单 */
    const MENU_GET_URL = '/menu/get?';
    /* 删除自定义菜单 */
    const MENU_DEL_URL = '/menu/delete?';

    /** 添加个性菜单 */
    const COND_MENU_ADD_URL = '/menu/addconditional?';
    /* 删除个性菜单 */
    const COND_MENU_DEL_URL = '/menu/delconditional?';
    /* 测试个性菜单 */
    const COND_MENU_TRY_URL = '/menu/trymatch?';

    /**
     * 创建自定义菜单
     * @param array $data 菜单数组数据
     * @link https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421141013&token=&lang=zh_CN 文档
     * @return boolean
     */
    public function createMenu($data) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = $this->http_post(self::API_URL_PREFIX . self::MENU_ADD_URL . "access_token={$this->access_token}", self::json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return true;
        }
        return false;
    }

    /**
     * 获取所有菜单
     * @return array('menu'=>array())
     */
    public function getMenu() {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = $this->http_get(self::API_URL_PREFIX . self::MENU_GET_URL . "access_token={$this->access_token}");
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

    /**
     * 删除所有菜单
     * @return boolean
     */
    public function deleteMenu() {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = $this->http_get(self::API_URL_PREFIX . self::MENU_DEL_URL . "access_token={$this->access_token}");
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return true;
        }
        return false;
    }

    /**
     * 创建个性菜单
     * @param array $data 菜单数组数据
     * @link https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1455782296&token=&lang=zh_CN 文档
     * @return boolean
     */
    public function createCondMenu($data) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = $this->http_post(self::API_URL_PREFIX . self::COND_MENU_ADD_URL . "access_token={$this->access_token}", self::json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || !empty($json['errcode']) || empty($json['menuid'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return empty($json['menuid']);
        }
        return false;
    }

    /**
     * 删除个性菜单
     * @param type $menuid
     * @return boolean
     */
    public function deleteCondMenu($menuid) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array('menuid' => $menuid);
        $result = $this->http_post(self::API_URL_PREFIX . self::COND_MENU_DEL_URL . "access_token={$this->access_token}", self::json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return true;
        }
        return false;
    }

    /**
     * 测试并返回个性化菜单
     * @param type $openid
     * @return boolean
     */
    public function tryCondMenu($openid) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array('user_id' => $openid);
        $result = $this->http_post(self::API_URL_PREFIX . self::COND_MENU_TRY_URL . "access_token={$this->access_token}", self::json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

}
