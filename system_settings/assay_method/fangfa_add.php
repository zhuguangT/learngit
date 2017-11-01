<?php
include("../../temp/config.php");
$zt	= 'no';
$old_fa	= $DB->fetch_one_assoc("select id from `assay_method` where `method_number`='{$_POST['bzh']}' AND `method_name`='{$_POST['fangfa_name']}'");
if(empty($old_fa['id'])){
	$insert	= $DB->query("INSERT INTO `assay_method` SET `method_number`='{$_POST['bzh']}',`method_name`='{$_POST['fangfa_name']}'");
	if($DB->insert_id()){
		$zt	= "yes";
	}
}
echo $zt;
?>
