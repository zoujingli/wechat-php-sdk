<?php

!defined('BASEPATH') && exit('No direct script access allowed');

/**
 * 微信接口响应
 *
 * @category app
 * @subpackage controllers/wechat
 * @author Anyon <zoujingli@qq.com>
 * @date 2015/11/30 20:52
 */
class Api extends CI_Controller {

    /**
     * 微信消息对象
     * @var WechatReceive 
     */
    protected $wechat;

    /**
     * 微信openid
     * @var type 
     */
    protected $openid;

    /**
     * 接口入口
     */
    public function index() {
        /* 创建接口操作对象 */
        $this->wechat = &load_wechat('Receive');
        /* 验证接口 */
        if ($this->wechat->valid() === FALSE) {
            log_message('ERROR', "微信被动接口验证失败，{$this->wechat->errMsg}[{$this->wechat->errCode}]");
            exit($this->wechat->errMsg);
        }
        /* 获取openid */
        $this->openid = $this->wechat->getRev()->getRevFrom();
        /* 记录接口日志 */
        $this->_logs();
        /* 分别执行对应类型的操作 */
        switch ($this->wechat->getRev()->getRevType()) {
            case WechatReceive::MSGTYPE_TEXT:
                $keys = $this->wechat->getRevContent();
                return $this->_keys("wechat_keys#keys#{$keys}");
            case WechatReceive::MSGTYPE_EVENT:
                return $this->_event();
            case WechatReceive::MSGTYPE_IMAGE:
                return $this->_image();
            case WechatReceive::MSGTYPE_LOCATION:
                return $this->_location();
            default:
                return $this->_default();
        }
    }

    /**
     * 关键字处理
     * @param type $keys      关键字（常规或规格关键字）
     * @param type $default   是否启用默认模式
     * @return type
     */
    protected function _keys($keys, $default = FALSE) {
        list($table, $field, $value) = explode('#', $keys . '##');
        if ($this->db->table_exists($table) && ($info = $this->db->where($field, $value)->get($table)->first_row('array'))) {
            if (!empty($info['type']) && $info['type'] === 'customservice') {
                $this->wechat->sendCustomMessage(array('touser' => $this->openid, 'msgtype' => 'text', 'text' => array('content' => $info['content'])));
                return $this->wechat->transfer_customer_service()->reply();
            }
            array_key_exists('status', $info) && empty($info['status']) && exit('success');
            switch ($info['type']) {
                case 'keys':/* 关键字 */
                    empty($info['content']) && empty($info['name']) && exit('success');
                    return $this->_keys('wechat_keys#keys#' . (empty($info['content']) ? $info['name'] : $info['content']));
                case 'text':/* 文本消息 */
                    empty($info['content']) && exit('success');
                    return $this->wechat->text($info['content'])->reply();
                case 'news':/* 图文消息 */
                    empty($info['news_id']) && exit('success');
                    return $this->_news($info['news_id']);
                case 'music':/* 音频消息 */
                    (empty($info['music_url']) || empty($info['music_title']) || empty($info['music_desc'])) && exit('success');
                    $this->load->library('NewsData');
                    $media_id = empty($info['music_image']) ? '' : $this->newsdata->upload_media($info['music_image'], 'image');
                    empty($media_id) && exit('success');
                    return $this->wechat->music($info['music_title'], $info['music_desc'], $info['music_url'], $info['music_url'], $media_id)->reply();
                case 'voice':/* 语音消息 */
                    empty($info['voice_url']) && exit('success');
                    $this->load->library('NewsData');
                    $media_id = $this->newsdata->upload_media($info['voice_url'], 'voice')->reply();
                    empty($media_id) && exit('success');
                    return $this->wechat->voice($media_id)->reply();
                case 'image':/* 图文消息 */
                    empty($info['image_url']) && exit('success');
                    $this->load->library('NewsData');
                    $media_id = $this->newsdata->upload_media($info['image_url'], 'image');
                    empty($media_id) && exit('success');
                    return $this->wechat->image($media_id)->reply();
                case 'video':/* 视频消息 */
                    (empty($info['video_url']) || empty($info['video_desc']) || empty($info['video_title'])) && exit('success');
                    $this->load->library('NewsData');
                    $data = array('title' => $info['video_title'], 'introduction' => $info['video_desc']);
                    $media_id = $this->newsdata->upload_media($info['video_url'], 'video', TRUE, $data);
                    if (false === $this->wechat->video($media_id, $info['video_title'], $info['video_desc'])->reply()) {
                        return $this->wechat->text("{$this->wechat->errMsg}[{$this->wechat->errCode}]")->reply();
                    }
                    return true;
            }
        }

        if ($default) {
            exit('success');
        }
        return $this->_keys('wechat_keys#keys#default', TRUE);
    }

