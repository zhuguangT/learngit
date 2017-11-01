<?php
/*
	功能：获取统计报告所需各种条件
	作者：高龙
	时间：2016/5/6
*/
if(!empty($_POST['alone_vid'])){
	$vid_arr=$_POST['alone_vid'];
	$vid_arr_s=implode(',', $vid_arr);
}else{
	echo "<script>alert('请先选择化验项目'); window.close();</script>";
}
//查询出所有的项目名称
$xm_sql="SELECT id,value_C FROM assay_value";
$xm_query=$DB->query($xm_sql);
while($xm_rs=$DB->fetch_assoc($xm_query)){
	$xm[$xm_rs['id']]=$xm_rs['value_C'];
}

//查询出每种水样类型下项目的名称
$jcbz_sql="SELECT aj.vid,aj.dw,n.module_value2 as water_type,aj.value_C,aj.xz,aj.panduanyiju FROM n_set n JOIN assay_jcbz aj ON n.id=aj.jcbz_bh_id WHERE module_name='jcbz_bh' AND module_value3='1' ";
$jcbz_query=$DB->query($jcbz_sql);
while($jcbz_rs=$DB->fetch_assoc($jcbz_query)){
	$jcbz[$jcbz_rs['water_type']][$jcbz_rs['vid']]=$jcbz_rs['value_C'];
}
//将传过来的年份和月份转化成具体的时间段
switch ($_GET['bg']) {
	case 'nianbao'://年报
		$time_start	=$time_duan['begin_date'];
		$time_end	=$time_duan['end_date'];
		break;
	case 'yuebao'://月报
		if($time_duan['month_type'] == '本月'){
			$time_start = $_GET['year'].'-'.$_GET['month'].'-'.$time_duan['day1'];
			$time_end   = $_GET['year'].'-'.$_GET['month'].'-'.$time_duan['day2'];
		}else{
			if($_GET['month'] == '01'){
				$time_start = ($_GET['year']-1).'-'.'12'.'-'.$time_duan['day1'];
				$time_end   = $_GET['year'].'-'.$_GET['month'].'-'.$time_duan['day2'];
			}else{
				$smonth = $_GET['month'] - 1;
				if($smonth < 10){
					$smonth = '0'.$smonth;
				}
				$time_start = $_GET['year'].'-'.$smonth.'-'.$time_duan['day1'];
				$time_end   = $_GET['year'].'-'.$_GET['month'].'-'.$time_duan['day2'];
			}
		}
		break;
	case 'zhoubao'://周报
		$shi = array('01','03','05','07','08','10','12');
		$year  = substr($_GET['begin_date'],0,4);
		$month = substr($_GET['begin_date'],5,2);
		$day   = substr($_GET['begin_date'],8,2);
		$zhou_arr['周一'] = $_GET['begin_date'];
		$zhou2_arr = array('周二','周三','周四','周五','周六','周日');
		for($i=0;$i<6;$i++){//***
			$day = $day+1;
			if($day < 10){
				$day = '0'.$day;
			}
			if(in_array($month,$shi) || $month == '02'){
				if($month == '02'){
					if($day > 29){
						$month = '0'.($month+1);
						$day   = '01';
						$zhou_arr[$zhou2_arr[$i]] = $year.'-'.$month.'-'.$day;
					}else{
						$zhou_arr[$zhou2_arr[$i]] = $year.'-'.$month.'-'.$day;
					}
					
				}
				if(in_array($month, $shi)){
					if($day > 31){
						$month = $month + 1;
						$day   = '01';
						if($month < 10){
							$month = '0'.$month;
							$zhou_arr[$zhou2_arr[$i]] = $year.'-'.$month.'-'.$day;
						}
						if($month >=12){
							$year = $year + 1;
							$month = '01';
							$zhou_arr[$zhou2_arr[$i]] = $year.'-'.$month.'-'.$day;
						}
						if($month>=10 && $month<=11){
							$zhou_arr[$zhou2_arr[$i]] = $year.'-'.$month.'-'.$day;
						}
					}else{
						$zhou_arr[$zhou2_arr[$i]] = $year.'-'.$month.'-'.$day;
					}
					
				}
			}else{
				if($day > 30){
					$month = $month+1;
					$day   = '01';
					if($month < 10){
						$month = '0'.$month;
						$zhou_arr[$zhou2_arr[$i]] = $year.'-'.$month.'-'.$day;
					}else{
						$zhou_arr[$zhou2_arr[$i]] = $year.'-'.$month.'-'.$day;
					}
				}else{
					$zhou_arr[$zhou2_arr[$i]] = $year.'-'.$month.'-'.$day;
				}
			}
		}//***
		$time_start = $zhou_arr[$time_duan['week1']];
		$time_end   = $zhou_arr[$time_duan['week2']];
		break;
	case 'ribao'://日报
		if(!empty($time_duan)){
			$day = substr($_GET['date'],8);
			if(($day - $time_duan['before_days']) >= 1){
				$day_time = substr($_GET['date'],0,8);
				$time_start = $day_time.($day - $time_duan['before_days']);
				$time_end   = $day_time.($day - $time_duan['before_days']);
			}else{
				$time_start = $_GET['date'];
				$time_end   = $_GET['date'];
			}
		}else{
			$time_start = $_GET['date'];
			$time_end   = $_GET['date'];
		}
		break;
	default:
		# code...
		break;
}
if($_POST['month_type']=='any_data'){//任意成果查询
	$time_start	=$_POST['time_start'];
	$time_end	=$_POST['time_end'];
}
$cy_date_sq	= "(cr.cy_date between '".$time_start."' AND '".$time_end."' OR c.cy_date between '".$time_start."' AND '".$time_end."' )";
$bg_year	= $_GET['year'];//截取报告显示的年份
$site_arr	= $cy_date_arr=$unit_arr	= array();

