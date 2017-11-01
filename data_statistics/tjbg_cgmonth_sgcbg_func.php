<?php
/*
	功能：存放常规月报所需要的函数
	作者：高龙
	时间：2016/5/16
*/
//封装一个用于读取站点数据数组的函数
function dqsj($resultss,$unit_arr,$site_inf,$site_inf_arr){//start***
	foreach($resultss as $k=>$v){//**
		$vid_data_td='';
		foreach($site_inf as $k2=>$v2){
			$vd0_class	= $tid	= '';
			if(!empty($site_inf_arr[$k2][$k])){
				if(isset($site_inf_arr[$k2][$k]['vd0'])){
					$vd0=$site_inf_arr[$k2][$k]['vd0'];
				}else{
					$vd0='';
				}
				if(!empty($site_inf_arr[$k2][$k]['tid'])){
					$vd0_class	= "vd0_button";
					$tid		= $site_inf_arr[$k2][$k]['tid'];
				}
			}else{
				$vd0 = '/';
			}
			$vid_data_td.="<td  class='{$vd0_class}' tid='{$tid}' style='vnd.ms-excel.numberformat:@'>".$vd0."</td>";
		}
		$xm_danwei='';//每次初始化防止项目单位出错
		if(isset($unit_arr[$k])){//获取项目单位
			$xm_danwei = $unit_arr[$k];
		}
		$week_bg_line="<tr align=\"center\"><td>".$v."</td><td>".$xm_danwei."</td>".$vid_data_td."</tr>";
	}//**
	
	return $week_bg_line;
}//end***
?>
