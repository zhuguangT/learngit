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
$trade_global['daohang'][]	= array('icon'=>'','html'=>'任意成果查询','href'=>"./data_statistics/any_sites_result.php?set_id={$_GET['set_id']}");
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
	if(empty($gx_set_json['result_mb_name'])){//管网合格率报表的模板
		if($cg_rs['name_str']=='month_bg' && stristr($cg_rs['baogao_name'],'管网合格率')){
			$gx_set_json['set_mb_name']	= "month_bg_gw_hgl";
			$gx_set_json['result_mb_name']  = "month_bg_gw_hgl";
			$gx_set_json_str	= JSON($gx_set_json);
			$DB->query("UPDATE `baogao_list` SET `gx_set`='{$gx_set_json_str}' WHERE `id`='{$cg_rs['id']}'");
		}
	}
	if($info['bg_hz']=='1'){
		$checked3	= 'checked="checked"';
		$checked4	= '';
	}
}
############ajax修改区域开始##################
//ajax调用n_set定义的默认成果表使用的行数和列数，并返回到页面显示到对应输入框中(项目横表和竖表的行数和列数)
if($_GET['mb_value']){
	if($_GET['set_id']){
		//获取用户自己填写的 行数和列数 并返回页面显示到对应输入框中
		if($info['cgb_mb']==$_GET['mb_value']){
			$json_arr['col_max']=$info['col_max'];
			$json_arr['row_max']=$info['row_max'];
			$json=JSON($json_arr);
			echo $json;exit();
		}
	}
	//获取系统自动默认的 行数和列数，并返回页面显示到对应输入框中
	$n_set_rs=$DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='cgb_mb' AND `fzx_id`='".$fzx_id."' AND `module_value1`='".$_GET['mb_value']."'");
	if(!empty($n_set_rs)){
		$json_arr=json_decode($n_set_rs['module_value2'],true);
		if(empty($json_arr['col_max'])){
			$json_arr['col_max']=6;
		}
		if(empty($json_arr['row_max'])){
			$json_arr['row_max']=31;
		}
		echo JSON($json_arr);exit();
	}else{
		//如果数据库没有默认数据，则新插入一条
		$json_arr['col_max']=6;
		$json_arr['row_max']=31;
		$json=JSON($json_arr);
		$DB->query("INSERT INTO `n_set` (fzx_id,module_name,module_value1,module_value2) values('".$fzx_id."','cgb_mb','".$_GET['mb_value']."','".$json."'");
		echo $json;exit();
	}
}
//ajax修改n_set表定义的成果表的模板的行列
if($_GET['cgb_mb']){
	$json_arr	= array();
	$json_arr['col_max']= $_GET['col_max'];
	$json_arr['row_max']= $_GET['row_max'];
	$json	= JSON($json_arr);
	$query	= $DB->query("UPDATE `n_set` SET `module_value2`='".$json."' WHERE `fzx_id`='".$fzx_id."' AND `module_name`='cgb_mb' AND `module_value1`='".$_GET['cgb_mb']."'");
	if($DB->affected_rows()){
		echo 1;
	}else{
		echo 0;
	}
	exit();
}
//ajax修改n_set定义的默认成果表使用的表头参数(在设置页面点击表头参数时触发change_bt_cs)
if($_POST['bt_cs_arr']){
	$n_set_rs=$DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE module_name='cgb_bt_cs' AND fzx_id='".$fzx_id."'");
	if(!empty($n_set_rs)){
		$bt_cs_str=implode(',',$_POST['bt_cs_arr']);
		$DB->query("UPDATE `n_set` SET module_value1='".$bt_cs_str."' WHERE module_name='cgb_bt_cs' AND fzx_id='".$fzx_id."'");
	}else{
		$bt_cs_str=implode(',',$_POST['bt_cs_arr']);
		$DB->query("INSERT INTO `n_set` (fzx_id,module_name,module_value1) values('".$fzx_id."','cgb_bt_cs','".$bt_cs_str."')");
	}
	echo '1';
	exit();
}
#############ajax修改区域结束#############
#############下拉菜单筛选区域##############
//当前任务类型
if(!isset($_GET['site_type'])){
	if(isset($info['site_type'])){
		$site_type =$info['site_type'];
	}else{
		$site_type ='1';
	}
}else{
	$site_type=$_GET['site_type'];
}
//获得任务类型的下拉菜单
foreach($global['site_type'] as $key=>$value){
	if($key==$site_type){
		$site_type_str.="<option  selected='selected' value=".$key.">".$value."</opion>";
	}else{
		$site_type_str.="<option value=".$key.">".$value."</opion>";
	}
}

