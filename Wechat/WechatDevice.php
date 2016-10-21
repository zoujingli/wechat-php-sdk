<?php

namespace Wechat;

use Wechat\Lib\WechatCommon;
 
/**
 * 微信设备相关SDK
 * @author Anyon <zoujingli@qq.com>
 * @date 2016-08-22 10:35
 */
class WechatDevice extends WechatCommon {

    const SHAKEAROUND_DEVICE_APPLYID = '/shakearound/device/applyid?'; //申请设备ID
    const SHAKEAROUND_DEVICE_UPDATE = '/shakearound/device/update?'; //编辑设备信息
    const SHAKEAROUND_DEVICE_SEARCH = '/shakearound/device/search?'; //查询设备列表
    const SHAKEAROUND_DEVICE_BINDLOCATION = '/shakearound/device/bindlocation?'; //配置设备与门店ID的关系
    const SHAKEAROUND_DEVICE_BINDPAGE = '/shakearound/device/bindpage?'; //配置设备与页面的绑定关系
    const SHAKEAROUND_MATERIAL_ADD = '/shakearound/material/add?'; //上传摇一摇图片素材
    const SHAKEAROUND_PAGE_ADD = '/shakearound/page/add?'; //增加页面
    const SHAKEAROUND_PAGE_UPDATE = '/shakearound/page/update?'; //编辑页面
    const SHAKEAROUND_PAGE_SEARCH = '/shakearound/page/search?'; //查询页面列表
    const SHAKEAROUND_PAGE_DELETE = '/shakearound/page/delete?'; //删除页面
    const SHAKEAROUND_USER_GETSHAKEINFO = '/shakearound/user/getshakeinfo?'; //获取摇周边的设备及用户信息
    const SHAKEAROUND_STATISTICS_DEVICE = '/shakearound/statistics/device?'; //以设备为维度的数据统计接口
    const SHAKEAROUND_STATISTICS_PAGE = '/shakearound/statistics/page?'; //以页面为维度的数据统计接口

    /**
     * 申请设备ID
     * [applyShakeAroundDevice 申请配置设备所需的UUID、Major、Minor。
     * 若激活率小于50%，不能新增设备。单次新增设备超过500 个，需走人工审核流程。
     * 审核通过后，可用迒回的批次ID 用“查询设备列表”接口拉取本次申请的设备ID]
     * @param array $data
     * array(
     *      "quantity" => 3,         //申请的设备ID 的数量，单次新增设备超过500 个,需走人工审核流程(必填)
     *      "apply_reason" => "测试",//申请理由(必填)
     *      "comment" => "测试专用", //备注(非必填)
     *      "poi_id" => 1234         //设备关联的门店ID(非必填)
     * )
     * @return boolean|mixed
     * {
      "data": {
      "apply_id": 123,
      "device_identifiers":[
      {
      "device_id":10100,
      "uuid":"FDA50693-A4E2-4FB1-AFCF-C6EB07647825",
      "major":10001,
      "minor":10002
      }
      ]
      },
      "errcode": 0,
      "errmsg": "success."
      }
      apply_id:申请的批次ID，可用在“查询设备列表”接口按批次查询本次申请成功的设备ID
      device_identifiers:指定的设备ID 列表
      device_id:设备编号
      uuid、major、minor
      audit_status:审核状态。0：审核未通过、1：审核中、2：审核已通过；审核会在三个工作日内完成
      audit_comment:审核备注，包括审核不通过的原因
     * @access public
     * @author polo<gao.bo168@gmail.com>
     * @version 2015-3-25 下午1:24:06
     * @copyright Show More
     */

