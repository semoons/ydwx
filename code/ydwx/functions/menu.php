<?php

/**
 * 返回通过API设置的菜单
 * 
 * @param unknown $accessToken
 * @return array(Menu)
 * @see http://mp.weixin.qq.com/wiki/16/ff9b7b85220e1396ffa16794a9d95adc.html
 */
function ydwx_menu_get($accessToken){
    $http = new YDHttp();
    $menus = json_decode($http->get(YDWX_WEIXIN_BASE_URL."menu/get?access_token=".$accessToken), true);

    $array = array();
    if( ! @$menus['menu']['button'])return array();
    
    foreach ($menus['menu']['button'] as $menu){
        $array[] = YDWXMenu::build($menu);
    }
    return $array;
}

/**
 * 创建菜单
 * 
 * @param unknown $accessToken
 * @param unknown $menus YDWXMenu数组
 * @return boolean
 * @see http://mp.weixin.qq.com/wiki/13/43de8269be54a0a6f64413e4dfa94f39.html
 */
function ydwx_menu_create($accessToken, $menus){
    $http = new YDHttp();
    
    $save_menus = array();
    foreach ($menus as $menu){
        $save_menus['button'][] = $menu->toArray();
    }
    $info = $http->post(YDWX_WEIXIN_BASE_URL."menu/create?access_token=".$accessToken, 
            ydwx_json_encode($save_menus));
    $res  = new YDWXResponse($info);
    if($res->isSuccess())return true;
    throw new YDWXException($res->errmsg.ydwx_json_encode($save_menus), $res->errcode);
}

/**
 * 删除api创建的菜单
 * @param unknown $accessToken
 * @return boolean
 * @see http://mp.weixin.qq.com/wiki/16/8ed41ba931e4845844ad6d1eeb8060c8.html
 */
function ydwx_menu_delete($accessToken){
    $http = new YDHttp();
    $info = json_decode($http->get(YDWX_WEIXIN_BASE_URL."menu/delete?access_token=".$accessToken), true);
    
    return ! $info['errcode'];
    
}

function ydwx_get_current_selfmenu_info($accessToken){
    $http = new YDHttp();
    $menus = json_decode($http->get(YDWX_WEIXIN_BASE_URL."get_current_selfmenu_info?access_token=".$accessToken), true);
    
    $array = array();
    if( ! @$menus['selfmenu_info']['button'])return array();
    
    foreach ($menus['selfmenu_info']['button'] as $menu){
        $array[] = YDWXMenu::build($menu);
    }
    return $array;
}