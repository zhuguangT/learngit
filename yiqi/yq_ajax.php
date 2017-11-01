<?php
include "../temp/config.php";
if($_GET['act']=='xiufenlei'){
	$yqid = $_GET['yqid'];
	$fenlei = $_GET['fenlei'];
	$upsql = $DB->query("update yiqi set yq_xianshi='".$fenlei."' where id='".$yqid."'");
	if($upsql){
		echo 'ok';
	}else{
		echo 'wrong';
	}
}
?>