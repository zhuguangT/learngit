<?php
/**
 * 功能：保存添加的标准样品，把数据插入到cy_rec表中
 * 作者：zhengsen
 * 时间：2014-06-19
**/
require_once "../temp/config.php";
require_once INC_DIR . "cy_func.php";
if(!$u['userid']){
	nologin();
}
$cyd = get_cyd( $_POST['cyd_id'] );
$bzyp_nums=count($_POST['bzyp']);
if(!empty($_POST['bzyp']))
{
	foreach($_POST['bzyp'] as $key=>$value){
		$vid_str=implode(',',$value);
		$water_types=explode(',',$cyd['water_type']);
		$water_type_one=end($water_types);
		$bzyp=$DB->fetch_one_assoc("SELECT wz_name FROM `bzwz` WHERE id='".$key."'");
		$by_data = array();
		$by_data['cyd_id']      = $_POST['cyd_id'];
		$by_data['sid']         = '-3';
		$by_data['river_name']  = "标准样品";
		$by_data['site_name']   = $bzyp['wz_name']; //标样名称
		$by_data['assay_values']= $vid_str;
		$by_data['bar_code']    = new_bar_code($cyd['site_type'],$water_type_one,$cyd['cy_date']);//新样品编号
		$by_data['bar_code_position']   = $cyd['bar_code_position'];
		$by_data['by_id']       = $key; //标样id
		$by_data['zk_flag']     = 3;
		$by_data['create_date'] = date('Y-m-d');
		$by_data['create_man']  = $u['userid'];
		$by_data['status']      = 1;
		new_record('cy_rec', $by_data);
	}
}
prompt("您此次共添加了{$bzyp_nums}个标准样品!");
gotourl("fp_csrw.php?cyd_id={$_POST[cyd_id]}");
