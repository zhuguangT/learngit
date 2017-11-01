<?php
require '../temp/config.php';
$fzx_id=$_SESSION['u']['fzx_id'];
//获取正确的上级url，放到导航栏中
$url	= "$rooturl/user_manage/hn_usermanager.php";
foreach ($_SESSION['url_stack'] as $key => $value) {
	if(stristr($value,'hn_usermanager.php?') && !stristr($value,'&print')){
		$url	= $value;
		break;
	}
}
//删除文件
if($_POST['handle'] == 'del_file'){
	$sql = "SELECT * FROM `hn_users` WHERE `uid` = '{$_POST['uid']}'";
	$data  = $DB->fetch_one_assoc($sql);
	$filename_old_arr = json_decode($data['filename_old'] , true);
	$filename_new_arr = json_decode($data['filename_new'] , true);
	if(unlink("./upfile/".$filename_new_arr[$_POST['key']])){
		unset($filename_new_arr[$_POST['key']]);
		unset($filename_old_arr[$_POST['key']]);
		$name_new_json = json_encode($filename_new_arr , JSON_UNESCAPED_UNICODE);
		$name_old_json = json_encode($filename_old_arr , JSON_UNESCAPED_UNICODE);
		$sql = "UPDATE `hn_users` SET `filename_old` = '{$name_old_json}' , `filename_new` = '{$name_new_json}' WHERE `uid` = '{$_POST['uid']}'";
		if($DB->query($sql)){
			echo 'ok';
		}else{
			echo 'wrong';
		}
	}
	die;
}
if(!empty($_FILES['upfile']['name'][0])){
	foreach($_FILES['upfile']['name'] as $key=>$value){
		if($_FILES['upfile']['size'][$key]<100000000){
			$xxx	= explode('.',$_FILES['upfile']['name'][$key]);
			$cnt	= count($xxx);
			$newname= date(ymdhis).$u['fzx_id']."_{$key}.".$xxx[$cnt-1]; 
			$path	= "./upfile/".$newname;
			//将文件移入指定文件夹
			if(move_uploaded_file($_FILES['upfile']['tmp_name'][$key],$path)){
				$name_new_arr[] = $newname;
				$name_old_arr[] = $_FILES['upfile']['name'][$key];
			}
		}
	}
	$name_new_json = json_encode($name_new_arr , JSON_UNESCAPED_UNICODE);
	$name_old_json = json_encode($name_old_arr , JSON_UNESCAPED_UNICODE);
	$file_sql = ",`filename_old` = '{$name_old_json}' , `filename_new` = '{$name_new_json}'";
}else{
	$file_sql = '';
}
if(empty($_POST)){//未点击修改按钮之前
		//#########导航
	$daohang = array(
		array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
		array('icon'=>'','html'=>'人员档案管理','href'=>$url),
		array('icon'=>'','html'=>'修改'.$_GET['r'].'个人信息','href'=>'hn_usermanager_mod.php?r='.$_GET['r'].'&uid='.$_GET['uid'].'&sex='.$_GET['sex']),
	);
	$trade_global['js'] = array('jquery.date_input.js');
	$trade_global['css'] = array('date_input.css');
	$trade_global['daohang'] = $daohang;

	$username=$_GET['r']; 
	$id=$_GET['uid'];
	$sex=$_GET['sex'];
	if($sex == '男'){
		$nan = 'selected';
		$nv = '';
	}else{
		$nan = '';
		$nv = 'selected';
	}	   
		$r=$username;
		$sex=$sex;	  
		$sql="SELECT hn_users.*,users.group FROM `hn_users` join users on users.id=hn_users.uid  WHERE hn_users.uid='$id'";
		$R=$DB->fetch_one_array($sql);
		if(!empty($R['filename_old']) && !empty($R['filename_new'])){
			$filename_old_arr = json_decode($R['filename_old'] , true);
			$filename_new_arr = json_decode($R['filename_new'] , true);
			foreach($filename_old_arr as $key=>$value){
				$files .= "<span style='white-space:nowrap;'><a href='./upfile/{$filename_new_arr[$key]}'>$value</a>&nbsp;<a class='glyphicon glyphicon-remove red' style='cursor:pointer;' onclick='del_file(this,$R[uid],$key);'></a><br></span>";
			}
		}

		$jid=$R['jid'];
		
		//echo $jid;
		if($jid!=''){
		$minzu=$R['minzu'];
		$zsny=$R['csrq'];
		$zm=$R['zm'];
		$zc=$R['zc'];
		$gw=$R['gw'];
		$zhiwu=$R['zhiwu'];    
		
		$canjia=$R['gzny'];
		$nx=$R['gwsj'];
		$jsnx	= $R['jsnx'];

		$bysj=$R['bysj'];
		$xuexiao=$R['xuexiao'];
		$zhuanye=$R['zy'];
		$xuezhi=$R['xuezhi'];
		$xuewei=$R['whcd'];
		$jingli=$R['gzjl'];
		$jilu=$R['jzjl'];
		$impower=$R['impower'];
		$education=$R['education'];
		$train=$R['train'];
		$job=$R['job'];
		$beizu=$R['bz'];
		if($R['group']=='离职'){
			$zaizhi="离职";
			$lz_che="selected='selected'";
			$zz_che='';
		}else{
			$zaizhi="在职";
			$lz_che="";
			$zz_che="selected='selected'";
		}
	}
	if(!empty($xuewei)){
		$xuewei = "<option value='$xuewei' selected>$xuewei</option>";
	}
	if(!empty($zc)){
		$zc = "<option value = '$zc' selected>$zc</option>";
	}
	disp('user_manager/usermanager_modify');
}else{
	$username=$_POST['r']; 
	$id=$_POST['id'];
	$sex=$_POST['sex'];
	$minzu=$_POST['minzu'];
	
	$zsny=$_POST['zsny'];
	$canjia=$_POST['canjia'];
	$zm=$_POST['zm'];
	$zc=$_POST['zc'];
	$gw=$_POST['gw'];
	$nx=$_POST['nx'];
	$jsnx	= $_POST['jsnx'];
	$zhiwu=$_POST['zhiwu'];
	$bysj=$_POST['bysj'];
	$xuexiao=$_POST['xuexiao'];
	$zhuanye=$_POST['zhuanye'];
	$xuezhi=$_POST['xuezhi'];
	$xuewei=$_POST['xuewei'];
	$jingli=$_POST['jingli'];
	$jilu=$_POST['jilu'];
	$impower=$_POST['impower'];
	$education=$_POST['education'];
	$train=$_POST['train'];
	$job=$_POST['job'];
	$beizu=$_POST['beizu'];    
	$sql="SELECT * FROM `hn_users`  WHERE uid='$id'";
	$R=$DB->fetch_one_array($sql);
	$jid=$R['jid'];
	//echo $uid;exit;
	if($jid!=''){
		//更新
		 $R=$DB->query("update  `hn_users` set csrq='$zsny',whcd='$xuewei',zc='$zc',gw='$gw',gwsj='$nx',`jsnx`='$jsnx',minzu='$minzu',
			gzny='$canjia',zm='$zm',zhiwu='$zhiwu',bysj='$bysj',xuexiao='$xuexiao',zy='$zhuanye',xuezhi='$xuezhi',gzjl='$jingli',jzjl='$jilu',impower='$impower',education='$education',train='$train',job='$job'
			,bz='$beizu'  $file_sql  WHERE uid='$id'");
		}
		else{
			//插入
			$R=$DB->query("insert into  `hn_users`  set uid=$id,csrq='$zsny',whcd='$xuewei',zc='$zc',gw='$gw',gwsj='$nx',`jsnx`='$jsnx',minzu='$minzu',
			gzny='$canjia',zm='$zm',zhiwu='$zhiwu',bysj='$bysj',xuexiao='$xuexiao',zy='$zhuanye',xuezhi='$xuezhi',gzjl='$jingli',jzjl='$jilu',impower='$impower',education='$education',train='$train',job='$job'
			,bz='$beizu' $file_sql");
		}
	$s_name = $_POST['s_name']; //原来的姓名
	$s_sex = $_POST['s_sex'];//原来的性别
	$s_zz = $_POST['s_zz'];//原来的在职状态
	$xingming = $_POST['xingming'];//新的姓名
	$sex = $_POST['sex'];//新的性别
	$zz = $_POST['zz'];//新的在职状态
	//如果姓名或性别或职务状态 有变化 则更新users表
	if($s_name != $xingming || $s_sex != $sex || $s_zz != $zz){
		if($zz=='在职'&& $s_zz=='离职')//确定在职状态改变
		{
			$zz_sql=",`group` = '' ";
		}elseif($zz=='离职')//确定新的在职状态为离职
		{
			$zz_sql=", `group` = '离职' ";
		}else{//在职状态不发生改变
			$zz_sql='';
		}
		$users = "update users set `userid`='$xingming',`sex`='$sex' $zz_sql where fzx_id='".$fzx_id."' and id='$id'";
		$DB->query($users);
	}
	echo '<div style="text-align:center;margin-top:200px;">修改成功,1秒后返回</div>',"<script>setTimeout('location.href=\"$url\"',1000);</script>";//$rooturl/user_manage/hn_usermanager.php
	//gotourl("$rooturl/user/hn_usermanager.php");
}
?>