//当前水样类型
if(!isset($_GET['water_type'])){
	if(isset($info['water_type'])){
		$water_type = $info['water_type'];
	}else{
		$water_type = '全部';
	}
}else{
	$water_type=$_GET['water_type'];
}
//获得本中心的水样类型
$sql_leixing = $DB->query("SELECT id,lname FROM `leixing` WHERE parent_id='0' AND act='1'");
while($lx = $DB->fetch_assoc($sql_leixing))
{
	if($lx['id']==$water_type){
		$lxlist .= "<option selected=\"selected\"  value=\"$lx[id]\">$lx[lname]</option><optgroup style=\"padding-left:21px\">";
	}else{
		$lxlist.="<option value='$lx[id]'>$lx[lname]</option><optgroup style=\"padding-left:21px\">";
	}
	$sql_xleixing = $DB->query("SELECT id as xid,lname,parent_id FROM `leixing` WHERE  parent_id='{$lx['id']}' AND act='1'");
	while($xlx = $DB->fetch_assoc($sql_xleixing)){
		if($xlx['xid']==$water_type){
			$lxlist.="<option value='$xlx[xid]' selected=\"selected\">$xlx[lname]</option>";
		}else{
			$lxlist.="<option value='$xlx[xid]'>$xlx[lname]</option>";
		}
	}
	$lxlist.="</optgroup>";
}
//当前统计参数
if(empty($_GET['tjcs'])){
	$tjcs='';
}else{
	$tjcs=$_GET['tjcs'];
}
//获得统计参数下拉菜单
$tjcs_sql	= "SELECT DISTINCT `module_value1`,`id` FROM `n_set` WHERE (fzx_id='1' or fzx_id='".$u['fzx_id']."') AND module_name='tjcs'";
$tjcs_query	= $DB->query($tjcs_sql);
while($tjcs_rs=$DB->fetch_assoc($tjcs_query)){
	$tjcs_arr[$tjcs_rs['id']]=$tjcs_rs['module_value1'];
}
if(!empty($tjcs_arr)){
	foreach($tjcs_arr as $key=>$value){
		if($tjcs==$key){
			$tjcs_str.="<option  selected='selected' value=".$key.">".$value."</option>";
		}else{
			$tjcs_str.="<option value=".$key.">".$value."</option>";
		}
	}
}
##################################
###############站点选择区域###########
//获取某任务类型和某水样类型下的所有批次
$group_name_arr=get_group_names($site_type,$water_type,1);
//当前批次名称
if(!isset($_GET['group_name'])){
    $group_name = '';
}else{
	$group_name=$_GET['group_name'];
}
//获得批次的下拉菜单
if(!empty($group_name_arr)){
	foreach($group_name_arr as $key=>$value){
		if( $group_name===$value){
			$group_name_str.="<option  selected='selected' value=".$value.">".$value."</option>";
		}else{
			$group_name_str.="<option value=".$value.">".$value."</option>";
		}
	}
}
//根据任务类型和水样类别取出所有的站点
if(!empty($water_type)&&$water_type!='全部'){
	$sql_water_type=" AND s.water_type='{$water_type}'";
}
if(!empty($group_name)){
	$sql_group_name="AND sg.group_name='{$group_name}'";
}
if(!empty($tjcs)){
	$sql_tjcs="AND (s.tjcs='{$tjcs}' or s.tjcs like '%{$tjcs},%' or s.tjcs like '%,{$tjcs}%') ";
}
if($site_type == '1'){
	$sql_site_type	= "AND s.site_type in('0','".$site_type."') ";
}else{
	$sql_site_type	= "AND s.site_type='".$site_type."' ";
}
$group_sql = "SELECT  sg.id as sid,sg.group_name,s.site_name,s.id FROM site_group sg LEFT JOIN sites s ON s.id = sg.site_id WHERE (s.`fzx_id`='{$fzx_id}' || s.`fp_id`='$fzx_id') {$sql_site_type}
   {$sql_water_type} {$sql_group_name} AND sg.`group_name`!='' $sql_tjcs ORDER BY sg.`group_name`";
