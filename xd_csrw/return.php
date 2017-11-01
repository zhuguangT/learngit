<?php
/**
 * 功能：撤销操作（从已生成化验的状态改变为已接收状态）
 * 作者：zhengsen
 * 时间：2014-06-15
**/
include "../temp/config.php";
if(!$u['userid']){
	nologin();
}
if($_GET['action'] == 'return'){
	$DB->query("UPDATE cy SET status='5' ,csrw_xdcs_user=NULL,xdcs_qz_date=NULL,hyd_count='0',hyd_wc_count='0' WHERE `id`='".$_GET['did']."'");
	$DB->query("DELETE FROM `assay_pay` WHERE `cyd_id`='".$_GET['did']."' AND is_xcjc='0'");
	$sql_xcjc_tid	= $DB->query("SELECT id FROM `assay_pay` WHERE `cyd_id`='{$_GET['did']}' AND `is_xcjc`!='0'");
	$num_xcjc_tid	= $DB->num_rows($sql_xcjc_tid);
	$where_xcjc_tid	= '';
	//退回化验单时，不去除assay_pcy和assay_order中现场检测项目的记录
	if($num_xcjc_tid>0){
		while($rs_xcjc_tid = $DB->fetch_assoc($sql_xcjc_tid)){
			$where_xcjc_tid	.= $rs_xcjc_tid['id'].",";
		}
		$where_xcjc_tid	= " AND `tid` not in (".substr($where_xcjc_tid,0,-1).")";
	}
	$DB->query("DELETE FROM `assay_order` WHERE `cyd_id`='".$_GET['did']."' $where_xcjc_tid");
	gotourl($url[$_u_][1]);
}
?>
