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
if(empty($_POST['alone_sites'])){
	$_POST['alone_sites']	= array();
}
$all_vid	= implode(",",$_POST['alone_vid']);
$all_sites	= $_POST['alone_sites'];
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
##########
$sql_where	.= " AND cy_date BETWEEN '".$sql_begin_date."' AND '".$_POST['end_date']."' ";
$site_arr	= $xm_arr	= $result	= $result_alone	= $result_merger	= array();
foreach($all_sites as $group_name_key=>$sites_arr){
	if(empty($_POST['alone_sites'][$group_name_key])){
		$_POST['alone_sites'][$group_name_key]	= array();
	}
	$site_arr=$_POST['alone_sites'][$group_name_key];
	$site_str="'".@implode("','",$site_arr)."'";
	if(empty($site_str) || empty($all_vid)){
		continue;
	}
	$sql	= "SELECT * FROM `changbu_data` WHERE `site_name` IN (".$site_str.") $sql_where ORDER BY cy_date";
	$query	= $DB->query($sql);
	while($rs=$DB->fetch_assoc($query)){
		//根据设置判断本月累计的起止时间
		$tmp_c_date	= explode('-', $rs['cy_date']);
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
		#####获取对应水样类型下的 项目名称
		//项目数组
		$max_water_type	= get_water_type_max($rs['water_type'],$u['fzx_id']);
		//获取结果数据
		$vd0_arr	= json_decode($rs['json_data'],true);
		foreach((array)$vd0_arr as $key=>$value){
			if(empty($jcbz[$max_water_type][$key])){
				$xm_arr[$key]	= $xm[$key];//防止在assay_jcbz表里没有初始化某项目导致项目为空（初始化完成后应该去掉）
			}else{
				$xm_arr[$key]	= $jcbz[$max_water_type][$key];
			}
			if($value != '--'){
				$vd0 = $value;
				/*if(in_array($rs['vid'],$global['modi_data_vids'])&&$vd0=='0'&&$vd0!=''){
					$vd0='未检出';
				}*/
			}else{
				//continue;
				$vd0 = $value;
			}
			$vd0 = str_replace(" ","",$vd0);
			//匹配标准限值
			$jcxz	= '';
			if(is_array($pd_jcxz_arr[$max_water_type][$key])){
				//按照水样类型区分
				if($pd_jcxz_arr[$max_water_type][$key][$rs['water_type']]){
					$jcxz	= $pd_jcxz_arr[$max_water_type][$key][$rs['water_type']];
				}else{
					if(!empty($pd_jcxz_arr[$max_water_type][$key][$max_water_type])){
						$jcxz	= $pd_jcxz_arr[$max_water_type][$key][$max_water_type];
					}else{
						$jcxz	= $pd_jcxz_arr[$max_water_type][$key]['其他'];
					}
				}
			}else{
				$jcxz	= $pd_jcxz_arr[$max_water_type][$key];
			}
			//判断是否合格
			$return_data=is_chaobiao($key,$max_water_type,$jcxz,$vd0);
			###整合成 合格率统计的数组
			if(in_array($rs['site_name'], $_POST['alone_sites'][$group_name_key]) && in_array($key, $_POST['alone_vid'])){
				//初始化次数统计数组
				if(empty($result_alone['month'][$rs['site_name']][$key])){
					$result_alone['month'][$rs['site_name']][$key]['num']	= 0;
					$result_alone['month'][$rs['site_name']][$key]['ok_num']= 0;
				}
				if(empty($result_alone['year'][$rs['site_name']][$key])){
					$result_alone['year'][$rs['site_name']][$key]['num']	= 0;
					$result_alone['year'][$rs['site_name']][$key]['ok_num']= 0;
				}
				if($vd0 == '--'){
					continue;
				}
				//本月统计
				if($rs['cy_date']>=$_POST['begin_date'] && $rs['cy_date']<=$_POST['end_date']){
					$result_alone['month'][$rs['site_name']][$key]['num']++;
					if(!$return_data['status']){//合格次数
						$result_alone['month'][$rs['site_name']][$key]['ok_num']++;
					}
				}
				//年统计
				$result_alone['year'][$rs['site_name']][$key]['num']++;
				if(!$return_data['status']){//合格次数
					$result_alone['year'][$rs['site_name']][$key]['ok_num']++;
				}
			}
		}
	}
}
//print_rr($result_alone);
$rowspan_num	= count($result_alone['month']);
$line2	= '<tr>';
$lines	= "<tr><td rowspan='2' colspan='2'>指标</td>";
foreach ($xm_arr as $key => $value) {
	$lines	.= "<td colspan='3'>{$value}</td>";
	$line2	.= "<td width='32.7'>合格瓶次</td><td width='32.7'>化验瓶次</td><td width='45'>合格率(%)</td>";
}
$lines	.= "</tr>{$line2}</tr>";
$year_lines	= '';
$month_ok_num	= $month_num	= $year_ok_num	= $year_num	= array();
foreach ((array)$result_alone['month'] as $key_site_name => $value_arr){
	if($year_lines ==''){
		$lines		.= "<tr style='mso-height-source:auto;'><td rowspan='".($rowspan_num+1)."' width='25'>本月</td>";
		$year_lines	.= "<tr style='mso-height-source:auto;'><td rowspan='".($rowspan_num+1)."' width='25'>年初累计</td>";
	}else{
		$lines	.= "<tr>";
		$year_lines	.= '</tr>';
	}
	$lines	.= "<td width='55' height='45.33'>{$key_site_name}</td>";
	$year_lines	.= "<td width='55' height='45.33'>{$key_site_name}</td>";
	//需要增加一个按照项目排序的功能，防止特殊情况
	foreach ($value_arr as $key => $value) {
		if(empty($month_ok_num[$key])){
			$month_ok_num[$key]	= 0;
		}
		if(empty($month_num[$key])){
			$month_num[$key]	= 0;
		}
		if(empty($year_ok_num[$key])){
			$year_ok_num[$key]	= 0;
		}
		if(empty($year_num[$key])){
			$year_num[$key]	= 0;
		}
		$lines	.= "<td>{$value['ok_num']}</td><td>{$value['num']}</td><td>".hegelv($value['ok_num'],$value['num'],0)."</td>";
		$year_lines	.= "<td>{$result_alone['year'][$key_site_name][$key]['ok_num']}</td><td>{$result_alone['year'][$key_site_name][$key]['num']}</td><td>".hegelv($result_alone['year'][$key_site_name][$key]['ok_num'],$result_alone['year'][$key_site_name][$key]['num'],0)."</td>";
		$month_ok_num[$key]	+= $value['ok_num'];
		$month_num[$key]	+= $value['num'];
		$year_ok_num[$key]	+= $result_alone['year'][$key_site_name][$key]['ok_num'];
		$year_num[$key]		+= $result_alone['year'][$key_site_name][$key]['num'];
	}
	$lines	.= "</tr>";
	$year_lines	.= "</tr>";
}
//本月综合合格率
$lines		.= "<tr height='45.33'><td>总计</td>";
$year_lines	.= "<tr height='45.33'><td>总计</td>";
foreach($month_num as $key=>$value){
	$lines		.= "<td>{$month_ok_num[$key]}</td><td>{$value}</td><td>".hegelv($month_ok_num[$key],$value,0)."</td>";
	$year_lines	.= "<td>{$year_ok_num[$key]}</td><td>{$year_num[$key]}</td><td>".hegelv($year_ok_num[$key],$year_num[$key],0)."</td>";
}
$lines		.= "</tr>";
$year_lines	.= '</tr>';
$lines	.= $year_lines;

$title	= $_POST['cgb_title']."（{$year}年{$month}月）";
$water_area_months	= temp("any_data/month_bg_cc_hgl_export.html");
if(empty($result_alone['month'])){
	echo "<script>alert('未获取到分厂数据'); window.close();</script>";
}
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
function hegelv($hege_num,$all_num,$baoliu_num=2){

	if($hege_num>$all_num){
		return false;
	}
	if($all_num	== 0){
		$hegelv_value	= '100';
		if($baoliu_num>0){
			$hegelv_value	.= ".";
			for($i=0;$i<$baoliu_num;$i++){
				$hegelv_value	.= '0';
			}
		}
	}else{
		$hegelv_value	= '';
		//合格率计算并转换为 % ，且保留2位小数
		$hegelv_value	= number_format((($hege_num / $all_num) * 100),$baoliu_num);
	}
	return $hegelv_value;
}