$group_query=$DB->query($group_sql);
while($group_rs=$DB->fetch_assoc($group_query)){
	$group_data[$group_rs['group_name']][$group_rs['sid']]=$group_rs['site_name'];	
}
$group_site_str	= $checked_sites_str	= '';
$line_nums		= '8';//每行显示的个数
//遍历每个批次下的站点
$j=1;
if(!empty($group_data)){
	foreach($group_data as $key=>$value){
		$i=1;
		$g_checked='';
		if(@in_array($key,$info['group_name'])){
			$g_checked='checked="checked"';
			$checked_sites_str	.= "<tr><td colspan=".$line_nums."><label><input name=\"group_name[]\" type=\"checkbox\" value='".$key."' id='".$j."' onclick=\"check_sites(this)\" $g_checked/><span class='pc_css'>".$key."</span></label></td></tr>";
		}
		$group_site_str.="<tr><td colspan=".$line_nums."><label><input name=\"group_name[]\" type=\"checkbox\" value='".$key."' id='".$j."' onclick=\"check_sites(this)\" $g_checked/><span class='pc_css'>".$key."</span></label></td></tr>";
		$count=count($value);
		foreach($value as $k=>$v){
			if($i==1){
				$group_site_str		.= "<tr>";
				$checked_sites_str	.= "<tr>";
			}
			$s_checked='';
			if(@in_array($k,$info['sites'][$key])){
				$s_checked='checked="checked"';
			}
			if($count%$line_nums&&$i==$count){
				$add_tds=$line_nums-$count%$line_nums+1;
				$group_site_str.="<td colspan=".$add_tds."><span class=\"s_float\"><label><input name=\"alone_sites[]\" type=\"checkbox\" value=".$k." class=".$j." onclick=\"check_group(this)\" $s_checked />".$v."</label></span></td>";
				if($s_checked=='checked="checked"'){
					$checked_sites_str	.= "<td colspan=".$add_tds."><span class=\"s_float\"><label><input name=\"alone_sites[]\" type=\"checkbox\" value=".$k." class=".$j." onclick=\"check_group(this)\" $s_checked />".$v."</label></span></td>";
				}
			}else{
				$group_site_str.="<td><span class=\"s_float\"><label><input  name=\"alone_sites[]\" type=\"checkbox\" value=".$k." class=".$j." onclick=\"check_group(this)\" $s_checked/>".$v."</label></span></td>";
				if($s_checked=='checked="checked"'){
					$checked_sites_str	.= "<td><span class=\"s_float\"><label><input  name=\"alone_sites[]\" type=\"checkbox\" value=".$k." class=".$j." onclick=\"check_group(this)\" $s_checked/>".$v."</label></span></td>";
				}
			}
			if($i%$line_nums==0||$i==$count){
				$group_site_str.="</tr>";
				$checked_sites_str	.= "</tr>";
			}
			if($i%$line_nums==0&&$count>$i){
				$group_site_str.="<tr>";
				$checked_sites_str	.= "</tr>";
			}
			$i++;
		}
		$j++;
	}
}
###############项目选择区域####################
//项目模板下拉菜单
$xmmb_options	= '';
$sql_xmmb		= $DB->query("SELECT * FROM `n_set` WHERE  module_name='xmmb'");
while($rs_xmmb	= $DB->fetch_assoc($sql_xmmb)){
	if($rs_xmmb['module_value1']==$info['xmmb']){
		$xmmb_options	.= "<option value=".$rs_xmmb['module_value1']." selected=\"selected\">".$rs_xmmb['module_value2']."</option>";
	}else{
		$xmmb_options	.= "<option value=".$rs_xmmb['module_value1'].">".$rs_xmmb['module_value2']."</option>";
	}
}

