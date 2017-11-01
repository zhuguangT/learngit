<?php
/*
*功能：水质周报
*作者：hanfeng
*时间：2015-05-17
*/
include '../temp/config.php';

if($u['userid'] == ''){
	nologin();
}
//ajax修改水质公示月报的发布时间
if($_POST['action']=='save_fb_date'){
	if($_POST['fb_date']&&$_POST['year']&&$_POST['month']){
		$fb_date_query=$DB->query("SELECT * FROM n_set WHERE module_name='month_fb_date' AND module_value1='".$_POST['year']."'");
		$fb_date_nums=$DB->num_rows($fb_date_query);
		if(!$fb_date_nums){
			$DB->query("INSERT INTO n_set(fzx_id,module_name,module_value1)values('".$u['fzx_id']."','month_fb_date','".$_POST['year']."')");
		}
		$fb_date_rs=$DB->fetch_one_assoc("SELECT * FROM n_set WHERE module_name='month_fb_date' AND module_value1='".$_POST['year']."'");
		if(!empty($fb_date_rs['module_value2'])){
			$fb_date_arr=json_decode($fb_date_rs['module_value2'],true);
		}
		$fb_date_arr[$_POST['year'].'-'.$_POST['month']]=$_POST['fb_date'];
		$fb_date_json=JSON($fb_date_arr);
		$DB->query("UPDATE n_set SET module_value2='".$fb_date_json."' WHERE id='".$fb_date_rs['id']."'");
		if($DB->affected_rows()){
			echo 1;
		}else{
			echo 0;
		}
	}
	exit();
}

//导航
$trade_global['daohang'] = array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'常规公报','href'=>'./data_statistics/tjbg_gb.php'),
);
$trade_global['js']		= array('date-time/bootstrap-datepicker.min.js','date-time/bootstrap-timepicker.min.js','boxy.js');
$trade_global['css']	= array('lims/main.css','datepicker.css','bootstrap-timepicker.css','lims/buttons.css','boxy.css');
if(empty($_GET['action'])){
	$_GET['action']='day';
}
$fzx_id	= $u['fzx_id'];

if(empty($_GET['year'])){
	$year=date('Y');
}else{
	$year=$_GET['year'];
}
if(empty($_GET['month'])){
	$month=date('m');;
}else{
	$month=$_GET['month'];
}


