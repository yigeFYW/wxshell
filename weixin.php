<?php
/***
 * 微信开发入口文件!
 * 作者:一个放羊娃
 * 电话:18695275660
 * 微信:huanghe6233
 * 个人博客:hhsblog.cn
 * 2016年4月7日
 ***/
include('./library/init.php');
$wechatObj = new wechatCallbackapiTest();
//判断是否是验证访问,已验证访问responseMsg()方法
if(!isset($_GET['echostr'])){
    $wechatObj->responseMsg();
}else{
    $wechatObj->valid();
}


?>