//项目排序模板下拉菜单
$option_px_mb='';
$n_set_sql="SELECT * FROM n_set WHERE module_name='xm_px'";
$n_set_query=$DB->query($n_set_sql);
while($n_set_rs=$DB->fetch_assoc($n_set_query)){
	if($info['xm_px_id']==$n_set_rs['id']){
		$option_px_mb.="<option value=".$n_set_rs['id']." selected=\"selected\">".$n_set_rs['module_value2']."</option>";
	}else{
		$option_px_mb.="<option value=".$n_set_rs['id'].">".$n_set_rs['module_value2']."</option>";
	}
}
//项目获取
$all_assay_value = array();
$xm_sql="SELECT av.* FROM `assay_value` av JOIN `xmfa` x ON x.xmid=av.id WHERE x.act='1' AND x.fzx_id='".$fzx_id."'  GROUP BY av.id ORDER BY av.id";
$xm_query=$DB->query($xm_sql);
$all_assay_values= $checked_values_arr	= array();
while($xm_rs=$DB->fetch_assoc($xm_query)){
	$s_checked='';
	if(@in_array($xm_rs['id'],$info['vid'])){
		$s_checked='checked="checked"';
		$checked_values_arr	= array_merge($checked_values_arr,convert_assay_value(array($xm_rs['id']),$s_checked));
	}
	$all_assay_values=array_merge($all_assay_values,convert_assay_value(array($xm_rs['id']),$s_checked));
}


$k=1;
if(!empty($all_assay_values)){
	$count=count($all_assay_values);
	foreach($all_assay_values as $key=>$value){
			if($k==1){
				$vid_str.="<tr>";
			}
			if($count%$line_nums&&$k==$count){
				$add_tds=$line_nums-$count%$line_nums+1;
				$vid_str.="<td align='left' colspan=".$add_tds."><span class='s_float'><label>".$value."</label></span></td>";
			}else{
				$vid_str.="<td ><span class='s_float'><label>".$value."</label></span></td>";
			}
			if($k%$line_nums==0||$k==$count){
				$vid_str.="</tr>";
			}
			if($k%$line_nums==0&&$count>$k){
				$vid_str.="<tr>";
			}
			$k++;
	}
}
$checked_vid_str	= "<tr><td>".implode('</td><td>',$checked_values_arr)."</td></tr>";
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

$sz_pj='';
$bz='';
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
$jl_bh_option='<option value="" >不显示</option>';
for($bh=1;$bh<=30;$bh++){
	$jl_bh_str='QDSZJC-RR-00'.$bh;
	if($info_jl_bh==$jl_bh_str){
		$jl_bh_option.="<option value=".$jl_bh_str." selected=\"selected\">".$jl_bh_str."</option>";
	}else{
		$jl_bh_option.="<option value=".$jl_bh_str.">".$jl_bh_str."</option>";
	}
}	
###############时间选择区域##############
if(!empty($info['begin_date'])){
	$begin_date	= $info['begin_date'];
}else{
	$begin_date	= date('Y-m'.'-01');//开始时间
}
if(!empty($info['end_date'])){
	$end_date	= $info['end_date'];
}else{
	$end_date	= date('Y-m-d');//终止时间
}


