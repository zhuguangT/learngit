<?php
/**
 * 功能：删除化验单
 * 作者: Mr Zhou
 * 日期: 2014-09-04 
 * 描述: 
*/
include '../temp/config.php';
$fzx_id = FZX_ID;
$_GET['hyd_id'] = intval($_GET['hyd_id']);
$pay=$DB->fetch_one_assoc("SELECT `cyd_id` FROM `assay_pay` WHERE `id`='{$_GET['hyd_id']}' AND `fzx_id` = $fzx_id");
if(!$pay['cyd_id']){
	gotourl($url[1],'您提供的化验单号有误！');
}
$DB->query("UPDATE `cy` SET `hyd_count`=`hyd_count`-1 WHERE `id`='{$pay['cyd_id']}' AND `fzx_id` = $fzx_id");

$R=$DB->query("SELECT `vid`,`cid` FROM `assay_order` WHERE `tid`='{$_GET['hyd_id']}'");
while($r=$DB->fetch_assoc($R)){
	$a=$DB->fetch_one_assoc("SELECT `assay_values` FROM `cy_rec` WHERE `id`='{$r['cid']}'");
	$a=elementsToArray($a['assay_values']);
	$a=implode(',',array_diff($a,array($r['vid'])));
	$DB->query("UPDATE `cy_rec` SET `assay_values`='$a' WHERE `id`='{$r['cid']}'");
}
$DB->query("DELETE FROM `assay_pay` WHERE `id`='{$_GET['hyd_id']}' AND `fzx_id` = $fzx_id");
$DB->query("DELETE FROM `assay_order` WHERE `tid`='{$_GET['hyd_id']}'");
gotourl($url[1]);
?>