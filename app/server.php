<?php 

require('../library/init.php');
if(isset($_GET['numup'])){
	$mysql = new Mysql();
	$id = $_GET['id'];
	$mysql->query("update WX_face set zan = zan+1 where id=".$id.";");
	echo $mysql->getOne("select zan from WX_face where id=".$id.";");
}
if(isset($_GET['_num'])){
	$mysql = new Mysql();
	$id = $_GET['id'];
	$mysql->query("update WX_face set zan = zan-1 where id=".$id);
	echo $mysql->getOne("select zan from WX_face where id=".$id);	
}

?>