//天数
$day1_options=$day2_options=$m_options='';
for($t=1;$t<=31;$t++){
	if($t<10){
		$t='0'.$t;
	}
	if(empty($day2)){
		$day2	= '31';
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
$any_sites_result_body=temp("any_data/any_sites_result_body");
//统计时间范围的显示
//if($u['admin']){
	$target	= "target='_blank'";
	$qushitu= "&nbsp;&nbsp;<input class=\"btn btn-xs btn-primary\" type=\"submit\" name=\"sub\" value=\"查看趋势图\" />";
//}

$target		= 'target="_blank"';
$date_str	= "起始日期:<input type=\"text\" size=\"10\" name=\"begin_date\" id=\"begin_date\" class=\"date-picker\" value=".$begin_date." />
终止日期:<input type=\"text\" size=\"10\" id=\"end_date\"  class=\"date-picker\"  name=\"end_date\"  value=".$end_date." />";
$submit_str	= "<center><input class=\"btn btn-xs btn-primary\" type=\"submit\" name=\"sub\" value=\"查看成果\" />&nbsp;&nbsp;<input class=\"btn btn-xs btn-primary\" type=\"submit\" name=\"sub\" value=\"下载成果\" />$qushitu</center>";

if($cg_rs['name_str']=='month_bg'){
	$target		= '';
	$date_str	= '起始日期:<select name="month_type" id="month_type" onchange="date_check()">'.$m_options.'</select><select name="day1" onchange="date_check()" id="day1">'.$day1_options.'</select><span style="letter-spacing:20px">&nbsp;</span>终止日期:<select ><option value="本月">本月</option></select><select name="day2" onchange="date_check()" id="day2">'.$day2_options.'</select>';
	$submit_str	= "<center><input class=\"btn btn-xs btn-primary\" type=\"submit\" name=\"sub\" value=\"保存\" />";
}
if($cg_rs['name_str']=='tjbg_year'){
	$target		= '';
	//$date_str	= '起始日期:<select name="month_type" id="month_type" onchange="date_check()">'.$m_options.'</select><select name="day1" onchange="date_check()" id="day1">'.$day1_options.'</select><span style="letter-spacing:20px">&nbsp;</span>终止日期:<select ><option value="本月">本月</option></select><select name="day2" onchange="date_check()" id="day2">'.$day2_options.'</select>';
	//$date_str	= '<input type="hidden" name="month_type" value="本年" />';
	$submit_str	= "<center><input class=\"btn btn-xs btn-primary\" type=\"submit\" name=\"sub\" value=\"保存\" />";
}

if($cg_rs['name_str']=='important_site'){
	$target		= '';
	//$date_str	= '起始日期:<select name="month_type" id="month_type" onchange="date_check()">'.$m_options.'</select><select name="day1" onchange="date_check()" id="day1">'.$day1_options.'</select><span style="letter-spacing:20px">&nbsp;</span>终止日期:<select ><option value="本月">本月</option></select><select name="day2" onchange="date_check()" id="day2">'.$day2_options.'</select>';
	//$date_str	= '<input type="hidden" name="month_type" value="本年" />';
	$submit_str	= "<center><input class=\"btn btn-xs btn-primary\" type=\"submit\" name=\"sub\" value=\"保存\" />";
}
###############时间选择区域结束##############
$array_canshu	= array(
	'jichu'	=> array(
		"title"		=> "<td align='left' >标题名称：<textarea type='text' name='cgb_title' id='cgb_title' style='width:60%;height:50px'>{$cgb_title}</textarea></td>",
		"bumen"		=> "<td align='left'>监测部门：<textarea type='text' name='jc_bm' id='jc_bm' style='width:60%;height:60px'>{$jc_bm}</textarea></td>",
		"tb_date"	=> "<td align='left'>填表日期：<input type='text' size='10' name='tb_date' id='tb_date' class='date-picker' value='{$tb_date}'/></td>",
		"pingjia"	=> "<td align='left'>水质评价：<textarea type='text' name='sz_pj' id='sz_pj' style='width:60%;height:50px'>{$sz_pj}</textarea>",
		"beizhu"	=> "<td align='left'>备注：<textarea type='text'  name='bz' id='bz' style='width:60%;height:50px'>{$bz}</textarea></td>",
		"jilu_bh"	=> "<td align='left'>记录编号：<select name='jl_bh' id='jl_bh'>{$jl_bh_option}</select></td>"
		),
	'canshu'	=> array(
		"xm_zongheng"	=> "<td align='left' colspan='2'><span style='width:80px;text-align:left' class='s_float'><input type='radio' name='cgb_mb' value='1'  onclick=\"change_mb(this)\" $checked1/>项目横向展示</span><span class='s_float' style='width:80px;text-align:left'><input type='radio' name='cgb_mb' value='2'  onclick=\"change_mb(this)\" $checked2/>项目竖列展示</span></td>",
		"bg_zongheng"	=> "<td align='left' colspan='2'><span style='width:80px;text-align:left' class='s_float'><input type='radio' name='bg_hz' value='1' $checked3 />表格竖版</span><span class='s_float' style='width:80px;text-align:left'><input type='radio' name='bg_hz' value='2' $checked4 />表格横版</span></td>",
		"tr_td_num"		=> "<td align='left' colspan='2'>每页站点数：<input type='text' name='col_max' value='{$col_max}' style='width:45px' id='col_max' onblur=\"save_hl(this)\" onafterpaste=\"this.value=this.value.replace(/\D/g,'')\" onkeyup=\"this.value=this.value.replace(/\D/g,'')\">&nbsp;&nbsp;每页项目数：<input type='text' name='row_max' value='{$row_max}' style='width:45px' id='row_max' onblur=\"save_hl(this)\" onafterpaste=\"this.value=this.value.replace(/\D/g,'')\" onkeyup=\"this.value=this.value.replace(/\D/g,'')\"></td>",
		"show_result"		=> "<td align='left' colspan='2'><label><input type='radio' name='export_element[]' value='result' />显示结果</label><label><input type='radio' name='export_element[]' value='max_min_value' />显示极值、均值</label></td>"
		//"bg_canshu"		=> "<td align='left' colspan='2'>$cgb_bt_cs</td>"
	)
);
if($_GET['action']=='day_bg_mb'||$_GET['action']=='week_bg_mb'||$_GET['action']=='gb_month_mb'){
	$target=$date_str='';
	$any_sites_result_body=temp("any_data/any_sites_result_body2");
	if($_GET['action']=='week_bg_mb'||$_GET['action']=='gb_month_mb'){
		$any_sites_result_body="";
	}
	//日报只需要一个标题
	if($_GET['action']=='day_bg_mb'){
		$any_sites_result_body  = "<table style='margin-top:20px' class='table table-striped table-bordered table-hover center'><tr><td align='left' width='50%'>标题名称：<textarea type='text' name='cgb_title' id='cgb_title' style='width:60%;height:50px'>{$cgb_title}</textarea></td></tr></table>";
	}
	$submit_str="<center><input class=\"btn btn-xs btn-primary\" type=\"submit\" name=\"sub\" value=\"保存\" />";
}
if($gx_set_json['canshu']){
	$any_sites_result_body	= "<table style='margin-top:20px' class='table table-striped table-bordered table-hover center'>";
	$i	= 0;
	foreach ($gx_set_json['canshu'] as $key => $value_arr) {
		if($key=='jichu'){
			$any_sites_result_body	.= "<tr><td colspan='6' align='center'>成果表基础信息</td></tr>";
		}else if($key=='canshu'){
			$any_sites_result_body	.= "<tr><td colspan='6' align='center'>成果表参数设置</td></tr>";
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
$month_type	= "";
if(!empty($gx_set_json['set_mb_name'])){
	disp("any_data/{$gx_set_json['set_mb_name']}");
}else{
	//此参数在数据接收页面可以判断为：是任意成果查询页面发送的数据
	$month_type	= "<input type='hidden' name='month_type' value='any_data' />";
	disp("any_data/any_sites_result");
}
