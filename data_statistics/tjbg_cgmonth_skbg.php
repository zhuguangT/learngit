<?php
/*
	功能：查看和下载常规月报的信息
	作者：高龙
	时间：2016/6/14
*/
	

	//获取站点名称和具体采样时间
	$site_name_td=$cy_time_td='';
	foreach ($site_inf as $site_value) {
		$site_name_td.= "<td rowspan='2'>".$site_value['site_name']."</td>";
		$cy_time_td.="<td>".$site_value['cy_time']."</td>";
	}

	//标题需要合并的列
	$cols1=count($site_inf);
	$z_cols=$mbjbls+$cols1;

	//取出所有批次下的站点数据
	foreach($return_result_arr as $value){
		foreach($value as $k => $y){
			$site_inf_arr[$k]=$y;
		}
	}

	//计算平均值所需要的数值
	$site_sum = count($site_inf);
	
	//开始计算报表的模板
	$i = 0;
	foreach($xm_arr as $ks=>$vs){//**
		$i++;
		$resultss[$ks] = $vs;
		$week_bg_line.= dqsj($resultss,$unit_arr,$site_inf,$site_inf_arr,$site_sum);//调用读取数组数据的函数
		if($i == count($xm_arr)){//计算该报告的模板
			$tjbg=temp("any_data/".$mbname);
		}
		$resultss = '';//赋空为了防止影响下一次循环
	}//**
?>