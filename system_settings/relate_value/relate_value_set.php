<?php
/*
*   关联项目设置
*/
include '../../temp/config.php';
//导航
$trade_global['daohang'] = array(
    array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
    array('icon'=>'','html'=>'关联项目列表','href'=>'./system_settings/relate_value/relate_value_list.php'),
    array('icon'=>'','html'=>'关联项目设置','href'=>"./system_settings/relate_value/relate_value_set.php?set_id={$_GET['set_id']}")
);
$fzx_id	= $u['fzx_id'];

//根据id从数据库 获取 已添加的 关联项目数据
$tmp_vid_arr	= array();
$moren_water_type	= $panduan_yiju	= $note	= '';
if(!empty($_GET['set_id'])){
	$relate_value_rs	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `id`='{$_GET['set_id']}'");
	//项目名称
	$value_name_str	= '';
	if(!empty($relate_value_rs['module_value1'])){
		$tmp_vid_arr	= explode(',',$relate_value_rs['module_value1']);
	}
	$moren_water_type	= $relate_value_rs['module_value4'];
	$panduan_yiju		= $relate_value_rs['module_value2'];
	$note	= $relate_value_rs['module_value3'];
	/*$moren_water_type	= $relate_value_rs['module_value3'];
	if(!empty($relate_value_rs['module_value2'])){
		$json_arr		= json_decode($relate_value_rs['module_value2']);
		$panduan_yiju	= $json_arr[0];
		$note			= $json_arr[1];
	}*/
}
//获取全部项目展示出来（区分已添加的和未添加的） 并且生成可搜索的下拉菜单
$vid_option	= '';
if(!empty($_SESSION['assayvalueC'])){
	//根据session只获取  有检测方法的项目显示出来
	//print_rr($_SESSION['assayvalueC']);
	$checked_label	= $checkbox_label	= '';
	foreach ($_SESSION['assayvalueC'] as $key => $value) {
		if(in_array($key,$tmp_vid_arr)){
			$checked_label	.= "<label class='show'><input type='checkbox' name='vid[]' value='$key' checked />$value</label>";
		}else{
			$checkbox_label	.= "<label class='show'><input type='checkbox' name='vid[]' value='$key' />$value</label>";
		}
		$vid_option	.= "<option value='$key'>$value</option>";
	}
}else{
	//获取出全部项目显示出来
	$checked_label	= $checkbox_label	= '';
	$value_all_sql	= $DB->query("SELECT * FROM `assay_value` WHERE 1 ORDER BY `seq`");
	while ($value_all_rs = $DB->fetch_assoc($value_all_sql)) {
		//print_rr($value_all_rs);
		if(in_array($key,$tmp_vid_arr)){
			$checked_label	.= "<label class='show'><input type='checkbox' name='vid[]' value='{$value_all_rs['id']}' checked />{$value_all_rs['valueC']}</label>";
		}else{
			$checkbox_label	.= "<label class='show'><input type='checkbox' name='vid[]' value='{$value_all_rs['id']}' />{$value_all_rs['valueC']}</label>";
		}
		$vid_option	.= "<option value='{$value_all_rs['id']}'>{$value_all_rs['valueC']}</option>";
	}
}
//获取水样类型的下拉菜单，默认全部
$leixing_option	= get_syleixing($moren_water_type,'123');//123是去除 请选择的option
disp("relate_value_set.html");
?>