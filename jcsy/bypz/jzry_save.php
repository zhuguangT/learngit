<?php
/**
 * 功能：标(基)准溶液配制
 * 作者：Mr Zhou
 * 日期：2014-12-02
 * 描述：
*/
include '../../temp/config.php';
$fzx_id=FZX_ID;
if(''==$_GET['wz_nd']){
	$_GET['wz_nd'] = '-';
}
$ziduan = array( 'vid', 'pzrq', 'sjmc', 'ry_type', 'wz_type', 'wz_mc', 'hxs', 'wz_id', 'sj_id', 'start_date', 'gztj', 'wz_bh', 'wz_nd', 'wz_z', 'drtj', 'sy_rj', 'sj_nd', 'sj_yxrq', 'pz_note', 'pz_user');
foreach($ziduan as $value){
	if(''!=trim($_GET[$value])){
		$sql_arr[] = "`$value`='".trim($_GET[$value])."'";
	}
}
$sql_str = implode(',', $sql_arr);
if('add' == $_GET['action']){
	$sql = "INSERT INTO jzry SET `fzx_id`='$fzx_id', $sql_str,`create_date`=curdate()";
}else if('edit' == $_GET['action']){
	$sql = "UPDATE `jzry` SET `fzx_id`='$fzx_id', $sql_str WHERE `id`='{$_GET['byid']}'";
}else if('delete'== $_GET['action']){
	$sql = "DELETE FROM `jzry` WHERE `fzx_id`='$fzx_id' AND `id` = '{$_GET['byid']}'";
}
$DB->query($sql);
gotourl($last_url);