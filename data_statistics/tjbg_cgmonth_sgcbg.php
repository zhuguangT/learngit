<?php
/*
	功能：查看和下载常规月报信息
	时间：2016/6/2
	作者：高龙
*/
	$mbhtd = $mb_arr['mbhtd'];//接受配置信息
	$mbhtd2 = $mb_arr['mbhtd2'];//接受配置信息
	//配置表格前几行的信息
	$pz_table_one = array(
		"cyrq" => "cy_date",
		"cysj" => "cy_time",
		"ypbh" => "bar_code",
		"xm"   => "xm",
	);
	$pz_table_two = array(
		"cy_date"	=> "采样日期",
		"cy_time"	=> "采样时间",
		"bar_code"	=> "水样编号",
		"xm"	=> "项目",
		"wrw"	=> "污染物",
	);
	$pz_table_jg	= $talbe_bt_tr	= [];
	foreach ($mbhtd as $v) {//根据$mbhtd里的信息开始配置信息
		$v 	= empty($pz_table_one[$v])?$v:$pz_table_one[$v];
		$talbe_bt_tr[$v]	.= "<td colspan='2'>{$pz_table_two[$v]}</td>";
	}
	/*foreach($mbhtd2 as $v){//根据$mbhtd2里面的信息开始配置
		$mbh_name = $pz_table_two[$v];
	}*/
	/*
	<tr align="center" style="mso-height-source:auto;">
		<td colspan="2">{$mbh_name}</td>{$talbe_h_td}
	</tr>
	*/
	//获取站点名称
	$site_name_td=$talbe_h_td='';
	foreach ($site_inf as $site_value) {
		$site_name_td.= "<td>".$site_value['site_name']."</td>";
		foreach ($talbe_bt_tr as $field_key => $field_value) {
			$talbe_bt_tr[$field_key]	.= "<td>{$site_value[$field_key]}</td>";
		}
	}
	$talbe_bt_tr	= "<tr>".implode('</tr><tr>', $talbe_bt_tr)."</tr>";
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
		$week_bg_line.= dqsj($resultss,$unit_arr,$site_inf,$site_inf_arr);//调用读取数组数据的函数
		if($i == count($xm_arr)){//计算该报告的模板
			$tjbg=temp("any_data/".$mbname);
		}
		$resultss = '';//赋空为了防止影响下一次循环
	}//**
?>