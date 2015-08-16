<?php
/**
 * 公众号微信内Web OAuth登陆，有两种情况
 * 一是公众号（订阅号、服务号）；一种是企业号
 * 该页面可通过Redirect方式进行访问，或者直接在需要的地方include_once
 * 该认证流程会得到用户的完整信息
 */

chdir(dirname(__FILE__));//把工作目录切换到文件所在目录

include_once dirname(__FILE__).'/__config__.php';

// state为交互时双方都会带着的get参数，用于做一些逻辑判断，如果没指定，则默认一个
if( ! @$_GET['state'] ){
    $state = "ydwx";
}

$redirect = YDWX_SITE_URL.'ydwx/auth.php';

$appid  = WEIXIN_ACCOUNT_TYPE == WEIXIN_ACCOUNT_CROP ? WEIXIN_CROP_ID : WEIXIN_APP_ID;
$secret = WEIXIN_ACCOUNT_TYPE == WEIXIN_ACCOUNT_CROP ? WEIXIN_CROP_SECRET : WEIXIN_APP_SECRET;

//第一步，引导用户到微信页面授权
if( ! @$_GET['code'] &&  ! @$_GET['state']){
    ob_clean();
    header("Location: https://open.weixin.qq.com/connect/oauth2/authorize?appid="
        .$appid."&redirect_uri={$redirect}&response_type=code&scope=snsapi_userinfo&state={$state}#wechat_redirect");
    die;
}

//用户取消授权后返回本页面
if( ! @$_GET['code'] && @$_GET['state']){
    YDWXHook::do_hook(YDWXHook::AUTH_CANCEL);
    die;
}

//第二步，用户授权后返回，获取授权用户信息
if (WEIXIN_ACCOUNT_TYPE != WEIXIN_ACCOUNT_CROP){
    $http = new YDHttp();
    $info = json_decode($http->get("https://api.weixin.qq.com/sns/oauth2/access_token?appid="
            .$appid."&secret=".$secret."&code=".$_GET['code']."&grant_type=authorization_code"), true);
    
    if( !@$info['openid']){
        YDWXHook::do_hook(YDWXHook::AUTH_FAIL, YDWXAuthFailResponse::errMsg($info['errmsg'], $info['errcode']));
        die;
    }
    
    YDWXHook::do_hook(YDWXHook::AUTH_INAPP_SUCCESS, ydwx_sns_userinfo($info['access_token'],     $info['openid'], $_GET['state']));
}else{
    //企业号返回的是code，可直接获取用户的信息
    $access_token = YDWXHook::do_hook(YDWXHook::GET_ACCESS_TOKEN);
    if($access_token){
        YDWXHook::do_hook(YDWXHook::AUTH_CROP_SUCCESS,  ydwx_crop_user_info($access_token, $_GET['code'], $_GET['state']));
    }else{
        YDWXHook::do_hook(YDWXHook::AUTH_FAIL,   YDWXAuthFailResponse::errMsg("未取得access token"));
    }
}
