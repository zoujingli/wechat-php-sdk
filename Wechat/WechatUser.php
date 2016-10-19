<?php

namespace Wechat;

use Wechat\Lib\WechatCommon; 

/**
 * 微信粉丝操作SDK
 * 
 * @author Anyon <zoujingli@qq.com>
 * @date 2016/06/28 11:20
 */
class WechatUser extends WechatCommon {

    /** 获取粉丝列表 */
    const USER_GET_URL = '/user/get?';
    /* 获取粉丝信息 */
    const USER_INFO_URL = '/user/info?';
    /* 更新粉丝标注 */
    const USER_UPDATEREMARK_URL = '/user/info/updateremark?';

    /** 创建标签 */
    const TAGS_CREATE_URL = '/tags/create?';
    /* 获取标签列表 */
    const TAGS_GET_URL = '/tags/get?';
    /* 更新标签 */
    const TAGS_UPDATE_URL = '/tags/update?';
    /* 删除标签 */
    const TAGS_DELETE_URL = '/tags/delete?';
    /* 获取标签下的粉丝列表 */
    const TAGS_GET_USER_URL = '/user/tag/get?';
    /* 批量为粉丝打标签 */
    const TAGS_MEMBER_BATCHTAGGING = '/tags/members/batchtagging?';
    /* 批量为粉丝取消标签 */
    const TAGS_MEMBER_BATCHUNTAGGING = '/tags/members/batchuntagging?';
    /* 获取粉丝的标签列表 */
    const TAGS_LIST = '/tags/getidlist?';

    /** 获取分组列表 */
    const GROUP_GET_URL = '/groups/get?';
    /* 获取粉丝所在的分组 */
    const USER_GROUP_URL = '/groups/getid?';
    /* 创建分组 */
    const GROUP_CREATE_URL = '/groups/create?';
    /* 更新分组 */
    const GROUP_UPDATE_URL = '/groups/update?';
    /* 删除分组 */
    const GROUP_DELETE_URL = '/groups/delete?';
    /* 修改粉丝所在分组 */
    const GROUP_MEMBER_UPDATE_URL = '/groups/members/update?';
    /* 批量修改粉丝所在分组 */
    const GROUP_MEMBER_BATCHUPDATE_URL = '/groups/members/batchupdate?';

    /** 获取黑名单列表 */
    const BACKLIST_GET_URL = '/tags/members/getblacklist?';
    /* 批量拉黑粉丝 */
    const BACKLIST_ADD_URL = '/tags/members/batchblacklist?';
    /* 批量取消拉黑粉丝 */
    const BACKLIST_DEL_URL = '/tags/members/batchunblacklist?';

