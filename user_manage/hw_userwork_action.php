<?php
include "../temp/config.php";
$fzx_id=$_SESSION['u']['fzx_id'];
if($_GET['id']){
	$sql = "delete from `n_set` where fzx_id=".$fzx_id." and id=$_GET[id]";
	if($DB->query($sql)){
		 header("Location:hw_userdetile.php?user=$_GET[user]");
	}
	die;
}
if($_GET['zid'] && is_numeric($_GET['zid'])){	
	echo $mod_sql = 'select `id`,`name`,`str1`,`str2`,`str3`,`str4`,`str5` from n_set where fzx_id='.$fzx_id.' and id ='.$_GET['zid'];exit;
	$row = $DB->fetch_one_assoc($mod_sql);
	extract($row);
	disp('hw_userwork_action');
	die;
}
if(!empty($_POST['user'])){
	$assay_value = trim($_POST['assay_value']);
	$method = trim($_POST['method']);
	$expiry_remind = (int)trim($_POST['expiry_remind']);
	if(!$expiry_remind){
		echo '<script>alert("提醒天数必须是数字");history.go(-1)</script>';
		die;
	}
	else
		$expiry_remind = trim($_POST['expiry_remind']);
	$sql="update n_set set `str1`='$assay_value',`str2`='$method',`str4`='$expiry_remind',`str5`='$_POST[expiry_date]' where fzx_id='$fzx_id' and id=$_POST[id] and name = 'worke'";
	if($DB->query($sql)){
		header("location:hw_userdetile.php?user=$user");
	}
}

?>
