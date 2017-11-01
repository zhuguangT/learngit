<?php
/**
 * 功能：个性化配置的保存界面
 * 作者：hanfeng
 * 日期：2015-03-06
 * 描述：
*/
include("../../temp/config.php");
//##############样品编号格式配置
//样品编号结构配置
if(!empty($bar_code_make)){
	$bar_code_make_str	= implode("+",$bar_code_make);
	$bar_code_make_old	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='bar_code_make' LIMIT 1");
	if(!empty($bar_code_make_old['id'])){
		$DB->query("UPDATE `n_set` SET module_value1='{$bar_code_make_str}' WHERE id='{$bar_code_make_old['id']}'");
	}else{
		$DB->query("INSERT INTO `n_set` SET module_name='bar_code_make',module_value1='{$bar_code_make_str}'");
	}
}
//任务类型标识
if(!empty($site_type_mark)){
	$bar_code_mark_site= array();
	$sql_bar_code_mark_site = $DB->query("SELECT * FROM `n_set` WHERE `module_name`='bar_code_mark_site'");
	while ($rs_bar_code_mark_site=$DB->fetch_assoc($sql_bar_code_mark_site)) {
		$bar_code_mark_site[]	= $rs_bar_code_mark_site['module_value2'];
	}
	foreach($site_type_mark as $key=>$value){
		if(in_array($key,$bar_code_mark_site)){
			$DB->query("UPDATE `n_set` SET `module_value1`='{$value}' WHERE `module_name`='bar_code_mark_site' AND `module_value2`='{$key}'");
		}else{
			$DB->query("INSERT INTO `n_set` SET `module_name`='bar_code_mark_site',`module_value2`='{$key}',`module_value1`='{$value}'");
		}
	}
}
//水样类型标识
if(!empty($water_type_mark)){
	foreach ($water_type_mark as $key => $value) {
		$DB->query("UPDATE `leixing` SET `bar_code_mark`='{$value}' WHERE id='{$key}'");
	}
}
//样品编号按年、按月编号的配置
if(!empty($_POST['bar_code_create'])){
	//先搜索下数据库里有没有记录如果有就更新最新配置，如果没有就插入一条记录
	$bar_code_create	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='bar_code_create' limit 1");
	if(!empty($bar_code_create['module_value1'])){
		//与数据库不一样的时候才更改
		if($bar_code_create['module_value1'] != $_POST['bar_code_create']){
			$DB->query("UPDATE `n_set` SET `module_value1`='{$_POST['bar_code_create']}' WHERE `id`='{$bar_code_create['id']}'");
		}
	}else{
		$DB->query("INSERT INTO `n_set` SET `fzx_id`='1',`module_name`='bar_code_create',`module_value1`='{$_POST['bar_code_create']}'");
	}
	
}

###########报告查看数据限制
$show_shuju_str	= '';
if(!empty($_POST['show_shuju'])){
	$shou_shuju_old	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='show_shuju'");
	//先搜索下数据库里有没有记录如果有就更新最新配置，如果没有就插入一条记录
	$show_shuju_str	= implode(",", $_POST['show_shuju']);
	if(!empty($shou_shuju_old['module_value1'])){
		$DB->query("UPDATE `n_set` SET `module_value1`='{$show_shuju_str}' WHERE `id`='{$shou_shuju_old['id']}'");
	}else{
		$DB->query("INSERT INTO `n_set` SET `module_name`='show_shuju',`module_value1`='{$show_shuju_str}'");
	}
}
###########下达采样任务页面设置：同时生成室内空白设置
$create_snkb_str	= '';
if(!empty($_POST['xdcy'])){
	$create_snkb_old	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='xdcy'");
	//先搜索下数据库里有没有记录如果有就更新最新配置，如果没有就插入一条记录
	$create_snkb_str	= JSON($_POST['xdcy']);
	if(!empty($create_snkb_old['module_value1'])){
		$DB->query("UPDATE `n_set` SET `module_value1`='{$create_snkb_str}' WHERE `id`='{$create_snkb_old['id']}'");
	}else{
		$DB->query("INSERT INTO `n_set` SET `module_name`='xdcy',`module_value1`='{$create_snkb_str}'");
	}
}
echo "<script>location.href='$rooturl/system_settings/view_settings/view_settings.php';</script>";
?>