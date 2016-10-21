<?php

namespace Wechat;

use Prpcrypt;
use Wechat\Lib\WechatCommon; 

/**
 * 微信消息对象解析SDK
 * 
 * @author Anyon <zoujingli@qq.com>
 * @date 2016/06/28 11:29
 */
class WechatReceive extends WechatCommon {

    /** 消息推送地址 */
    const CUSTOM_SEND_URL = '/message/custom/send?';
    const MASS_SEND_URL = '/message/mass/send?';
    const TEMPLATE_SET_INDUSTRY_URL = '/message/template/api_set_industry?';
    const TEMPLATE_ADD_TPL_URL = '/message/template/api_add_template?';
    const TEMPLATE_SEND_URL = '/message/template/send?';
    const MASS_SEND_GROUP_URL = '/message/mass/sendall?';
    const MASS_DELETE_URL = '/message/mass/delete?';
    const MASS_PREVIEW_URL = '/message/mass/preview?';
    const MASS_QUERY_URL = '/message/mass/get?';

    /** 消息回复类型 */
    const MSGTYPE_TEXT = 'text';
    const MSGTYPE_IMAGE = 'image';
    const MSGTYPE_LOCATION = 'location';
    const MSGTYPE_LINK = 'link';
    const MSGTYPE_EVENT = 'event';
    const MSGTYPE_MUSIC = 'music';
    const MSGTYPE_NEWS = 'news';
    const MSGTYPE_VOICE = 'voice';
    const MSGTYPE_VIDEO = 'video';

    /** 文件过滤 */
    protected $_text_filter = true;

    /** 消息对象 */
    private $_receive;

    /**
     * 获取微信服务器发来的内容
     * @return \WechatReceive
     */
    public function getRev() {
        if ($this->_receive) {
            return $this;
        }
        $postStr = !empty($this->postxml) ? $this->postxml : file_get_contents("php://input");
        !empty($postStr) && $this->_receive = (array) simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        return $this;
    }

    /**
     * 获取微信服务器发来的信息数据
     * @return type
     */
    public function getRevData() {
        return $this->_receive;
    }

    /**
     * 获取消息发送者
     * @return boolean|string
     */
    public function getRevFrom() {
        if (isset($this->_receive['FromUserName'])) {
            return $this->_receive['FromUserName'];
        } else {
            return false;
        }
    }

    /**
     * 获取消息接受者
     * @return boolean|string
     */
    public function getRevTo() {
        if (isset($this->_receive['ToUserName'])) {
            return $this->_receive['ToUserName'];
        } else {
            return false;
        }
    }

    /**
     * 获取接收消息的类型
     * @return boolean|string
     */
    public function getRevType() {
        if (isset($this->_receive['MsgType'])) {
            return $this->_receive['MsgType'];
        } else {
            return false;
        }
    }

    /**
     * 获取消息ID
     * @return boolean
     */
    public function getRevID() {
        if (isset($this->_receive['MsgId'])) {
            return $this->_receive['MsgId'];
        } else {
            return false;
        }
    }

    /**
     * 获取消息发送时间
     */
    public function getRevCtime() {
        if (isset($this->_receive['CreateTime'])) {
            return $this->_receive['CreateTime'];
        } else {
            return false;
        }
    }

    /**
     * 获取卡券事件推送 - 卡卷审核是否通过
     * 当Event为 card_pass_check(审核通过) 或 card_not_pass_check(未通过)
     * @return string|boolean  返回卡券ID
     */
    public function getRevCardPass() {
        if (isset($this->_receive['CardId'])) {
            return $this->_receive['CardId'];
        } else {
            return false;
        }
    }

    /**
     * 获取卡券事件推送 - 领取卡券
     * 当Event为 user_get_card(用户领取卡券)
     * @return array|boolean
     */
    public function getRevCardGet() {
        $array = array();
        if (isset($this->_receive['CardId'])) {
            /* 卡券 ID */
            $array['CardId'] = $this->_receive['CardId'];
        }
        if (isset($this->_receive['IsGiveByFriend'])) {
            /* 是否为转赠，1 代表是，0 代表否。 */
            $array['IsGiveByFriend'] = $this->_receive['IsGiveByFriend'];
        }
        $array['OldUserCardCode'] = $this->_receive['OldUserCardCode'];
        if (isset($this->_receive['UserCardCode']) && !empty($this->_receive['UserCardCode'])) {
            /* code 序列号。自定义 code 及非自定义 code的卡券被领取后都支持事件推送。 */
            $array['UserCardCode'] = $this->_receive['UserCardCode'];
        }
        if (isset($array) && count($array) > 0) {
            return $array;
        } else {
            return false;
        }
    }