//年份的显示
$now_year=date('Y');
$year_option='';
for($begin_year;$begin_year<=$now_year;$begin_year++){
	if($year==$begin_year){
		$year_option.="<option value=".$begin_year." selected=\"selected\">".$begin_year."</option>";
	}else{
		$year_option.="<option value=".$begin_year." >".$begin_year."</option>";
	}
}
//月份显示
$month_option='';
if($year<date('Y')){
	$max_month=12;
}else{
	$max_month=date('m');
}
for($m=1;$m<=$max_month;$m++){
	if($m<10){
		$m='0'.$m;
	}
	if($month==$m){
		$month_option.="<option value=".$m." selected=\"selected\">".$m."</option>";
	}else{
		$month_option.="<option value=".$m.">".$m."</option>";
	}
}
if($_GET['action']=='day' || $_GET['action']=='day_modify_ajax'){
	######日报ajax修改填表日期
	if($_GET['action']=='day_modify_ajax'){
		if(!empty($_GET['date_value'])){
			$rs_set		= $DB->fetch_one_assoc("SELECT * FROM `baogao_list` WHERE fzx_id='".$fzx_id."' AND `name_str`='day_bg_mb' AND `day`='moren'");
			$n_peizhi	= json_decode($rs_set['result_set'],true);
			if(!is_array($n_peizhi['tb_date'])){
				$n_peizhi['tb_date']= array();
			}
			if($_GET['date_value'] == $_GET['tb_date_value']){
				unset($n_peizhi['tb_date'][$_GET['date_value']]);
			}else{
				$n_peizhi['tb_date'][$_GET['date_value']]	= $_GET['tb_date_value'];
			}
			$n_peizhi_json	= JSON($n_peizhi);
			$DB->query("UPDATE `baogao_list` SET `result_set`='{$n_peizhi_json}' WHERE `id`='{$rs_set['id']}'");
			if($DB->affected_rows()){
				echo "yes";
			}else{
				echo "no";
			}
		}
		exit;
	}
	if($year<date('Y')||($year==date('Y')&&$month<date('m'))){
		$day=date('t',strtotime($year.'-'.$month.'-01'));
	}else{
		$day=date('j');
	}
	$day_gb_line='';
	$y_m=$year.'-'.$month;
	//查询水质日报的信息
	$rs_set= $DB->fetch_one_assoc("SELECT * FROM `baogao_list` WHERE `fzx_id`='".$fzx_id."' AND `name_str`='day_bg_mb' AND `day`='moren'");
	if(empty($rs_set)){
		$DB->query("INSERT INTO `baogao_list` SET `fzx_id`='{$fzx_id}',`name_str`='day_bg_mb',`baogao_name`='水质日报',`day`='moren'");
	}else{
		$n_peizhi	= json_decode($rs_set['result_set'],true);
	}
	$weekarray=array("日","一","二","三","四","五","六");
	for($t=1;$t<=$day;$t++){
		$xh=$t;
		if($t<10){
			$t='0'.$t;
		}
		$date=$y_m."-".$t;
		$cb_rs=array();
		if(!empty($n_peizhi['tb_date'][$date])){
			$tb_date	= $n_peizhi['tb_date'][$date];
		}else{
			$tb_date	= $date;
		}
		$date_week	= "(周".$weekarray[date('w',strtotime($date))].")";
		$day_gb_line.="<tr><td style='min-width:25px;'>".$xh."</td><td style='min-width:120px;'>".$date.$date_week."</td><td style='min-width:80px;'><input type='text'  name='tb_date' class='date-picker' value='{$tb_date}' date_value='{$date}' readonly/></td><td style='min-width:295px;'><a class=\"btn btn-xs btn-primary\" target=\"_blank\" href=\"tjbg_cgmonth_bg.php?set_id=$rs_set[id]&date=$date&action=see&bg=ribao\">查看</a>&nbsp;<a class=\"btn btn-xs btn-primary\" target=\"_blank\" href=\"tjbg_cgmonth_bg.php?set_id=$rs_set[id]&date=$date&action=xia&bg=ribao\">下载</a></td></tr>";
	}

	disp("tjbg_gb.html");
}
if($_GET['action']=='week'){
	$query=$DB->query("SELECT * FROM `baogao_list` where `fzx_id`='".$fzx_id."' AND `name_str`='week_bg_mb' AND `week`='moren'");
	if(!$query){
		$DB->query("INSERT INTO `baogao_list` SET `fzx_id`='{$fzx_id}',`name_str`='week_bg_mb',`baogao_name`='水质周报',`week`='moren'");
	}
	$rs=$DB->fetch_one_assoc("SELECT * FROM `baogao_list` where `fzx_id`='".$fzx_id."' AND `name_str`='week_bg_mb' AND `week`='moren'");
	$week_nums=0;
    $week_arr=array("1"=>"第一周","2"=>"第二周","3"=>"第三周","4"=>"第四周","5"=>"第五周","6"=>"第六周");
	$days = date('t', strtotime($year.'-'.$month .'-01'));//返回天数  
	if($year==date('Y')){
		if($month==date('n')){
			$days=date('j');
		}
	}
	for($d=1;$d<=$days;$d++){
		if(!$start_date){
			$start_date=$year.'-'.$month .'-'.$d;
			if(date('w',strtotime($start_date))>0&&date('w',strtotime($start_date))<6){
				$cha=date('w',strtotime($start_date))-1;
				$start_date=date("Y-m-d",strtotime('-'.$cha.' day '.$start_date));
			}else{
				$start_date='';
			}
		}
		if($d<10){
			$d='0'.$d;
		}
		$week=date('w',strtotime($year.'-'.$month .'-'.$d));
		if(($week==5||$d==$days)&&$start_date){
			$end_date=$year.'-'.$month .'-'.$d;
			if(date('w',strtotime($end_date))<5){
				$add=5-date('w',strtotime($end_date));
				$end_date=date("Y-m-d",strtotime('+'.$add.' day '.$end_date));
			}
			$week_nums++;
			$week_bg_line.="<tr><td style='min-width:30px;'>".$week_arr[$week_nums]."</td>
			<td style='min-width:80px;'>".$start_date."</td>
			<td style='min-width:80px;'>".$end_date."</td>
			<td style='min-width:290px;' nowrap><a class=\"btn btn-xs btn-primary\" target=\"_blank\" href=\"tjbg_cgmonth_bg.php?set_id=$rs[id]&begin_date=$start_date&end_date=$end_date&action=see&bg=zhoubao\">查看明细</a>&nbsp;<a class=\"btn btn-xs btn-primary\" target=\"_blank\" href=\"tjbg_cgmonth_bg.php?set_id=$rs[id]&begin_date=$start_date&end_date=$end_date&action=xia&bg=zhoubao\" >下载明细</a></td>
			</tr>";
			$start_date='';
		}
	}
	
	echo temp("any_data/tjbg_week_list");
	exit();
}
if($_GET['action']=='month'){
	//查询当前年份的发布日期
	$fb_json=$DB->fetch_one_assoc("SELECT * FROM n_set WHERE module_name='month_fb_date' AND module_value1='".$year."'");
	$fb_date_arr=json_decode($fb_json['module_value2'],true);
	$is_month_data=$DB->fetch_one_assoc("SELECT * FROM `baogao_list` where `fzx_id`='".$fzx_id."' AND `name_str`='gb_month_mb' AND `month`='moren'");
	if(!$is_month_data){
		$DB->query("INSERT INTO `baogao_list` SET `fzx_id`='{$fzx_id}',`name_str`='gb_month_mb',`baogao_name`='水质月报',`month`='moren'");
	}
	$rs=$DB->fetch_one_assoc("SELECT * FROM `baogao_list` where `fzx_id`='".$fzx_id."' AND `name_str`='gb_month_mb' AND `month`='moren'");
	$month_bg_line='';
	for($m=1;$m<=$max_month;$m++){
		if($m<10){
			$month='0'.$m;
		}else{
			$month=$m;
		}
		$fb_date='';
		if(empty($fb_date_arr[$year.'-'.$month])){
			$fb_date=date('Y-m-d',strtotime("+1 months", strtotime($year.'-'.$month.'-10')));
		}else{
			$fb_date=$fb_date_arr[$year.'-'.$month];
		}
		$month_bg_line.="<tr><td>".$m."</td>
		<td>".$year."-".$month."</td>
		<td><input type=\"text\" id='".$year."-".$month."' class=\"date-picker\" readonly=\"readonly\" value=".$fb_date." onchange=\"save_fb_date(this,".$year.",'".$month."')\"></td>
		<td ><a class=\"btn btn-xs btn-primary\" target=\"_blank\" href=\"tjbg_month_bg.php?set_id=$rs[id]&year_month=".$year."-".$month."&year=".$year."&month=".$month."&action=view\">查看明细</a>&nbsp;<a class=\"btn btn-xs btn-primary\" target=\"_blank\" href=\"tjbg_month_bg2.php?set_id=$rs[id]&year_month=".$year."-".$month."&year=".$year."&month=".$month."&action=view\" >查看公报</a>&nbsp;<a class=\"btn btn-xs btn-primary\" target=\"_blank\" href=\"tjbg_month_bg.php?set_id=$rs[id]&year_month=".$year."-".$month."&year=".$year."&month=".$month."&action=load\" >下载明细</a>&nbsp;<a class=\"btn btn-xs btn-primary\" target=\"_blank\" href=\"tjbg_month_bg2.php?set_id=$rs[id]&year_month=".$year."-".$month."&year=".$year."&month=".$month."&action=load\" >下载公报</a></td>
		</tr>";
	}
	echo temp("any_data/tjbg_month_list");
	exit();
}
if($_GET['action']=='year'){
	$tjbg_year_line= '';
	$xuhao	= '';
	$month_bg_arr	= array();
	$sql_baogao_list= $DB->query("SELECT * FROM `baogao_list` WHERE `fzx_id`='".$fzx_id."' AND `name_str`='tjbg_year' AND (`year`='{$year}' OR `year`='moren') ORDER BY `px`,`year`");
	while($rs_baogao_list = $DB->fetch_assoc($sql_baogao_list)){
		//sql的month排序上是先正常月份后moren所以当读到moren却还没有正常月份的记录时，就说明没有正常月份的记录
		if($rs_baogao_list['year'] == 'moren'){
			if(!in_array($rs_baogao_list['baogao_name'],$month_bg_arr)){
				//新插入一条记录
				$DB->query("INSERT INTO `baogao_list` SET `fzx_id`='$fzx_id',`px`='{$rs_baogao_list['px']}',`name_str`='tjbg_year',`baogao_name`='{$rs_baogao_list['baogao_name']}',`year`='$year',`result_set`='{$rs_baogao_list['result_set']}',`gx_set`='{$rs_baogao_list['gx_set']}'");
				$rs_baogao_list['id']	= $DB->insert_id();
				$rs_baogao_list['year']= $month;
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
		if(empty($gx_set_json['set_mb_name'])){//管网合格率报表的模板
			if($rs_baogao_list['name_str']=='month_bg' && stristr($rs_baogao_list['baogao_name'],'管网合格率')){
				$gx_set_json['set_mb_name']	= "month_bg_gw_hgl";
				$gx_set_json['result_mb_name']     = "month_bg_gw_hgl";
				$gx_set_json_str	= JSON($gx_set_json);
				$DB->query("UPDATE `baogao_list` SET `gx_set`='{$gx_set_json_str}' WHERE `id`='{$cg_rs['id']}'");
			}
		}
		//判断报告是否审核，如果审核就不能再修改设置了。
		$can_click	= '';
		if($rs_baogao_list['status']){
			$can_click	= ' onclick="alert(\'报告已签发,不允许再修改\');return false;" ';
		}
		//显示行信息
		if(!empty($gx_set_json['set_mb_name'])){
			$link_php	= $gx_set_json['set_mb_name'].'.php';
		}else{
			$link_php	= 'any_sites_result.php';
		}
		$xuhao++;
		$tjbg_year_line	.= "<tr>
									<td>$xuhao</td>
									<td>".$rs_baogao_list['baogao_name']."</td>
									<td>".$year."年</td>
									<td nowrap>
										<a class=\"btn btn-xs btn-primary\" target=\"_blank\" href=\"select_export_mb.php?set_id=$rs_baogao_list[id]&year={$year}&action=查看成果\">查看</a>&nbsp;
										<a class=\"btn btn-xs btn-primary\" target=\"_blank\" href=\"select_export_mb.php?set_id=$rs_baogao_list[id]&year={$year}&action=下载成果\">下载</a>&nbsp;
										<a class=\"btn btn-xs btn-primary\" target=\"_blank\" href=\"$link_php?set_id=$rs_baogao_list[id]&action={$rs_baogao_list[name_str]}\" $can_click>设置</a>
									</td>
								</tr>";
	}
	echo temp("any_data/tjbg_year_list");
	exit();
}
?>
