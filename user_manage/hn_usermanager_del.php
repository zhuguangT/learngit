<?php
	require '../temp/config.php';
	$fzx_id=$_SESSION['u']['fzx_id'];
	$uid = $_GET['uid'];
	$userid = $_GET['userid'];
	if(strstr($_SERVER['HTTP_REFERER'],'hn_usermanager.php')){
		if(is_numeric($uid) && $userid != 'admin'){
			$sql = 'delete from hn_users where `uid`='.$uid;
			$sql_user = 'delete from users where fzx_id='.$fzx_id.' and `id`='.$uid;
			//if($DB->query($sql) && $DB->query($sql_user))
			if($DB->query($sql_user))
				gotourl("$rooturl/user_manage/hn_usermanager.php");
		}elseif($userid == 'admin')
			echo '<div style="text-align:center;margin-top:200px">不能删除admin,1秒后返回</div>','<script>setTimeout("history.go(-1)",1000);</script>';

	}
?>