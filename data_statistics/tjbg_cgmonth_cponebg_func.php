<?php
/*
	功能：存放常规月报所需要的函数
	作者：高龙
	时间：2016/5/16
*/
//封装一个用于读取站点数据数组的函数
function dqsj($resultss,$unit_arr,$site_infor,$return_max_min,$return_jc_cb_sum,$time_months,$xmcs){//start***
	foreach($resultss as $k=>$v){//**
		$vid_data_td='';
		foreach($site_infor as $k2=>$v2){///**
			if(!empty($return_jc_cb_sum[$k2][$k])){
				$vdg = $return_max_min[$k2][$k][$time_months]['max'];//最高值
				$vdd = $return_max_min[$k2][$k][$time_months]['min'];//最低值
				$vdp = $return_max_min[$k2][$k][$time_months]['avg']['value'];//平均值
				$vdj = $return_jc_cb_sum[$k2][$k][$time_months]['jc_sum'];//检测次数
				$vdc = $return_jc_cb_sum[$k2][$k][$time_months]['cb_sum'];//超标次数
				$vdh = ($vdj) - ($return_jc_cb_sum[$k2][$k][$time_months]['cb_sum']);//合格次数
				$vdl = (($vdh/$vdj)*100).'%';//合格率
			}else{
				$vdg = '/';
				$vdd = '/';
				$vdp = '/';
				$vdj = '/';
				$vdc = '/';
				$vdh = '/';
				$vdl = '/';
			}

			//配置一个数组
			$h2ll = array(
				'zg' => "<td style='vnd.ms-excel.numberformat:@'>".$vdg."</td>",
				'zd' => "<td style='vnd.ms-excel.numberformat:@'>".$vdd."</td>",
				'pj' => "<td style='vnd.ms-excel.numberformat:@'>".$vdp."</td>",
				'jycs' => "<td style='vnd.ms-excel.numberformat:@'>".$vdj."</td>",
				'cbcs' => "<td style='vnd.ms-excel.numberformat:@'>".$vdc."</td>",
				'hgcs' => "<td style='vnd.ms-excel.numberformat:@'>".$vdh."</td>",
				'hgl' => "<td style='vnd.ms-excel.numberformat:@'>".$vdl."</td>",
			);

			//开始配置要显示的数据
			foreach($xmcs as $vs){
				$vid_data_td.=$h2ll[$vs];
			}

			$vdg=$vdd=$vdp=$vdj=$vdh=$vdl='';//防止影响下一次循环
		}///**
		$xm_danwei='';//每次初始化防止项目单位出错
		if(isset($unit_arr[$k])){//获取项目单位
			$xm_danwei = $unit_arr[$k];
		}
		$week_bg_line="<tr align=\"center\"><td>".$v."</td><td>".$xm_danwei."</td>".$vid_data_td."</tr>";
	}//**
	
	return $week_bg_line;
}//end***
?>
