<?php
/**
 * 功能：化验单搜索
 * 作者: Mr Zhou
 * 日期: 2014-09-03 
 * 描述: 
*/
include('../temp/config.php');
$r['tid']   = 0;
if(trim($_POST['tid'])){
    $sql    = "SELECT `id` AS tid FROM `assay_pay` WHERE `id`={$_POST['tid']} LIMIT 1";
    $r      = $DB->fetch_one_assoc($sql);
}else{
    $sql    = "SELECT tid FROM `assay_order` WHERE bar_code = '{$_POST['yp_id']}' AND `vid`='{$_POST['vid']}' LIMIT 1";
    $r      = $DB->fetch_one_assoc($sql);
}
if($r['tid'] != 0){
    gotourl('assay_form.php?tid='.$r[tid]);
}else{
	prompt('未找到符合条件的化验单,请检查你输入的数据!');
	gotourl($url[$_u_][1]);
}
?>
