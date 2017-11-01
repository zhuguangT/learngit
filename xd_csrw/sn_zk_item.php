<?php
/**
 * 功能：显示室内质控的项目(室内平行,加标回收)
 * 作者：zhengsen
 * 时间：2014-06-16
**/
include("../temp/config.php");
include_once INC_DIR.'/cy_func.php';
if(!$u['userid']){
	nologin();
}
$fzx_id=$u['fzx_id'];
if ( $_GET['action']&&$_GET['rec_id'] ){
    $rec_id=$_GET['rec_id'];
}
else{
    bad_request();
}

$current_assay_value=array();
$all_assay_value=array();
$rs=$DB->fetch_one_assoc("SELECT snpx_item,jbhs_item,assay_values,water_type,bar_code,cyd_id FROM `cy_rec` WHERE id='".$rec_id."'");
if($_GET['action']=='snpx'&&!empty($rs['snpx_item'])){
	$current_assay_value = explode(',',$rs['snpx_item']);
}elseif($_GET['action']=='jbhs'&&!empty($rs['jbhs_item'])){
	$current_assay_value = explode(',',$rs['jbhs_item']);
}
if($_GET['action']=='snpx'){
	$bt='室内平行';
}else{
	$bt='加标回收';
}
if(!empty($rs['assay_values'])){
	$all_assay_value=explode(',',$rs['assay_values']);
}
if(!empty($rs['water_type'])){
	$water_type=$rs['water_type'];
}else{
	$lx_zf=substr($rs['bar_code'],1,1);//代表水样类型的字符
	$water_type=array_search($lx_zf,$global['bar_code']['water_type']);
}
$water_type=get_water_type_max($water_type,$fzx_id);
//查询出现场项目
$pay_sql="SELECT * FROM assay_pay WHERE cyd_id='".$rs['cyd_id']."' AND is_xcjc='1' GROUP BY vid";
$pay_query=$DB->query($pay_sql);
$xcjc_arr=array();
while($pay_rs=$DB->fetch_assoc($pay_query)){
	$xcjc_arr[]=$pay_rs['vid'];
}
//如果当前选择的项目不为空
if(!empty($current_assay_value)){
	$current_vid_str=implode(',',$current_assay_value);
	$current_sql="SELECT value_C,id FROM assay_value WHERE id in (".$current_vid_str.")";
	$current_query=$DB->query($current_sql);
	while($current_rs=$DB->fetch_assoc($current_query)){
		$current_value_C[$current_rs['id']]=$current_rs['value_C'];
	}
}
$opt_assay_value = array_diff( $all_assay_value, $current_assay_value );
//如果没有选择的项目不为空
if(!empty($opt_assay_value)){
	$opt_vid_str=implode(',',$opt_assay_value);
	$opt_sql="SELECT value_C,id FROM assay_value WHERE id in (".$opt_vid_str.")";
	$opt_query=$DB->query($opt_sql);
	while($opt_rs=$DB->fetch_assoc($opt_query)){
		$opt_value_C[$opt_rs['id']]=$opt_rs['value_C'];
	}
}
$cy_rs=$DB->fetch_one_assoc("SELECT * FROM cy WHERE id='".$rs['cyd_id']."'");
if($cy_rs['status']>='6'){
	$save_button='';
}else{
	$save_button='<center><input class="btn btn-xs btn-primary" type="submit" value="保存"></center>';
}
if(!empty($current_value_C))
{
	
	$current_nums=count($current_assay_value);
	$add_tds=5-($current_nums%5);
	$i=0;
	$current_line='';
	foreach($current_value_C as $k =>$v)
	{
		$i++;
		if($i%5==1)
		{
			$current_line.="<tr>";
		}	
		if($current_nums==$i&&$add_tds!=5){
			$current_line.="<td width='20%'><label><input type=\"checkbox\" checked=\"checked\" name=\"vid[]\" value=\"{$k}\" />".$v."</label></td>";
			for($j=0;$j<$add_tds;$j++){
				$current_line.="<td width='20%'>&nbsp;</td>";
			}
		}
		else
		$current_line.="<td><label><input type=\"checkbox\" checked=\"checked\" name=\"vid[]\" value=\"{$k}\" />".$v."</label></td>";
		if($i%5==0||($current_nums==$i))
		{
			$current_line.="</tr>";
		}
	}
}
$no_select_nums=count($opt_assay_value);
$add_tds=5-($no_select_nums%5);
$i=0;
if(!empty($opt_value_C)){
	foreach($opt_value_C as $k2=>$v2){
		$i++;
		$disabled='';
		if(in_array($k2,$xcjc_arr)){
			$disabled='disabled="disabled"';
		}
		if($i%5==1){
			$no_select_line.="<tr>";
		}
		if($no_select_nums==$i&&$add_tds!=5){
			$no_select_line.="<td width='20%'><label><input type=\"checkbox\" name=\"vid[]\" value=\"{$k2}\" $disabled />".$v2."</label></td>";
				for($j=0;$j<$add_tds;$j++){
					$no_select_line.="<td width='20%'>&nbsp;</td>";
				}
		}
		else{
			$no_select_line.="<td width='20%'><label><input type=\"checkbox\"   name=\"vid[]\" value=\"{$k2}\" $disabled />".$v2."</label></td>";
		}
		if($i%5==0||($no_select_nums==$i)){
			$no_select_line.="</tr>";
		}
	}
}
$dhy_obj_json = json_encode($dhy_arr);
echo temp('sn_zk_item');
?>