    /**
     * 回复图文
     * @param type $news_id
     */
    protected function _news($news_id = 0) {
        $this->load->library('NewsData');
        $newsinfo = $this->newsdata->readyById($news_id);
        if (!empty($newsinfo)) {
            $newsdata = array();
            foreach ($newsinfo['articles'] as &$vo) {
                $newsdata[] = array('Title' => $vo['title'], 'Description' => $vo['digest'], 'PicUrl' => $vo['local_url'], 'Url' => site_url("news-{$vo['id']}"));
            }
            $this->wechat->news($newsdata)->reply();
        } else {
            exit('success');
        }
    }

    /**
     * 事件处理
     * @return type
     */
    protected function _event() {
        $event = $this->wechat->getRevEvent();
        switch (strtolower($event['event'])) {
            case 'subscribe':/* 关注事件 */
                $this->_sync_fans(true);
                if (!empty($event['key']) && stripos($event['key'], 'qrscene_') !== false) {
                    $this->_spread(preg_replace('|^.*?(\d+).*?$|', '$1', $event['key']));
                }
                return $this->_keys('wechat_keys#keys#subscribe');
            case 'unsubscribe':/* 取消关注 */
                $this->_sync_fans(false);
                exit('success');
            case 'click': /* 点击链接 */
                return $this->_keys($event['key']);
            case 'scancode_push':
            case 'scancode_waitmsg':/* 扫码推事件 */
                $scanInfo = $this->wechat->getRev()->getRevScanInfo();
                if (isset($scanInfo['ScanResult'])) {
                    return $this->_keys($scanInfo['ScanResult']);
                }exit('success');
            case 'scan':
                if (!empty($event['key'])) {
                    return $this->_spread($event['key']);
                }exit('success');
        }
    }

    /**
     * 推荐好友扫码关注
     * @param type $key
     */
    protected function _spread($key) {
        $fans = $this->db->where('id', $key)->get('wechat_fans')->first_row('array');
        if (empty($fans) || $fans['openid'] === $this->openid) {
            return;
        }
        # 标识推荐关系
        $this->db->reset_query();
        $this->db->where('openid', $this->openid)->where('spread_openid is ', ' NULL', FALSE)->or_where('spread_openid', '');
        $this->db->update('wechat_fans', array('spread_at' => date('Y-m-d H:i:s'), 'spread_openid' => $fans['openid']));
        # 推荐成功的奖励 
        // @todo
    }

    /**
     * 位置类事情回复
     */
    protected function _location() {
        $vo = $this->wechat->getRevData();
        $url = "http://apis.map.qq.com/ws/geocoder/v1/?location={$vo['Location_X']},{$vo['Location_Y']}&key=ZBHBZ-CHQ2G-RDXQF-I5TUX-SAK53-A5BZT";
        $data = json_decode(file_get_contents($url), true);
        if (!empty($data) && intval($data['status']) === 0) {
            $msg = $data['result']['formatted_addresses']['recommend'];
        } else {
            $msg = "{$vo['Location_X']},{$vo['Location_Y']}";
        }
        $this->wechat->text($msg)->reply();
    }

    /**
     * 默认事件处理
     */
    protected function _default() {
        $this->wechat->transfer_customer_service()->reply();
        exit('success');
    }

    /**
     * 图片事件处理
     */
    protected function _image() {
        $this->wechat->transfer_customer_service()->reply();
        exit('success');
    }

    /**
     * 记录接口日志
     */
    protected function _logs() {
        $data = $this->wechat->getRev()->getRevData();
        if (empty($data)) {
            return;
        }
        if (isset($data['Event']) && in_array($data['Event'], array('scancode_push', 'scancode_waitmsg', 'scan'))) {
            $scanInfo = $this->wechat->getRev()->getRevScanInfo();
            $data = array_merge($data, $scanInfo);
        }
        if (isset($data['Event']) && in_array($data['Event'], array('location_select'))) {
            $locationInfo = $this->wechat->getRev()->getRevSendGeoInfo();
            $data = array_merge($data, $locationInfo);
        }
        $this->formdata->save('wechat_message', array_change_key_case($data, CASE_LOWER));
    }

    /**
     * 同步粉丝状态
     * @param type $subscribe
     */
    protected function _sync_fans($subscribe = TRUE) {
        if ($subscribe) {
            $wechat = & load_wechat('User');
            $user = $wechat->getUserInfo($this->openid);
            $user['subscribe'] = intval($subscribe);
            $user['nickname'] = Emoji::unified_to_html(Emoji:: to_unified($user['nickname']));
            $user['subscribe_at'] = date('Y-m-d H:i:s', $user['subscribe_time']);
            $this->formdata->save('wechat_fans', $user, 'openid');
        } else {
            $this->formdata->save('wechat_fans', array('openid' => $this->openid, 'subscribe' => '0'), 'openid');
        }
    }

}
