<?php
/*
*功能：显示任意成果查询页面
*作者：hanfeng
*时间：2014-08-21
*/
include '../temp/config.php';
require_once "$rootdir/inc/site_func.php";

if($u['userid'] == ''){
	nologin();
}
//导航
$trade_global['daohang'][]	= array('icon'=>'','html'=>'报告内容设置','href'=>"./data_statistics/any_sites_result.php?set_id={$_GET['set_id']}");
$_SESSION['daohang']['any_sites_result']	= $trade_global['daohang'];
$trade_global['js']		= array('date-time/bootstrap-datepicker.min.js','date-time/bootstrap-timepicker.min.js','boxy.js');
$trade_global['css']	= array('lims/main.css','datepicker.css','bootstrap-timepicker.css','lims/buttons.css','boxy.css');
$fzx_id		= $u['fzx_id'];
#################//取出该报告的默认配置信息（ajax时也会用到）##############
$gx_set_json= array();
$checked4	= 'checked="checked"';
if($_GET['set_id']){
	$info	= array();
	$cg_rs	= $DB->fetch_one_assoc("SELECT * FROM `baogao_list` WHERE `id`='".$_GET['set_id']."'");
	if(!empty($cg_rs['result_set'])){
		$info	= json_decode($cg_rs['result_set'],true);
	}
	if(!empty($cg_rs['gx_set'])){
		$gx_set_json= json_decode($cg_rs['gx_set'],true);
	}
	if($info['bg_hz']=='1'){
		$checked3	= 'checked="checked"';
		$checked4	= '';
	}
}
###############站点选择区域###########
$sql_where	= '';
$group_names= array();
if(empty($info['alone_group_name'])){
	$info['alone_group_name']	= array();
}
if(empty($info['merger_group_name'])){
	$info['merger_group_name']	= array();
}
$group_names= array_merge($info['alone_group_name'],$info['merger_group_name']);
if(!empty($group_names)){
	$sql_where	= " AND `group_name` in ('".implode("','",$group_names)."') ";
}
$group_sql	= "SELECT  sg.group_name,s.site_name,s.id FROM site_group sg LEFT JOIN sites s ON s.id = sg.site_id WHERE 1 $sql_where ORDER BY sg.sort asc,sg.group_name,sg.site_sort,sg.ctime DESC";
$group_query=$DB->query($group_sql);
$alone_sites_arr	= $merger_sites_arr	= $group_bz	= array();
$i	= 0;
while($group_rs=$DB->fetch_assoc($group_query)){
	//单独统计合格率的站点
	if(@in_array($group_rs['id'], $info['alone_sites'][$group_rs['group_name']])){
		if(empty($alone_sites_arr[$group_rs['group_name']])){
			//批次名称
			$group_bz[$group_rs['group_name']]	= $i++;
			$alone_sites_arr[$group_rs['group_name']]	.= "<div style='clear:left;text-align:left;'><label><input name=\"alone_group_name[]\" type=\"checkbox\" value='{$group_rs['group_name']}' site_group='yes' bz='{$group_bz[$group_rs['group_name']]}' checked /><span class='pc_css'>{$group_rs['group_name']}</span></label></div><div>";
		}
		//站点名称
		$alone_sites_arr[$group_rs['group_name']]	.= "<span class=\"s_float\"><label><input  name=\"alone_sites[{$group_rs['group_name']}][]\" type=\"checkbox\" value=".$group_rs['id']." site='yes' bz='{$group_bz[$group_rs['group_name']]}' checked />".$group_rs['site_name']."</label></span>";
	}
	//综合统计合格率的站点
	if(@in_array($group_rs['id'], $info['merger_sites'][$group_rs['group_name']])){
		if(empty($merger_sites_arr[$group_rs['group_name']])){
			//批次名称
			$merger_sites_arr[$group_rs['group_name']]	.= "<div style='clear:left;text-align:left;'><label><input name=\"merger_group_name[]\" type=\"checkbox\" value='{$group_rs['group_name']}' site_group='yes' bz='{$group_bz[$group_rs['group_name']]}' checked /><span class='pc_css'>{$group_rs['group_name']}</span></label></div><div>";
		}
		//站点名称
		$merger_sites_arr[$group_rs['group_name']]	.= "<span class=\"s_float\"><label><input  name=\"merger_sites[{$group_rs['group_name']}][]\" type=\"checkbox\" value=".$group_rs['id']." site='yes' bz='{$group_bz[$group_rs['group_name']]}' checked />".$group_rs['site_name']."</label></span>";
	}
}
$alone_sites_str	= $merger_sites_str = '';
if(!empty($alone_sites_arr)){
	$alone_sites_str	= implode('</div>', $alone_sites_arr)."</div>";
}
if(!empty($merger_sites_arr)){
	$merger_sites_str	= implode('</div>', $merger_sites_arr)."</div>";
}
###############项目选择区域####################
$alone_value_str	= '';
if(!empty($info['alone_vid'])){
	foreach ($info['alone_vid'] as $value_id) {
		$alone_value_str	.= "<span class='s_float'><label><input type='checkbox' name='alone_vid[]' value='{$value_id}' checked />".$_SESSION['assayvalueC'][$value_id]."</label></span>";
	}
}
$merger_value_str	= '';
if(!empty($info['merger_vid'])){
	foreach ($info['merger_vid'] as $value_id) {
		$merger_value_str	.= "<span class='s_float'><label><input type='checkbox' name='merger_vid[]' value='{$value_id}' checked />".$_SESSION['assayvalueC'][$value_id]."</label></span>";
	}
}
###################项目选择区域 结束###############
##################成果表基础信息配置区域###############
//成果表模板默认选项
$cgb_mb_rs=$DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE module_name='cgb_mb' AND module_value3='1'");
//选中表格类型
if($cgb_mb_rs['module_value1']==1){
	$checked1='checked="checked"';
}else{
	$checked2='checked="checked"';
}

