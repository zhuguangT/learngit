<?php
/*
*   关联项目设置
*/
include '../../temp/config.php';
$fzx_id	= $u['fzx_id'];
//print_rr($_POST);
$_POST['set_id']= (int)$_POST['set_id'];
$panduan_yiju	= get_str($_POST['panduan_yiju']);
$note			= get_str($_POST['note']);
//将vid整合成字符串
$module_value1	= '';
if(!empty($_POST['vid'])){
	$module_value1	= implode(',',$_POST['vid']);
}
//将判断依据和备注 合并到一起存储到module_value2中
//$module_value2	= JSON(array($panduan_yiju,$note));
$module_value2	= $panduan_yiju;
$module_value3	= $note;
//将水样类型存储到 module_value3中
//$module_value3	= $_POST['water_type'];
$module_value4	= $_POST['water_type'];
//如果存在set_id就更改该记录，如果不存在就插入新的记录
if(!empty($_POST['set_id'])){
	$DB->query("UPDATE `n_set` SET `module_value1`='{$module_value1}',`module_value2`='{$module_value2}',`module_value3`='{$module_value3}',`module_value4`='{$module_value3}' WHERE `id`='{$_POST['set_id']}'");
}else{
	$DB->query("INSERT INTO `n_set` SET `fzx_id`='$fzx_id',`module_name`='relate_value',`module_value1`='{$module_value1}',`module_value2`='{$module_value2}',`module_value3`='{$module_value3}',`module_value4`='{$module_value4}'");
}
gotourl("$rooturl/system_settings/relate_value/relate_value_list.php");
?>