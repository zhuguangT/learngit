<?php
require '../temp/config.php';
$fzx_id=$_SESSION['u']['fzx_id'];

//判断传递过来的m值（从人员档案管理目录传递过来的）
if($_GET['m'] == 'file'){
	$all = explode('-', $_GET['all']);
	$id = $all[0];
	$uid = $all[1];
	$username= $all[2];

	//#########导航
	$daohang = array(
		array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
		array('icon'=>'','html'=>'人员档案管理','href'=>'user_manage/hn_usermanager.php'),
		array('icon'=>'','html'=>$username.'档案管理目录','href'=>'user_manage/hn_files.php?r='.$username.'&uid='.$uid),
		array('icon'=>'','html'=>'修改'.$username.'档案','href'=>'user_manage/hn_files_add.php?all='.$_GET['all'].'&m=file')
	);
	$trade_global['daohang'] = $daohang;

	$sql = "select * from user_files where fzx_id='$fzx_id' and `id` = $id and `u_id`=$uid";
	$row = $DB->fetch_one_assoc($sql);
	if(!empty($row['src'])){
		$check_file = '查看文件';
		$del_file = '删除文件';
	}
	disp('user_manager/hn_files_mod');
	die;
}
if($_GET['m'] == 'del_file'){
	extract($_GET);
	$update = '';
	if(file_exists('./upload/'.$file))
		@unlink('./upload/'.$file);
	$update = "update user_files set `src`='' where fzx_id='$fzx_id' and `id`=$oid and `u_id`=$uid";
	$DB->query($update);
	echo '<script>history.go(-1)</script>';
	die;
}
if(!empty($_POST['uid']) && is_numeric($_POST['uid'])){
	$_POST = array_map('mysql_real_escape_string', $_POST);
	extract($_POST);
	/*
			档案名称	档案编号	归档日期	销毁日期	备注	文件	序号 	用户id	父id
		id 	name 		str1 		str2 		str3 		str4 	str5 	int1 	int2 	int3 	json
	*/
	if(!empty($_FILES['file'])){
		if($_FILES[file][size]<100000000){	
			$xxx = explode('.',$_FILES[file][name]);
			$cnt = count($xxx);
			$newname =date('Ymdhis').".".$xxx[$cnt-1]; 
			$path = "$rootdir/user_manage/upload/".$newname;
				if(!move_uploaded_file($_FILES[file][tmp_name],$path)){
					$newname = '';
				}
				
		}
	}
	if(!empty($newname)){
		$old = "select `src` from user_files where fzx_id='$fzx_id' and `id`=$oid and `u_id`=$uid";
		$row = $DB->fetch_one_assoc($old);
		if(file_exists('./upload/'.$row['src']))
			@unlink('./upload/'.$row['src']);
		$sql = "update user_files set `name`='$bdname',`remark`='$beizhu',`src`='$newname' where fzx_id='$fzx_id' and `id`=$oid and `u_id`=$uid";
	}else{
		$sql = "update user_files set `name`='$bdname',`remark`='$beizhu' where fzx_id='$fzx_id' and `id`=$oid and `u_id`=$uid";
	}
	$DB->query($sql);
	if($DB->affected_rows())
		echo '<div style="text-align:center;margin-top:200px;">修改成功,1秒后返回</div>',"<script>setTimeout('location.href=\"hn_files.php?pid=$pid&qj_name=$qj_name&r=$r&uid=$uid\"',1000);</script>";
	else
		echo '<div style="text-align:center;margin-top:200px;">数据修改失败或者数据未修改,2秒后返回</div>',"<script>setTimeout('location.href=\"hn_files.php?pid=$pid&qj_name=$qj_name&r=$r&uid=$uid\"',2000);</script>";
}
?>
