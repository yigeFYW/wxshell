<?php 

require('../lib/init.php');
$wx = new Weixin();
$a = '{
	"button":[
		{"name":"趣味应用","sub_button":[
			{"name":"玩玩游戏","type":"click","key":"game"},{"name":"查查天气","type":"location_select","key":"weather"},{"name":"测测颜值","type":"pic_photo_or_album","key":"face++"},{"name":"一站到底","type":"click","key":"question"},{"name":"今日福利","type":"click","key":"girl"}
		]},
		{"name":"开发动态","sub_button":[
			{"name":"博客","type":"view","url":"http://hhsblog.cn"},{"name":"营销经验","type":"view","url":"http://youzip.com"}
		]}
	]
}';
$b = $wx->create_menu($a);
if(empty($b)){exit('调用接口失败');}
if(isset($b['errmsg']) && $b['errmsg'] == 'ok'){
	echo '菜单创建成功!';
}else{
	echo '菜单创建失败!';
}
?>