<?php
/*
*功能：查看和下载水质周报信息
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
		$_POST=json_decode($cg_rs['result_set'],true);
	}
}
//print_rr($_GET);
//print_rr($_POST);
if(!empty($_POST['vid'])){
	$vid_arr=$_POST['vid'];
	$vid_str=implode(',',$vid_arr);
}else{
	echo "<script>alert('请先选择化验项目'); window.close();</script>";
}
//查询出每种水样类型下项目的名称
$jcbz_sql="SELECT aj.vid,n.module_value2 as water_type,aj.value_C,aj.xz FROM n_set n JOIN assay_jcbz aj ON n.id=aj.jcbz_bh_id WHERE module_name='jcbz_bh' AND module_value3='1' ";
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
$site_arr=$cy_date_arr=$round_num_arr	= $jcx_arr	= $avg_data_arr	= array();
//查询周报的数据
foreach($_POST['sites'] as $group_name_key=>$sites_arr){
	$site_arr=array_unique(array_merge($site_arr,$sites_arr));
	$site_str=implode(',',$sites_arr);
	$sql="SELECT cr.*,ao.cid,ao.id as aid,ao.vd0,ao.ping_jun,ao.vid,ao.hy_flag,c.cy_date as c_date,s.st_type,cr.site_name,ap.unit,ap.jc_xz,s.sgnq_code,s.sgnq,s.xz_area,cr.water_height,cr.liu_l,
	cr.qi_wen FROM cy c LEFT JOIN cy_rec cr ON cr.cyd_id = c.id LEFT JOIN assay_order AS ao ON  ao.cid=cr.id LEFT JOIN sites AS s ON s.id = ao.sid  LEFT JOIN assay_pay ap  ON ao.tid=ap.id WHERE c.cy_date between '".$_GET['begin_date']."' AND '".$_GET['end_date']."' AND
    c.site_type = '".$_POST['site_type']."' AND s.id IN (".$site_str.") AND ao.vid IN (".$vid_str.") AND cr.zk_flag >= '0'	 AND ao.hy_flag>=0 AND c.group_name='".$group_name_key."' group by aid,s.xz_area  ORDER BY cr.water_type desc,c.cy_date, cr.bar_code";
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
		//水样类型
		//$result[$rs['id']]['water_type']=$rs['water_type'];
		//站点名称
		//$result[$rs['id']]['site_name']=$rs['site_name'];
		//采样时间
		$cy_date=date('Y.n.j',strtotime($rs['c_date']));
		if((!isset($result[$rs['water_type']]['cy_date'])||!in_array($cy_date,$result[$rs['water_type']]['cy_date']))&&!empty($cy_date)){
			$result[$rs['water_type']]['cy_date'][]=$cy_date;
		}
		//化验项目数据,如果$global['bg_pingjun']不为空才去平均值
		if(!empty($rs['ping_jun'])&&$global['bg_pingjun']) {
			$vd0 = $rs['ping_jun'];
		}else{
			$vd0 =$rs['vd0'];
		}
		if(in_array($rs['vid'],$global['modi_data_vids'])&&$vd0<='0'&&$vd0!=''){
			$vd0='未检出';
		}
		$vd0 = str_replace(" ","",$vd0);
		/*$return_data=is_chaobiao($rs['vid'],$max_water_type,$jcxz_arr[$max_water_type][$rs['vid']],$vd0);
		if($return_data['status']){
			$vd0='<span style="color:red">'.$vd0.'*</span>';
		}*/
		if($vd0!=''){
			//将数字变成 float型，方便后面对比得出最大值、最小值
			$tmp_vd0	= (float)$vd0;
			if(stristr($vd0,'.')){
				$tmp_num	= strlen(substr($vd0,(strrpos($vd0,'.')+1)));
			}else{
				$tmp_num	= 0;
			}
			if($tmp_num > 0){
				$tmp_vd0	= number_format($tmp_vd0, $tmp_num);
			}
			if(($tmp_vd0 != '0' && $tmp_vd0==$vd0) || $vd0=='0'){
				$vd0	= $tmp_vd0;
			}
			$result[$rs['water_type']]['vid'][$rs['vid']][$rs['id']]=$vd0;
			//结果小于检出限时，记录下检出限的一半的值。方便后面计算平均值
			if(stristr($vd0,"<")){
				$avg_vd0	= str_replace("<",'',$vd0)/2;
			}else{
				$avg_vd0	= $vd0;
			}
			$avg_data_arr[$rs['water_type']]['vid'][$rs['vid']][$rs['id']]	= $avg_vd0;
			//记录最大修约位数
			if(empty($round_num_arr[$rs['vid']])){
				$round_num_arr[$rs['vid']]	= 0;
			}
			if($tmp_num>$round_num_arr[$rs['vid']]){
				$round_num_arr[$rs['vid']]	= $tmp_num;
			}
			//记录检出限
			if(!in_array($rs['vid'],$jcx_arr) && stristr($vd0,"<")){
				$jcx_arr[$rs['vid']]	= str_replace('<','',$vd0);
			}
		}

		//$result[$rs['id']]['vid'][$rs['vid']]['unit']=$rs['unit'];

	}
}

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
$xm_name_td=$xz_td=$data_cs_td='';
foreach($xm_arr as $key=>$value){
	$unit_str='';
	if(!empty($unit_arr[$key])){
		$unit_str='('.$unit_arr[$key].')';
	}
	$xm_name_td.="<td colspan=\"3\">".$value.$unit_str."</td>";
	$data_cs_td.="<td nowrap>最高</td><td nowrap>最低</td><td nowrap>平均</td>";
	$xz_td.="<td colspan=\"3\">".$jcxz_arr[$max_water_type][$key]."</td>";
}
//print_rr($result);exit();
//print_rr($xm_arr);exit();
//print_rr($vid_arr);
//标题需要合并的列
$cols1=count($xm_arr)*3;
$z_cols=2+$cols1;
if(empty($result)){
	echo "<script>alert('没有查询任何数据'); window.close();</script>";exit();
}
//标题的显示
$title="青岛市城市供水水质公报（".date('Y年n月j日',strtotime($_GET['end_date']))."发布）";
//报告的显示
$week_bg_line='';
$cy_date_width=70;
$wt_width=70;
$vid_width=((1000-$cy_date_width-$site_width)/$cols1)-4;
//print_rr($round_num_arr);
//print_rr($xm_arr);
//print_rr($result);
//print_rr($avg_data_arr);
ksort($result);//排序
foreach($result as $k =>$v){
	$jcrq='';
	if(!empty($v['cy_date'])){
		if(count($v['cy_date'])==1){
			$jcrq=$v['cy_date'][0];
		}else{
			$min_date=min($v['cy_date']);
			$max_date=max($v['cy_date']);
			$jcrq=$min_date."～".$max_date;  // 检测日期区间
		}
	}
	$vid_data_td='';
	foreach($xm_arr as $k2=>$v2){
		if(isset($v['vid'][$k2])){
			$vid_data=$v['vid'][$k2];
			$max=max($vid_data);
			$min=min($vid_data);
			//小于检出限的情况用检出限的一半进行计算
			$avg=array_sum($avg_data_arr[$k]['vid'][$k2])/count($avg_data_arr[$k]['vid'][$k2]);
			/*if($avg>0){
				$avg=number_format($avg,2);
			}*/
			//判断如果是色度平均值默认显示<5,如果是肉眼可见物默认显示无
			if($avg <= '0'){
				if($k2=='96'){
					$avg	= '无';
				}else if(in_array($k2,$global['modi_data_vids'])){
					$avg	= '未检出';
				}
			}else{
				//将平均值判断检出限
				if(!empty($jcx_arr)){
					if($avg<$jcx_arr[$k2]){
						$avg	= "<".$jcx_arr[$k2];
					}
				}
			}
			if((float)$avg == $avg && (float)$avg!='0'){
				$avg	= _round($avg,$round_num_arr[$k2]);//将结果进行 四舍六入五单双修约（汉字以及小于检出限的结果不处理）
			}
			$vid_data_td.="<td style='vnd.ms-excel.numberformat:@'>".$max."</td><td style='vnd.ms-excel.numberformat:@'>".$min."</td><td style='vnd.ms-excel.numberformat:@'>".$avg."</td>";
		}else{
			$vid_data_td.='<td>--</td><td>--</td><td>--</td>';
		}
	}
	$week_bg_line.="<tr align=\"center\" style=\"height:2cm\"><td>".$jcrq."</td><td>".$lx_name_arr[$k]."</td>".$vid_data_td."</tr>";
}
$file_name=$title;
if($_GET['action']=='view'){
	echo temp("any_data/tjbg_week_bg2");
}
if($_GET['action']=='load'){
	header("Content-Type:   application/msexcel");        
    header("Content-Disposition:   attachment;   filename=".$file_name.".xls");        
    header("Pragma:   no-cache");        
    header("Expires:   0");        
	echo temp("any_data/tjbg_week_bg2");
}
?>