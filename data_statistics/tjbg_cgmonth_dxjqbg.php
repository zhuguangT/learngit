<?php
/*
	功能：查看和下载常规月报信息
	时间：2016/6/2
	作者：高龙
*/

	//获取批次名称和站点编号
	$pc_name_td=$site_code_td='';//给这些值赋空防止其值受到影响
	foreach($pc_information as $k=>$v){
		$hbls = count($v['site_code']);
		$pc_name_td.="<td colspan=".$hbls.">".$v['group_name']."</td>";
		foreach($v['site_code'] as $value){
			$site_code_td.="<td>".$value."</td>";
		}
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
	
	//开始计算报表的模板
	$i = 0;
	foreach($xm_arr as $ks=>$vs){//**
		$i++;
		$resultss[$ks] = $vs;
		$week_bg_line.= dqsj($resultss,$unit_arr,$pc_information,$site_inf_arr);//调用读取数组数据的函数
		if($i == count($xm_arr)){//计算该报告的模板
			$tjbg=temp("any_data/".$mbname);
		}
		$resultss = '';//赋空为了防止影响下一次循环
	}//**
?>