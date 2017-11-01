<?php
include "../temp/config.php";
$fzx_id=$_SESSION['u']['fzx_id'];
if($_POST['action'] == 'uptime'){
	$sesql = $DB->fetch_one_assoc("select * from users_zheng where fid='".$_POST['fid']."' and userid='".$_POST['uid']."'");
	if($sesql['id']){
		$upsql = $DB->query("update users_zheng set limit_date ='".$_POST['shijian']."' where fid='".$_POST['fid']."' and userid='".$_POST['uid']."'");
		if($upsql){
			echo 'ok';
		}else{
			echo 'wrong';
		}
	}else{
		$insql = $DB->query("insert into users_zheng set limit_date ='".$_POST['shijian']."',fid='".$_POST['fid']."',userid='".$_POST['uid']."'");
		if($insql){
			echo 'ok';
		}else{
			echo 'wrong';
		}
	}
	
}