    /**
     * 获取卡券事件推送 - 删除卡券
     * 当Event为 user_del_card(用户删除卡券)
     * @return array|boolean
     */
    public function getRevCardDel() {
        if (isset($this->_receive['CardId'])) {  //卡券 ID
            $array['CardId'] = $this->_receive['CardId'];
        }
        if (isset($this->_receive['UserCardCode']) && !empty($this->_receive['UserCardCode'])) {
            /* code 序列号。自定义 code 及非自定义 code的卡券被领取后都支持事件推送 */
            $array['UserCardCode'] = $this->_receive['UserCardCode'];
        }
        if (isset($array) && count($array) > 0) {
            return $array;
        } else {
            return false;
        }
    }

    /**
     * 获取接收消息内容正文
     */
    public function getRevContent() {
        if (isset($this->_receive['Content'])) {
            return $this->_receive['Content'];
        } else if (isset($this->_receive['Recognition'])) { //获取语音识别文字内容，需申请开通
            return $this->_receive['Recognition'];
        } else {
            return false;
        }
    }

    /**
     * 获取接收消息图片
     */
    public function getRevPic() {
        if (isset($this->_receive['PicUrl'])) {
            return array(
                'mediaid' => $this->_receive['MediaId'],
                'picurl'  => (string) $this->_receive['PicUrl'], //防止picurl为空导致解析出错
            );
        } else {
            return false;
        }
    }

    /**
     * 获取接收消息链接
     */
    public function getRevLink() {
        if (isset($this->_receive['Url'])) {
            return array(
                'url'         => $this->_receive['Url'],
                'title'       => $this->_receive['Title'],
                'description' => $this->_receive['Description']
            );
        } else {
            return false;
        }
    }

    /**
     * 获取接收地理位置
     */
    public function getRevGeo() {
        if (isset($this->_receive['Location_X'])) {
            return array(
                'x'     => $this->_receive['Location_X'],
                'y'     => $this->_receive['Location_Y'],
                'scale' => $this->_receive['Scale'],
                'label' => $this->_receive['Label']
            );
        } else {
            return false;
        }
    }

    /**
     * 获取上报地理位置事件
     */
    public function getRevEventGeo() {
        if (isset($this->_receive['Latitude'])) {
            return array(
                'x'         => $this->_receive['Latitude'],
                'y'         => $this->_receive['Longitude'],
                'precision' => $this->_receive['Precision'],
            );
        }
        return false;
    }

    /**
     * 获取接收事件推送
     */
    public function getRevEvent() {
        if (isset($this->_receive['Event'])) {
            $array['event'] = $this->_receive['Event'];
        }
        if (isset($this->_receive['EventKey'])) {
            $array['key'] = $this->_receive['EventKey'];
        }
        if (isset($array) && count($array) > 0) {
            return $array;
        }
        return false;
    }

    /**
     * 获取自定义菜单的扫码推事件信息
     *
     * 事件类型为以下两种时则调用此方法有效
     * Event	 事件类型，scancode_push
     * Event	 事件类型，scancode_waitmsg
     *
     * @return: array | false
     * array (
     *     'ScanType'=>'qrcode',
     *     'ScanResult'=>'123123'
     * )
     */
    public function getRevScanInfo() {
        if (isset($this->_receive['ScanCodeInfo'])) {
            if (!is_array($this->_receive['ScanCodeInfo'])) {
                $array = (array) $this->_receive['ScanCodeInfo'];
                $this->_receive['ScanCodeInfo'] = $array;
            } else {
                $array = $this->_receive['ScanCodeInfo'];
            }
        }
        if (isset($array) && count($array) > 0) {
            return $array;
        }
        return false;
    }

