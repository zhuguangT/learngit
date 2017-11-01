<?php
	include "../temp/config.php";
	$fzx_id=$_SESSION['u']['fzx_id'];

//#########导航
$daohang = array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
		array('icon'=>'','html'=>'人员档案管理','href'=>'user_manage/hn_usermanager.php'),
        array('icon'=>'','html'=>$_GET['r'].'档案管理目录','href'=>'user_manage/hn_files.php?r='.$_GET['r'].'&uid='.$_GET['uid']),
        array('icon'=>'','html'=>'添加'.$_GET['r'].'档案','href'=>'user_manage/hn_files_add.php?r='.$_GET['r'].'&uid='.$_GET['uid'])
);
$trade_global['daohang'] = $daohang;

$name=$_GET['r'];
	$qj_name = $_GET['qj_name'];
	if($_GET['m'] == 't'){
		$uid = $_GET['uid'];
		$username = $_GET['r'];
		disp('user_manager/hn_files_add');
		die;
	}
	if(empty($_POST['bdname'])){
     		echo "<script>alert('表单名称不能为空');history.go(-1)</script>";
     		die;
    }
		$path ='';
		if(!empty($_FILES['file'])){
			if($_FILES[file][size]<100000000){	
				$file_name = $_FILES['file']['name'];
				$xxx = explode('.',$_FILES[file][name]);
				$cnt = count($xxx);
				$newname =date('Ymdhis').".".$xxx[$cnt-1]; 
				if(!is_dir("$rootdir/user_manage/upload/")){
					mkdir("$rootdir/user_manage/upload/",0777);
				}
				$path = "$rootdir/user_manage/upload/".$newname;
					if(!move_uploaded_file($_FILES[file]['tmp_name'],$path)){
						$newname = '';
					}
					
			}
		}
		$_POST = array_map('mysql_real_escape_string', $_POST);
		extract($_POST,EXTR_OVERWRITE);
		/*
			字段int1存储序号 int2 存储用户id  str1存储档案编号 str2存储归档日期 str3存储销毁日期 str4存储备注 name存储档案名称
				档案名称	档案编号	归档日期	销毁日期	备注	文件	序号 	用户id	父id
			id 	name 		str1 		str2 		str3 		str4 	str5 	int1 	int2 	int3 	json
		*/
		if(!empty($uid) && is_numeric($uid)){ 
			//$addsql = "insert into n_set(`name`,`str5`,`str4`,`int2`,`fzx_id`) values('$bdname','$newname','$beizhu','$uid','$fzx_id')";
			$addsql = "insert into user_files (`name`,`file_name`,`src`,`remark`,`u_id`,`fzx_id`,`time`) values ('$bdname','$file_name','$newname','$beizhu','$uid','$fzx_id','".time()."')";
			$addsqls = $DB->query($addsql);
			gotourl("hn_files.php?uid=$uid&r=$r");
		}
?>