    public function applyShakeAroundDevice($data) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = $this->http_post(self::API_BASE_URL_PREFIX . self::SHAKEAROUND_DEVICE_APPLYID . "access_token={$this->access_token}", self::json_encode($data));
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
     * 编辑设备信息
     * [updateShakeAroundDevice 编辑设备的备注信息。可用设备ID或完整的UUID、Major、Minor指定设备，二者选其一。]
     * @param array $data
     * array(
     *      "device_identifier" => array(
     *          		"device_id" => 10011,   //当提供了device_id则不需要使用uuid、major、minor，反之亦然
     *          		"uuid" => "FDA50693-A4E2-4FB1-AFCF-C6EB07647825",
     *          		"major" => 1002,
     *          		"minor" => 1223
     *      ),
     *      "comment" => "测试专用", //备注(非必填)
     * )
     * {
      "data": {
      },
      "errcode": 0,
      "errmsg": "success."
      }
     * @return boolean
     * @author binsee<binsee@163.com>
     * @version 2015-4-20 23:45:00
     */
    public function updateShakeAroundDevice($data) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = $this->http_post(self::API_BASE_URL_PREFIX . self::SHAKEAROUND_DEVICE_UPDATE . "access_token={$this->access_token}", self::json_encode($data));
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
     * 查询设备列表
     * [searchShakeAroundDevice 查询已有的设备ID、UUID、Major、Minor、激活状态、备注信息、关联门店、关联页面等信息。
     * 可指定设备ID 或完整的UUID、Major、Minor 查询，也可批量拉取设备信息列表。]
     * @param array $data
     * $data 三种格式:
     * ①查询指定设备时：$data = array(
     *                              "device_identifiers" => array(
     *                                                          array(
     *                                                              "device_id" => 10100,
     *                                                              "uuid" => "FDA50693-A4E2-4FB1-AFCF-C6EB07647825",
     *                                                              "major" => 10001,
     *                                                              "minor" => 10002
     *                                                          )
     *                                                      )
     *                              );
     * device_identifiers:指定的设备
     * device_id:设备编号，若填了UUID、major、minor，则可不填设备编号，若二者都填，则以设备编号为优先
     * uuid、major、minor:三个信息需填写完整，若填了设备编号，则可不填此信息
     * +-------------------------------------------------------------------------------------------------------------
     * ②需要分页查询或者指定范围内的设备时: $data = array(
     *                                                  "begin" => 0,
     *                                                  "count" => 3
     *                                               );
     * begin:设备列表的起始索引值
     * count:待查询的设备个数
     * +-------------------------------------------------------------------------------------------------------------
     * ③当需要根据批次ID 查询时: $data = array(
     *                                      "apply_id" => 1231,
     *                                      "begin" => 0,
     *                                      "count" => 3
     *                                    );
     * apply_id:批次ID
     * +-------------------------------------------------------------------------------------------------------------
     * @return boolean|mixed
     * 正确迒回JSON 数据示例：
     * 字段说明
      {
      "data": {
      "devices": [          //指定的设备信息列表
      {
      "comment": "", //设备的备注信息
      "device_id": 10097, //设备编号
      "major": 10001,
      "minor": 12102,
      "page_ids": "15369", //与此设备关联的页面ID 列表，用逗号隔开
      "status": 1, //激活状态，0：未激活，1：已激活（但不活跃），2：活跃
      "poi_id": 0, //门店ID
      "uuid": "FDA50693-A4E2-4FB1-AFCF-C6EB07647825"
      },
      {
      "comment": "", //设备的备注信息
      "device_id": 10098, //设备编号
      "major": 10001,
      "minor": 12103,
      "page_ids": "15368", //与此设备关联的页面ID 列表，用逗号隔开
      "status": 1, //激活状态，0：未激活，1：已激活（但不活跃），2：活跃
      "poi_id": 0, //门店ID
      "uuid": "FDA50693-A4E2-4FB1-AFCF-C6EB07647825"
      }
      ],
      "total_count": 151 //商户名下的设备总量
      },
      "errcode": 0,
      "errmsg": "success."
      }
     * @access public
     * @author polo<gao.bo168@gmail.com>
     * @version 2015-3-25 下午1:45:42
     * @copyright Show More
     */
    public function searchShakeAroundDevice($data) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = $this->http_post(self::API_BASE_URL_PREFIX . self::SHAKEAROUND_DEVICE_SEARCH . "access_token={$this->access_token}", self::json_encode($data));
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
     * [bindLocationShakeAroundDevice 配置设备与门店的关联关系]
     * @param string $device_id 设备编号，若填了UUID、major、minor，则可不填设备编号，若二者都填，则以设备编号为优先
     * @param int $poi_id 待关联的门店ID
     * @param string $uuid UUID、major、minor，三个信息需填写完整，若填了设备编号，则可不填此信息
     * @param int $major
     * @param int $minor
     * @return boolean|mixed
     * 正确返回JSON 数据示例:
     * {
      "data": {
      },
      "errcode": 0,
      "errmsg": "success."
      }
     * @access public
     * @author polo<gao.bo168@gmail.com>
     * @version 2015-4-21 00:14:00
     * @copyright Show More
     */
    public function bindLocationShakeAroundDevice($device_id, $poi_id, $uuid = '', $major = 0, $minor = 0) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        if (!$device_id) {
            if (!$uuid || !$major || !$minor) {
                return false;
            }
            $device_identifier = array(
                'uuid'  => $uuid,
                'major' => $major,
                'minor' => $minor
            );
        } else {
            $device_identifier = array(
                'device_id' => $device_id
            );
        }
        $data = array(
            'device_identifier' => $device_identifier,
            'poi_id'            => $poi_id
        );
        $result = $this->http_post(self::API_BASE_URL_PREFIX . self::SHAKEAROUND_DEVICE_BINDLOCATION . "access_token={$this->access_token}", self::json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json; //这个可以更改为返回true
        }
        return false;
    }

    /**
     * [bindPageShakeAroundDevice 配置设备与页面的关联关系。
     * 支持建立或解除关联关系，也支持新增页面或覆盖页面等操作。
     * 配置完成后，在此设备的信号范围内，即可摇出关联的页面信息。
     * 若设备配置多个页面，则随机出现页面信息]
     * @param string $device_id 设备编号，若填了UUID、major、minor，则可不填设备编号，若二者都填，则以设备编号为优先
     * @param array $page_ids 待关联的页面列表
     * @param number $bind 关联操作标志位， 0 为解除关联关系，1 为建立关联关系
     * @param number $append 新增操作标志位， 0 为覆盖，1 为新增
     * @param string $uuid UUID、major、minor，三个信息需填写完整，若填了设备编号，则可不填此信息
     * @param int $major
     * @param int $minor
     * @return boolean|mixed
     * 正确返回JSON 数据示例:
     * {
      "data": {
      },
      "errcode": 0,
      "errmsg": "success."
      }
     * @access public
     * @author polo<gao.bo168@gmail.com>
     * @version 2015-4-21 00:31:00
     * @copyright Show More
     */
    public function bindPageShakeAroundDevice($device_id, $page_ids = array(), $bind = 1, $append = 1, $uuid = '', $major = 0, $minor = 0) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        if (!$device_id) {
            if (!$uuid || !$major || !$minor) {
                return false;
            }
            $device_identifier = array(
                'uuid'  => $uuid,
                'major' => $major,
                'minor' => $minor
            );
        } else {
            $device_identifier = array(
                'device_id' => $device_id
            );
        }
        $data = array(
            'device_identifier' => $device_identifier,
            'page_ids'          => $page_ids,
            'bind'              => $bind,
            'append'            => $append
        );
        $result = $this->http_post(self::API_BASE_URL_PREFIX . self::SHAKEAROUND_DEVICE_BINDPAGE . "access_token={$this->access_token}", self::json_encode($data));
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
     * 上传在摇一摇页面展示的图片素材
     * 注意：数组的键值任意，但文件名前必须加@，使用单引号以避免本地路径斜杠被转义
     * @param array $data {"media":'@Path\filename.jpg'} 格式限定为：jpg,jpeg,png,gif，图片大小建议120px*120 px，限制不超过200 px *200 px，图片需为正方形。
     * @return boolean|array
     * {
      "data": {
      "pic_url":"http://shp.qpic.cn/wechat_shakearound_pic/0/1428377032e9dd2797018cad79186e03e8c5aec8dc/120"
      },
      "errcode": 0,
      "errmsg": "success."
      }
      }
     * @author binsee<binsee@163.com>
     * @version 2015-4-21 00:51:00
     */
    public function uploadShakeAroundMedia($data) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = $this->http_post(self::API_URL_PREFIX . self::SHAKEAROUND_MATERIAL_ADD . "access_token={$this->access_token}", $data, true);
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
     * [addShakeAroundPage 增加摇一摇出来的页面信息，包括在摇一摇页面出现的主标题、副标题、图片和点击进去的超链接。]
     * @param string $title 在摇一摇页面展示的主标题，不超过6 个字
     * @param string $description 在摇一摇页面展示的副标题，不超过7 个字
     * @param sting $icon_url 在摇一摇页面展示的图片， 格式限定为：jpg,jpeg,png,gif; 建议120*120 ， 限制不超过200*200
     * @param string $page_url 跳转链接
     * @param string $comment 页面的备注信息，不超过15 个字,可不填
     * @return boolean|mixed
     * 正确返回JSON 数据示例:
     * {
      "data": {
      "page_id": 28840 //新增页面的页面id
      }
      "errcode": 0,
      "errmsg": "success."
      }
     * @access public
     * @author polo<gao.bo168@gmail.com>
     * @version 2015-3-25 下午2:57:09
     * @copyright Show More
     */
    public function addShakeAroundPage($title, $description, $icon_url, $page_url, $comment = '') {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array(
            "title"       => $title,
            "description" => $description,
            "icon_url"    => $icon_url,
            "page_url"    => $page_url,
            "comment"     => $comment
        );
        $result = $this->http_post(self::API_BASE_URL_PREFIX . self::SHAKEAROUND_PAGE_ADD . "access_token={$this->access_token}", self::json_encode($data));
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
     * [updateShakeAroundPage 编辑摇一摇出来的页面信息，包括在摇一摇页面出现的主标题、副标题、图片和点击进去的超链接。]
     * @param int $page_id
     * @param string $title 在摇一摇页面展示的主标题，不超过6 个字
     * @param string $description 在摇一摇页面展示的副标题，不超过7 个字
     * @param sting $icon_url 在摇一摇页面展示的图片， 格式限定为：jpg,jpeg,png,gif; 建议120*120 ， 限制不超过200*200
     * @param string $page_url 跳转链接
     * @param string $comment 页面的备注信息，不超过15 个字,可不填
     * @return boolean|mixed
     * 正确返回JSON 数据示例:
     * {
      "data": {
      "page_id": 28840 //编辑页面的页面ID
      }
      "errcode": 0,
      "errmsg": "success."
      }
     * @access public
     * @author polo<gao.bo168@gmail.com>
     * @version 2015-3-25 下午3:02:51
     * @copyright Show More
     */
    public function updateShakeAroundPage($page_id, $title, $description, $icon_url, $page_url, $comment = '') {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array(
            "page_id"     => $page_id,
            "title"       => $title,
            "description" => $description,
            "icon_url"    => $icon_url,
            "page_url"    => $page_url,
            "comment"     => $comment
        );
        $result = $this->http_post(self::API_BASE_URL_PREFIX . self::SHAKEAROUND_PAGE_UPDATE . "access_token={$this->access_token}", self::json_encode($data));
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
     * [searchShakeAroundPage 查询已有的页面，包括在摇一摇页面出现的主标题、副标题、图片和点击进去的超链接。
     * 提供两种查询方式，①可指定页面ID 查询，②也可批量拉取页面列表。]
     * @param array $page_ids
     * @param int $begin
     * @param int $count
     * ①需要查询指定页面时:
     * {
      "page_ids":[12345, 23456, 34567]
      }
     * +-------------------------------------------------------------------------------------------------------------
     * ②需要分页查询或者指定范围内的页面时:
     * {
      "begin": 0,
      "count": 3
      }
     * +-------------------------------------------------------------------------------------------------------------
     * @return boolean|mixed
     * 正确返回JSON 数据示例:
      {
      "data": {
      "pages": [
      {
      "comment": "just for test",
      "description": "test",
      "icon_url": "https://www.baidu.com/img/bd_logo1.png",
      "page_id": 28840,
      "page_url": "http://xw.qq.com/testapi1",
      "title": "测试1"
      },
      {
      "comment": "just for test",
      "description": "test",
      "icon_url": "https://www.baidu.com/img/bd_logo1.png",
      "page_id": 28842,
      "page_url": "http://xw.qq.com/testapi2",
      "title": "测试2"
      }
      ],
      "total_count": 2
      },
      "errcode": 0,
      "errmsg": "success."
      }
     * 字段说明:
     * total_count 商户名下的页面总数
     * page_id 摇周边页面唯一ID
     * title 在摇一摇页面展示的主标题
     * description 在摇一摇页面展示的副标题
     * icon_url 在摇一摇页面展示的图片
     * page_url 跳转链接
     * comment 页面的备注信息
     * @access public
     * @author polo<gao.bo168@gmail.com>
     * @version 2015-3-25 下午3:12:17
     * @copyright Show More
     */
    public function searchShakeAroundPage($page_ids = array(), $begin = 0, $count = 1) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        if (!empty($page_ids)) {
            $data = array(
                'page_ids' => $page_ids
            );
        } else {
            $data = array(
                'begin' => $begin,
                'count' => $count
            );
        }
        $result = $this->http_post(self::API_BASE_URL_PREFIX . self::SHAKEAROUND_PAGE_SEARCH . "access_token={$this->access_token}", self::json_encode($data));
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
     * [deleteShakeAroundPage 删除已有的页面，包括在摇一摇页面出现的主标题、副标题、图片和点击进去的超链接。
     * 只有页面与设备没有关联关系时，才可被删除。]
     * @param array $page_ids
     * {
      "page_ids":[12345,23456,34567]
      }
     * @return boolean|mixed
     * 正确返回JSON 数据示例:
     * {
      "data": {
      },
      "errcode": 0,
      "errmsg": "success."
      }
     * @access public
     * @author polo<gao.bo168@gmail.com>
     * @version 2015-3-25 下午3:23:00
     * @copyright Show More
     */
    public function deleteShakeAroundPage($page_ids = array()) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array(
            'page_ids' => $page_ids
        );
        $result = $this->http_post(self::API_BASE_URL_PREFIX . self::SHAKEAROUND_PAGE_DELETE . "access_token={$this->access_token}", self::json_encode($data));
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
     * [getShakeInfoShakeAroundUser 获取设备信息，包括UUID、major、minor，以及距离、openID 等信息。]
     * @param string $ticket 摇周边业务的ticket，可在摇到的URL 中得到，ticket生效时间为30 分钟
     * @return boolean|mixed
     * 正确返回JSON 数据示例:
     * {
      "data": {
      "page_id ": 14211,
      "beacon_info": {
      "distance": 55.00620700469034,
      "major": 10001,
      "minor": 19007,
      "uuid": "FDA50693-A4E2-4FB1-AFCF-C6EB07647825"
      },
      "openid": "oVDmXjp7y8aG2AlBuRpMZTb1-cmA"
      },
      "errcode": 0,
      "errmsg": "success."
      }
     * 字段说明:
     * beacon_info 设备信息，包括UUID、major、minor，以及距离
     * UUID、major、minor UUID、major、minor
     * distance Beacon 信号与手机的距离
     * page_id 摇周边页面唯一ID
     * openid 商户AppID 下用户的唯一标识
     * poi_id 门店ID，有的话则返回，没有的话不会在JSON 格式内
     * @access public
     * @author polo<gao.bo168@gmail.com>
     * @version 2015-3-25 下午3:28:20
     * @copyright Show More
     */
    public function getShakeInfoShakeAroundUser($ticket) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array('ticket' => $ticket);
        $result = $this->http_post(self::API_BASE_URL_PREFIX . self::SHAKEAROUND_USER_GETSHAKEINFO . "access_token={$this->access_token}", self::json_encode($data));
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
     * [deviceShakeAroundStatistics 以设备为维度的数据统计接口。
     * 查询单个设备进行摇周边操作的人数、次数，点击摇周边消息的人数、次数；查询的最长时间跨度为30天。]
     * @param int $device_id 设备编号，若填了UUID、major、minor，即可不填设备编号，二者选其一
     * @param int $begin_date 起始日期时间戳，最长时间跨度为30 天
     * @param int $end_date 结束日期时间戳，最长时间跨度为30 天
     * @param string $uuid UUID、major、minor，三个信息需填写完成，若填了设备编辑，即可不填此信息，二者选其一
     * @param int $major
     * @param int $minor
     * @return boolean|mixed
     * 正确返回JSON 数据示例:
     * {
      "data": [
      {
      "click_pv": 0,
      "click_uv": 0,
      "ftime": 1425052800,
      "shake_pv": 0,
      "shake_uv": 0
      },
      {
      "click_pv": 0,
      "click_uv": 0,
      "ftime": 1425139200,
      "shake_pv": 0,
      "shake_uv": 0
      }
      ],
      "errcode": 0,
      "errmsg": "success."
      }
     * 字段说明:
     * ftime 当天0 点对应的时间戳
     * click_pv 点击摇周边消息的次数
     * click_uv 点击摇周边消息的人数
     * shake_pv 摇周边的次数
     * shake_uv 摇周边的人数
     * @access public
     * @author polo<gao.bo168@gmail.com>
     * @version 2015-4-21 00:39:00
     * @copyright Show More
     */
    public function deviceShakeAroundStatistics($device_id, $begin_date, $end_date, $uuid = '', $major = 0, $minor = 0) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        if (!$device_id) {
            if (!$uuid || !$major || !$minor) {
                return false;
            }
            $device_identifier = array(
                'uuid'  => $uuid,
                'major' => $major,
                'minor' => $minor
            );
        } else {
            $device_identifier = array(
                'device_id' => $device_id
            );
        }
        $data = array(
            'device_identifier' => $device_identifier,
            'begin_date'        => $begin_date,
            'end_date'          => $end_date
        );
        $result = $this->http_post(self::API_BASE_URL_PREFIX . self::SHAKEAROUND_STATISTICS_DEVICE . "access_token={$this->access_token}", self::json_encode($data));
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
     * [pageShakeAroundStatistics 以页面为维度的数据统计接口。
     * 查询单个页面通过摇周边摇出来的人数、次数，点击摇周边页面的人数、次数；查询的最长时间跨度为30天。]
     * @param int $page_id 指定页面的ID
     * @param int $begin_date 起始日期时间戳，最长时间跨度为30 天
     * @param int $end_date 结束日期时间戳，最长时间跨度为30 天
     * @return boolean|mixed
     * 正确返回JSON 数据示例:
     * {
      "data": [
      {
      "click_pv": 0,
      "click_uv": 0,
      "ftime": 1425052800,
      "shake_pv": 0,
      "shake_uv": 0
      },
      {
      "click_pv": 0,
      "click_uv": 0,
      "ftime": 1425139200,
      "shake_pv": 0,
      "shake_uv": 0
      }
      ],
      "errcode": 0,
      "errmsg": "success."
      }
     * 字段说明:
     * ftime 当天0 点对应的时间戳
     * click_pv 点击摇周边消息的次数
     * click_uv 点击摇周边消息的人数
     * shake_pv 摇周边的次数
     * shake_uv 摇周边的人数
     * @author binsee<binsee@163.com>
     * @version 2015-4-21 00:43:00
     */
    public function pageShakeAroundStatistics($page_id, $begin_date, $end_date) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $data = array(
            'page_id'    => $page_id,
            'begin_date' => $begin_date,
            'end_date'   => $end_date
        );
        $result = $this->http_post(self::API_BASE_URL_PREFIX . self::SHAKEAROUND_STATISTICS_DEVICE . "access_token={$this->access_token}", self::json_encode($data));
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
