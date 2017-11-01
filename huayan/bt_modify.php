<?php
/**
 * 功能：表头设置
 * 作者: 铁龙
 * 日期: 2014-04-21
 * 描述: 实现对化验单表头编辑的修改和保存等操作
*/
include '../temp/config.php';
$tid	= intval($_GET['tid']);
$fid	= intval($_GET['fid']);
//编辑参数名称
if($_GET['item'] == 'edit' && trim($_GET['table_id']) ){
	$DB->query("UPDATE `bt_muban` SET c{$_GET['tdid']}='{$_GET['newnm']}' WHERE `id`='".trim($_GET['table_id'])."'");
	gotourl("$rooturl/huayan/assay_form.php?tid=$tid#tabs-2");
	die;
}
//点击保存按钮修改参数值
$sql	= array();
$tid	= intval($_POST['tid']);
$fid	= intval($_POST['fid']);
for($i	= 6; $i <= 33; $i++){
	$sql[] = "`td$i`='".trim($_POST['td'.$i])."'";
	if(''!=trim($_POST['td'.$i])){
		$sql2[] =  "`td$i`='".trim($_POST['td'.$i])."'";
	}
}
$sql[] = "`btdata`='".JSON($_POST['btdata'])."'";
$sql2[] = "`btdata`='".JSON($_POST['btdata'])."'";
if(!empty($sql)){
	$sql_str = implode(',',$sql);
	$sql_str2 = implode(',',$sql2);
	$_POST['lines'] = (0==intval($_POST['lines']))?12:intval($_POST['lines']);
	$_POST['zongheng'] = in_array($_POST['zongheng'],array('zong', 'heng')) ? $_POST['zongheng'] : 'heng';
	$DB->query("UPDATE `bt` SET $sql_str ,`zongheng`='{$_POST['zongheng']}',`lines`='{$_POST['lines']}' WHERE `fid`='$fid'" );
	if(''!=$sql_str2){
		$DB->query("UPDATE `assay_pay` SET $sql_str2 WHERE  `fid`='$fid' AND `fzx_id` ={$u['fzx_id']} AND `id` = '$tid' AND `over` IN ('未开始','已开始')");
	}
}
gotourl("$rooturl/huayan/assay_form.php?tid=$tid");
?>