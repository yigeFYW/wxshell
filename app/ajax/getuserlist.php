<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/21
 * Time: 19:24
 */
require ('../lib/init.php');
$wx = new Weixin();
$userList = $wx->get_user_list();
$num = $userList['total'];
find($userList);
//判断是否已拉取完成
$i = 1;
$j = $userList['total']/$userList['count'];
if($j != 1){
    //用户多余1万
    while($i<$j){
        $user = $wx->get_user_list($userList['next_openid']);
        find($user);
        $i++;
    }
}

function find($userList){
    $arr = $userList['data']['openid'];
    $for = array();
    $mysql = new Mysql();
    $wx = new Weixin();
    $for = $mysql->getAll("select openid from WX_up_user");
    $row = array();
    foreach($for as $v){
        $row[] = $v['openid'];
    }
    foreach($arr as $v) {
        if (!in_array($v, $row)) {
            //如果不存在就调用getinfo方法
            $userinfo = $wx->get_user_info($v);
            $data = array('openid' => $userinfo['openid'], 'nickname' => $userinfo['nickname'], 'sex' => $userinfo['sex'], 'province' => $userinfo['province'], 'city' => $userinfo['city'], 'country' => $userinfo['country'], 'sub_time' => $userinfo['subscribe_time'], 'headimgurl' => "hhsblog.cn" . $wx->down_headimg($userinfo['headimgurl']));
            $mysql->Exec("WX_up_user", $data);
        }
    }
}
echo "拉取成功!";