    /**
     * 获取自定义菜单的图片发送事件信息
     *
     * 事件类型为以下三种时则调用此方法有效
     * Event	 事件类型，pic_sysphoto        弹出系统拍照发图的事件推送
     * Event	 事件类型，pic_photo_or_album  弹出拍照或者相册发图的事件推送
     * Event	 事件类型，pic_weixin          弹出微信相册发图器的事件推送
     *
     * @return: array | false
     * array (
     *   'Count' => '2',
     *   'PicList' =>array (
     *         'item' =>array (
     *             0 =>array ('PicMd5Sum' => 'aaae42617cf2a14342d96005af53624c'),
     *             1 =>array ('PicMd5Sum' => '149bd39e296860a2adc2f1bb81616ff8'),
     *         ),
     *   ),
     * )
     *
     */
    public function getRevSendPicsInfo() {
        if (isset($this->_receive['SendPicsInfo'])) {
            if (!is_array($this->_receive['SendPicsInfo'])) {
                $array = (array) $this->_receive['SendPicsInfo'];
                if (isset($array['PicList'])) {
                    $array['PicList'] = (array) $array['PicList'];
                    $item = $array['PicList']['item'];
                    $array['PicList']['item'] = array();
                    foreach ($item as $key => $value) {
                        $array['PicList']['item'][$key] = (array) $value;
                    }
                }
                $this->_receive['SendPicsInfo'] = $array;
            } else {
                $array = $this->_receive['SendPicsInfo'];
            }
        }
        if (isset($array) && count($array) > 0) {
            return $array;
        }
        return false;
    }

    /**
     * 获取自定义菜单的地理位置选择器事件推送
     *
     * 事件类型为以下时则可以调用此方法有效
     * Event	 事件类型，location_select        弹出地理位置选择器的事件推送
     *
     * @return: array | false
     * array (
     *   'Location_X' => '33.731655000061',
     *   'Location_Y' => '113.29955200008047',
     *   'Scale' => '16',
     *   'Label' => '某某市某某区某某路',
     *   'Poiname' => '',
     * )
     *
     */
    public function getRevSendGeoInfo() {
        if (isset($this->_receive['SendLocationInfo'])) {
            if (!is_array($this->_receive['SendLocationInfo'])) {
                $array = (array) $this->_receive['SendLocationInfo'];
                if (empty($array['Poiname'])) {
                    $array['Poiname'] = "";
                }
                if (empty($array['Label'])) {
                    $array['Label'] = "";
                }
                $this->_receive['SendLocationInfo'] = $array;
            } else {
                $array = $this->_receive['SendLocationInfo'];
            }
        }
        if (isset($array) && count($array) > 0) {
            return $array;
        }
        return false;
    }

    /**
     * 获取接收语音推送
     */
    public function getRevVoice() {
        if (isset($this->_receive['MediaId'])) {
            return array(
                'mediaid' => $this->_receive['MediaId'],
                'format'  => $this->_receive['Format'],
            );
        }
        return false;
    }

    /**
     * 获取接收视频推送
     */
    public function getRevVideo() {
        if (isset($this->_receive['MediaId'])) {
            return array(
                'mediaid'      => $this->_receive['MediaId'],
                'thumbmediaid' => $this->_receive['ThumbMediaId']
            );
        }
        return false;
    }

    /**
     * 获取接收TICKET
     */
    public function getRevTicket() {
        if (isset($this->_receive['Ticket'])) {
            return $this->_receive['Ticket'];
        }
        return false;
    }

    /**
     * 获取二维码的场景值
     */
    public function getRevSceneId() {
        if (isset($this->_receive['EventKey'])) {
            return str_replace('qrscene_', '', $this->_receive['EventKey']);
        }
        return false;
    }

    /**
     * 获取主动推送的消息ID
     * 经过验证，这个和普通的消息MsgId不一样
     * 当Event为 MASSSENDJOBFINISH 或 TEMPLATESENDJOBFINISH
     */
    public function getRevTplMsgID() {
        if (isset($this->_receive['MsgID'])) {
            return $this->_receive['MsgID'];
        }
        return false;
    }

    /**
     * 获取模板消息发送状态
     */
    public function getRevStatus() {
        if (isset($this->_receive['Status'])) {
            return $this->_receive['Status'];
        }
        return false;
    }

