<?php
/*
*功能：保存报告设置的项目
*作者：zhengsen
*时间：2016-02-19
*/
include '../temp/config.php';
$fzx_id	= FZX_ID;//中心id
$xmid = implode(',',$_POST['vid']);//接受项目id，并将此数组合成字符串
$water_type = $_POST['water_type'];//接收水样类型
//修改厂部项目id
$old_cbxm_id  = $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='cbxm_id' AND module_value2='{$water_type}'");
if(!empty($old_cbxm_id['id'])){
    $result = $DB->query("UPDATE `n_set` SET `module_value1` = '{$xmid}' WHERE `module_name`='cbxm_id' AND module_value2='{$water_type}'");
}else{
    $result = $DB->query("INSERT INTO `n_set` SET `fzx_id`='{$fzx_id}',`module_value1` = '{$xmid}',`module_name`='cbxm_id', module_value2='{$water_type}' ");
}
if($result){
	echo 1;
}else{
	echo 0;
}
?>