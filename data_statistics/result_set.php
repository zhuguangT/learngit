<?php
/*
*功能：配置报告信息
*作者：hanfeng
*时间：2014-08-21
*/
include '../temp/config.php';
require_once "$rootdir/baogao/bg_func.php";
if($u['userid'] == ''){
	nologin();
}
$fzx_id		= $u['fzx_id'];
//导航
$trade_global['daohang'][]			= array('icon'=>'','html'=>'报告内容设置','href'=>"./data_statistics/result_set.php?set_id={$_GET['set_id']}");
$_SESSION['daohang']['result_set']	= $trade_global['daohang'];
$trade_global['js']					= array('date-time/bootstrap-datepicker.min.js','date-time/bootstrap-timepicker.min.js','boxy.js');
$trade_global['css']				= array('lims/main.css','datepicker.css','bootstrap-timepicker.css','lims/buttons.css','boxy.css');
#################//取出该报告的默认配置信息（ajax时也会用到）##############
$gx_set_json= $result_set_json	= array();
$chart_button	= '';
if($_GET['set_id']){
	$cg_rs	= $DB->fetch_one_assoc("SELECT * FROM `baogao_list` WHERE `id`='".$_GET['set_id']."'");
	//报告获取内容参数（站点、项目、时间等）
	if(!empty($cg_rs['result_set'])){
		$result_set_json= json_decode($cg_rs['result_set'],true);
	}
	//报告个性设置参数（模板、是否显示备注、按照什么排序等）
	if(!empty($cg_rs['gx_set'])){
		$gx_set_json	= json_decode($cg_rs['gx_set'],true);
	}
	if($cg_rs['name_str'] == 'any_search'){
		$chart_button	= "<a class=\"btn btn-xs btn-primary\" href=\"{$rooturl}/data_statistics/tjbg_cgmonth_bg.php?set_id={$_GET[set_id]}&action=see&bg=nianbao\" target='_blank' >查看成果</a>  <a class=\"btn btn-xs btn-primary\" href=\"{$rooturl}/data_statistics/tjbg_cgmonth_bg.php?set_id={$_GET[set_id]}&action=xia&bg=nianbao\" target='_blank' >下载成果</a>  <a class=\"btn btn-xs btn-primary\" href=\"{$rooturl}/data_statistics/tjbg_cgmonth_bg.php?set_id={$_GET[set_id]}&action=chart&bg=nianbao\" target=\"_blank\" >查看趋势图</a>";
	}
}
###############时间选择区域##############
$date_str	= choose_date_html($result_set_json['choose_date'],$_GET['type']);//bg_func.php 1参：报告统计周期类型，2参：日期设置的参数
###############时间选择区域结束##############
###############站点选择区域###########
$alone_sites_str	= '';
if(!empty($result_set_json['finish_sites'])){//根据已检测的批次设置的站点信息
	$sites_cid_str	= implode(",",$result_set_json['finish_sites']);
	$sql_cy_rec		= $DB->query("SELECT cr.id as cid,cr.`site_name`,cy.id as cyd_id,cy.group_name FROM `cy_rec` AS cr INNER JOIN `cy` ON cr.cyd_id=cy.id WHERE `cr`.`id` in ({$sites_cid_str}) ORDER BY cy.id");
	$tmp_cyd_id	= array();
	$i	= 0;
	while($rs_cy_rec	= $DB->fetch_assoc($sql_cy_rec)){
		if(!in_array($rs_cy_rec['group_name'], $tmp_cyd_id)){
			$i++;
			$tmp_cyd_id[]	= $rs_cy_rec['group_name'];
			$alone_sites_str	.= "<div style='clear:left;text-align:left;'><label><input name=\"alone_group_name[]\" field='result_set' type=\"checkbox\" value='{$rs_cy_rec['group_name']}' site_group='yes' bz='{$i}' checked /><span class='pc_css'>{$rs_cy_rec['group_name']}</span></label></div><div>";
		}
		$alone_sites_str	.= "<span class=\"s_float\"><label><input  name=\"finish_sites[]\" field='result_set' type=\"checkbox\" value=".$rs_cy_rec['cid']." site='yes' bz='{$i}' checked />".$rs_cy_rec['site_name']."</label></span>";
	}
}else if(!empty($result_set_json['alone_sites'])){//提前设置好的 站点信息
	$sites_grid_str	= implode(",",$result_set_json['alone_sites']);
	$sql_sites		= $DB->query("SELECT sg.id as gr_id,sg.`group_name`,s.id as sid,s.site_name FROM `site_group` AS sg INNER JOIN `sites` AS s ON sg.site_id=s.id WHERE `sg`.`id` in ({$sites_grid_str}) ORDER BY sg.`group_name`");
	$tmp_group_name	= array();
	$i	= 0;
	while($rs_sites	= $DB->fetch_assoc($sql_sites)){
		if(!in_array($rs_sites['group_name'], $tmp_group_name)){
			$i++;
			$tmp_group_name[]	= $rs_sites['group_name'];
			$alone_sites_str	.= "<div style='clear:left;text-align:left;'><label><input name=\"alone_group_name[]\" field='result_set' type=\"checkbox\" value='{$rs_sites['group_name']}' site_group='yes' bz='{$i}' checked /><span class='pc_css'>{$rs_sites['group_name']}</span></label></div><div>";
		}
		$alone_sites_str	.= "<span class=\"s_float\"><label><input  name=\"alone_sites[]\" field='result_set' type=\"checkbox\" value=".$rs_sites['gr_id']." site='yes' bz='{$i}' checked />".$rs_sites['site_name']."</label></span>";
	}
}
###############站点选择区域结束###########
###########项目排序选择
//项目排序模板下拉菜单
$option_px_mb='';
$px_vids	= array();
$n_set_sql="SELECT * FROM n_set WHERE module_name='xm_px'";
$n_set_query=$DB->query($n_set_sql);
while($n_set_rs=$DB->fetch_assoc($n_set_query)){
	if($result_set_json['xm_px_id']==$n_set_rs['id']){
		$px_vids	= explode(',',$n_set_rs['module_value1']);
		$option_px_mb.="<option value=".$n_set_rs['id']." selected=\"selected\">".$n_set_rs['module_value2']."</option>";
	}else{
		$option_px_mb.="<option value=".$n_set_rs['id'].">".$n_set_rs['module_value2']."</option>";
	}
}
###############项目选择区域####################
$alone_value_str	= '';
if(!empty($result_set_json['alone_vid'])){
	//根据设置进行项目排序
	if(!empty($px_vids)){
		$xm_arr_temp=array();
		foreach($px_vids as $key=>$value){
			if(@in_array($value,$result_set_json['alone_vid'])){
				$xm_arr_temp[$value]=$value;
				$alone_key	= array_search($value, $result_set_json['alone_vid']);
				unset($result_set_json['alone_vid'][$alone_key]);
			}
		}
		$result_set_json['alone_vid']=array_merge($xm_arr_temp,$result_set_json['alone_vid']);
	}
	$value_num	= count($result_set_json['alone_vid']);
	foreach ($result_set_json['alone_vid'] as $value_id) {
		//$alone_value_str	.= "<span class='s_float'><label><input type='checkbox' name='alone_vid[]' field='result_set' value='{$value_id}' checked />".$_SESSION['assayvalueC'][$value_id]."</label></span>";
			$alone_value_str	.= "<div class='col-xs-1'>
					<label>
						<input type='checkbox' name='alone_vid[]' field='result_set' value='{$value_id}' checked>
						<span class='value_C' title='{$_SESSION['assayvalueC'][$value_id]}'>{$_SESSION['assayvalueC'][$value_id]}</span>
					</label>
				</div>";
	}
	if($value_num>0){
		$value_num	= "(共{$value_num}项)";
	}else{
		$value_num	= '';
	}
}
###################项目选择区域 结束###############
##################成果表基础信息配置区域###############
###获取报告配置信息中的成果表基础信息###
if(!empty($result_set_json)){
	$col_max=$result_set_json['col_max'];
	$row_max=$result_set_json['row_max'];
	$bt_cs_arr=$result_set_json['cgb_bt_cs'];
	if($result_set_json['cgb_mb']==1){
		$checked1='checked="checked"';
		$checked2="";
	}else{
		$checked2='checked="checked"';
	}
	$jc_bm		= $result_set_json['jc_bm'];
	$tb_date	= $result_set_json['tb_date'];
	$sz_pj		= $result_set_json['sz_pj'];
	$bz			= $result_set_json['bz'];
	$info_jl_bh	= $result_set_json['jl_bh'];
	$cgb_title	= $result_set_json['cgb_title'];
	$day1		= $result_set_json['day1'];
	$day2		= $result_set_json['day2'];
	$month_type	= $result_set_json['month_type'];
}
if(empty($tb_date)||$ta_date=='0000-00-00'){
	$tb_date=date('Y-m-d');
}
if(empty($col_max)){
	$col_max="14";
}
if(empty($row_max)){
	$row_max="16";
}
###获取报告配置信息中的成果表基础信息###
$array_canshu	= array(
	'jichu'	=> array(
		"title"		=> "<td align='left' >标题名称：<textarea type='text' name='cgb_title' id='cgb_title' field='result_set' style='width:60%;height:50px'>{$cgb_title}</textarea></td>",
		"bumen"		=> "<td align='left'>监测部门：<textarea type='text' name='jc_bm' id='jc_bm' field='result_set' style='width:60%;height:60px'>{$jc_bm}</textarea></td>",
		"tb_date"	=> "<td align='left'>填表日期：<input type='text' size='10' name='tb_date' id='tb_date' class='date-picker' field='result_set' value='{$tb_date}'/></td>",
		"pingjia"	=> "<td align='left'>水质评价：<textarea type='text' name='sz_pj' id='sz_pj' field='result_set' style='width:60%;height:50px'>{$sz_pj}</textarea>",
		"beizhu"	=> "<td align='left'>备注：<textarea type='text'  name='bz' id='bz' field='result_set' style='width:60%;height:50px'>{$bz}</textarea></td>",
		"jilu_bh"	=> "<td align='left'>记录编号：<select name='jl_bh' id='jl_bh' field='result_set'>{$jl_bh_option}</select></td>"
		),
	'canshu'	=> array(
		"xm_zongheng"	=> "<td align='left' ><span style='text-align:left' class='s_float'><label><input type='radio' name='cgb_mb' value='1' field='result_set' onclick=\"change_mb(this)\" $checked1/>项目横向展示</label></span><span class='s_float' style='text-align:left'><label><input type='radio' name='cgb_mb' field='result_set' value='2'  onclick=\"change_mb(this)\" $checked2/>项目竖列展示</label></span></td>",
		"bg_zongheng"	=> "<td align='left' ><span style='text-align:left' class='s_float'><input type='radio' name='bg_hz' field='result_set' value='1' $checked3 />表格竖版</span><span class='s_float' style='text-align:left'><input type='radio' name='bg_hz' field='result_set' value='2' $checked4 />表格横版</span></td>",
		"tr_td_num"		=> "<td align='left' >每页站点数：<input type='text' name='col_max' field='result_set' value='{$col_max}' style='width:45px' id='col_max' onblur=\"save_hl(this)\" onafterpaste=\"this.value=this.value.replace(/\D/g,'')\" onkeyup=\"this.value=this.value.replace(/\D/g,'')\">&nbsp;&nbsp;每页项目数：<input type='text' name='row_max' field='result_set' value='{$row_max}' style='width:45px' id='row_max' onblur=\"save_hl(this)\" onafterpaste=\"this.value=this.value.replace(/\D/g,'')\" onkeyup=\"this.value=this.value.replace(/\D/g,'')\"></td>",
		"bg_canshu"		=> "<td align='left' >$cgb_bt_cs</td>"
	)
);
if($gx_set_json['canshu']){
	$any_sites_result_body	= "<table style='margin-top:20px' class='table table-striped table-bordered table-hover center canshu_set'>";
	$i	= 0;
	foreach ($gx_set_json['canshu'] as $key => $value_arr) {
		if($key=='jichu'){
			$any_sites_result_body	.= "<tr><th colspan='2' style='font-size:16px;' align='center'>成果表基础信息</th></tr>";
		}else if($key=='canshu'){
			$any_sites_result_body	.= "<tr><th colspan='2' style='font-size:16px;' align='center'>成果表参数设置</th></tr>";
		}
		foreach($value_arr as $value){
			if(!empty($array_canshu[$key][$value])){
				$i++;
				//按照2列分组
				if($i=='1'){
					$any_sites_result_body	.= "<tr>";
				}
				$any_sites_result_body	.= $array_canshu[$key][$value];
				if($i=='2'){
					$any_sites_result_body	.= "</tr>";
					$i=0;
				}
			}
		}
	}
	//参数个数为奇数时，需要增加一列空td
	if($i=='1'){
		$any_sites_result_body	.= "<td></td</tr>";
	}
	$any_sites_result_body	.= "</table>";
}
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
//print_rr($result_set_json);
//成果表表头参数
$bt_cs_arr=array();
$cgb_bt_cs_rs=$DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE module_name='cgb_bt_cs' AND fzx_id='".$fzx_id."'");
if(!empty($cgb_bt_cs_rs)){
	$bt_cs_arr=explode(',',$cgb_bt_cs_rs['module_value1']);
}
$jc_bm='供水水质监测中心';
$cgb_title='成果统计表';//成果统计表

