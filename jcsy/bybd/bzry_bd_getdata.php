<?php
/**
 * 功能：标准溶液标定
 * 作者：Mr Zhou
 * 日期：2015-01-20
 * 描述：标准溶液标定获取基础信息：项目方法，标液，基准溶液信息
*/
include "../../temp/config.php";
$fzx_id = FZX_ID;
$data = array();
$vid = intval($_GET['vid']);
$bzry_id =  intval($_GET['bzry_id']);
$jzry_id =  intval($_GET['jzry_id']);
$arow = $DB->fetch_one_assoc("SELECT * FROM `jzry_bd` WHERE `id`='{$_GET['id']}' AND `fzx_id`='{$fzx_id}'");
if(empty($arow['bzry_bdrq'])){
	$arow['bzry_bdrq'] = date('Y-m-d');
}
$end_date = $arow['bzry_bdrq'];//date('Y-m-d',strtotime('-1 month',strtotime($arow['bzry_bdrq'])));
//查询检测依据
$sql = "SELECT am.`method_number` AS jcyj FROM `xmfa` xf LEFT JOIN `assay_method` am ON xf.`fangfa`=am.`id` WHERE xf.`xmid`='{$vid}' AND `mr`=1 AND `fzx_id`='$fzx_id' LIMIT 1";
$row = $DB->fetch_one_assoc($sql);
$data['jcyj'] = $row['jcyj'];
//查询待标定标准溶液
$sql = "SELECT `id`,`sjmc`,`pzrq`,`sj_nd` FROM `jzry` WHERE `fzx_id`='$fzx_id' AND `ry_type`='标准溶液' AND `vid`='$vid' AND `pzrq`<='{$end_date}' AND `sj_yxrq`>='{$end_date}' OR `id`='$bzry_id' ORDER BY `id` DESC";
$query = $DB->query($sql);
$bzry = '';
while ($row=$DB->fetch_assoc($query)) {
	$bzry .= '<option value="'.$row['id'].'**'.$row['sjmc'].'**'.$row['pzrq'].'**'.$row['sj_nd'].'**#'.$row['id'].'#">
		'.$row['sjmc'].'--
		配制日期：'.$row['pzrq'].'
		浓度：'.$row['sj_nd'].'</option>';
}
if(''==$bzry){
	$bzry = '<option value="">请先配置标准溶液</option>';
}
$data['bzry'] = $bzry;
//查询基准溶液
$sql = "SELECT `id`,`sjmc`,`pzrq`,`sj_nd` FROM `jzry` WHERE `fzx_id`='$fzx_id' AND `ry_type`='基准溶液' AND `vid`='$vid' AND `pzrq`<='{$end_date}' AND `sj_yxrq`>='{$end_date}' OR `id`='$jzry_id' ORDER BY `id` DESC";
$jzry = '';
$query = $DB->query($sql);
while ($row=$DB->fetch_assoc($query)) {
	$jzry .= '<option value="'.$row['id'].'**'.$row['sjmc'].'**'.$row['pzrq'].'**'.$row['sj_nd'].'**#'.$row['id'].'#">
		'.$row['sjmc'].'--
		配制日期：'.$row['pzrq'].'
		浓度：'.$row['sj_nd'].'</option>';
}
if(''==$jzry){
	$jzry = '<option value="">请先配置基准溶液</option>';
}
$data['jzry'] = $jzry;
$sql ="SELECT * FROM  `jzry_bd` WHERE `fzx_id`='$fzx_id' AND `vid`='$vid' ORDER BY `id` DESC LIMIT 1";
$data['last'] = $DB->fetch_one_assoc($sql);
echo json_encode($data);
