<?php
/*
	功能：查看任意分厂数据
	作者：高龙
	时间：2016/8/28
*/
include("../temp/config.php");//包含配置文件
if($u['userid']==''){//判定用户是否登陆
	nologin();
}
//导航
$trade_global['daohang'][] = array('icon'=>'','html'=>'厂部数据查看','href'=>"./changbu/cb_see.php?begin_date={$_GET['begin_date']}&end_date={$_GET['end_date']}&water_type={$_GET['water_type']}");
$_SESSION['daohang']['cb_see']	= $trade_global['daohang'];
$trade_global['js']		= array('date-time/bootstrap-datepicker.min.js','date-time/bootstrap-timepicker.min.js','boxy.js');//包含公共的js文件
$trade_global['css']	= array('lims/main.css','datepicker.css','bootstrap-timepicker.css','lims/buttons.css','boxy.css');//包含公共的css文件
$fzx_id		= $u['fzx_id'];//获取用户ID

//接受传过来的数据
$begin_date = $_GET['begin_date'];
$end_date   = $_GET['end_date'];
$water_type = $_GET['water_type'];
$seexia     = $_GET['seexia'];
//默认数据
if(empty($_GET['begin_date'])){
	$begin_date = date("Y-m-d");
}
if(empty($_GET['end_date'])){
	$end_date = date("Y-m-d");
}
if(empty($_GET['water_type'])){
	$water_type = 88; //出厂水id
}
if(empty($_GET['seexia'])){
	$seexia = 'see'; //默认是查看表格
}

//根据水样类型查询项目
$xmid_arr = $DB->fetch_one_assoc("select `module_value1` from `n_set` where `module_value2`= '{$water_type}' and `module_name` = 'cbxm_id'");//查询厂部项目id
if(!empty($xmid_arr['module_value1'])){
	$vid_arr = $xmid_arr['module_value1'];//表格中的项目
}else{
	$vid_arr = "'94','93','95','484','6','1','104'";//默认的表格中的项目
}

//查询项目名称
$sql_value	= "SELECT * FROM `assay_value` WHERE `id` IN (".$vid_arr.")";
$query_value= $DB->query($sql_value);
while($rs_value=$DB->fetch_assoc($query_value)){
	$xm[$rs_value['id']]=$rs_value['value_C'];
	if($seexia == 'see'){
		$xm_name_td.="<th>".$rs_value['value_C']."</th>";
	}
	if($seexia == 'xia'){
		$xm_name_td.="<td>".$rs_value['value_C']."</td>";
	}
	
}
$xm_count	= count($xm);
//查询所有的水样类型
$sylx_arr = "'地下水','出厂水','水库水','工序水','地表水','水源调查'";//水样类型
$sql_lx="SELECT * FROM leixing WHERE lname in ({$sylx_arr})";
$query_lx=$DB->query($sql_lx);
$lx_options	= $leixing_moren	= $title_water_type	= '';
while($rs_lx=$DB->fetch_assoc($query_lx)){
	$selected	= '';
	if(!empty($water_type) && $water_type==$rs_lx['id']){
		$selected			= ' selected';
	}
	if($rs_lx['lname'] == '工序水'){
		switch ($water_type) {
			case '工序水(一厂)':
				$lx_options.="<option value='工序水(一厂)' selected>工序水(一厂)</option><option value='工序水(二厂)' >工序水(二厂)</option>";
				break;
			case '工序水(二厂)':
				$lx_options.="<option value='工序水(一厂)'>工序水(一厂)</option><option value='工序水(二厂)' selected>工序水(二厂)</option>";
				break;
			default:
				$lx_options.="<option value='工序水(一厂)'>工序水(一厂)</option><option value='工序水(二厂)'>工序水(二厂)</option>";
				break;
		}
	}else{
		$lx_options.="<option value=".$rs_lx['id']." $selected>".$rs_lx['lname']."</option>";
	}
}

//读取各个分厂的站点*******************************
$fcsite = $DB->query("select `site_name` from `sites` where `site_mark`='fc_site'");
while ($fcsite_arr = $DB->fetch_assoc($fcsite)) {
	$fcoption.="<option value='".$fcsite_arr['site_name']."'>".$fcsite_arr['site_name']."</option>";
}

//根据条件查询结果
$result = $DB->query("SELECT * from `changbu_data` where `cy_date` >='".$begin_date."' AND `cy_date` <='".$end_date."' and `water_type`='".$water_type."' order by cy_date asc ");
if($result){
	while ($changbu = $DB->fetch_assoc($result)) {
		$jieguo=$vd0_td=$vd0_tds='';
		$jieguo = json_decode($changbu['json_data'],true);
		foreach ($xm as $key => $value) {
			if(!empty($jieguo[$key])){
				$vd0 = $jieguo[$key];
			}else{
				$vd0 = '';
			}
			$vd0_td .= "<td><input type='text' name=\"jieguo[$key][]\" value='{$vd0}' /></td>";
			$vd0_tds .= "<td>".$vd0."</td>";
		}
		$day_into_line .= "<tr onclick=change_color(this) ><td class='date_input'><input type='text' name=\"cy_date[]\" value={$changbu['cy_date']} class=\"date-picker\" style='width:80px !important;' /><input type='hidden'  name=\"cb_id[]\" value={$changbu['id']} /></td><td><input type='text' name=\"cy_time[]\" oninput=\"time(this);\" value={$changbu['cy_time']} /></td><td><input type='text' name=\"site_name[]\" value={$changbu['site_name']} list='save' autocomplete='off' style='min-width:100px !important;' /><datalist id='save'>".$fcoption."</datalist></td>{$vd0_td}</tr>";
		$day_into_lines .= "<tr><td>{$changbu['cy_date']}</td><td>{$changbu['cy_time']}</td><td>{$changbu['site_name']}</td>{$vd0_tds}</tr>";
	}
}

if($seexia == 'see'){
	disp("changbu/cb_see");
}
if($seexia == 'xia'){
	$title_name = '分厂水质数据';
	$fc_bg = temp("changbu/cb_xia");
	header("Content-Type:   application/msexcel");        
	header("Content-Disposition:   attachment;   filename=".$title_name.".xls");        
	header("Pragma:   no-cache");        
	header("Expires:   0");
	echo "
		<html xmlns:v=\"urn:schemas-microsoft-com:vml\" xmlns:o=\"urn:schemas-microsoft-com:office:office\"
		xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\">
		<title></title>
		<head></head>
		<body>".$fc_bg."</body>
		</html>
	";
}
?>