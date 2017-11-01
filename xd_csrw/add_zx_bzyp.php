<?php
/**
 * 功能：显示添加中心标准样品的页面
 * 作者：zhengsen
 * 时间：2014-06-18
**/
require_once("../inc/cy_func.php");
require_once("../temp/config.php");
if(!$u['userid']){
	nologin();
}
$cyd = get_cyd( $_GET['cyd_id'] );
$fzx_id=$u['fzx_id'];
$assay_values = get_all_assay_value( $_GET['cyd_id'] );
if( !empty($cyd['xc_exam_value'])) {
	$xcjc_vid_arr=explode(',',$cyd['xc_exam_value']);
	$assay_values = array_diff( $assay_values,$xcjc_vid_arr );
}

if( empty($assay_values) ){
	die( '该批样品全部无效, 不能添加质控样品' );
}
$assay_value_list = implode(',',$assay_values);
//查询符合条件的标准样品
$now_date=date('Y-m');
$bzyp_sql="SELECT b.id,b.wz_bh,b.wz_name,bd.vid,av.value_C FROM `bzwz_fp` bf LEFT JOIN `bzwz` b on bf.by_id=b.id LEFT JOIN `bzwz_detail` bd ON b.id=bd.wz_id LEFT JOIN `assay_value` av ON bd.vid = av.id WHERE b.wz_type='标准样品' AND bf.fzx_id='".$fzx_id."' AND bf.jc_date='".$now_date."' AND bd.vid IN (".$assay_value_list.") ORDER BY b.wz_bh";
$bzyp_query = $DB->query( $bzyp_sql );
$result = array();
while($bzyp_rs=$DB->fetch_assoc($bzyp_query)){
	$result[$bzyp_rs['wz_bh']]['vid'][$bzyp_rs['vid']]=$bzyp_rs['value_C'];
	$result[$bzyp_rs['wz_bh']]['id']=$bzyp_rs['id'];
	$result[$bzyp_rs['wz_bh']]['name']=$bzyp_rs['wz_name'];
}
if(!empty($result)){
	foreach($result as $key=>$value){
		$zk_vid_checkbox='';
		if(is_array($value['vid'])){
			foreach($value['vid'] as $k=>$v){
				$zk_vid_checkbox.="<input type='checkbox' name='bzyp[{$value[id]}][]' value='{$k}'>{$v}&nbsp;";
			}
		}
		$bzyp_lines.="<tr><td>".$key."</td><td align='left'>".$zk_vid_checkbox."</td></tr>";
	}
}

echo temp("add_zx_bzyp");


