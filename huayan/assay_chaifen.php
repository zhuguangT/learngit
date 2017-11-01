<?php
/**
 * 功能：化验单合并与拆分
 * 作者: Mr Zhou
 * 日期: 2014-09-04 
 * 描述: 
*/
include '../temp/config.php';
require ('./assay_form_func.php');
$fzx_id = FZX_ID;
$staurl	= $_SESSION['begin_url'];
$hyd_id = intval($_REQUEST['hyd_id']);
$hyd = get_hyd_data( $hyd_id );//得到化验单数据

//功能模块一:在任务列表中选择
if($_GET['action']=='chaifen'){
	$hyd_01 = $hyd_id;
	$hyd_02 = 0;
	$sql = "SELECT ao.id,ap.id tid,ao.bar_code,ao.vd0,ao._vd0,ap.unit FROM `assay_pay` ap LEFT JOIN `assay_order` ao ON ap.id=ao.tid WHERE ap.cyd_id='{$hyd['cyd_id']}' AND ap.vid='{$hyd['vid']}' AND `fzx_id`='$fzx_id' ORDER BY `tid`, `bar_code` ";
	$R=$DB->query($sql);
	$var = array();
	while($row=$DB->fetch_assoc($R)){
		$hyd_02 = ($row['tid']!=$hyd_id)? $row['tid']:$hyd_02;
		$check1 = ($row['tid']==$hyd_id)?'checked':'';
		$check2 = ($row['tid']!=$hyd_id)?'checked':'';
		$var[$row['id']] = $row['tid'];
		$lines.=temp('hyd/assay_chaifen_line');
	}
	if($hyd_02>0){
		$z_hyd	 = ($hyd_01<$hyd_02)?$hyd_01:$hyd_02;
	}else{
		$z_hyd = $hyd_01;
	}

	$href = $rooturl.'/huayan/assay_form.php?tid=';
	if($hyd_02>0){
		$hyd_002 = '<a href="'.$href.$hyd_02.'" target="_blank">化验单['.$hyd_02.']</a>';
	}else{
		$hyd_002 = '化验单[新建]';
	}

	$js_data = json_encode($var);
	echo temp('hyd/assay_chaifen');
}else if($_POST['action']=='save'){
	//功能模块二:执行完上面的功能模块一后,出现参数列表,当选中一个参数时执行下面的代码
	$z_hyd  = intval($_POST['z_hyd']); 
	$hyd_01 = intval($_POST['hyd_01']);
	$hyd_02 = intval($_POST['hyd_02']);
	foreach ($_POST as $oid => $tid) {
		if(intval($oid)>0){
			$pay_data[intval($tid)][] = $oid;
		}
	}
	$sql = "SELECT * FROM `assay_pay` WHERE `id`='$z_hyd'";
	$hyd = $DB->fetch_one_assoc($sql);
	unset($hyd['id']);
	$hyd_data = array();
	$hyd_data_str = '';
	foreach ($hyd as $key => $value) {
		if(in_array($key,array('sign_date_01', 'sign_date_012', 'sign_date_02', 'sign_date_03', 'sign_date_04'))){
			$hyd_data[] = "`$key`=NULL";
		}else{
			$hyd_data[] = "`$key`='$value'";
		}
	}
	$hyd_data_str = implode(',', $hyd_data);
	$hyd_id = 0;
	if(count($pay_data[$hyd_01])==0){
		$hyd_id = $hyd_02;
		$DB->query("DELETE FROM `assay_pay` WHERE `id`='$hyd_01'");
	}else if(count($pay_data[$hyd_02])==0){
		$hyd_id = $hyd_01;
		$DB->query("DELETE FROM `assay_pay` WHERE `id`='$hyd_02'");
	}
	foreach ($pay_data as $tid => $orders) {
		if($tid==0){
			$sql = "INSERT INTO `assay_pay` SET $hyd_data_str";
			$DB->query($sql);
			$tid = $DB->insert_id();
		}
		foreach ($orders as $key => $oid) {
			$sql = "UPDATE `assay_order` SET `tid`='$tid' WHERE `id`='$oid'";
			$DB->query($sql);
		}
	}
	if($hyd_id>0){
		$_SESSION['begin_url'] = 'assay_form.php?tid='.$hyd_id;
	}
	gotourl($_SESSION['begin_url']);
}else{
	gotourl($_SESSION['begin_url']);
}
?>