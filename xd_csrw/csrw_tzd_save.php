<?php
/**
 * 功能：保存测试任务通知单的信息、测试任务通知单签字
 * 作者：zhengsen
 * 时间：2014-06-30
**/
require_once '../temp/config.php';
$fzx_id = $u['fzx_id'];
//查询所有人员
$renarr = array();
$rensql = $DB->query("select id,userid from users where fzx_id='".$fzx_id."' and `group`<>'0'");
while($ren = $DB->fetch_assoc($rensql)){
	$renarr[$ren['userid']] = $ren['id'];
}
$cyd_id = intval($_POST['cyd_id']);
!$cyd_id && die("采样单ID错误！");
if(!empty($_POST['csrw_xdcy_user'])){
	$cy_rs=$DB->fetch_one_assoc("SELECT status,json FROM cy WHERE id='{$cyd_id}'");
	$cy_json_arr= json_decode($cy_rs['json'],true);
	$cy_json_arr['userid_img']['cy_rwxd_user']	= $u['userid_img'];//电子签名信息
	if($cy_rs['status']<1){
		$cy_json	= JSON($cy_json_arr);
		$DB->query("UPDATE cy SET cy_rwxd_user='{$u['userid']}',cy_rwxd_user_qz_date=CURDATE(),status='1',`json`='{$cy_json}' WHERE id='{$cyd_id}'");
	}
	$cy_json_arr['userid_img']['csrw_xdcy_user']	= $u['userid_img'];//电子签名信息
	$cy_json	= JSON($cy_json_arr);
	$cy_sql="UPDATE `cy` SET  jcwc_date='{$_POST['jcwc_date']}',cy_dept='{$_POST['cy_dept']}',jc_dept='{$_POST['jc_dept']}',jc_yiju='{$_POST['jc_yiju']}',
	csrw_tzd_note='{$_POST['csrw_tzd_note']}',csrw_xdcy_user='{$u['userid']}',xdcy_qz_date=CURDATE(),`json`='{$cy_json}' WHERE id='{$cyd_id}'";
	$cy_query=$DB->query($cy_sql);
}
elseif(!empty($_POST['csrw_xdcs_user'])){
	$cy_rs		= $DB->fetch_one_assoc("SELECT json FROM cy WHERE id='".$_POST['cyd_id']."'");
	$cy_json_arr= json_decode($cy_rs['json'],true);
	$cy_json_arr['userid_img']['csrw_xdcs_user']	= $u['userid_img'];//电子签名信息
	$cy_json	= JSON($cy_json_arr);
	$cy_sql		= "UPDATE `cy` SET  jcwc_date='{$_POST['jcwc_date']}',cy_dept='{$_POST['cy_dept']}',jc_dept='{$_POST['jc_dept']}',jc_yiju='{$_POST['jc_yiju']}',
	csrw_tzd_note='{$_POST['csrw_tzd_note']}',csrw_xdcs_user='{$u['userid']}',xdcs_qz_date=CURDATE(),`json`='{$cy_json}' WHERE id='{$cyd_id}'";
	$cy_query=$DB->query($cy_sql);
	if(!empty($_POST['cy_user'])){
		$DB->query("UPDATE `cy` SET cy_date='{$_POST['cy_date']}',cy_user='{$_POST['cy_user']}',cy_user2='{$_POST['cy_user2']}' WHERE id='{$cyd_id}'");
		$uid1 = $renarr[$_POST['cy_user']];
		$uid2 = $renarr[$_POST['cy_user2']];
		$DB->query("UPDATE `assay_pay` SET `userid`='{$_POST['cy_user']}',uid='$uid1',uid2='$uid2',`userid2`='{$_POST['cy_user2']}' WHERE cyd_id='{$cyd_id}' AND is_xcjc='1'");
	}
	//签字后同时生成化验单
	gotourl("$rooturl/xd_csrw/create_hyd.php?cyd_id={$cyd_id}");
}else{
	$cy_sql="UPDATE `cy` SET  jcwc_date='{$_POST['jcwc_date']}',cy_dept='{$_POST['cy_dept']}',jc_dept='{$_POST['jc_dept']}',jc_yiju='{$_POST['jc_yiju']}',
	csrw_tzd_note='{$_POST['csrw_tzd_note']}' WHERE id='{$cyd_id}'";
	$cy_query=$DB->query($cy_sql);
	if(!empty($_POST['cyd_bh'])){
		$DB->query("UPDATE `cy` SET cyd_bh='{$_POST['cyd_bh']}',cy_date='{$_POST['cy_date']}' WHERE id='{$cyd_id}'");
	}
	if(!empty($_POST['cy_user'])){
		$DB->query("UPDATE `cy` SET cy_date='{$_POST['cy_date']}',cy_user='{$_POST['cy_user']}',cy_user2='{$_POST['cy_user2']}' WHERE id='{$cyd_id}'");
		$uid1 = $renarr[$_POST['cy_user']];
		$uid2 = $renarr[$_POST['cy_user2']];
		$DB->query("UPDATE `assay_pay` SET `userid`='{$_POST['cy_user']}',uid='$uid1',uid2='$uid2',`userid2`='{$_POST['cy_user2']}' WHERE cyd_id='{$cyd_id}' AND is_xcjc='1'");
	}
}
if(empty($_POST['hid'])){
	gotourl("fp_csrw.php?cyd_id={$cyd_id}");
}
?>
