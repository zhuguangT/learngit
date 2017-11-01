<?php
/*
	功能：查看和下载常规月报信息
	时间：2016/6/2
	作者：高龙
*/
	//截取月份
	$time_months = substr($time_start,0,7);

	//某水类型几项的指标合格率
	$water_zbhgl = $mb_arr['water_zbhgl'];

	//获取站点名称
	$site_name_td = '';//给值赋空防止出错
	foreach($site_infor as $k=>$v){
		$site_name_td.="<td>".$v['site_name']."</td>";
	}

	//标题需要合并的列
	$cols1=count($site_infor);
	$z_cols=$mbjbls+$cols1;
	//开始计算报表的模板
	$xm_arr_sum = count($xm_arr);//计算项目总数
	$i = 0;
	foreach($xm_arr as $ks=>$vs){//**
		$i++;
		$resultss[$ks] = $vs;
		foreach($resultss as $k=>$v){//**
		$vid_data_td=$vid_data_td1=$vid_data_td2='';
			foreach($site_infor as $k2=>$v2){////**
			if(!empty($return_jc_cb_sum[$k2][$k])){
				$vd0 = $return_jc_cb_sum[$k2][$k][$time_months]['jc_sum'];//项目检测次数
				$vd1 = ($vd0)-($return_jc_cb_sum[$k2][$k][$time_months]['cb_sum']);//项目合格次数
				$vd2 = (($vd1/$vd0)*100).'%';//项目合格率
				$vd2s = (($vd1/$vd0)*100);//项目合格率
			}else{
				$vd0='/';
				$vd1='/';
				$vd2='/';
				$vd2s='/';
			}
			$site_xmhgl_arr[$k2][$k]['hgl'] = $vd2s;//将每个站点的每个项目的合格率放在此数组中
			$vid_data_td.="<td style='vnd.ms-excel.numberformat:@'>".$vd0."</td>";
			$vid_data_td1.="<td style='vnd.ms-excel.numberformat:@'>".$vd1."</td>";
			$vid_data_td2.="<td style='vnd.ms-excel.numberformat:@'>".$vd2."</td>";
			$vd0=$vd2=$vd1=$vd2s='';//防止影响下一次循环
			}////**
		
		$week_bg_line.="<tr align=\"center\"><td rowspan='3'>".$v."</td><td>检测次数</td>".$vid_data_td."</tr><tr align=\"center\"><td>合格次数</td>".$vid_data_td1."</tr><tr align=\"center\"><td>合格率</td>".$vid_data_td2."</tr>";
	}//**
		if($i == $xm_arr_sum){//计算该报告的模板
			foreach($site_xmhgl_arr as $v){
				$sum = 0;
				foreach ($v as $value) {
					if($value !== '/'){
						$sum = $sum + $value['hgl'];
					}
				}
					$site_hgl = (($sum/$xm_arr_sum)*100).'%';//计算一个站点的合格率
					$site_hgl_td.="<td>".$site_hgl."</td>";
			}
			$week_bg_line.="<tr><td colspan='2'>".$water_zbhgl."</td>".$site_hgl_td."</tr>";
			$tjbg=temp("any_data/".$mbname);
		}
		$resultss = '';//赋空为了防止影响下一次循环
	}//**
?>