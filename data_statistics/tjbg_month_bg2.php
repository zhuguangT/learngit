<?php
/*
*功能：查看和下载水质月报信息
*作者：zhengsen
*时间：2015-09-1
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
		$_POST=json_decode($cg_rs['result_set'],true);
	}
	if(!empty($cg_rs['gx_set'])){
		$gx_set	= json_decode($cg_rs['gx_set'],true);
		$xz_area_px	= $gx_set['xz_area_px'];
	}
}
//查询发布日期
$fb_date_rs=$DB->fetch_one_assoc("SELECT * FROM n_set WHERE module_name='month_fb_date' AND module_value1='".$_GET['year']."'");
if(!empty($fb_date_rs['module_value2'])){
	$fb_date_arr=json_decode($fb_date_rs['module_value2'],true);
}
if($fb_date_arr[$_GET['year_month']]){
	$fb_date=$fb_date_arr[$_GET['year_month']];
}else{
	$fb_date=date('Y年n月j日',strtotime("+1 months",strtotime($_GET['year_month'].'-10')));
}
//print_rr($_GET);
//print_rr($_POST);exit();
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
		if($vd0!=''){
			$result[$rs['xz_area']][$lx_name_arr[$rs['water_type']]]['vid'][$rs['vid']][$rs['id']]=$vd0;
		}

		//$result[$rs['id']]['vid'][$rs['vid']]['unit']=$rs['unit'];

	}
}
//取出空的数组（可能会有空分区的数组）
$result	= array_filter($result);
//获取采样时间的范围
if(!empty($cy_date_arr)){
	if(min($cy_date_arr)==max($cy_date_arr)){
		$cy_date_fw=date('Y年n月j日',strtotime($cy_date_arr[0]));
	}else{
		$cy_date_fw = date('Y年n月j日',strtotime(min($cy_date_arr)))."至".date('Y年n月j日',strtotime(max($cy_date_arr)));
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
		$unit_str='('.$unit_arr[$key].')';
	}
	$xm_name_td.="<td>".$value.$unit_str."</td>";
	$xz_td.="<td>".$jcxz_arr[$max_water_type][$key]."</td>";
}
//print_rr($result);exit();
//print_rr($xm_arr);exit();
//print_rr($vid_arr);
//标题需要合并的列
$cols1=count($xm_arr);
$z_cols=2+$cols1;
$bz_cols=$z_cols-1;
if(empty($result)){
	echo "<script>alert('没有查询任何数据'); window.close();</script>";exit();
}
//标题的显示
$title="青岛市城市供水水质公报（".$fb_date."发布）";
//报告的显示
$week_bg_line='';
$area_width=102;
$wt_width=86;
$site_width	= 0;
$vid_width=((1000-$area_width-$wt_width-$site_width)/$cols1)-4;
//print_rr($xm_arr);
//print_rr($result);
$k=0;
foreach($result as $key =>$value){
	$water_type_nums=count($value);
	foreach($value as $key2=>$value2){
		$k++;
		$vid_data_td='';
		foreach($xm_arr as $k2=>$v2){
			$vid_data=array();
			if(isset($value2['vid'][$k2])){
				$vid_data=$value2['vid'][$k2];
				//判断如果是色度平均值默认显示<5,如果是肉眼可见物默认显示无
				if($k2=='93'){//色度
					$avg='<5';
				}else if($k2=='96'){//肉眼可见物
					$avg='无';
				}elseif($k2=='484'){//氯气及游离氯制剂（游离氯）
					$eyhl_data=array();
					$avg1=$avg2='';
					if(!empty($vid_data)){
						foreach($vid_data as $k3=>$v3){
							if(stristr($v3,"*")){
								$eyhl_data[$k3]=$v3;
								unset($vid_data[$k3]);
							}
						}
						if(!empty($vid_data)){
							$avg1=array_sum($vid_data)/count($vid_data);
							if($avg1>0){
								$avg1=number_format($avg1,2);
							}
							if(in_array($k2,$global['modi_data_vids'])&&$avg1=='0'){
								$avg1='未检出';
							}
						}
						if(!empty($eyhl_data)){
							$avg2=array_sum($eyhl_data)/count($eyhl_data);
							if($avg2>0){
								$avg2=number_format($avg2,2);
								$avg2.="*";
							}
							if(in_array($k2,$global['modi_data_vids'])&&$avg2=='0'){
								$avg2='未检出';
							}
						}
						if($avg1&&$avg2){
							$avg=$avg1.'/'.$avg2.'*';
						}else{
							$avg=$avg1.$avg2;
						}
						
					}
				}else{
					$avg=array_sum($vid_data)/count($vid_data);
					if($avg>0){
						$avg=number_format($avg,2);
					}
					if(in_array($k2,$global['modi_data_vids'])&&$avg=='0'){
						$avg='未检出';
					}
				}
				$vid_data_td.="<td style='vnd.ms-excel.numberformat:@'>".$avg."</td>";
			}else{
				$vid_data_td.='<td>--</td>';
			}
		}
		if($k==1){
			$week_bg_line.="<tr align=\"center\"><td rowspan='".$water_type_nums."'>".$key."</td><td>".$key2."</td>".$vid_data_td."</tr>";
		}else{
			$week_bg_line.="<tr align=\"center\"><td>".$key2."</td>".$vid_data_td."</tr>";
		}
	}
	$k=0;
}

$file_name=$title;
if($_GET['action']=='view'){
	echo temp("any_data/tjbg_month_bg2");
}
if($_GET['action']=='load'){
	header("Content-Type:   application/msexcel");        
    header("Content-Disposition:   attachment;   filename=".$file_name.".xls");        
    header("Pragma:   no-cache");        
    header("Expires:   0");        
	echo temp("any_data/tjbg_month_bg2");
}
?>