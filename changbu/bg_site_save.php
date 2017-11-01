<?php
/*
*功能：修改厂部默认站点
*作者：高龙
*时间：2016-11-9
*系统：兰州
*/
include '../temp/config.php';
$fzx_id	= FZX_ID;//中心id
$water_type = $_POST['water_type'];//接收水样类型
$sites = $_POST['sites'];//接受传过来的站点id数组
foreach ($sites as $key => $value) {//将所有批次下的站点放到一个数组中
	foreach ($value as $k => $v) {
		$sites_arr[] = $v;
	}
}
$site_id = @implode(',', $sites_arr);//将站点id数组合成字符串
//修改厂部默认站点id
$old_cbsite_id  = $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='cbsite_id' AND module_value2='{$water_type}'");
if(!empty($old_cbsite_id['id'])){
    $result = $DB->query("UPDATE `n_set` SET `module_value1` = '{$site_id}' WHERE `module_name`='cbsite_id' AND module_value2='{$water_type}'");
}else{
    $result = $DB->query("INSERT INTO `n_set` SET `fzx_id`='{$fzx_id}',`module_value1` = '{$site_id}',`module_name`='cbsite_id', module_value2='{$water_type}' ");
}
if($result){
	echo 1;
}else{
	echo 0;
}
?>