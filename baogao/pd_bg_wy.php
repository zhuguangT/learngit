<?php
/*
	功能：用于判定每份报告的编号是否唯一
	作者: 高龙
	时间：2016-8-3
*/
	include("../temp/config.php");
	$bg_bh = $_POST['bg_bh']; //接受报告编号
	if(isset($bg_bh)){
		$result = $DB->query("SELECT `id` FROM `report` WHERE `bg_bh` = ".$bg_bh."");
		$num = $DB->num_rows($result);
	}
	
	if($num >= 1){
		echo '0';
	}else{
		echo '1';
	}
	
	
	
?>