<?php
include("../temp/config.php");
$fzx_id = $u['fzx_id'];
$userid = $u['userid'];
//导航
$trade_global['daohang'] = array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'密码修改','href'=>'./user_manage/userpass.php')
);
if(''==$_GET['action']){
	//头像
	$file_src = $u['portrait'];
	if(''==$u['portrait'] || !file_exists($rootdir.$file_src)){
		$rule =  "/([\x{4e00}-\x{9fa5}]|[0-9a-zA-Z]){1}/u";
		preg_match($rule,$u['userid'],$first_name);
		$portrait = '<span class="nav-user-name">'.$first_name[0].'</span>';
	}else{
		$portrait = '<img class="nav-user-photo" src="'.$rooturl.$file_src.'" alt="'.$u['userid'].'" />';
	}
	//个性签名图片
	$userid_img_src = $u['userid_img'];
	if(''==$u['userid_img'] || !file_exists($rootdir.$userid_img_src)){
		$userid_img_show = '<span class="nav-user-name">无</span>';
		$file_button_name= '上传签名';
	}else{
		$userid_img_show = '<img src="'.$rooturl.$userid_img_src.'" alt="'.$u['userid'].'" />';
		$file_button_name= '更改签名';
	}
	disp('userpass');
}else {
	if($u['id']!=$_GET['id']){
		die('{"error:"1","massage":"只能修改自己密码，禁止非法提交！"}');
	}
	if('user_update_form'==$_GET['action']){
		$desc = trim($_GET['desc']);
		$nickname = trim($_GET['nickname']);
		$has_data = $DB->fetch_one_assoc("SELECT `id` FROM `users` WHERE `nickname`='$nickname' AND `id`!={$u['id']}");
		if(intval($has_data['id'])>0){
			die(json_encode(array('error'=>'1','massage'=>'用户名称'.$nickname.'已被使用，请改用其他名称。')));
		}
		if(''!=$nickname){
			$query = $DB->query("UPDATE `users` SET `nickname`='$nickname',`desc`='$desc' WHERE `id`='{$u['id']}'");
			if($query){
				$_SESSION['u']['nickname'] = $u['nickname'] = $nickname;
				die('{"error":"0"}');
			}else{
				die(json_encode(array('error'=>'1','massage'=>'12个人资料修改失败！')));
			}
		}else{
			die(json_encode(array('error'=>'1','massage'=>'用户名不能为空')));
		}
	}else if('user_pwd_form'==$_GET['action']){
		$pwd_old = trim($_GET['pwd_old']);
		$pwd_new = trim($_GET['pwd_new']);
		$pwd_again = trim($_GET['pwd_again']);
		if(''==$pwd_new||strlen($pwd_new)<6){
			die(json_encode(array('error'=>'1','massage'=>'密码不能为空并且长度不能少于6位！')));
		}
		if(''!=$pwd_new && $pwd_new==$pwd_again){
			$user_info = $DB->fetch_one_assoc("SELECT `password` FROM `users` WHERE `id`='{$u['id']}'");
			if($pwd_old==$pwd_new){
				die(json_encode(array('error'=>'1','massage'=>'您输入新旧密码一样！')));
			}else if(md5($pwd_old)!=$user_info['password']){
				die(json_encode(array('error'=>'1','massage'=>'您输入密码不正确！')));
			}else{
				$password = md5($pwd_new);
				$query = $DB->query("UPDATE `users` SET `password`='$password' WHERE `id`='{$u['id']}'");
				if($query){
					die('{"error":"0"}');
				}else{
					die(json_encode(array('error'=>'1','massage'=>'修改失败')));
				}
			}
		}else{
			die(json_encode(array('error'=>'1','massage'=>'您输入的两次密码不一致！')));
		}
	}else if('file_upload'==$_GET['action'] || 'userid_img'==$_GET['action']){
		$user_id	= $u['id'];
		if(!is_dir($rootdir.'/img/user/'))
		{
			if(!@mkdir($rootdir.'/img/user/')){
				die(json_encode(array('error'=>'1','massage'=>'系统文件夹创建失败，请联系统工程师进行修复。')));
			}
		}
		$input_name	= $_GET['input_name'];
		if(empty($input_name)){
			die(json_encode(array("error"=>'1','massage'=>'请工程师在action链接上增加input_name参数')));
		}
		$files_arr	= $_FILES[$input_name];

		if(!in_array($files_arr['type'],array('image/jpeg','image/png'))){
			die(json_encode(array('error'=>'1','massage'=>'上传图片必须是<strong>png/jpg</strong>格式！')));
		}
		$file_name	= $user_id.'_'.time().'.jpg';
		$file_src	= $rootdir.'/img/user/'.$file_name;
		if('file_upload'==$_GET['action']){
			if(stristr($u[$input_name],"/img/user/")){
				$old_file_src	= $rootdir.$u[$input_name];
			}else{
				$old_file_src	= $rootdir.'/img/user/'.$u[$input_name];
			}
			if(''!=$u[$input_name]&&file_exists($$old_file_src))//判断文件是否存在
			{	//删除文件
				if(!unlink($$old_file_src)){
					die(json_encode(array('error'=>'1','massage'=>'原有头像删除失败，请重新上传！')));
				}
			  	clearstatcache();//清空缓存
			}
		}else{
			//保存签名图片修改记录
			if(!empty($old_file_src)){
				$old_file_src	= $rootdir.$u['userid_img'];
			$insert_userid_img	= $DB->query("INSERT INTO `n_set` SET `module_name`='userid_img',`module_value1`='$old_file_src',`module_value2`='$file_src',`module_value3`='".date('Y-m-d H:i:s')."',`module_value4`='{$user_id}'");
			}
		}
		if(is_uploaded_file($files_arr['tmp_name']))
		{
			if(intval($files_arr['size'])>512000){
				die(json_encode(array('error'=>'1','massage'=>'上传的图像文件不得大于500KB！')));
			}
			if(move_uploaded_file($files_arr['tmp_name'],$file_src)){
				//更新session和$u变量中的头像文件名称
				$file_src = str_replace($rootdir,'',$file_src);
				$_SESSION['u'][$input_name] = $u[$input_name] = $file_src;
				$$input_name = '<img class="nav-user-photo" src="'.$rooturl.$file_src.'" alt="'.$u['userid'].'" />';
				$DB->query("UPDATE `users` SET `$input_name`='$file_src' WHERE `id`='{$user_id}' ");
				die(json_encode(array('error'=>'0','massage'=>'头像更新成功！',$input_name=>$$input_name)));
			}else{
				die(json_encode(array('error'=>'1','massage'=>'头像更新失败，请重新上传。！')));
			}
		}else{
			die(json_encode(array('error'=>'1','massage'=>'没有接收到图片数据，请重新上传！')));
		}
	}else{
		die(json_encode(array('error'=>'1','massage'=>'数据请求有误，请刷新重试！')));
	}
}
?>