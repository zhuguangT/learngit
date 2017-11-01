<?php
include "../temp/config.php";
//导航
if($_GET['action']=='add'){
  $daohang= array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'仪器管理','href'=>"$rooturl/yiqi/hn_yiqimanager.php"),
        array('icon'=>'','html'=>'仪器管理目录','href'=>$_SESSION['url_stack'][1]),
	array('icon'=>'','html'=>'新增仪器记录','href'=>"$rooturl/yiqi/yiqijb.php?id={$_GET['id']}&action={$_GET['action']}")
  );
}else{
	$daohang= array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'仪器管理','href'=>"$rooturl/yiqi/hn_yiqimanager.php"),
        array('icon'=>'','html'=>'仪器管理目录','href'=>$_SESSION['url_stack'][1]),
        array('icon'=>'','html'=>'修改仪器记录','href'=>"$rooturl/yiqi/yiqijb.php?id={$_GET['id']}&action={$_GET['action']}")
  );
}
$trade_global['daohang']= $daohang;
if($_GET['action']=='add'){
	$title = "<h3 class='header smaller center title'>新增仪器记录</h3>";
	$id = $_GET['id'];
	$luan = $id;
	$sub = "<input type=\"submit\" name=\"add\" value=\"保存\">";
}
if($_GET['action']=='fix'){
	$bd = $_GET['bd'];
	$id = $_GET['id'];
	$pid = $_GET['pid'];
	$bz = $_GET['bz'];
	$fi = $_GET['file'];
	$title = "<h3 class='header smaller center title'>修改仪器记录</h3>";
	$file = "<input type=\"hidden\" name=\"file2\" value=\"$fi\">";
	$pid = "<input type=\"hidden\" name=\"pid\" value=\"$pid\">";
	$sub = "<input type=\"submit\" name=\"fix\" value=\"修改\">";
	$luan = '';
}
disp('yiqijb');
?>
