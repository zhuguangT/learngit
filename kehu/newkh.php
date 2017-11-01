<?php
include "../temp/config.php";
//导航
$trade_global['daohang'][]	= array('icon'=>'','html'=>'添加客户','href'=>"$rooturl/kehu/newkh.php");
$_SESSION['daohang']['newkh']	= $trade_global['daohang'];
if($_GET['action']=='新建'){
	$title ="<h2>添加客户</h2>";
	$bc = "<input type=\"submit\" class=\"btn btn-xs btn-primary\" name=\"tijiao\" value=\"提交\" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type=\"button\" class=\"btn btn-xs btn-primary\" name=\"fh\" value=\"返回\" onclick=\"location = '$rooturl/kehu/kh_list.php';\"/>";
}elseif($_GET['action']=='删除'){
	$r = $DB->query("update kehu set act=0 where id='".$_GET['kid']."'");
	gotourl("$rooturl/kehu/kh_list.php");die();
}elseif($_GET['action']=='xiugai'){
	$r = $DB->fetch_one_assoc("select * from kehu where id='".$_GET['kid']."'");
	$title ="<h2>修改客户信息</h2>";
	$bc = "<input type=\"submit\" class=\"btn btn-xs btn-primary\" name=\"tijiao\" value=\"修改\" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type=\"button\" class=\"btn btn-xs btn-primary\" name=\"fh\" value=\"返回\" onclick=\"location = '$rooturl/kehu/kh_list.php';\"/>";
}else{
	var_dump($_POST);
	if($_POST){
		if($_POST['kid']){
			$s_where = " id='".$_POST['kid']."'";
		}else{
			$s_where = " name='".$_POST['name']."'";
		}
		//echo "select * from kehu where name='".$_POST['name']."' $s_where";
		$r = $DB->fetch_one_assoc("select * from kehu where $s_where");
		if(!$r['id']){
			$DB->query("insert into kehu set name='".$_POST['name']."',lxr='".$_POST['lxr']."',tel='".$_POST['tel']."',dizhi='".$_POST['dizhi']."',note='".$_POST['note']."'");
		}else{
			$DB->query("update kehu set name='".$_POST['name']."',lxr='".$_POST['lxr']."',tel='".$_POST['tel']."',dizhi='".$_POST['dizhi']."',note='".$_POST['note']."',act='1' where id='".$r['id']."'");
		}
	}
	gotourl("$rooturl/kehu/kh_list.php");
	die();
}
//删除处理

disp("kehu/newkh");

?>