$sz_pj='出厂水和管网水所检项目均符合《生活饮用水卫生标准》GB5749-2006的要求';
$bz='*表示不符合标准要求。建议集团公司主管部门与市环保部门积极联系，做好水源的保护。';
$cgb_bt_cs='';
if(!empty($global['cgb_bt_cs'])){
	foreach($global['cgb_bt_cs'] as $key=>$value){
		if($value=='site_name'){
			$cgb_bt_cs.="<label><input type='checkbox' name='cgb_bt_cs[]' field='result_set' onclick='return false' checked='checked' value={$key}>{$key}</label>&nbsp;";
		}else{
			if(@in_array($key,$bt_cs_arr)){
				$cgb_bt_cs.="<label><input type='checkbox' checked='checked' field='result_set' onclick='change_bt_cs(this)' name='cgb_bt_cs[]' value={$key}>{$key}</label>&nbsp;";
			}else{
				$cgb_bt_cs.="<label><input type='checkbox' onclick='change_bt_cs(this)' field='result_set' name='cgb_bt_cs[]' value={$key}>{$key}</label>&nbsp;";
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
$gx_set_json['set_mb_name']	= 'result_set';
if(!empty($gx_set_json['set_mb_name'])){
	disp("any_data/{$gx_set_json['set_mb_name']}");
}else{
	disp("any_data/any_sites_result");
}