//查询月报的数据
foreach($_POST['sites'] as $group_name_key=>$sites_arr){//***************************
	$site_arr=array_unique(array_merge($site_arr,$sites_arr));
	$site_str=implode(',',$sites_arr);
	$sql="SELECT ss.sgnq,ss.site_code,ss.xz_area,cr.cy_date,c.cy_date as cyd_date,c.id as cy_id,c.group_name,cr.id,cr.sid,cr.bar_code,cr.water_type,cr.cyd_id,cr.river_name,cr.site_name,cr.cy_way,cr.cy_time,cr.water_height,cr.liu_l,cr.qi_wen,ao.vid,ap.unit FROM   cy_rec cr LEFT JOIN assay_order ao on cr.id=ao.cid LEFT JOIN cy c ON cr.cyd_id = c.id  LEFT JOIN assay_pay ap ON ao.tid = ap.id  LEFT JOIN sites ss ON cr.sid = ss.id WHERE ".$cy_date_sq." AND cr.sid IN (".$site_str.") AND ao.vid IN (".$vid_arr_s.") AND cr.zk_flag >= '0' ORDER BY cr.water_type,c.cy_date, cr.bar_code";
	$query=$DB->query($sql);
	while($rs=$DB->fetch_assoc($query)){//**************
		//求最大的水样类型
		$max_water_type=get_water_type_max($rs['water_type'],$u['fzx_id']);

		//提取批次id和cy_rec的id
		$pcid_arr[$rs['cyd_id']][] = $rs['id'];
		//获取采样时间
		if(!empty($rs['cy_date'])){
			$tmp_date	= $rs['cy_date'];
		}else{
			$tmp_date	= $rs['cyd_date'];
		}
		$cy_date[] = $tmp_date;
		//获取每个检测项目的单位
		if(!empty($rs['unit']) && !array_key_exists($rs['vid'],$unit_arr)){
			$unit_arr[$rs['vid']] = $rs['unit'];
		}
	
		//提取每个站点所必要的信息
		$site_inf[$rs['id']]['site_name'] = $rs['site_name'];	//站点名称
		$site_inf[$rs['id']]['cy_way'] = $rs['cy_way'];		//采样位置		   
		$site_inf[$rs['id']]['cy_date'] = $tmp_date;	//采样日期
		$site_inf[$rs['id']]['cy_time'] = $rs['cy_time'];	//采样时间
		$site_inf[$rs['id']]['liu_l'] = $rs['liu_l'];		//流量
		$site_inf[$rs['id']]['qi_wen'] = $rs['qi_wen' ];	//气温
		$site_inf[$rs['id']]['water_height'] = $rs['water_height'];  //水位
		$site_inf[$rs['id']]['river_name'] = $rs['river_name'];  //河流
		$site_inf[$rs['id']]['sgnq'] = $rs['sgnq'];   //水功能区
		$site_inf[$rs['id']]['site_code'] = $rs['site_code'];   //站点编码
		$site_inf[$rs['id']]['bar_code'] = $rs['bar_code'];   //样品编码

		//取出与站点有关的信息
		$site_infor[$rs['sid']]['site_name'] = $rs['site_name'];

		//取出行政区下的所有的站点
		if($rs['xz_area'] != ''){
			$xzq_inf[$rs['xz_area']][$rs['sid']]['site_name'] = $rs['site_name'];
		}
		
		//提取每个批次的信息
		$pc_information[$rs['cy_id']]['group_name'] = $rs['group_name'];//批次名称
		$pc_information[$rs['cy_id']]['site_code'][$rs['id']] = $rs['site_code'];//站点编码

	}//**************
}//***************************
	//判断有没有数据
if(empty($pcid_arr)){
	echo "<script>alert('没有查询到任何数据！！'); window.close();</script>";exit();
}
	//设定时间条件
	if(!empty($cy_date)){
		$cy_dates = array_filter($cy_date);
		$begin_date_t = min($cy_dates);
		$end_date_t = max($cy_dates);
	}
	
	//将时间转化为年月日
	$begin_date_arr = explode('-', $begin_date_t);
	$end_date_arr   = explode('-', $end_date_t);
	$begin_ymr = $begin_date_arr[0].'年'.$begin_date_arr[1].'月'.$begin_date_arr[2].'日';
	$end_ymr   = $end_date_arr[0].'年'.$end_date_arr[1].'月'.$end_date_arr[2].'日';

	//设定站点cy的id和cy_rec的id
	if(!empty($pcid_arr)){
		foreach ($pcid_arr as $key => $value) {
			$cids_arr_t[$key] = implode(',', array_unique($value));
		}
	}

	//设定项目条件
	$vids_arr_t = $vid_arr;
	if(!empty($_POST['export_element'])){
		$export_element_t = $_POST['export_element'];
	}else{
		//此报告不用求平均值和最大最小值因此将$export_element_t数组赋result就行
		$export_element_t = array("result","jc_cb_sum","max_min_value");
	}
?>