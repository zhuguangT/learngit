<?php
/*
**作者：李岩
**时间：2016/5/3
**作用：用来对人员的排序
**
*/
require '../temp/config.php';
$fzx_id	= $_SESSION['u']['fzx_id'];
$old_px = $_POST['s_px'];
$change_px = $_POST['px_id'];
$jid = $_POST['jid'];
if(!empty($_POST['jid']) && $_POST['px_id'] > 0 && is_numeric($_POST['jid']) && is_numeric($_POST['px_id'])){
	if($_POST['s_px']){
		//要区分分中心
		if(!empty($_POST['fzx'])){
			$fzx_id	= $_POST['fzx'];
		}
		if($old_px > $change_px){
			$sql = "SELECT `px_id` , `jid` FROM `hn_users` AS hu LEFT JOIN `users` AS u ON hu.`uid` = u.`id` WHERE hu.`px_id` < '{$old_px}' AND hu.`px_id` >= '{$change_px}' AND u.`fzx_id` = '{$fzx_id}'";
			$re = $DB->query($sql);
			$i = $change_px+1;
			while($data = $DB->fetch_assoc($re)){
				$sql = "UPDATE `hn_users` SET `px_id` = '{$i}' WHERE `jid` = '{$data['jid']}'";
				$DB->query($sql);
				$i++;
			}
			$sql = "UPDATE `hn_users` SET `px_id` = '{$change_px}' WHERE `jid` = '{$jid}'";
			if($DB->query($sql)){
				echo "ok";
			}else{
				echo 'wrong';
			}
			die;
		}else if($change_px > $old_px){
			$sql = "SELECT `px_id` , `jid` FROM `hn_users` AS hu LEFT JOIN `users` AS u ON hu.`uid` = u.`id` WHERE hu.`px_id` > '{$old_px}' AND hu.`px_id` <= '{$change_px}' AND u.`fzx_id` = '{$fzx_id}'";
			$re = $DB->query($sql);
			$i = $old_px;
			while($data = $DB->fetch_assoc($re)){
				$sql = "UPDATE `hn_users` SET `px_id` = '{$i}' WHERE `jid` = '{$data['jid']}'";
				$DB->query($sql);
				$i++;
			}
			$sql = "UPDATE `hn_users` SET `px_id` = '{$change_px}' WHERE `jid` = '{$jid}'";
			if($DB->query($sql)){
				echo 'ok';
			}else{
				echo 'wrong';
			}
			die;
		}
	}
}
?>