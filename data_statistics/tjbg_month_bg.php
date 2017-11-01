<?php
/*
*功能：查看和下载水质月报信息
*作者：zhengsen
*时间：2015-06-17
 */
include '../temp/config.php';
include INC_DIR . "cy_func.php";
include '../baogao/bg_func.php';
if($u['userid']==''){
	nologin();
}
$fzx_id=$u['fzx_id'];
if($_GET['set_id']){
	$cg_rs=$DB->fetch_one_assoc("SELECT * FROM `baogao_list` WHERE id='".$_GET['set_id']."'");
	if(!empty($cg_rs['result_set'])){
		$_POST	= json_decode($cg_rs['result_set'],true);
	}
	if(!empty($cg_rs['gx_set'])){
		$gx_set	= json_decode($cg_rs['gx_set'],true);
		$xz_area_px	= $gx_set['xz_area_px'];
	}
}
if(!empty($_POST['vid'])){
	$vid_arr=$_POST['vid'];
	$vid_str=implode(',',$vid_arr);
}else{
	echo "<script>alert('请先选择化验项目'); window.close();</script>";
}
//查询出每种水样类型下项目的名称
$jcbz_sql="SELECT aj.vid,n.module_value2 as water_type,aj.value_C,aj.xz,aj.panduanyiju FROM n_set n JOIN assay_jcbz aj ON n.id=aj.jcbz_bh_id WHERE module_name='jcbz_bh' AND module_value3='1' ";
$jcbz_query=$DB->query($jcbz_sql);
while($jcbz_rs=$DB->fetch_assoc($jcbz_query)){
	$jcbz[$jcbz_rs['water_type']][$jcbz_rs['vid']]=$jcbz_rs['value_C'];
	if(!empty($jcbz_rs['panduanyiju'])){
		$pd_jcxz_arr[$jcbz_rs['water_type']][$jcbz_rs['vid']]=$jcbz_rs['panduanyiju'];
	}else{
		$pd_jcxz_arr[$jcbz_rs['water_type']][$jcbz_rs['vid']]=$jcbz_rs['xz'];
	}
	$jcxz_arr[$jcbz_rs['water_type']][$jcbz_rs['vid']]=$jcbz_rs['xz'];
}
//查询出所有的项目名称
$xm_sql="SELECT id,value_C FROM assay_value";
$xm_query=$DB->query($xm_sql);
while($xm_rs=$DB->fetch_assoc($xm_query)){
	$xm[$xm_rs['id']]=$xm_rs['value_C'];
}
//查询出所有的水样类型
$sql_lx="SELECT * FROM leixing WHERE (fzx_id=1 or fzx_id=0)";
$query_lx=$DB->query($sql_lx);
while($rs_lx=$DB->fetch_assoc($query_lx)){
	$lx_name_arr[$rs_lx['id']]=$rs_lx['lname'];
}
//按照区域排序(存储在 baogao_list表的gx_set字段)
foreach ((array)$xz_area_px as $value) {
	$result[$value]	= array();
}
$site_arr=$cy_date_arr=array();
//查询月报的数据
foreach($_POST['sites'] as $group_name_key=>$sites_arr){
	$site_arr=array_unique(array_merge($site_arr,$sites_arr));
	$site_str=implode(',',$sites_arr);
	$sql="SELECT cr.*,ao.cid,ao.id as aid,ao.vd0,ao.ping_jun,ao.vid,ao.hy_flag,c.cy_date as c_date,s.st_type,cr.site_name,ap.unit,ap.jc_xz,s.sgnq_code,s.sgnq,s.xz_area,cr.water_height,cr.liu_l,
	cr.qi_wen FROM cy c LEFT JOIN cy_rec cr ON cr.cyd_id = c.id LEFT JOIN assay_order AS ao ON  ao.cid=cr.id LEFT JOIN sites AS s ON s.id = ao.sid  LEFT JOIN assay_pay ap  ON ao.tid=ap.id WHERE c.id =(SELECT cy.id FROM cy left join cy_rec on cy.id=cy_rec.cyd_id WHERE cy.cy_date like '".$_GET['year_month']."%' AND cy.`group_name`='".$group_name_key."' AND cy_rec.sid IN (".$site_str.") group by cy_rec.cyd_id ORDER BY cy.cy_date DESC,cy.id desc limit 1) AND s.id IN (".$site_str.") AND ao.vid IN (".$vid_str.") AND cr.zk_flag >= '0'	 AND ao.hy_flag>=0  group by aid,s.xz_area,cr.water_type  ORDER BY cr.water_type,c.cy_date, cr.bar_code";
	$query=$DB->query($sql);
	while($rs=$DB->fetch_assoc($query)){
		//项目数组
		$max_water_type=get_water_type_max($rs['water_type'],$u['fzx_id']);
		if(empty($unit_arr[$rs['vid']])){
			$unit_arr[$rs['vid']]=$rs['unit'];//项目单位
		}
		//采样时间
		if(!empty($rs['c_date'])&&$rs['c_date']!='0000-00-00'&&!in_array($rs['c_date'],$cy_date_arr)){
			$cy_date_arr[] = $rs['c_date'];//结束日期
		}
		if(empty($rs['xz_area'])){
			$rs['xz_area']='无分区';
		}
		//时间格式转换
		$rs['c_date']=date('Y.n.j',strtotime($rs['c_date']));
		//水样类型
		$result[$rs['xz_area']][$rs['water_type']][$rs['c_date']][$rs['id']]['water_type']=$lx_name_arr[$rs['water_type']];
		//站点名称
		$result[$rs['xz_area']][$rs['water_type']][$rs['c_date']][$rs['id']]['site_name']=$rs['site_name'];
		//化验项目数据
		if(!empty($rs['ping_jun'])&&$global['bg_pingjun']) {
			$vd0 = $rs['ping_jun'];
		}else{
			$vd0 =$rs['vd0'];
		}
		if(in_array($rs['vid'],$global['modi_data_vids'])&&$vd0<='0'&&$vd0!=''){
			$vd0='未检出';
		}
		$vd0 = str_replace(" ","",$vd0);
		$return_data=is_chaobiao($rs['vid'],$max_water_type,$pd_jcxz_arr[$max_water_type][$rs['vid']],$vd0);
		if($return_data['status']){
			$vd0='<span style="color:red">'.$vd0.'*</span>';
		}
		$result[$rs['xz_area']][$rs['water_type']][$rs['c_date']][$rs['id']]['vid'][$rs['vid']]=$vd0;

		//$result[$rs['id']]['vid'][$rs['vid']]['unit']=$rs['unit'];

	}
}
//去除空的数组（可能会有空分区的数组）
$result	= array_filter($result);
//print_rr($result);exit();
//获取采样时间的范围
if(!empty($cy_date_arr)){
	if(min($cy_date_arr)==max($cy_date_arr)){
		$cy_date_fw=$cy_date_arr[0];
	}else{
		$cy_date_fw = min($cy_date_arr)."～".max($cy_date_arr);
	}
}
//给传过来的项目赋值项目名称
foreach($vid_arr as $key=>$value){
	if(empty($jcbz[$max_water_type][$value])){
		$xm_arr[$value]=$xm[$value];//防止在assay_jcbz表里没有初始化某项目导致项目为空（初始化完成后应该去掉）
	}else{
		$xm_arr[$value]=$jcbz[$max_water_type][$value];
	}
}
//print_rr($result);exit();
//获取项目排序的设置
if($_POST['xm_px_id']){
	$xm_px_rs=$DB->fetch_one_assoc("SELECT * FROM n_set WHERE id='".$_POST['xm_px_id']."'");
	$xm_px_arr=explode(",",$xm_px_rs['module_value1']);
}
//根据设置进行项目排序
if(!empty($xm_px_arr)){
	$xm_arr_temp=array();
	foreach($xm_px_arr as $key=>$value){
		if(!empty($xm_arr[$value])){
			$xm_arr_temp[$value]=$xm_arr[$value];
			unset($xm_arr[$value]);
		}
	}
	$xm_arr=$xm_arr_temp+$xm_arr;
}

