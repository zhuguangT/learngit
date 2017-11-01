<?php
include("../temp/config.php");
include("../inc/cy_func.php");//需要get_water_type_max函数
//ini_set("display_errors","on");
if($u['userid']==''){
	nologin();
}
//导航
$trade_global['daohang'][]	= array('icon'=>'','html'=>'厂部数据录入','href'=>"./changbu/cb_input.php?year={$_GET['year']}&month={$_GET['month']}&water_type={$_GET['water_type']}");
$_SESSION['daohang']['cb_input']	= $trade_global['daohang'];
$trade_global['js']		= array('date-time/bootstrap-datepicker.min.js','date-time/bootstrap-timepicker.min.js','boxy.js');
$trade_global['css']	= array('lims/main.css','datepicker.css','bootstrap-timepicker.css','lims/buttons.css','boxy.css');
$fzx_id		= $u['fzx_id'];

##########管网水、出厂水下拉菜单查询管网水和出厂水
if(!empty($_GET['water_type'])){
	$moren_water_type = $_GET['water_type'];
}else{
	$moren_water_type = 88;//此ID是出厂水类型的ID
}

//根据水样类型从n_set表中查询出与其相对应的站点
$siteid = $DB->fetch_one_assoc("SELECT `module_value1` from `n_set` where `module_value2`= '{$moren_water_type}' and `module_name` = 'cbsite_id'");

if(!empty($siteid['module_value1'])){
	$result = $DB->query("SELECT `site_name` from `sites` where `id` in({$siteid['module_value1']}) and `site_mark`='fc_site'");
	while ($sitearr = $DB->fetch_assoc($result)) {
		$default_site[] = $sitearr['site_name'];
	}
}
$sylx_arr = "'地下水','出厂水','水库水','水源调查','工序水','地表水'";//水样类型
$sql_lx="SELECT `id`,`lname` FROM leixing WHERE lname in ({$sylx_arr})";
$query_lx=$DB->query($sql_lx);
$lx_options	= $leixing_moren	= $title_water_type	= '';
while($rs_lx=$DB->fetch_assoc($query_lx)){
	//默认的水样类型,后面获取上次数据的sql用到
	$selected	= '';
	if(!empty($moren_water_type) && $moren_water_type==$rs_lx['id']){
		$leixing_moren		= $rs_lx['id'];//未找到用途
		$title_water_type	= "(".$rs_lx['lname'].")";//标题
		$selected			= ' selected';
	}
	if($leixing_moren == ''){
		$leixing_moren		= $rs_lx['id'];
		$title_water_type	= "(".$rs_lx['lname'].")";
	}
	if($rs_lx['lname'] == '工序水'){
		switch ($moren_water_type) {
			case '工序水(一厂)':
				$lx_options	.= "<option value='工序水(一厂)' selected>工序水(一厂)</option><option value='工序水(二厂)' >工序水(二厂)</option>";
				break;
			case '工序水(二厂)':
				$lx_options	.= "<option value='工序水(一厂)'>工序水(一厂)</option><option value='工序水(二厂)' selected>工序水(二厂)</option>";
				break;
			default:
				$lx_options	.= "<option value='工序水(一厂)'>工序水(一厂)</option><option value='工序水(二厂)'>工序水(二厂)</option>";
				break;
		}
	}else{
		$lx_options.="<option value=".$rs_lx['id']." $selected>".$rs_lx['lname']."</option>";
	}
	$leixing[]=$rs_lx['id'];
}

//表格中的项目
$xmid_arr = $DB->fetch_one_assoc("select `module_value1` from `n_set` where `module_value2`= '{$moren_water_type}' and `module_name` = 'cbxm_id'");//查询厂部项目id
if(!empty($xmid_arr['module_value1'])){
	$vid_arr = array_filter(explode(',', $xmid_arr['module_value1']));//表格中的项目
}else{
	$vid_arr	= array(94,93,95,484,6,1,104,96,569);//默认的表格中的项目
}
$vid_value	= array();
$vid_str	= implode(',',$vid_arr);

##############获取项目名称
$xm	= array();
//从数据库获取项目名称
$sql_value	= "SELECT id,value_C FROM `assay_value` WHERE `id` IN (".$vid_str.")";
$query_value= $DB->query($sql_value);
while($rs_value=$DB->fetch_assoc($query_value)){
	$xm[$rs_value['id']]=$rs_value['value_C'];
}

//读取各个分厂的站点*******************************
$fcsite = $DB->query("select `site_name`,`id` from `sites` where `site_mark`='fc_site'");
while ($fcsite_arr = $DB->fetch_assoc($fcsite)) {
	$fcoption.="<option value='".$fcsite_arr['site_name']."' {$selected}>".$fcsite_arr['site_name']."</option>";
}
//*************************************************
################获取数据
$into_vid_td='';
$xm_nums=count($vid_arr);
$colspan	= $xm_nums+2;
$date_width=130;
$site_width=150;
$vid_width=(1085-$date_width-$site_width-15)/$xm_nums;

//本月今天的默认数据及输入行显示
$xm_name_td	= $xz_td	= $into_vid_td	= '';
foreach($vid_arr as $key=>$value){
	$into_vid_td.="<td><input type=\"text\" name=\"vid[$value][]\" value='{$moren_value}' moren_value='{$moren_value}' /></td>";
	$value_C=$xm[$value];
	$xm_name_td.="<th>".$value_C."</th>";
}
//默认显示15条可输入的行
$h	= 15;
$day_into_line	= '';
$now_month	= date("Y-m");
$now_date = date("Y-m-d");//当天日期
$now_time = date("H:i");//当前时间
if($_GET['caiyangdate']){
	$now_time = $_GET['nowtime'];
	$now_date = $_GET['nowdate'];
}
if($_GET['caiyangtime']){
	$now_date = $_GET['nowdate'];
	$now_time = $_GET['nowtime'];
}
$load_focus	= '';
for($i=0;$i<=$h;$i++){
	$xuhao++;
	$moren	= 'moren';
	$day_into_line.="<tr><td style=\"min-width:80px;\"><input type=\"text\" size=\"10\" name=\"cy_date[]\"  class=\"date-picker\" value='{$now_date}' /></td><td style=\"min-width:70px;\"><input type=\"text\" size=\"10\" name=\"cy_time[]\" oninput=\"time(this);\" onblur=\"return check_time(this)\" value='{$now_time}'  /></td>
	<td style=\"min-width:80px;\">
	<input type=\"text\" name=\"site_name[]\" value='{$default_site[$i]}' $load_focus  list='site' autocomplete='off' />
	<datalist id='site'>".$fcoption."</datalist>
	</td>".$into_vid_td."</tr>";
}
$day_into_line	= $old_result_tr.$day_into_line;
disp("changbu/cb_input.html");
?>