    /**
     * 获取群发或模板消息发送结果
     * 当Event为 MASSSENDJOBFINISH 或 TEMPLATESENDJOBFINISH，即高级群发/模板消息
     */
    public function getRevResult() {
        if (isset($this->_receive['Status'])) { //发送是否成功，具体的返回值请参考 高级群发/模板消息 的事件推送说明
            $array['Status'] = $this->_receive['Status'];
        }
        if (isset($this->_receive['MsgID'])) { //发送的消息id
            $array['MsgID'] = $this->_receive['MsgID'];
        }
        //以下仅当群发消息时才会有的事件内容
        if (isset($this->_receive['TotalCount'])) {  //分组或openid列表内粉丝数量
            $array['TotalCount'] = $this->_receive['TotalCount'];
        }
        if (isset($this->_receive['FilterCount'])) { //过滤（过滤是指特定地区、性别的过滤、用户设置拒收的过滤，用户接收已超4条的过滤）后，准备发送的粉丝数
            $array['FilterCount'] = $this->_receive['FilterCount'];
        }
        if (isset($this->_receive['SentCount'])) {  //发送成功的粉丝数
            $array['SentCount'] = $this->_receive['SentCount'];
        }
        if (isset($this->_receive['ErrorCount'])) { //发送失败的粉丝数
            $array['ErrorCount'] = $this->_receive['ErrorCount'];
        }
        if (isset($array) && count($array) > 0) {
            return $array;
        }
        return false;
    }

    /**
     * 获取多客服会话状态推送事件 - 接入会话
     * 当Event为 kfcreatesession 即接入会话
     * @return string | boolean  返回分配到的客服
     */
    public function getRevKFCreate() {
        if (isset($this->_receive['KfAccount'])) {
            return $this->_receive['KfAccount'];
        }
        return false;
    }

    /**
     * 获取多客服会话状态推送事件 - 关闭会话
     * 当Event为 kfclosesession 即关闭会话
     * @return string | boolean  返回分配到的客服
     */
    public function getRevKFClose() {
        if (isset($this->_receive['KfAccount'])) {
            return $this->_receive['KfAccount'];
        }
        return false;
    }

    /**
     * 获取多客服会话状态推送事件 - 转接会话
     * 当Event为 kfswitchsession 即转接会话
     * @return array | boolean  返回分配到的客服
     * {
     *     'FromKfAccount' => '',      //原接入客服
     *     'ToKfAccount' => ''            //转接到客服
     * }
     */
    public function getRevKFSwitch() {
        if (isset($this->_receive['FromKfAccount'])) {  //原接入客服
            $array['FromKfAccount'] = $this->_receive['FromKfAccount'];
        }
        if (isset($this->_receive['ToKfAccount'])) { //转接到客服
            $array['ToKfAccount'] = $this->_receive['ToKfAccount'];
        }
        if (isset($array) && count($array) > 0) {
            return $array;
        }
        return false;
    }

