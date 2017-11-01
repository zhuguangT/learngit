<?php
/**
 * 功能： 报告模板信息的修改
 * 作者： zhengsen
 * 日期： 2015-10-26
 * 描述：处理报告模版列表页面的操作请求 
*/
include '../temp/config.php';
if($_GET['mbid']&&$_GET['action']=='change_status'){

	$sql = "SELECT state FROM report_template WHERE id ='".$mbid."'"; 
	$rs=$DB->fetch_one_assoc($sql);
	if($rs['state'] == 1){
		$state = 0;
	}else{
		$state = 1;
	}
	$sql = "UPDATE report_template SET state = '".$state."' WHERE id = '".$mbid."'";
	$query = $DB->query($sql);
}


if($_POST['mbid']){
	$sql = "UPDATE report_template SET te_name = '".$_POST['mbname']."',state = '".$_POST['state']."',jiego = '".$_POST['sm_mb_order']."',hang1 = '".$_POST['hang1']."' ,hang2 = '".$_POST['hang2']."' WHERE id ='".$_POST['mbid']."'";
	$query = $DB->query($sql);

}
if($DB->affected_rows()){	  
	echo "<script>alert('更新成功！');location.href='bg_mb_list.php'</script>";
}else{
	echo "<script>alert('更新失败！请联系系统管理员');location.href='bg_mb_list.php'</script>";
}

?>
