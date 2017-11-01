<?php
/*
*功能：重要站点数据统计分析页面
*/
include '../temp/config.php';

if($u['userid'] == ''){
	nologin();
}
$fzx_id	= $u['fzx_id'];

//导航
$trade_global['daohang'][] = array('icon'=>'','html'=>'重要站点数据分析','href'=>"./data_statistics/important_site_result_list.php?year={$_GET['year']}");
$_SESSION['daohang']['important_site_result_list']	= $trade_global['daohang'];
if(empty($_GET['year'])){
	$year	= $_GET['year']	= date('Y');
}

//查询出月报统计表的模板信息
$tjbg_month_line= '';
$xuhao	= '';
$month_bg_arr	= array();
$sql_baogao_list= $DB->query("SELECT * FROM `baogao_list` WHERE `fzx_id`='".$fzx_id."' AND `name_str`='important_site' AND (`year`='{$year}' OR `year`='moren') ORDER BY `px`,`year`");
while($rs_baogao_list = $DB->fetch_assoc($sql_baogao_list)){
	//sql的month排序上是先正常月份后moren所以当读到moren却还没有正常月份的记录时，就说明没有正常月份的记录
	if($rs_baogao_list['year'] == 'moren'){
		if(!in_array($rs_baogao_list['baogao_name'],$month_bg_arr)){
			//新插入一条记录
			if(!empty($rs_baogao_list['result_set'])){
				$rs_baogao_list_json	= json_decode($rs_baogao_list['result_set'],true);
				$tmp_begin_date	= explode('-',$rs_baogao_list_json['begin_date']);
				$tmp_end_date	= explode('-',$rs_baogao_list_json['end_date']);
				$rs_baogao_list_json['begin_date']	= $year.'-'.$tmp_begin_date[1].'-'.$tmp_begin_date[2];
				$rs_baogao_list_json['end_date']	= ($year+1).'-'.$tmp_end_date[1].'-'.$tmp_end_date[2];
				$rs_baogao_list['result_set']	= JSON($rs_baogao_list_json);
			}
			$DB->query("INSERT INTO `baogao_list` SET `fzx_id`='$fzx_id',`px`='{$rs_baogao_list['px']}',`name_str`='important_site',`baogao_name`='{$rs_baogao_list['baogao_name']}',`year`='$year',`result_set`='{$rs_baogao_list['result_set']}',`gx_set`='{$rs_baogao_list['gx_set']}'");
			$rs_baogao_list['id']	= $DB->insert_id();
			$rs_baogao_list['month']= $month;
		}else{
			//默认配置的记录不需要显示，只显示本月的记录即可
			continue;
		}
	}else{
		//如果本月有配置，就记录下来
		$month_bg_arr[]	= $rs_baogao_list['baogao_name'];
	}
	$gx_set_json	= array();
	if(!empty($rs_baogao_list['gx_set'])){
		$gx_set_json= json_decode($rs_baogao_list['gx_set'],true);
	}
	//显示行信息
	if(!empty($gx_set_json['set_mb_name'])){
		$link_php	= $gx_set_json['set_mb_name'].'.php';
	}else{
		$link_php	= 'any_sites_result.php';
	}
	$xuhao++;
	$tjbg_month_line	.= "<tr>
								<td>$xuhao</td>
								<td>".$rs_baogao_list['baogao_name']."</td>
								<td>".$year."年</td>
								<!--<td>-/-</td>-->
								<td nowrap>
									<a class=\"btn btn-xs btn-primary\" target=\"_blank\" href=\"select_export_mb.php?set_id=$rs_baogao_list[id]&year={$year}&month={$month}&action=查看成果\">查看</a>&nbsp;
									<a class=\"btn btn-xs btn-primary\" target=\"_blank\" href=\"select_export_mb.php?set_id=$rs_baogao_list[id]&year={$year}&month={$month}&action=下载成果\">下载</a>&nbsp;
									<a class=\"btn btn-xs btn-primary\" target=\"_blank\" href=\"$link_php?set_id=$rs_baogao_list[id]&action={$rs_baogao_list[name_str]}\" $can_click>设置</a>
								</td>
							</tr>";
}


//年份下拉列表的显示
$now_year=date('Y');
$year_option='';
for($begin_year;$begin_year<=$now_year;$begin_year++){
	if($year==$begin_year){
		$year_option.="<option value=".$begin_year." selected=\"selected\">".$begin_year."</option>";
	}else{
		$year_option.="<option value=".$begin_year." >".$begin_year."</option>";
	}
}
disp("any_data/important_site_result_list.html");
?>
