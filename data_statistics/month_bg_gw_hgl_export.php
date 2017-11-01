<?php
/*
*功能：查看和下载水功能区月报告信息
*作者：zhengsen
*时间：2014-10-23
 */
include_once '../temp/config.php';
include INC_DIR . "cy_func.php";
include '../baogao/bg_func.php';
if($u['userid']==''){
	nologin();
}
$fzx_id=$u['fzx_id'];
#######老代码
//查询下化验单数据在什么状态下能显示到报告上
$show_shuju_arr	= array();
$show_shuju_old	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='show_shuju' ORDER BY id DESC LIMIT 1");
if(!empty($show_shuju_old['module_value1'])){
	$show_shuju_arr	= explode(",",$show_shuju_old['module_value1']);
}
//查询出每种水样类型下项目的限值
$jcbz_sql="SELECT aj.vid,n.module_value2 as water_type,aj.value_C,aj.xz,aj.panduanyiju FROM n_set n JOIN assay_jcbz aj ON n.id=aj.jcbz_bh_id WHERE module_name='jcbz_bh' AND module_value3='1' ";
$jcbz_query=$DB->query($jcbz_sql);
while($jcbz_rs=$DB->fetch_assoc($jcbz_query)){
	$jcbz[$jcbz_rs['water_type']][$jcbz_rs['vid']]=$jcbz_rs['value_C'];
	if(!empty($jcbz_rs['panduanyiju'])){
		$pdyj_arr=json_decode($jcbz_rs['panduanyiju'],true);
		if(!empty($pdyj_arr)){
			$pd_jcxz_arr[$jcbz_rs['water_type']][$jcbz_rs['vid']]	= $pdyj_arr;
		}else{
			$pd_jcxz_arr[$jcbz_rs['water_type']][$jcbz_rs['vid']]=$jcbz_rs['panduanyiju'];
		}
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
#############老代码结束
$all_vid	= $all_sites	= '';
if(empty($_POST['alone_vid'])){
	$_POST['alone_vid']	= array();
}
if(empty($_POST['merger_vid'])){
	$_POST['merger_vid']= array();
}
if(empty($_POST['alone_sites'])){
	$_POST['alone_sites']	= array();
}
if(empty($_POST['merger_sites'])){
	$_POST['merger_sites']	= array();
}
$all_vid	= implode(",",array_unique(array_merge($_POST['alone_vid'],$_POST['merger_vid'])));
$all_sites	= array_merge($_POST['alone_sites'],$_POST['merger_sites']);
//放到循环站点的时候，如果两个数组都有，就先合并在查询
//print_rr($_POST);
$sql_where	= '';
if(!empty($_POST['site_type'])){
	$sql_where	.= " AND c.site_type = '".$_POST['site_type']."' ";
}
if($_POST['month_type'] == '上月'){
	$sql_begin_date	= ($year-1)."-12-{$_POST['day1']}";
}else{
	$sql_begin_date	= "$year-01-{$_POST['day1']}";
}
##########//临时为青岛添加 2016年后，就可以删除掉(下面还有一部分)
if($dw_biaozhi == 'qdzls' && $year=='2015' && $month>9){
	$sql_begin_date	= "2015-10-{$_POST['day1']}";
}
##########
$sql_where	.= " AND c.cy_date BETWEEN '".$sql_begin_date."' AND '".$_POST['end_date']."' ";
$site_arr	= $xm_arr	= $result	= $result_alone	= $result_merger	= array();
foreach($all_sites as $group_name_key=>$sites_arr){
	if(empty($_POST['alone_sites'][$group_name_key])){
		$_POST['alone_sites'][$group_name_key]	= array();
	}
	if(empty($_POST['merger_sites'][$group_name_key])){
		$_POST['merger_sites'][$group_name_key]	= array();
	}
	$site_arr=@array_unique(@array_merge($_POST['alone_sites'][$group_name_key],$_POST['merger_sites'][$group_name_key]));
	$site_str=@implode(',',$site_arr);
	if(empty($site_str) || empty($all_vid)){
		continue;
	}
	$sql	= "SELECT ao.sid,ao.water_type,ao.vd0,ao.ping_jun,ao.vid,ao.hy_flag,c.cy_date as c_date,s.syxz,s.st_type,s.site_name,ap.over,ap.unit,ap.jc_xz FROM cy c LEFT JOIN assay_order AS ao ON  ao.cyd_id=c.id LEFT JOIN sites AS s ON s.id = ao.sid LEFT JOIN assay_pay ap  ON ao.tid=ap.id WHERE s.id IN (".$site_str.") AND ao.vid IN (".$all_vid.") $sql_where AND ao.hy_flag>=0 AND c.group_name='".$group_name_key."' ORDER BY c.cy_date,ao.bar_code";//c.cy_date BETWEEN '".$_POST['begin_date']."' AND '".$_POST['end_date']."'
	$query	= $DB->query($sql);
	while($rs=$DB->fetch_assoc($query)){
		#####获取对应水样类型下的 项目名称
		//项目数组
		$max_water_type=get_water_type_max($rs['water_type'],$u['fzx_id']);
		if(empty($jcbz[$max_water_type][$rs['vid']])){
			$xm_arr[$rs['vid']]=$xm[$rs['vid']];//防止在assay_jcbz表里没有初始化某项目导致项目为空（初始化完成后应该去掉）
		}else{
			$xm_arr[$rs['vid']]=$jcbz[$max_water_type][$rs['vid']];
		}
		//化验项目数据（只有show_shuju_arr中允许的数据才会进入统计）
		if(!empty($show_shuju_arr) && !in_array($rs['over'],$show_shuju_arr)){
			$vd0	= '';
			continue;
		}else{
			if(!empty($rs['ping_jun'])&&$global['bg_pingjun']){
				$vd0 = $rs['ping_jun'];
			}else{
				$vd0 =$rs['vd0'];
			}
			if(in_array($rs['vid'],$global['modi_data_vids'])&&$vd0=='0'&&$vd0!=''){
				$vd0='未检出';
			}
			$vd0 = str_replace(" ","",$vd0);
		}
		//站点特殊标准限值判断
		if(!empty($rs['syxz'])){
			$rs['syxz']	= explode(',',$rs['syxz']);
		}
		//匹配标准限值
		$jcxz	= '';
		if(is_array($pd_jcxz_arr[$max_water_type][$rs['vid']])){
			foreach((array)$rs['syxz']	as $value_syxz){
				//按照站点特殊限制区分
				if(!empty($pd_jcxz_arr[$max_water_type][$rs['vid']][$value_syxz])){
					$jcxz	= $pd_jcxz_arr[$max_water_type][$rs['vid']][$value_syxz];
					continue;
				}
			}
			//按照水样类型区分
			if(empty($jcxz)){
				if($pd_jcxz_arr[$max_water_type][$rs['vid']][$rs['water_type']]){
					$jcxz	= $pd_jcxz_arr[$max_water_type][$rs['vid']][$rs['water_type']];
				}else{
					if(!empty($pd_jcxz_arr[$max_water_type][$rs['vid']][$max_water_type])){
						$jcxz	= $pd_jcxz_arr[$max_water_type][$rs['vid']][$max_water_type];
					}else{
						$jcxz	= $pd_jcxz_arr[$max_water_type][$rs['vid']]['其他'];
					}
				}
			}
		}else{
			$jcxz	= $pd_jcxz_arr[$max_water_type][$rs['vid']];
		}
		//判断是否合格
		$return_data=is_chaobiao($rs['vid'],$max_water_type,$jcxz,$vd0);
		###整合成 合格率统计的数组
		//根据月份区分开
		$tmp_c_date	= explode('-', $rs['c_date']);
		if($_POST['month_type'] == '上月'){
			if($tmp_c_date[1] == '1' || $tmp_c_date[1] == '01'){
				$prev_month	= 12;
			}else{
				$prev_month	= $tmp_c_date[1]-1;
			}
			if(strlen($prev_month) == '1'){
				$prev_month	= '0'.$prev_month;
			}
			$month_bz	= (int)$tmp_c_date[1];
			$begin_date	= $tmp_c_date[0].'-'.$prev_month.'-'.$_POST['day1'];
			$end_date	= $tmp_c_date[0].'-'.$tmp_c_date[1].'-'.$_POST['day2'];
		}else{
			$month_bz	= (int)$tmp_c_date[1];
			$begin_date	= $tmp_c_date[0].'-'.$tmp_c_date[1].'-'.$_POST['day1'];
			$end_date	= $tmp_c_date[0].'-'.$tmp_c_date[1].'-'.$_POST['day2'];
		}
		if(in_array($rs['sid'], $_POST['alone_sites'][$group_name_key]) && in_array($rs['vid'], $_POST['alone_vid'])){
			if(empty($result_alone['month'][$rs['vid']])){
				$result_alone['month'][$rs['vid']]['num']	= 0;
				$result_alone['month'][$rs['vid']]['ok_num']= 0;
			}
			if(empty($result_alone['year'][$rs['vid']])){
				$result_alone['year'][$rs['vid']]['num']	= 0;
				$result_alone['year'][$rs['vid']]['ok_num']= 0;
			}
			if($rs['c_date']>=$_POST['begin_date'] && $rs['c_date']<=$_POST['end_date']){
				$result_alone['month'][$rs['vid']]['num']++;
				if(!$return_data['status']){
					$result_alone['month'][$rs['vid']]['ok_num']++;
				}
			}
			$result_alone['year'][$rs['vid']]['num']++;
			if(!$return_data['status']){
				$result_alone['year'][$rs['vid']]['ok_num']++;
			}
		}else{
			if(in_array($rs['vid'], $_POST['merger_vid'])){
				$result_merger[$month_bz][$rs['vid']]['num']++;
				if(!$return_data['status']){
					$result_merger[$month_bz][$rs['vid']]['ok_num']++;
				}
			}
		}
	}
}
$now_month	= (int)$month;//上个php中GET赋值的
$merger_num_arr	= array();
foreach($result_merger as $key_month=>$vid_arr){
	$tmp_month_num	= array();
	foreach ($vid_arr as $vid => $num_arr){
		if($now_month == $key_month){
			$merger_num_arr['month']['num']		+= $num_arr['num'];
			$merger_num_arr['month']['ok_num']	+= $num_arr['ok_num'];
		}
		$tmp_month_num['year']['num']		+= $num_arr['num'];
		$tmp_month_num['year']['ok_num']	+= $num_arr['ok_num'];
	}
	//把每个月对应瓶次加起来
	$merger_num_arr['year']['num']		+= ($tmp_month_num['year']['num'] /3 ) * 90;//这里的 3应该对应 有都少个站点，90应该是用户自己可以设置的
	$merger_num_arr['year']['ok_num']	+= ($tmp_month_num['year']['ok_num'] /3 ) * 90;//这里的 3应该对应 有都少个站点，90应该是用户自己可以设置的
}
$merger_num_arr['month']['num']		= ($merger_num_arr['month']['num'] /3 ) * 90;
$merger_num_arr['month']['ok_num']	= ($merger_num_arr['month']['ok_num'] /3 ) * 90;
//print_rr($result_alone);
//print_rr($result_merger);
//print_rr($merger_num_arr);
//将内容显示出来
$month_ok_num	= $month_num	= $year_ok_num	= $year_num	= 0;
##########//临时为青岛添加 2016年后，就可以删除掉(下面还有一部分)
if($dw_biaozhi == 'qdzls' && $year=='2015' && $month>9){
	foreach ($result_alone['year'] as $key => $value){
		if($key=='104'){
			$result_alone['year'][$key]['num']		+= 486;
			$result_alone['year'][$key]['ok_num']	+= 486;
		}else{
			$result_alone['year'][$key]['num']		+= 1775; 
			$result_alone['year'][$key]['ok_num']	+= 1775;
		}
	}
	$merger_num_arr['year']['num']		+= 23490;
	$merger_num_arr['year']['ok_num']	+= 23490;
}
####################
$rowspan_num	= count($result_alone['month']);
$num_array		= array("一","二","三","四","五","六","七","八","九","十");
if(empty($num_array[($rowspan_num-1)])){
	$num_array[($rowspan_num-1)]	= $rowspan_num;
}
$lines	= "";
foreach ($result_alone['month'] as $key => $value){
	if($lines ==''){
		$lines	.= "<tr><td rowspan='".($rowspan_num+1)."' style='width:50px;'>常规日检{$num_array[($rowspan_num-1)]}项</td>";
	}else{
		$lines	.= "<tr>";
	}
	$lines	.= "<td style='width:200px'>{$xm_arr[$key]}</td><td>{$value['ok_num']}</td><td>{$value['num']}</td><td>".hegelv($value['ok_num'],$value['num'])."</td><td>{$result_alone['year'][$key]['ok_num']}</td><td>{$result_alone['year'][$key]['num']}</td><td>".hegelv($result_alone['year'][$key]['ok_num'],$result_alone['year'][$key]['num'])."</td></tr>";
	$month_ok_num	+= $value['ok_num'];
	$month_num		+= $value['num'];
	$year_ok_num	+= $result_alone['year'][$key]['ok_num'];
	$year_num		+= $result_alone['year'][$key]['num'];
}
//小计
$lines	.= "<tr><td>小计</td><td>$month_ok_num</td><td>$month_num</td><td>".hegelv($month_ok_num,$month_num)."</td><td>$year_ok_num</td><td>$year_num</td><td>".hegelv($year_ok_num,$year_num)."</td></tr>";
//常规29项
$lines	.= "<tr><td colspan='2'>常规月检".count($_POST['merger_vid'])."项</td><td>{$merger_num_arr['month']['ok_num']}</td><td>{$merger_num_arr['month']['num']}</td><td>".hegelv($merger_num_arr['month']['ok_num'],$merger_num_arr['month']['num'])."</td><td>{$merger_num_arr['year']['ok_num']}</td><td>{$merger_num_arr['year']['num']}</td><td>".hegelv($merger_num_arr['year']['ok_num'],$merger_num_arr['year']['num'])."</td></tr>";
//综合合格率
$all_month_ok_num	= $month_ok_num + $merger_num_arr['month']['ok_num'];
$all_month_num		= $month_num + $merger_num_arr['month']['num'];
$all_year_ok_num	= $year_ok_num+$merger_num_arr['year']['ok_num'];
$all_year_num		= $year_num+$merger_num_arr['year']['num'];
$lines	.= "<tr><td colspan='2'>综合合格率</td><td>".$all_month_ok_num."</td><td>".$all_month_num."</td><td>".hegelv($all_month_ok_num,$all_month_num)."</td><td>".$all_year_ok_num."</td><td>".$all_year_num."</td><td>".hegelv($all_year_ok_num,$all_year_num)."</td></tr>";

$title	= $_POST['cgb_title']."（{$year}年{$month}月）";
$water_area_months	= temp("any_data/month_bg_gw_hgl_export.html");
if($_POST['sub']=='查看成果'){
	echo $water_area_months;
}
if($_POST['sub']=='下载成果'){
	header("Content-Type:   application/msexcel");        
    header("Content-Disposition:   attachment;   filename=".$file_name.".xls");        
    header("Pragma:   no-cache");        
    header("Expires:   0");        
	echo $water_area_months;
}
//合格率计算
function hegelv($hege_num,$all_num){
	if($hege_num>$all_num){
		return false;
	}
	if($all_num	== 0){
		$hegelv_value	= '100.00';
	}else{
		$hegelv_value	= '';
		//合格率计算并转换为 % ，且保留2位小数
		$hegelv_value	= number_format((($hege_num / $all_num) * 100),2);
	}
	return $hegelv_value;
}
