<?php 
require("../lib/init.php");
$mysql = new Mysql();
if( (!isset($_GET['openid'])) || (!isset($_GET['id']))){
	header("Location:http://hhsblog.cn/index.html");
}
//有GET数据时判断是否在数据库中存在
$openid = $_GET['openid'];
$id = $_GET['id'];
$sql = "select * from WX_face where openid='".$openid."' and id=".$id.";";
$sql1 = "select nickname from WX_up_user where openid='".$openid."';";
$facelist = $mysql->getAll($sql);//脸部信息
$nickname = $mysql->getOne($sql1);//昵称
$double = false;
$zhishu = '';
if(count($facelist) == 2){
	$double = true;//为true为两个人
	if($facelist[0]['gender'] == $facelist[1]['gender']){
		//性别不等
		$zhishu = "【夫妻相指数】";
	}else{
		//性别相等
		if($facelist[0]['gender'] == "帅哥"){
			$zhishu = "【基友指数】";
		}else{
			$zhishu = "【闺蜜指数】";
		}
	}
}
foreach($facelist as $k=>$v){
	if($v['gender'] == "帅哥"){
		//为男
		if($v['yanzhi']<50){
			$a = "<br>该看医生了!";
		}else if($v['yanzhi']<60){
			$a = "<br>一个丑男无疑!";
		}else if($v['yanzhi']<70){
			$a = "<br>一张大众脸!";
		}else if($v['yanzhi']<80){
			$a = "<br>勉强算帅哥!";
		}else if($v['yanzhi']<90){
			$a = "<br>帅哥!我们做朋友吧!";
		}else{
			$a = "<br>你超帅!你超帅!你超帅!";
		}
	}else{
		//为女
		if($v['yanzhi']<50){
			$a = "<br>该看医生了!";
		}else if($v['yanzhi']<60){
			$a = "<br>死宅女一枚!";
		}else if($v['yanzhi']<70){
			$a = "<br>花好月圆!";
		}else if($v['yanzhi']<80){
			$a = "<br>一枚白富美!!";
		}else if($v['yanzhi']<90){
			$a = "<br>美女!我们做朋友吧!";
		}else{
			$a = "<br>美女,你的颜值爆表!";
		}
	}
	$facelist[$k]['yanzhipy'] = $a; 
}



include(ROOT."/app/face.html");
?>