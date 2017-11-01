<?php
/**
 * 功能：表头设置
 * 作者: 铁龙
 * 日期: 2014-03-31
 * 描述
*/
include ('../temp/config.php');
$fzx_id = FZX_ID;
$tid	= intval($_GET['tid']);
$fid	= intval($_GET['fid']);
if($fid == 0 || $tid == 0){
	echo json_encode(array('error'=>'1','content','必要参数化验单号，检测方法号错误！'));die;
}
$sql = "SELECT `bt_muban`.*,`bt_muban`.`id` hyd_bg_id,bt.*,ap.`td1`,ap.`td2`,ap.`td3`,ap.`td4`,ap.`td5`,ap.`yq_bh`,ap.`unit`,ap.`assay_element` FROM `assay_pay` ap LEFT JOIN `bt` ON bt.`fid`=ap.`fid` LEFT JOIN `bt_muban` ON `bt_muban`.id=ap.`table_id` WHERE ap.`id` = '$tid'";
$arow = $DB->fetch_one_assoc($sql);
$arow['btdata'] = json_decode($arow['btdata'],true);
//bt表没有和xmfa表对应的数据 则在bt表新建对应数据
if(!intval($arow['fid'])){
	$sql2 = "INSERT INTO bt (`fid`,`zongheng`) SELECT $fid,`zongheng` FROM `bt_muban` WHERE `id`= '{$arow['hyd_bg_id']}' ";
	if(!$DB->query($sql2)){
		echo json_encode(array('error'=>'1','notice'=>'获取表头配置信息失败，请联系管理员。','html'=>''));
		die;
	}
}
$zongheng	= $arow['zongheng'].'_biao';//表格纵横板式
$zongheng	= $$zongheng;//表格纵横板式的宽度
$zhanming = '样品编号';
$hjtj_bt=$aline='';
//化验单模板文件地址
$plan_file_path = $global['hyd']['plan_file_path'];
$hjtj_bt = temp($plan_file_path.'hjtj_bt');
$plan = temp($plan_file_path.'plan_'.$arow['table_name']);
$plan = preg_replace('/<script.*>(.*)<\/script>/isU','',$plan);
$$arow['zongheng'] = 'checked';
preg_match('/<script.*>(.*)<\/script>/isU',$plan,$arr);
$plan = str_replace($arr[0],'',$plan);
echo json_encode(array('error'=>0,'notice'=>'','html'=>temp('hyd/bt_shezi')));
//echo temp('hyd/bt_shezi');
?>