<?php
include "../temp/config.php";
// print_rr($_POST);
$sql = "SELECT * FROM `bzwz` WHERE `id` = '{$_POST['id']}'";
$data = $DB->fetch_one_assoc($sql);
$file_name_new_arr = json_decode($data['dilution_method_file'], true);
$file_name_old_arr = json_decode($data['dilution_method'], true);
if(unlink('./upfile/'.$file_name_new_arr[$_POST['key']])){
	unset($file_name_new_arr[$_POST['key']]);
	unset($file_name_old_arr[$_POST['key']]);
	$dilution_method = json_encode($file_name_old_arr , JSON_UNESCAPED_UNICODE);
	$dilution_method_file = json_encode($file_name_new_arr , JSON_UNESCAPED_UNICODE);
	$sql = "UPDATE `bzwz` SET `dilution_method` = '{$dilution_method}' , `dilution_method_file` = '{$dilution_method_file}' WHERE `id` = '{$_POST['id']}'";
	if($DB->query($sql)){
		echo "ok";
	}else{
		echo "wrong";
	}
}else{
	echo "wrong";
}
die;