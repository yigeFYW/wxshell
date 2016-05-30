<?php 

function joke(){
$mysql = new Mysql();
$id = rand(1,31);
$sql = "select content from joke where id =".$id."";
$joke = $mysql->getOne($sql);
return $joke;
}


?>