//获取项目名称和国家标准的td
$xm_name_td=$xz_td='';
foreach($xm_arr as $key=>$value){
	$unit_str='';
	if(!empty($unit_arr[$key])){
		$unit_str="<br style='mso-data-placement:same-cell;'/>(".$unit_arr[$key].")";
	}
	$xm_name_td.="<td>".$value.$unit_str."</td>";
	$xz_td.="<td>".$jcxz_arr[$max_water_type][$key]."</td>";
}
//print_rr($result);
//print_rr($xm_arr);exit();
//print_rr($vid_arr);
//标题需要合并的列
$cols1=count($xm_arr);
$z_cols=3+$cols1;
$cols2=$z_cols-1;
$col_one=intval($z_cols/3);
$col_two=$z_cols-(2*$col_one);
if(empty($result)){
	echo "<script>alert('没有查询任何数据'); window.close();</script>";exit();
}
//标题的显示
$title ="抽&nbsp;&nbsp;&nbsp;&nbsp;检&nbsp;&nbsp;&nbsp;&nbsp;明&nbsp;&nbsp;&nbsp;&nbsp;细";
//报告的显示
$week_bg_line='';
$cy_date_width=85;
$site_width=115;
$water_type_width=75;
$vid_width=((1000-$cy_date_width-$site_width-$water_type_width)/$cols1)-4;
//krsort($result);
//print_rr($result);exit();
$i=0;
foreach($result as $key =>$value){
	$week_bg_line.="<tr align=\"center\"><td colspan=".$z_cols." style=\"font-size:10.5pt;font-weight:bold\">".$key."</td></tr>";
	foreach($value as $key2=>$value2){
		ksort($value2);
		foreach($value2 as $key3=>$value3){
			foreach($value3 as $k=>$v){
				$vid_data_td='';
				foreach($xm_arr as $k2=>$v2){
					if(isset($v['vid'][$k2])){
						$vd0=$v['vid'][$k2];
					}else{
						$vd0='--';
					}
					$vid_data_td.="<td style='vnd.ms-excel.numberformat:@'>".$vd0."</td>";
				}
				$week_bg_line.="<tr align=\"center\"><td>".$key3."</td><td>".$v['site_name']."</td><td>".$v['water_type']."</td>".$vid_data_td."</tr>";
			}
		}
	}
	$i++;
	if($i==1){
		$week_bg_line.="<tr align=\"center\"><td >评价</td><td colspan=".$cols2." >本周所检指标符合《生活饮用水卫生标准》（GB5749-2006）的要求。</td></tr>";
	}else{
		$week_bg_line.="<tr align=\"center\"><td >备注</td><td colspan=".$cols2." >以上抽检指标符合GB5749-2006《生活饮用水卫生标准》。</td></tr>";
	}
}
$file_name=$_GET['year'].'年'.$_GET['month'].'月水质月报抽检明细';
if($_GET['action']=='view'){
	echo temp("any_data/tjbg_month_bg");
}
if($_GET['action']=='load'){
	header("Content-Type:   application/msexcel");        
    header("Content-Disposition:   attachment;   filename=".$file_name.".xls");        
    header("Pragma:   no-cache");        
    header("Expires:   0");        
	echo temp("any_data/tjbg_month_bg");
}
?>