if(!empty($cgb_mb_rs['module_value2'])){
	$col_row_arr=json_decode($cgb_mb_rs['module_value2'],true);
	$col_max=$col_row_arr['col_max'];
	$row_max=$col_row_arr['row_max'];
}
//print_rr($info);
//成果表表头参数
$bt_cs_arr=array();
$cgb_bt_cs_rs=$DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE module_name='cgb_bt_cs' AND fzx_id='".$fzx_id."'");
if(!empty($cgb_bt_cs_rs)){
	$bt_cs_arr=explode(',',$cgb_bt_cs_rs['module_value1']);
}
$jc_bm='供水水质监测中心';
$cgb_title='成果统计表';//成果统计表

$sz_pj='海润集团公司的出厂水和管网水所检项目均符合《生活饮用水卫生标准》GB5749-2006的要求';
$bz='*表示不符合标准要求。建议集团公司主管部门与市环保部门积极联系，做好水源的保护。';
if(!empty($info)){
	$col_max=$info['col_max'];
	$row_max=$info['row_max'];
	$bt_cs_arr=$info['cgb_bt_cs'];
	if($info['cgb_mb']==1){
		$checked1='checked="checked"';
		$checked2="";
	}else{
		$checked2='checked="checked"';
	}
	$jc_bm=$info['jc_bm'];
	$tb_date=$info['tb_date'];
	$sz_pj=$info['sz_pj'];
	$bz=$info['bz'];
	$info_jl_bh=$info['jl_bh'];
	$cgb_title=$info['cgb_title'];
	$day1=$info['day1'];
	$day2=$info['day2'];
	$month_type=$info['month_type'];
}
if(empty($tb_date)||$ta_date=='0000-00-00'){
	$tb_date=date('Y-m-d');
}
if(empty($col_max)){
	$col_max="6";
}
if(empty($row_max)){
	$row_max="31";
}

