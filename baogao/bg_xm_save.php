<?php
/*
*功能：保存报告设置的项目
*作者：zhengsen
*时间：2016-02-19
*/
include '../temp/config.php';
$fzx_id	= FZX_ID;//中心id
if($_POST['vid']!=''&&$_POST['bg_id'])//保存  
{
	$bg_id	= $_POST['bg_id'];
	$arr	= $_POST['vid'];
	$xm_str	= implode(',',$arr);

	$sql	= "update `report` set bg_xm='".$xm_str."'  where id='".$bg_id."' ";
	$result	= $DB->query($sql);
	if($DB->affected_rows()>=0){
		echo '1';
	}else{
		echo '0';
	}
	
}else{
	echo '0';
}
exit();

?>