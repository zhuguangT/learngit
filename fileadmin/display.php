<?php
include '../temp/config.php';

/*
**根据传过来的标示决定映射的页面
**
**
*/
if($_GET['action']=='upload'){//上传文件，传递页面：show.php
	//导航
	$trade_global['daohang'][]	= array('icon'=>'','html'=>'上传文件','href'=>$rooturl.'/fileadmin/display.php?id='.$_GET['id'].'&pid='.$_GET['pid'].'&action='.$_GET['action'].'&name='.$_GET['name']);
	$_SESSION['daohang']['display']= $trade_global['daohang'];
	$trade_global['js']				= array('date-time/bootstrap-datepicker.min.js','date-time/bootstrap-timepicker.min.js','boxy.js');
	$trade_global['css']            = array('lims/main.css','datepicker.css','bootstrap-timepicker.css','lims/buttons.css','boxy.css');

	$id		=  $_GET['id'];
	$pid	=  $_GET['pid'];
	$old_url= $rooturl.'/fileadmin/show.php?id='.$_GET['pid'].'&name='.$_GET['name'];
	$v		= $DB->fetch_one_assoc("select * from filemanage where id=$id");
	if($v['fb_date'] == '0000-00-00'){
		$v['fb_date']	= '';
	}
	$file_name_arr = json_decode($v['old_file_name'] , true);
	$file_link_arr = json_decode($v['file'] , true);
	foreach($file_name_arr as $key=>$value){
		$old_file_name	.= "<a href=upfile/$file_link_arr[$key] target=_blank>".$value.'<br>';
	}
	
	$str	= substr($old_file_name,0,strrpos($old_file_name,'.'));
	$file_name	= $str;
	disp('fileadmin/file_upload');
}


if($_GET['action']=='lei'){//增加类 传递页面：fileadmin.php
	//导航
	$daohang= array(
		array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
		array('icon'=>'','html'=>'文件管理','href'=>$rooturl.'/fileadmin/fileadmin.php'),
		array('icon'=>'','html'=>'增加类','href'=>$rooturl.'/fileadmin/display.php?pid='.$_GET['pid'].'&action=lei')
	);
	$trade_global['daohang']= $daohang;
	$pid=$_GET[pid];
	disp('fileadmin/file_add1');
}
if($_GET['action']=='file'){//给类添加文件（不上传文件）
	//导航
	$trade_global['daohang'][]= array('icon'=>'','html'=>'增加/添加文件','href'=>$rooturl.'/fileadmin/display.php?id='.$_GET['id'].'&action=file&name='.$_GET['name']);
	$_SESSION['daohang']['display'] = $trade_global['daohang'];
	$trade_global['js']				= array('date-time/bootstrap-datepicker.min.js','date-time/bootstrap-timepicker.min.js','boxy.js');
	$trade_global['css']            = array('lims/main.css','datepicker.css','bootstrap-timepicker.css','lims/buttons.css','boxy.css');
	$id		= $_GET[id];
	$name	= $_GET['name'];
	disp('fileadmin/file_add2');
}
?>
