<?php
/*
*   关联项目设置
*/
include '../../temp/config.php';
$fzx_id	= FZX_ID;
$set_id	= get_str($_GET['set_id']);
if(!empty($set_id)){
	$set_arr	= $DB->fetch_one_assoc("SELECT `id` FROM `n_set` WHERE `fzx_id`='$fzx_id' AND `module_name`='relate_value' AND `id`='$set_id'");
	if(!empty($set_arr['id'])){
		$DB->query("DELETE FROM `n_set` WHERE `id`='{$set_arr['id']}'");
	}
}else{
	echo "<script>alert('删除失败，请刷新页面重试');</script>";
}
gotourl("$rooturl/system_settings/relate_value/relate_value_list.php");
?>