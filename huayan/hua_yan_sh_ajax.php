<?php
/**
 * 功能：化验单签字
 * 作者: Mr Zhou
 * 日期: 2015-05-17
 * 描述: 化验单-签字,校核,复核,审核模块
 */
include '../temp/config.php';
require ('./assay_form_func.php');
$arow=$DB->fetch_one_assoc("SELECT * FROM `assay_pay` WHERE `id`={$_GET['hyd_id']}");
$sql = array();
$error = array('error'=>'0','content'=>'success','date'=>date('Y-m-d'));
if($_GET['jh_qz']=='sign'){
	if($u['jh'] || $u['admin']){
		$sign_date_02 = empty($arow['sign_date_02']) ? date('Y-m-d') : $arow['sign_date_02'];
		$sql[] = "UPDATE `assay_pay` SET `sign_02`='{$u['userid']}',`sign_date_02`='{$sign_date_02}',`over`='已校核' WHERE `over`='已完成'";
	}else{
		$error = array('error'=>'1','content'=>'你没有签字权限');
	}
} 
if($_GET['fh_qz']=='sign'){
	if($u['fh'] || $u['admin']){
		$sign_date_03 = empty($arow['sign_date_03']) ? date('Y-m-d') : $arow['sign_date_03'];
		$sql[] = "UPDATE `assay_pay` SET `sign_03`='{$u['userid']}',`sign_date_03`='{$sign_date_03}',`over`='已复核' WHERE `over`='已校核'";
	}else{
		$error = array('error'=>'1','content'=>'你没有签字权限');
	} 
}
if($_GET['sh_qz']=='sign'){
	if($u['sh'] || $u['admin']!='1'){
		$sign_date_04 = empty($arow['sign_date_04']) ? date('Y-m-d') : $arow['sign_date_04'];
		$sql[] = "UPDATE `assay_pay` SET `sign_04`='{$u['userid']}',`sign_date_04`='{$sign_date_04}',`over`='已审核' WHERE `over`='已复核'";
	}else{
		$error = array('error'=>'1','content'=>'你没有签字权限');
	} 
}
if($_GET['userid2']=='sign'){
		$sign_date_012 = empty($arow['sign_date_012']) ? date('Y-m-d') : $arow['sign_date_012'];
	$sql[] = "UPDATE `assay_pay` SET `sign_012`='{$u['userid']}',`sign_date_012`='{$sign_date_012}',`over`='已完成' WHERE `over`IN('已完成','已开始')";
}
//当第二化验员已经签字而第一化验员再签字时更改第一第二签字人顺序为第一化验员第一签字人，第二化验员为第二签字人
if($_GET['fx_user']=='sign' && $u['userid']==$arow['userid'] && $arow['sign_01']==$arow['userid2']){
	//如果第二化验员签字日期为空说明这是第一化验员第一次签字需要更新签字日期
	$sign_date_01	= empty($arow['sign_date_012'])	? date('Y-m-d')	: $arow['sign_date_01'];
	//如果第二化验员签字日期为空，说明这是第二化验员先签的字，所以第一化验员再签的时候需要更换位置
	$sign_date_012	= empty($arow['sign_date_012'])	? $arow['sign_date_01'] : $arow['sign_date_012'];
	$sql[] = "UPDATE `assay_pay` SET `sign_012`=`sign_01`, `sign_date_012`='{$sign_date_012}', `sign_01`='{$u['userid']}', `sign_date_01`='{$sign_date_01}' WHERE `over`='已完成'";
}
if(!intval($dhy_arr[$arow['vid']])){
	$hyd_id_str = intval($_GET['hyd_id']);
}else{
	//获取多合一化验单列表
	$hyd_id_arr = array();
	$vid_str = implode(',',$dhy_arr['xm'][$dhy_arr[$arow['vid']]]) ;
	$sql_hyd = "SELECT id FROM assay_pay WHERE cyd_id = {$arow['cyd_id']} AND vid IN ($vid_str)";
	$query = $DB->query($sql_hyd);
	while ($row=$DB->fetch_assoc($query)) {
		$hyd_id_arr[] = $row['id'];
	}
	$hyd_id_str = implode(',', $hyd_id_arr);
	
}
foreach ($sql as $key => $value) {
	$value .= " AND `id` in ($hyd_id_str)";
	$DB->query($value);
}
echo json_encode($error);
?>