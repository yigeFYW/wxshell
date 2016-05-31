<?php 
require('../../library/init.php');
$wx = new Weixin();
echo $wx->delete_menu()==ture?'删除成功!':'删除失败!';
?>