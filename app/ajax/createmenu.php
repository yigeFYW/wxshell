<?php 

require('../../library/init.php');
$wx = new Weixin();
$a = '{
	"button":[
		{"name":"趣味应用","sub_button":[
			{"name":"小游戏","type":"click","key":"game"},{"name":"查天气","type":"location_select","key":"weather"},{"name":"测颜值","type":"pic_photo_or_album","key":"face++"},{"name":"录音机","type":"click","key":"luyin"},{"name":"一站到底","type":"click","key":"question"}
		]},
		{"name":"个人简历","sub_button":[
			{"name":"基本信息","type":"view","url":"http://jl.hhsblog.cn/index.php"},{"name":"我会什么","type":"view","url":"http://jl.hhsblog.cn/skill.php"},{"name":"项目经验","type":"view","url":"http://jl.hhsblog.cn/production.php"},{"name":"联系我","type":"view","url":"http://jl.hhsblog.cn/contact.php"}
		]},
		{"name":"开发动态","sub_button":[
			{"name":"个人博客","type":"click","key":"blog"},{"name":"营销经验","type":"view","url":"http://www.yzipi.com"},{"name":"最新文章","type":"click","key":"news"}
		]}
	]
}';
/*
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
*/
$b = $wx->create_menu($a);
if(empty($b)){exit('调用接口失败');}
if(isset($b['errmsg']) && $b['errmsg'] == 'ok'){
	echo '菜单创建成功!';
}else{
	echo '菜单创建失败!';
}
?>
