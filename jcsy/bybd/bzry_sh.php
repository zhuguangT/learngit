<?php
/**
 * 功能：标准溶液标定
 * 作者：Mr Zhou
 * 日期：2015-01-21
 * 描述：标准溶液-签字,校核,复核,审核模块
*/
include "../../temp/config.php";
$fzx_id = FZX_ID;
$bzry_id = intval($_POST['bzry_id']);
if($bzry_id == 0){
	gotourl("bzry_list.php");
}
$sql = "SELECT * FROM `jzry_bd` WHERE `id`='$bzry_id' LIMIT 1";
$r=$DB->fetch_one_assoc($sql);

$msg="你没有签字权限,或者你试图重复签名(本系统要求化验人员,校核人员,复核人员,审核人员均不能为同一人)!";
if($_POST['fx_qz']=='签字'){
	if( $u['userid']==$_POST['fx_user'] || $u['admin']){
		$fx_qz_date = empty($r['fx_qz_date']) ? date('Y-m-d') : $r['fx_qz_date'];
		//$fx_qz_date = empty($_POST['fx_qz_date']) ? date('Y-m-d') : $_POST['fx_qz_date'];
		$DB->query("UPDATE `jzry_bd` set `fx_qz_date`='{$fx_qz_date}',`sign_01`='$u[userid]',`status`='正在使用' where `id`=$_POST[bzry_id]");
		$t=$DB->fetch_one_assoc("SELECT * from `jzry_bd` where `id`=$_POST[bzry_id]");
		$msg='';
	}
}else if($_POST['jh_qz']=='签字'){
	if(($u['jh'] && $u['userid']!=$_POST['sign_01']) || $u['admin']){
		$jh_qz_date = empty($r['jh_qz_date']) ? date('Y-m-d') : $r['jh_qz_date'];
		//$jh_qz_date = empty($_POST['jh_qz_date']) ? date('Y-m-d') : $_POST['jh_qz_date'];
		$DB->query("UPDATE `jzry_bd` set `jh_user`='{$u['userid']}',`jh_qz_date`='{$jh_qz_date}' where `id`=$_POST[bzry_id]");
	}
	$msg='';
}else if($_POST['fh_qz']=='签字'){
	if(($u['fh'] && $u['userid']!=$_POST['sign_01'] && $u['userid']!=$_POST['jh_user']) || $u['admin']){
		$fh_qz_date = empty($r['fh_qz_date']) ? date('Y-m-d') : $r['fh_qz_date'];
		//$fh_qz_date = empty($_POST['fh_qz_date']) ? date('Y-m-d') : $_POST['fh_qz_date'];
		$DB->query("UPDATE `jzry_bd` set `fh_user`='{$u['userid']}',`fh_qz_date`='{$fh_qz_date}' where `id`=$_POST[bzry_id]");
	}
	$msg='';
}else if($_POST['sh_qz']=='签字'&&($u['sh'] || $u['admin'])){
		$sh_qz_date = empty($r['sh_qz_date']) ? date('Y-m-d') : $r['sh_qz_date'];
		//$sh_qz_date = empty($_POST['sh_qz_date']) ? date('Y-m-d') : $_POST['sh_qz_date'];
	$DB->query("UPDATE `jzry_bd` set `sh_user`='{$u['userid']}',`sh_qz_date`='{$sh_qz_date}' where `id`=$_POST[bzry_id]");
	$msg='';
}
gotourl("bzry_bd.php?bd_id=$_POST[bzry_id]&action=view",$msg);
?>