    /**
     * 批量获取关注粉丝列表
     * @param type $next_openid
     * @return boolean
     */
    public function getUserList($next_openid = '') {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = $this->http_get(self::API_URL_PREFIX . self::USER_GET_URL . "access_token={$this->access_token}" . '&next_openid=' . $next_openid);
        if ($result) {
            $json = json_decode($result, true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

    /**
     * 获取关注者详细信息
     * @param string $openid
     * @return array {subscribe,openid,nickname,sex,city,province,country,language,headimgurl,subscribe_time,[unionid]}
     * @注意：unionid字段 只有在粉丝将公众号绑定到微信开放平台账号后，才会出现。建议调用前用isset()检测一下
     */
    public function getUserInfo($openid) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = $this->http_get(self::API_URL_PREFIX . self::USER_INFO_URL . "access_token={$this->access_token}&openid={$openid}");
        if ($result) {
            $json = json_decode($result, true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

    /**
     * 设置粉丝备注名
     * @param string $openid
     * @param string $remark 备注名
     * @return boolean|array
     */
    public function updateUserRemark($openid, $remark) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array('openid' => $openid, 'remark' => $remark);
        $result = $this->http_post(self::API_URL_PREFIX . self::USER_UPDATEREMARK_URL . "access_token={$this->access_token}", self::json_encode($data));
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

    /**
     * 获取粉丝分组列表
     * @return boolean|array
     */
    public function getGroup() {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = $this->http_get(self::API_URL_PREFIX . self::GROUP_GET_URL . "access_token={$this->access_token}");
        if ($result) {
            $json = json_decode($result, true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

    /**
     * 删除粉丝分组
     * @param type $id
     * @return boolean
     */
    public function delGroup($id) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array('group' => array('id' => $id));
        $result = $this->http_post(self::API_URL_PREFIX . self::GROUP_DELETE_URL . "access_token={$this->access_token}", self::json_encode($data));
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

    /**
     * 获取粉丝所在分组
     * @param string $openid
     * @return boolean|int 成功则返回粉丝分组id
     */
    public function getUserGroup($openid) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array('openid' => $openid);
        $result = $this->http_post(self::API_URL_PREFIX . self::USER_GROUP_URL . "access_token={$this->access_token}", self::json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            } else if (isset($json['groupid'])) {
                return $json['groupid'];
            }
        }
        return false;
    }

    /**
     * 新增自定分组
     * @param string $name 分组名称
     * @return boolean|array
     */
    public function createGroup($name) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array('group' => array('name' => $name));
        $result = $this->http_post(self::API_URL_PREFIX . self::GROUP_CREATE_URL . "access_token={$this->access_token}", self::json_encode($data));
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

    /**
     * 更改分组名称
     * @param int $groupid 分组id
     * @param string $name 分组名称
     * @return boolean|array
     */
    public function updateGroup($groupid, $name) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array('group' => array('id' => $groupid, 'name' => $name));
        $result = $this->http_post(self::API_URL_PREFIX . self::GROUP_UPDATE_URL . "access_token={$this->access_token}", self::json_encode($data));
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

    /**
     * 移动粉丝分组
     * @param int $groupid 分组id
     * @param string $openid 粉丝openid
     * @return boolean|array
     */
    public function updateGroupMembers($groupid, $openid) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array('openid' => $openid, 'to_groupid' => $groupid);
        $result = $this->http_post(self::API_URL_PREFIX . self::GROUP_MEMBER_UPDATE_URL . "access_token={$this->access_token}", self::json_encode($data));
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

    /**
     * 批量移动粉丝分组
     * @param type $groupid 分组ID
     * @param type $openid_list 粉丝openid数组(一次不能超过50个)
     * @return boolean|array
     */
    public function batchUpdateGroupMembers($groupid, $openid_list) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array('openid_list' => $openid_list, 'to_groupid' => $groupid);
        $result = $this->http_post(self::API_URL_PREFIX . self::GROUP_MEMBER_BATCHUPDATE_URL . "access_token={$this->access_token}", self::json_encode($data));
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

    /**
     * 新增自定标签
     * @param string $name 标签名称
     * @return boolean|array
     */
    public function createTags($name) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array('tag' => array('name' => $name));
        $result = $this->http_post(self::API_URL_PREFIX . self::TAGS_CREATE_URL . "access_token={$this->access_token}", self::json_encode($data));
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

    /**
     *  更新标签
     * @param type $id 标签id
     * @param type $name 标签名称
     * @return boolean|array
     */
    public function updateTag($id, $name) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array('tag' => array('id' => $id, 'name' => $name));
        $result = $this->http_post(self::API_URL_PREFIX . self::TAGS_UPDATE_URL . "access_token={$this->access_token}", self::json_encode($data));
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

    /**
     * 获取粉丝标签列表
     * @return boolean|array
     */
    public function getTags() {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = $this->http_get(self::API_URL_PREFIX . self::TAGS_GET_URL . "access_token={$this->access_token}");
        if ($result) {
            $json = json_decode($result, true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

    /**
     * 删除粉丝标签
     * @param type $id
     * @return boolean
     */
    public function delTag($id) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array('tag' => array('id' => $id));
        $result = $this->http_post(self::API_URL_PREFIX . self::TAGS_DELETE_URL . "access_token={$this->access_token}", self::json_encode($data));
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

    /**
     * 获取标签下的粉丝列表
     * @param type $tagid
     * @param type $next_openid
     * @return boolean
     */
    public function getTagUsers($tagid, $next_openid = '') {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array('tagid' => $tagid, 'next_openid' => $next_openid);
        $result = $this->http_post(self::API_URL_PREFIX . self::TAGS_GET_USER_URL . "access_token={$this->access_token}", self::json_encode($data));
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

    /**
     *  批量为粉丝打标签
     * @param type $tagid 标签ID
     * @param type $openid_list 粉丝openid数组，一次不能超过50个
     * @return boolean|array
     */
    public function batchAddUserTag($tagid, $openid_list) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array('openid_list' => $openid_list, 'tagid' => $tagid);
        $result = $this->http_post(self::API_URL_PREFIX . self::TAGS_MEMBER_BATCHTAGGING . "access_token={$this->access_token}", self::json_encode($data));
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

    /**
     *  批量为粉丝取消标签
     * @param type $tagid 标签ID
     * @param type $openid_list 粉丝openid数组，一次不能超过50个
     * @return boolean|array
     */
    public function batchDeleteUserTag($tagid, $openid_list) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array('openid_list' => $openid_list, 'tagid' => $tagid);
        $result = $this->http_post(self::API_URL_PREFIX . self::TAGS_MEMBER_BATCHUNTAGGING . "access_token={$this->access_token}", self::json_encode($data));
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

    /**
     *  获取粉丝的标签列表
     * @param type $openid 粉丝openid
     * @return boolean|array
     */
    public function getUserTags($openid) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array('openid' => $openid);
        $result = $this->http_post(self::API_URL_PREFIX . self::TAGS_LIST . "access_token={$this->access_token}", self::json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || !isset($json['tagid_list']) || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json['tagid_list'];
        }
        return false;
    }

    /**
     * 批量获取黑名单粉丝
     * @param type $begin_openid
     * @return boolean
     */
    public function getBacklist($begin_openid = '') {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = empty($begin_openid) ? array() : array('begin_openid' => $begin_openid);
        $result = $this->http_post(self::API_URL_PREFIX . self::BACKLIST_GET_URL . "access_token={$this->access_token}", self::json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

    /**
     * 批量拉黑粉丝
     * @param string $openids
     * @return boolean|array
     */
    public function addBacklist($openids) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array('opened_list' => $openids);
        $result = $this->http_post(self::API_URL_PREFIX . self::BACKLIST_ADD_URL . "access_token={$this->access_token}", self::json_encode($data));
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

    /**
     * 批量取消拉黑粉丝
     * @param string $openids
     * @return boolean|array
     */
    public function delBacklist($openids) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array('opened_list' => $openids);
        $result = $this->http_post(self::API_URL_PREFIX . self::BACKLIST_DEL_URL . "access_token={$this->access_token}", self::json_encode($data));
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