$cgb_bt_cs='';
if(!empty($global['cgb_bt_cs'])){
	foreach($global['cgb_bt_cs'] as $key=>$value){
		if($value=='site_name'){
			$cgb_bt_cs.="<label><input type='checkbox' name='cgb_bt_cs[]' onclick='return false' checked='checked' value={$key}>{$key}</label>&nbsp;";
		}else{
			if(@in_array($key,$bt_cs_arr)){
				$cgb_bt_cs.="<label><input type='checkbox' checked='checked' onclick='change_bt_cs(this)' name='cgb_bt_cs[]' value={$key}>{$key}</label>&nbsp;";
			}else{
				$cgb_bt_cs.="<label><input type='checkbox' onclick='change_bt_cs(this)' name='cgb_bt_cs[]' value={$key}>{$key}</label>&nbsp;";
			}
		}
	}
}
//记录编号
$jl_bh_option='';
for($bh=1;$bh<=30;$bh++){
	$jl_bh_str='QDSZJC-RR-00'.$bh;
	if($info_jl_bh==$jl_bh_str){
		$jl_bh_option.="<option value=".$jl_bh_str." selected=\"selected\">".$jl_bh_str."</option>";
	}else{
		$jl_bh_option.="<option value=".$jl_bh_str.">".$jl_bh_str."</option>";
	}
}
$any_sites_result_body="<table class='table table-striped table-bordered table-hover center'>
<tr><th colspan='3' style='font-size:16px;'>成果表基础信息</th></tr>
<tr >
<td align='left' >标题名称：<textarea type='text' name='cgb_title' id='cgb_title' style='width:60%;height:50px'>{$cgb_title}</textarea></td><td align='left'>监测部门：<textarea type='text' name='jc_bm' id='jc_bm' style='width:60%;height:60px'>{$jc_bm}</textarea></td>
<td >填表日期：<input type='text' size='10' name='tb_date' id='tb_date' class='date-picker' value='{$tb_date}'/></td></tr></table>";//temp("any_data/any_sites_result_body");
###############时间选择区域##############
$begin_date	= date('Y-m'.'-01');//开始时间
$end_date	= date('Y-m-d');//终止时间
//天数
$day1_options=$day2_options=$m_options='';
for($t=1;$t<=31;$t++){
	if($t<10){
		$t='0'.$t;
	}
	if($day1==$t){
		$day1_options.='<option value='.$t.' selected="selected">'.$t.'</option>';
	}else{
		$day1_options.='<option value='.$t.'>'.$t.'</option>';
	}
	if($day2==$t){
		$day2_options.='<option value='.$t.' selected="selected">'.$t.'</option>';
	}else{
		$day2_options.='<option value='.$t.'>'.$t.'</option>';
	}
}
$month_type_arr=array("本月","上月");
foreach($month_type_arr as $key=>$value){
	if($value==$month_type){
		$m_options.='<option value='.$value.' selected="selected">'.$value.'</option>';
	}else{
		$m_options.='<option value='.$value.'>'.$value.'</option>';
	}
}
$target		= '';
$date_str	= '起始日期:<select name="month_type" id="month_type" onchange="date_check()">'.$m_options.'</select><select name="day1" onchange="date_check()" id="day1">'.$day1_options.'</select><span style="letter-spacing:20px">&nbsp;</span>终止日期:<select ><option value="本月">本月</option></select><select name="day2" onchange="date_check()" id="day2">'.$day2_options.'</select>';
$submit_str	= "<center><input class=\"btn btn-xs btn-primary\" type=\"submit\" name=\"sub\" value=\"保存\" />";
###############时间选择区域结束##############
if(!empty($gx_set_json['set_mb_name'])){
	disp("any_data/{$gx_set_json['set_mb_name']}");
}else{
	disp("any_data/any_sites_result");
}
