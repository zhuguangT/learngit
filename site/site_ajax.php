<?php
/**
 * 功能：验证站点名称和站码
 * 作者：zhangdengsheng
 * 日期：2014-07-09
*/
include '../temp/config.php';
$zt='没有';
if(isset($_GET['code'])){
	$sql="SELECT count(id) AS sl,site_name FROM sites WHERE site_code='$_GET[code]'";
	$sgcc=$DB->fetch_one_assoc($sql);
	if($sgcc['sl']==0){
		$zt='没有';
		$ming='没有';
	}else{
		$zt='已存在';
		$ming=$sgcc['site_name'];
	}
	$arr = array("zt"=>$zt,"ming"=>$ming);
echo json_encode($arr);
	
}
if(isset($_GET['site_name'])){
	$sql="SELECT count(*) AS sl FROM sites WHERE site_name='$_GET[site_name]'";
	$sgcc=$DB->fetch_one_assoc($sql);
	if($sgcc['sl']==0){
		$zt='没有';
	}else{
		$zt='已存在';
	}
	$arr = array("zt"=>$zt);
echo json_encode($arr);
}
?>