    /**
     * 发送客服消息
     * @param array $data 消息结构{"touser":"OPENID","msgtype":"news","news":{...}}
     * @return boolean|array
     */
    public function sendCustomMessage($data) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = $this->http_post(self::API_URL_PREFIX . self::CUSTOM_SEND_URL . "access_token={$this->access_token}", self::json_encode($data));
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
     * 模板消息 设置所属行业
     * @param int $id1  公众号模板消息所属行业编号，参看官方开发文档 行业代码
     * @param int $id2  同$id1。但如果只有一个行业，此参数可省略
     * @return boolean|array
     */
    public function setTMIndustry($id1, $id2 = '') {
        if ($id1) {
            $data['industry_id1'] = $id1;
        }
        if ($id2) {
            $data['industry_id2'] = $id2;
        }
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = $this->http_post(self::API_URL_PREFIX . self::TEMPLATE_SET_INDUSTRY_URL . "access_token={$this->access_token}", self::json_encode($data));
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
     * 模板消息 添加消息模板
     * 成功返回消息模板的调用id
     * @param string $tpl_id 模板库中模板的编号，有“TM**”和“OPENTMTM**”等形式
     * @return boolean|string
     */
    public function addTemplateMessage($tpl_id) {
        $data = array('template_id_short' => $tpl_id);
        if (!$this->access_token && !$this->getAccessToken())
            return false;
        $result = $this->http_post(self::API_URL_PREFIX . self::TEMPLATE_ADD_TPL_URL . "access_token={$this->access_token}", self::json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json['template_id'];
        }
        return false;
    }

    /**
     * 发送模板消息
     * @param array $data 消息结构
     * {
     *      "touser":"OPENID",
     *       "template_id":"ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY",
     *       "url":"http://weixin.qq.com/download",
     *       "topcolor":"#FF0000",
     *       "data":{
     *           "参数名1": {
     *           "value":"参数",
     *           "color":"#173177"	 //参数颜色
     *       },
     *       "Date":{
     *           "value":"06月07日 19时24分",
     *           "color":"#173177"
     *       },
     *       "CardNumber":{
     *           "value":"0426",
     *           "color":"#173177"
     *      },
     *      "Type":{
     *          "value":"消费",
     *          "color":"#173177"
     *       }
     *   }
     * }
     * @return boolean|array
     */
    public function sendTemplateMessage($data) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = $this->http_post(self::API_URL_PREFIX . self::TEMPLATE_SEND_URL . "access_token={$this->access_token}", self::json_encode($data));
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
     * 转发多客服消息
     * Example: $obj->transfer_customer_service($customer_account)->reply();
     * @param string $customer_account 转发到指定客服帐号：test1@test
     */
    public function transfer_customer_service($customer_account = '') {
        $msg = array(
            'ToUserName'   => $this->getRevFrom(),
            'FromUserName' => $this->getRevTo(),
            'CreateTime'   => time(),
            'MsgType'      => 'transfer_customer_service',
        );
        if ($customer_account) {
            $msg['TransInfo'] = array('KfAccount' => $customer_account);
        }
        $this->Message($msg);
        return $this;
    }

    /**
     * 高级群发消息, 根据OpenID列表群发图文消息(订阅号不可用)
     * 	   注意：视频需要在调用uploadMedia()方法后，再使用 uploadMpVideo() 方法生成，
     *           然后获得的 mediaid 才能用于群发，且消息类型为 mpvideo 类型。
     * @param array $data 消息结构
     * {
     *     "touser"=>array(
     *         "OPENID1",
     *         "OPENID2"
     *     ),
     *      "msgtype"=>"mpvideo",
     *      // 在下面5种类型中选择对应的参数内容
     *      // mpnews | voice | image | mpvideo => array( "media_id"=>"MediaId")
     *      // text => array ( "content" => "hello")
     * }
     * @return boolean|array
     */
    public function sendMassMessage($data) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = $this->http_post(self::API_URL_PREFIX . self::MASS_SEND_URL . "access_token={$this->access_token}", self::json_encode($data));
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
     * 高级群发消息, 根据群组id群发图文消息(认证后的订阅号可用)
     * 	   注意：视频需要在调用uploadMedia()方法后，再使用 uploadMpVideo() 方法生成，
     *           然后获得的 mediaid 才能用于群发，且消息类型为 mpvideo 类型。
     * @param array $data 消息结构
     * {
     *     "filter"=>array(
     *         "is_to_all"=>False,     //是否群发给所有用户.True不用分组id，False需填写分组id
     *         "group_id"=>"2"     //群发的分组id
     *     ),
     *      "msgtype"=>"mpvideo",
     *      // 在下面5种类型中选择对应的参数内容
     *      // mpnews | voice | image | mpvideo => array( "media_id"=>"MediaId")
     *      // text => array ( "content" => "hello")
     * }
     * @return boolean|array
     */
    public function sendGroupMassMessage($data) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = $this->http_post(self::API_URL_PREFIX . self::MASS_SEND_GROUP_URL . "access_token={$this->access_token}", self::json_encode($data));
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
     *  高级群发消息, 删除群发图文消息(认证后的订阅号可用)
     * @param type $msg_id 消息ID
     * @return boolean
     */
    public function deleteMassMessage($msg_id) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = $this->http_post(self::API_URL_PREFIX . self::MASS_DELETE_URL . "access_token={$this->access_token}", self::json_encode(array('msg_id' => $msg_id)));
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
     * 高级群发消息, 预览群发消息(认证后的订阅号可用)
     *     注意：视频需要在调用uploadMedia()方法后，再使用 uploadMpVideo() 方法生成，
     *           然后获得的 mediaid 才能用于群发，且消息类型为 mpvideo 类型。
     * @param type $data
     * @消息结构
     * {
     *     "touser"=>"OPENID",
     *      "msgtype"=>"mpvideo",
     *      // 在下面5种类型中选择对应的参数内容
     *      // mpnews | voice | image | mpvideo => array( "media_id"=>"MediaId")
     *      // text => array ( "content" => "hello")
     * }
     * @return boolean|array
     */
    public function previewMassMessage($data) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = $this->http_post(self::API_URL_PREFIX . self::MASS_PREVIEW_URL . "access_token={$this->access_token}", self::json_encode($data));
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
     * 高级群发消息, 查询群发消息发送状态(认证后的订阅号可用)
     * @param type $msg_id 消息ID
     * @return boolean|array
     * {
     *     "msg_id":201053012,     //群发消息后返回的消息id
     *     "msg_status":"SEND_SUCCESS" //消息发送后的状态，SENDING表示正在发送 SEND_SUCCESS表示发送成功
     * }
     */
    public function queryMassMessage($msg_id) {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = $this->http_post(self::API_URL_PREFIX . self::MASS_QUERY_URL . "access_token={$this->access_token}", self::json_encode(array('msg_id' => $msg_id)));
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
     * 设置发送消息
     * @param type $msg 消息数组
     * @param type $append 是否在原消息数组追加
     * @return type
     */
    public function Message($msg = '', $append = false) {
        if (is_null($msg)) {
            $this->_msg = array();
        } elseif (is_array($msg)) {
            if ($append) {
                $this->_msg = array_merge($this->_msg, $msg);
            } else {
                $this->_msg = $msg;
            }
            return $this->_msg;
        }
        return $this->_msg;
    }

    /**
     * 设置文本消息
     * @Example: $obj->text('hello')->reply();
     * @param type $text 文本内容
     * @return \WechatReceive
     */
    public function text($text = '') {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $msg = array(
            'ToUserName'   => $this->getRevFrom(),
            'FromUserName' => $this->getRevTo(),
            'MsgType'      => self::MSGTYPE_TEXT,
            'Content'      => $this->_auto_text_filter($text),
            'CreateTime'   => time(),
            'FuncFlag'     => $FuncFlag
        );
        $this->Message($msg);
        return $this;
    }

    /**
     * 设置图片消息
     * @param type $mediaid 图片媒体ID
     * @return \WechatReceive
     */
    public function image($mediaid = '') {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $msg = array(
            'ToUserName'   => $this->getRevFrom(),
            'FromUserName' => $this->getRevTo(),
            'MsgType'      => self::MSGTYPE_IMAGE,
            'Image'        => array('MediaId' => $mediaid),
            'CreateTime'   => time(),
            'FuncFlag'     => $FuncFlag
        );
        $this->Message($msg);
        return $this;
    }

    /**
     *  设置语音消息
     * @param type $mediaid 语音媒体ID
     * @return \WechatReceive
     */
    public function voice($mediaid = '') {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $msg = array(
            'ToUserName'   => $this->getRevFrom(),
            'FromUserName' => $this->getRevTo(),
            'MsgType'      => self::MSGTYPE_VOICE,
            'Voice'        => array('MediaId' => $mediaid),
            'CreateTime'   => time(),
            'FuncFlag'     => $FuncFlag
        );
        $this->Message($msg);
        return $this;
    }

    /**
     * 设置回复消息
     * @Example: $obj->video('media_id','title','description')->reply();
     * @param type $mediaid 视频媒体ID
     * @param type $title 视频标题
     * @param type $description 视频描述
     * @return \WechatReceive
     */
    public function video($mediaid = '', $title = '', $description = '') {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $msg = array(
            'ToUserName'   => $this->getRevFrom(),
            'FromUserName' => $this->getRevTo(),
            'MsgType'      => self::MSGTYPE_VIDEO,
            'Video'        => array(
                'MediaId'     => $mediaid,
                'Title'       => $title,
                'Description' => $description
            ),
            'CreateTime'   => time(),
            'FuncFlag'     => $FuncFlag
        );
        $this->Message($msg);
        return $this;
    }

    /**
     * 设置回复音乐
     * @param type $title 音乐标题
     * @param type $desc 音乐描述
     * @param type $musicurl 音乐地址
     * @param type $hgmusicurl 高清音乐地址
     * @param type $thumbmediaid 音乐图片缩略图的媒体id（可选）
     * @return \WechatReceive
     */
    public function music($title, $desc, $musicurl, $hgmusicurl = '', $thumbmediaid = '') {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $msg = array(
            'ToUserName'   => $this->getRevFrom(),
            'FromUserName' => $this->getRevTo(),
            'CreateTime'   => time(),
            'MsgType'      => self::MSGTYPE_MUSIC,
            'Music'        => array(
                'Title'       => $title,
                'Description' => $desc,
                'MusicUrl'    => $musicurl,
                'HQMusicUrl'  => $hgmusicurl
            ),
            'FuncFlag'     => $FuncFlag
        );
        if ($thumbmediaid) {
            $msg['Music']['ThumbMediaId'] = $thumbmediaid;
        }
        $this->Message($msg);
        return $this;
    }

    /**
     * 设置回复图文
     * @param type $newsData
     * @return \WechatReceive
     * @数组结构:
     *  array(
     *  	"0"=>array(
     *  		'Title'=>'msg title',
     *  		'Description'=>'summary text',
     *  		'PicUrl'=>'http://www.domain.com/1.jpg',
     *  		'Url'=>'http://www.domain.com/1.html'
     *  	),
     *  	"1"=>....
     *  )
     */
    public function news($newsData = array()) {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $msg = array(
            'ToUserName'   => $this->getRevFrom(),
            'FromUserName' => $this->getRevTo(),
            'CreateTime'   => time(),
            'MsgType'      => self::MSGTYPE_NEWS,
            'ArticleCount' => count($newsData),
            'Articles'     => $newsData,
            'FuncFlag'     => $FuncFlag
        );
        $this->Message($msg);
        return $this;
    }

    /**
     * 回复微信服务器
     * @Example: $this->text('msg tips')->reply();
     * @param type $msg 要发送的信息, 默认取$this->_msg
     * @param type $return 是否返回信息而不抛出到浏览器 默认:否
     * @return boolean|echo
     */
    public function reply($msg = array(), $return = false) {
        if (empty($msg)) {
            if (empty($this->_msg)) {   //防止不先设置回复内容，直接调用reply方法导致异常
                return false;
            }
            $msg = $this->_msg;
        }
        $xmldata = self::arr2xml($msg);
        if ($this->encrypt_type == 'aes') { //如果来源消息为加密方式
            !class_exists('Prpcrypt', FALSE) && require __DIR__ . '/Lib/Prpcrypt.php';
            $pc = new Prpcrypt($this->encodingAesKey);
            $array = $pc->encrypt($xmldata, $this->appid);
            $ret = $array[0];
            if ($ret != 0) {
                $this->log('encrypt err!');
                return false;
            }
            $timestamp = time();
            $nonce = rand(77, 999) * rand(605, 888) * rand(11, 99);
            $encrypt = $array[1];
            $tmpArr = array($this->token, $timestamp, $nonce, $encrypt); //比普通公众平台多了一个加密的密文
            sort($tmpArr, SORT_STRING);
            $signature = sha1(implode($tmpArr));
            $format = "<xml><Encrypt><![CDATA[%s]]></Encrypt><MsgSignature><![CDATA[%s]]></MsgSignature><TimeStamp>%s</TimeStamp><Nonce><![CDATA[%s]]></Nonce></xml>";
            $xmldata = sprintf($format, $encrypt, $signature, $timestamp, $nonce);
        }
        if ($return) {
            return $xmldata;
        }
        echo $xmldata;
    }

    /**
     * 过滤文字回复\r\n换行符
     * @param type $text
     * @return type|string
     */
    private function _auto_text_filter($text) {
        if (!$this->_text_filter) {
            return $text;
        }
        return str_replace("\r\n", "\n", $text);
    }

}
