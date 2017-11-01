<?php
/**
 * 功能：显示化验单
 * 作者: 铁龙
 * 日期: 2014-04-08 
 * 描述: 现在化验项目都根据方法走，去xmfa表中查看化验单的关联数据
*/
header("Pragma: no-cache");
//判断是否是查看多张化验单
if(in_array(trim($_GET['qz']),array('jh','fh','sh'))){
	$href = explode('assay_form.php', $_SERVER['REQUEST_URI']);
	header('location:assay_form_qz.php'.$href[1]);die;
}
include ('../temp/config.php');
require ('./assay_form_func.php');
$fzx_id=FZX_ID;
$tid = intval($_GET['tid']);
if(!$tid){
	prompt('未提供正确化验单编号');
	gotourl($url[1]);
}else{
	$arow = get_hyd_data($tid);
}
//判断是否是多合一化验单
if(in_array($arow['vid'], explode(',',$dhy_arr['str2']))){
    $z_vid = $dhy_arr[$arow['vid']];
    $sql = "SELECT `id` FROM `assay_pay` WHERE `cyd_id`='{$arow['cyd_id']}' AND `vid`='$z_vid'";
    $row = $DB->fetch_one_assoc($sql);
    $arow = get_hyd_data($row['id']);
    $tid = $_GET['tid'] = $arow['id'];
}
$_SESSION['begin_url'] = $current_url;
//########导航
$trade_global['daohang'][]	= array('icon'=>'','html'=>'化验单'.$tid.'（'.$arow['over'].'）','href'=>$current_url);
$_SESSION['daohang']['assay_form']	= $trade_global['daohang'];
//js/css 文件引用
$trade_global['css']		= array('lims/main.css','datepicker.css','bootstrap-timepicker.css');
$trade_global['js']			= array('date-time/bootstrap-datepicker.min.js','date-time/bootstrap-timepicker.min.js','jquery.maskedinput.min.js');
$trade_global['hyd_config'] = $global['hyd'];

//判断是否是多合一化验单
if(in_array($arow['vid'], explode(',',$dhy_arr['str2']))){
    $z_vid = $dhy_arr[$arow['vid']];
    $sql = "SELECT `id` FROM `assay_pay` WHERE `cyd_id`='{$arow['cyd_id']}' AND `vid`='$z_vid'";
    $row = $DB->fetch_one_assoc($sql);
    gotourl($rooturl.'/huayan/assay_form.php?tid='.$row['id']);
}

$assay_form = ((''==$arow['id'])) ? '' : get_assay_form($arow);
if('1'==$_GET['ajax']){
	if(''==$arow['id']){
		echo json_encode(array('error'=>'1','html'=>$assay_form,'content'=>$tid.'号化验单不存在'));
	}else{
		echo json_encode(array('error'=>'0','html'=>$assay_form,'content'=>''));
	}
}else{
	disp('hyd/assay_form');
}
?>