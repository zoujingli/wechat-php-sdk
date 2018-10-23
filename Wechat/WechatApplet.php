<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/22
 * Time: 14:38
 */

namespace Wechat;


use Wechat\Lib\Cache;
use Wechat\Lib\Tools;

/**
 * 小程序第三方平台SDK
 * @version 1.0
 */
class WechatApplet extends WechatService
{
    public function __construct(array $options = array())
    {
        parent::__construct($options);
    }

    /**
     * 绑定/解绑 微信用户为小程序体验者 https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1489140588_nVUgx&token=&lang=zh_CN
     * @param string $action bind为绑定 unbind为解绑
     * @param string $wechatId 微信号
     * @return bool
     */
    public function tester($action, $wechatId){
        if($action != 'bind' && $action != 'unbind'){
            $this->errMsg = '参数错误.(bind/unbind)';
            $this->errCode = '';
            return false;
        }
        //是否授权
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        if($action == 'bind'){
            $url = 'https://api.weixin.qq.com/wxa/bind_tester?access_token='.$auth_info['authorizer_access_token'];
        }else{
            $url = 'https://api.weixin.qq.com/wxa/unbind_tester?access_token=TOKEN'.$auth_info['authorizer_access_token'];
        }

        $data['wechatid'] = $wechatId;
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode($data)));
        return $this->returnBool($returnData);

    }

    /**
     * 设置小程序业务域名 （仅供第三方代小程序调用）
     * @param string $action add添加, delete删除, set覆盖, get获取
     * @param array $webViewDomain request合法域名
     * @return bool | array  以下字段仅在get时返回 {webviewdomain}
     */
    public function webViewDomain($action = 'get',$webViewDomain = []){
        if($action != 'get'){
            if(empty($webViewDomain) ){
                $this->errMsg = '小程序业务域名必须';
                $this->errCode = '';
                return false;
            }
        }

        //是否授权
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        //组装
        $data['webviewdomain'] = $webViewDomain;

        $url = 'https://api.weixin.qq.com/wxa/modify_domain?access_token='.$auth_info['authorizer_access_token'];
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode($data)));
        return $this->returnBool($returnData);

    }

    /**
     * 设置小程序服务器域名
     * @param string $action add添加, delete删除, set覆盖, get获取
     * @param array $requestDomain request合法域名
     * @param array $wsRequestDomain socket合法域名
     * @param array $uploadDomain uploadFile合法域名
     * @param array $downloadDomain downloadFile合法域名
     * @return bool | array  以下字段仅在get时返回 {requestdomain,wsrequestdomain,uploaddomain,downloaddomain}
     */
    public function wxaDomain($action = 'get',$requestDomain = [],$wsRequestDomain=[],$uploadDomain=[],$downloadDomain=[]){
        #当参数是get时不需要填四个域名字段
        if($action != 'get'){
            if(empty($requestDomain) || empty($wsRequestDomain) || empty($uploadDomain) || empty($downloadDomain)){
                $this->errMsg = '当action参数不是get时需要填四个域名字段';
                $this->errCode = '';
                return false;
            }
        }

        //是否授权
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        //组装
        $data['requestdomain'] = $requestDomain;
        $data['wsrequestdomain'] = $wsRequestDomain;
        $data['uploaddomain'] = $uploadDomain;
        $data['downloaddomain'] = $downloadDomain;

        $url = 'https://api.weixin.qq.com/wxa/modify_domain?access_token='.$auth_info['authorizer_access_token'];
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode($data)));
        if($returnData['errcode'] == 0){
            if($action == 'get'){
                return $returnData;
            }else{
                return true;
            }
        }else{
            $this->errMsg = isset($returnData['errmsg'])?$returnData['errmsg']:'修改失败';
            $this->errCode =isset($returnData['errcode'])?$returnData['errcode']:'';
            return false;
        }

    }

    /**
     * 为授权的小程序帐号上传小程序代码
     * @param string $template_id  代码库中的代码模版ID
     * @param string $ext_json 第三方自定义的配置
     * @param string $user_version 代码版本号，开发者可自定义
     * @param string $user_desc 代码描述，开发者可自定义
     * @return bool
     */
    public function commit($template_id,$ext_json,$user_version,$user_desc){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        $data['template_id'] = $template_id;
        $data['ext_json'] = $ext_json;
        $data['user_version'] = $user_version;
        $data['user_desc'] = $user_desc;

        $url = 'https://api.weixin.qq.com/wxa/commit?access_token='.$auth_info['authorizer_access_token'];
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode($data)));
        return $this->returnBool($returnData);
    }

    /**
     * 获取体验小程序的体验二维码
     * @param string $path 指定体验版二维码跳转到某个具体页面 可不填
     * @return bool|mixed 返回图片资源
     */
    public function getQrcode($path = ''){
        //鉴权
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        if($path){
            $suffix = '&path='.$path;
        }else{
            $suffix = '';
        }

        $url = 'https://api.weixin.qq.com/wxa/commit?access_token='.$auth_info['authorizer_access_token'].$suffix;
        $returnData = Tools::httpGet($url);

        if(json_decode($returnData)){

            $this->errMsg = isset($returnData['errmsg'])?$returnData['errmsg']:'';
            $this->errCode =isset($returnData['errcode'])?$returnData['errcode']:'';
            return false;
        }else{

            return $returnData;
        }
    }

    /**
     * 获取授权小程序帐号的可选类目
     * @return bool | array {category_list:[]}
     */
    public function getCategory(){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }
        $url = 'https://api.weixin.qq.com/wxa/get_category?access_token='.$auth_info['authorizer_access_token'];
        $returnData = $this->parseJson(Tools::httpGet($url));
        return $this->returnResult($returnData);
    }

    /**
     * 获取小程序的第三方提交代码的页面配置（仅供第三方开发者代小程序调用）
     * @return bool|mixed {page_list:[]}
     */
    public function getPage(){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }
        $url = 'https://api.weixin.qq.com/wxa/get_page?access_token='.$auth_info['authorizer_access_token'];
        $returnData = $this->parseJson(Tools::httpGet($url));
        return $this->returnResult($returnData);
    }

    /**
     * 将第三方提交的代码包提交审核（仅供第三方开发者代小程序调用）
     * @param $item_list
     * @return bool|array {item_list:[{address,tag,first_class,second_class,first_id,second_id,title}]}
     */
    public function submitAudit($item_list){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }
        if(!is_array($item_list) || empty($item_list)){
            $this->errMsg = '提交审核项的一个列表';
            $this->errCode ='';
            return false;
        }

        $data['item_list'] = $item_list;
        $url = 'https://api.weixin.qq.com/wxa/submit_audit?access_token='.$auth_info['authorizer_access_token'];
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode($data)));
        return $this->returnResult($returnData);
    }

    /**
     * 回调 获取审核结果
     * @return array|bool 消息内容
     */
    public function revAuditResult(){
        $receive = new WechatReceive(array(
            'appid'          => $this->component_appid,
            'appsecret'      => $this->component_appsecret,
            'encodingaeskey' => $this->component_encodingaeskey,
            'token'          => $this->component_token,
            'cachepath'      => Cache::$cachepath
        ));
        # 会话内容解密状态判断
        if (false === $receive->valid()) {
            $this->errCode = $receive->errCode;
            $this->errMsg = $receive->errMsg;
            Tools::log("获取小程序审核结果失败. {$this->errMsg} [$this->errCode]", "ERR - {$this->authorizer_appid}");
            return false;
        }
        $data = $receive->getRev()->getRevData();
        if ($data['Event'] === 'weapp_audit_success' || $data['Event'] === 'weapp_audit_success' ) {
            return $data;
        }else{

            return false;
        }

    }

    /**
     * 查询某个指定版本的审核状态（仅供第三方代小程序调用）
     * @param string $audit_id 提交审核时获得的审核id
     * @return bool | array  {status:0审核成功|1 审核被拒(有返回reason) |2 审核中 , reason}
     */
    public function getAuditStatus($audit_id){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        $data['auditid'] = $audit_id;
        $url = 'https://api.weixin.qq.com/wxa/commit?access_token='.$auth_info['authorizer_access_token'];
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode($data)));
        return $this->returnResult($returnData);
    }

    /**
     * 查询最新一次提交的审核状态
     * @return bool | array  {auditid ,status:0审核成功|1 审核被拒(有返回reason) |2 审核中 , reason}
     */
    public function getLastAuditResult(){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }
        $url = 'https://api.weixin.qq.com/wxa/get_page?access_token='.$auth_info['authorizer_access_token'];

        $returnData = $this->parseJson(Tools::httpGet($url));
        return $this->returnResult($returnData);

    }

    /**
     * 发布已通过审核的小程序（仅供第三方代小程序调用)
     * @return bool
     */
    public function release(){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        $url = 'https://api.weixin.qq.com/wxa/release?access_token='.$auth_info['authorizer_access_token'];
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode([])));
        return $this->returnBool($returnData);
    }

    /**
     * 修改小程序线上代码的可见状态（仅供第三方代小程序调用）
     * @param string $action  设置可访问状态，发布后默认可访问，close为不可见，open为可见
     * @return bool
     */
    public function changeVisitstatus($action){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        if($action != 'close' || $action != 'open'){
            $this->errMsg = '参数错误: 仅为close/open';
            $this->errCode = '';
            return false;
        }


        $url = 'https://api.weixin.qq.com/wxa/change_visitstatus?access_token='.$auth_info['authorizer_access_token'];
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode([])));
        return $this->returnBool($returnData);
    }

    /**
     * 小程序版本回退（仅供第三方代小程序调用）
     * @return bool
     */
    public function revertCodeRelease(){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }
        $url = 'https://api.weixin.qq.com/wxa/revertcoderelease?access_token='.$auth_info['authorizer_access_token'];

        $returnData = $this->parseJson(Tools::httpGet($url));
        return $this->returnBool($returnData);
    }

    /**
     * 查询当前设置的最低基础库版本及各版本用户占比
     * @return bool | array  { now_version ,uv_info}
     */
    public function getSupportVersion(){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }


        $url = 'https://api.weixin.qq.com/wxa/change_visitstatus?access_token='.$auth_info['authorizer_access_token'];
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode([])));
        return $this->returnResult($returnData);
    }

    /**
     * 设置最低基础库版本（仅供第三方代小程序调用）
     * @param string $version 版本号
     * @return bool
     */
    public function setSupportVersion($version){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }


        $url = 'https://api.weixin.qq.com/cgi-bin/wxopen/setweappsupportversion?access_token='.$auth_info['authorizer_access_token'];
        $data['version'] = $version;
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode($data)));

        return $this->returnBool($returnData);
    }

    /**
     * 增加或修改二维码规则
     * @param string $prefix
     * @param  string $permit_sub_rule #https://mp.weixin.qq.com/debug/wxadoc/introduction/qrcode.html#前缀占用规则
     * @param string $path
     * @param string $open_version
     * @param array $debug_url
     * @param string $is_edit
     * @return bool
     */
    public function editOrAddQrcodeRule($prefix,$permit_sub_rule,$path,$open_version,$debug_url,$is_edit){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/wxopen/qrcodejumpadd?access_token='.$auth_info['authorizer_access_token'];

        $data['prefix'] = $prefix;
        $data['permit_sub_rule'] = $permit_sub_rule;
        $data['path'] = $path;
        $data['open_version'] = $open_version;
        $data['debug_url'] = $debug_url;
        $data['is_edit'] = $is_edit;

        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode($data)));

        return $this->returnResult($returnData);
    }

    /**
     * 获取已设置的二维码规则
     * @return bool |array 规则列表 {rule_list:[],list_size,qrcodejump_open,qrcodejump_pub_quota}
     */
    public function getQrcodeRule(){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/wxopen/qrcodejumpget?access_token='.$auth_info['authorizer_access_token'];

        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode([])));

        return $this->returnResult($returnData);
    }

    /**
     * 获取校验文件名称及内容
     * @return bool |array {file_name,file_content}
     */
    public function qrcodeValidFile(){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/wxopen/qrcodejumpdownload?access_token='.$auth_info['authorizer_access_token'];

        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode([])));

        return $this->returnResult($returnData);
    }

    /**
     * 发布已设置的二维码规则
     * @param string $prefix 二维码规则 (获取已设置二维码规则结果获得)
     * @return bool
     */
    public function releaseQrcodeRule($prefix){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/wxopen/qrcodejumpdownload?access_token='.$auth_info['authorizer_access_token'];

        $data['prefix'] = $prefix;
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode($data)));

        return $this->returnBool($returnData);
    }

    /**
     * 小程序审核撤回
     * @return bool
     * !单个帐号每天审核撤回次数最多不超过1次，一个月不超过10次。
     */
    public function revokeAudit(){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        $url = 'https://api.weixin.qq.com/wxa/undocodeaudit?access_token='.$auth_info['authorizer_access_token'];

        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode([])));

        return $this->returnBool($returnData);
    }

    /**
     * 分阶段发布接口
     * @param int $gray_percentage 灰度的百分比，1到100的整数
     * @return bool
     */
    public function grayRelease($gray_percentage){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        $url = 'https://api.weixin.qq.com/wxa/grayrelease?ccess_token='.$auth_info['authorizer_access_token'];
        $data['gray_percentage'] = $gray_percentage;
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode($data)));

        return $this->returnBool($returnData);
    }

    /**
     * 取消分阶段发布
     * @return bool
     */
    public function revertGrayRelease(){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        $url = 'https://api.weixin.qq.com/wxa/revertgrayrelease?access_token='.$auth_info['authorizer_access_token'];
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode([])));

        return $this->returnBool($returnData);
    }

    /**
     * 获取草稿箱内的所有临时代码草稿
     * @return bool |array {create_time,user_version,user_desc,draft_id}
     */
    public function getTemplateDrafList(){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }
        $url = 'https://api.weixin.qq.com/wxa/gettemplatedraftlist?access_token='.$auth_info['authorizer_access_token'];

        $returnData = $this->parseJson(Tools::httpGet($url));
        return $this->returnResult($returnData);
    }

    /**
     * 获取代码模版库中的所有小程序代码模版
     * @return bool |array  {template_list:[{create_time,user_version,user_desc,template_id}]}
     */
    public function getTemplateList(){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }
        $url = 'https://api.weixin.qq.com/wxa/gettemplatelist?access_token='.$auth_info['authorizer_access_token'];

        $returnData = $this->parseJson(Tools::httpGet($url));
        return $this->returnBool($returnData);
    }

    /**
     * 将草稿箱的草稿选为小程序代码模版
     * @param int $draft_id 草稿id
     * @return bool
     */
    public function addToTemplate($draft_id){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        $url = 'https://api.weixin.qq.com/wxa/addtotemplate?access_token='.$auth_info['authorizer_access_token'];
        $data['draft_id'] = $draft_id;
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode($data)));

        return $this->returnBool($returnData);
    }

    /**
     * 删除指定小程序代码模版
     * @param int $template_id
     * @return bool
     */
    public function delTemplate($template_id){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        $url = 'https://api.weixin.qq.com/wxa/deletetemplate?access_token='.$auth_info['authorizer_access_token'];
        $data['template_id'] = $template_id;
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode($data)));

        return $this->returnBool($returnData);
    }

    /**
     * 获取小程序模板库标题列表
     * @param int $offset offset表示从offset开始
     * @param int $count 拉取count条记录
     * @return bool |array {list:[{id,title}]}
     */
    public function msgTemplateList($offset,$count){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/library/list?access_token='.$auth_info['authorizer_access_token'];
        $data['offset'] = $offset;
        $data['count'] = $count;
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode($data)));

        return $this->returnResult($returnData);
    }

    /**
     * 通过消息模板id获取模板库某个模板标题下关键词库
     * @param int $msg_templet_id 消息模板id
     * @return bool | array  {id,title,keyword_list:[{keyword_id,name,example}]}
     */
    public function getMsgTemplate($msg_templet_id){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/library/get?access_token='.$auth_info['authorizer_access_token'];
        $data['id'] = $msg_templet_id;
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode($data)));

        return $this->returnResult($returnData);
    }

    /**
     * 组合模板并添加至帐号下的个人模板库
     * @param int $template_id  消息模板id
     * @param array $keyword_ids 关键词id数组 (通过消息模板详情接口获取
     * @return bool | array {template_id}
     */
    public function addMsgTemplate($template_id,$keyword_ids){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/add?access_token='.$auth_info['authorizer_access_token'];
        $data['id'] = $template_id;
        $data['keyword_id_list'] = $keyword_ids;
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode($data)));

        return $this->returnResult($returnData);
    }

    /**
     * 获取帐号下已存在的模板列表
     * @param int $offset offset表示从offset开始
     * @param int $count 拉取count条记录
     * @return bool |array {list:[{template_id,title,content,example}]}
     *
     */
    public function getMyMsgTemplateList($offset,$count){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/list?access_token='.$auth_info['authorizer_access_token'];
        $data['offset'] = $offset;
        $data['count'] = $count;
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode($data)));

        return $this->returnResult($returnData);
    }

    /**
     * 删除帐号下的某个模板
     * @param int $template_id 消息模板id
     * @return bool
     */
    public function delMsgTemplate($template_id){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/wxopen/template/del?access_token='.$auth_info['authorizer_access_token'];
        $data['template_id'] = $template_id;
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode($data)));

        return $this->returnResult($returnData);
    }

    /**
     * 设置小程序隐私设置（是否可被搜索）
     * @param int $status 1表示不可搜索，0表示可搜索
     * @return bool
     */
    public function setSearchStatus($status){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }

        $url = 'https://api.weixin.qq.com/wxa/changewxasearchstatus?access_token='.$auth_info['authorizer_access_token'];
        $data['status'] = $status;
        $returnData = $this->parseJson(Tools::httpPost($url, Tools::json_encode($data)));

        return $this->returnBool($returnData);
    }

    /**
     * 查询小程序当前隐私设置（是否可被搜索）
     * @return bool |array {status}
     */
    public function getSearchStatus(){
        $auth_info = $this->getAuthTokenCache();
        if(!$auth_info){
            return false;
        }
        $url = 'https://api.weixin.qq.com/wxa/getwxasearchstatus?access_token='.$auth_info['authorizer_access_token'];

        $returnData = $this->parseJson(Tools::httpGet($url));
        return $this->returnResult($returnData);

    }

    /**
     * 返回bool值
     * @param array $returnData 微信返回数据
     * @return bool
     */
    private function returnBool($returnData){
        if($returnData['errcode'] != 0){

            $this->errMsg = $returnData['errmsg'];
            $this->errCode = $returnData['errcode'] ;
            return false;
        }else{

            return true;
        }
    }

    /**
     * 返回数组
     * @param array $returnData 微信返回数据
     * @return bool |array
     */
    private function returnResult($returnData){
        if($returnData['errcode'] != 0){

            $this->errMsg = $returnData['errmsg'];
            $this->errCode = $returnData['errcode'] ;
            return false;
        }else{

            return $returnData;
        }
    }


}