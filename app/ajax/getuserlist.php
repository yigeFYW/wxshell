<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/21
 * Time: 19:24
 */
require ('../../library/init.php');
$wx = new Weixin();
//echo $wx->access_token;
$userList = $wx->get_user_list();

//print_r($userList);exit();
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
    $for = $mysql->getAll("select openid from wx_up_user");
    $row = array();
    foreach($for as $v){
        $row[] = $v['openid'];
    }
    foreach($arr as $v) {
        if (!in_array($v, $row)) {
            //如果不存在就调用getinfo方法
            $userinfo = $wx->get_user_info($v);
            $data = array('openid' => $userinfo['openid'], 'nickname' => $userinfo['nickname'], 'sex' => $userinfo['sex'], 'province' => $userinfo['province'], 'city' => $userinfo['city'], 'country' => $userinfo['country'], 'sub_time' => $userinfo['subscribe_time'], 'headimgurl' =>$userinfo['headimgurl']);
            $mysql->Exec("wx_up_user", $data);
            $data = array('openid'=>$userinfo['openid'],'nick'=>$userinfo['nickname'],'sex' => $userinfo['sex'],'address'=>$userinfo['country'].$userinfo['province'].$userinfo['city'],'headimg' =>$userinfo['headimgurl']);
            $mysql->Exec("user",$data);
        }
    }
}
echo "拉取成功!";
