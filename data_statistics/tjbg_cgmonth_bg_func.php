<?php
/*
	功能：存放常规月报所需要的函数
	作者：高龙
	时间：2016/5/16
*/
//封装一个用于读取站点数据数组的函数
function dqsj($resultss,$xm_arr,$site_inf,$pc_information,$mbtd){//start***
	foreach ($resultss as $key => $value) {///**
		$sit_num = count($value);//批次下的所有的站点数
		$ss = 0;
		foreach($value as $k=>$v){//**
			$vid_data_td='';
			foreach($xm_arr as $k2=>$v2){
				$vd0_class	= $tid	= $cyd_id = '';
				if(!empty($v[$k2])){
					if(isset($v[$k2]['vd0'])){  
						$vd0=$v[$k2]['vd0'];
					}else{
						$vd0='';
					}
					if(!empty($v[$k2]['tid'])){
						$vd0_class	= "vd0_button";
						$tid		= $v[$k2]['tid'];
					}else if(!empty($v[$k2]['cyd_id'])){
						$vd0_class	= "vd0_button";
						$cyd_id		= $v[$k2]['cyd_id'];
					}
				}else{
					$vd0 = '/';
				}
				$vid_data_td.="<td class='{$vd0_class}' tid='{$tid}' cyd_id='{$cyd_id}' style='vnd.ms-excel.numberformat:@'>".$vd0."</td>";
			}
			$qbtd = array(//配置本报表所需要的数据列
			"pcmc"=>"<td rowspan='{$sit_num}'>".$pc_information[$key]['group_name']."</td>",//批次名称
			"dmmc"=>"<td>".$site_inf[$k]['site_name']."</td>",//站点名称
			"cyrq"=>"<td>".$site_inf[$k]['cy_date']."</td>",//采样日期
			);
			$mbtdl = '';
			$ss++;
			if($ss > 1){//只让该批次下的第一个站点有批次名称
				unset($mbtd[0]);
			}
			//筛选报告所需要的列的数据
			foreach($mbtd as $v){
				$mbtdl.=$qbtd[$v];
			}
			$week_bg_line.="<tr align=\"center\">".$mbtdl.$vid_data_td."</tr>";
		}//**
	}///**
	
	return $week_bg_line;
}//end***
?>
