<?php
/**
 * 功能：下达采样任务页面
 * 作者：韩枫
 * 日期：2014-04-21
 * 描述
*/
include("../temp/config.php");
if($_GET['site_type']!='0'){
	gotourl("$rooturl/xd_cyrw/xd_cyrw_index.php?site_type={$_GET['site_type']}");
}
//导航
$daohang= array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'下达监督任务','href'=>$_SESSION['url_stack'][0])
);
$trade_global['daohang']= $daohang;
$trade_global['js'] = array('jquery.date_input.js');
$trade_global['css'] = array('date_input.css');
//登陆及权限判断
if($u['xd_cy_rw']!='1'){
	//跳转到登陆页
	echo "没有权限";
	exit;
}
$fzx_id         = $u['fzx_id'];
$cy_date        = date('Y-m-d');
$fp_id		= $_GET['fzx_id'];
$tjcs		= $_GET['tjcs'];
$xdcy_title	= '下达采样任务';
$disabled	= 'disabled=disabled';
$group_name	= '';
#########取出本单位所有的站点类别/任务类型/任务性质
$site_type_options      = "";
foreach($global['site_type'] as $key=>$value){
	if($site_type == $key){
		$site_type_options .= "<option value='$key' selected>$value</option>";
	}else{
		$site_type_options .= "<option value='$key'>$value</option>";
	}
}
#######取出所有的水样类型并存到数组中
$water_type_arr	= array();
$water_type_sql	= $DB->query("SELECT * FROM `leixing` WHERE 1");
while ($rs_water_type	= $DB->fetch_assoc($water_type_sql)) {
	$water_type_arr[$rs_water_type['id']]	= $rs_water_type['lname'];
}
########取出分中心列表
$fp_fzx_options = "<option value='全部'>全部</option>";
$sql_fzx        = $DB->query("SELECT * FROM  `hub_info` WHERE id!='$fzx_id'");
while($rs_fzx   = $DB->fetch_assoc($sql_fzx)){
	$rs_fzx['hub_name']	 = str_replace('辽宁省水环境监测中心','',$rs_fzx['hub_name']);
	if(empty($fp_id)){
		$fp_id  = $rs_fzx['id'];
	}
	if($fp_id==$rs_fzx['id']){
		$group_name	 = $rs_fzx['hub_name'].date('m月')."监督任务";
		$fp_fzx_options .= "<option value='{$rs_fzx['id']}' selected>{$rs_fzx['hub_name']}</option>";
	}else{
		$fp_fzx_options .= "<option value='{$rs_fzx['id']}'>{$rs_fzx['hub_name']}</option>";
	}
}
################取出所有流域
$site_area_options	= "<option value='全部'>全部</option>";
$where_sql_area	= '';
if((string)$fp_id!='全部'){
	$where_sql_area	= " AND si.fp_id='$fp_id' ";
}
$sql_area	= $DB->query("SELECT si.area FROM `sites` AS si  WHERE si.fzx_id='$fzx_id' $where_sql_area GROUP BY si.area ORDER BY si.area");
while ($rs_area = $DB->fetch_assoc($sql_area)) {
	//流域的下拉菜单
	if(empty($rs_area['area'])){
		$rs_area['area']	= '未填写流域';
	}
	if($_GET['area']==$rs_area['area']){
		$area_selected	= 'selected';
	}else{
		$area_selected	= '';
	}
	$site_area_options	.= "<option value='{$rs_area['area']}' $area_selected>{$rs_area['area']}</option>";
}
########取出所有的统计参数
$tjcs_options	= '';
$tjcs_arr	= array();
$sql_tjcs	= $DB->query("SELECT * FROM `n_set` WHERE fzx_id='$fzx_id' AND module_name='tjcs'");
while($rs_tjcs	= $DB->fetch_assoc($sql_tjcs)){
	$tjcs_arr[$rs_tjcs['id']]	= $rs_tjcs['module_value1'];
	if($rs_tjcs['id']==$tjcs){
		$tjcs_options   .= "<option value='{$rs_tjcs['id']}' selected>{$rs_tjcs['module_value1']}</option>";
	}else{
		$tjcs_options   .= "<option value='{$rs_tjcs['id']}'>{$rs_tjcs['module_value1']}</option>";
	}
	if(empty($tjcs)){
		$tjcs	 = $rs_tjcs['id'];
	}
}
#########取出全程序空白项目
$qckb_value     = $DB->fetch_one_assoc("select module_value1 from `n_set` where fzx_id='$fzx_id' and module_name='qckb_value' and module_value2='$site_type' order by id desc limit 1");
$qckb_value_arr = @explode(',',$qckb_value['module_value1']);
$qckb_value_all_num = count($qckb_value_arr);
#########取出现场平行项目
$xcpx_value     = $DB->fetch_one_assoc("select module_value1 from `n_set` where fzx_id='$fzx_id' and module_name='xcpx_value' and module_value2='$site_type' order by id desc limit 1");
$xcpx_value_arr = @explode(',',$xcpx_value['module_value1']);
$xcpx_value_all_num = count($xcpx_value_arr);
#########各中心设置的现场检测项目
$xcjc_value     = $DB->fetch_one_assoc("SELECT  module_value1 FROM `n_set` WHERE fzx_id='$fzx_id' AND module_name='xcjc_value' order by id desc limit 1");
$xcjc_value_arr = array_filter(@explode(',',$xcjc_value['module_value1']));
#########取出相同"任务类型"的上一批次任务所做的现场检测项目
$old_xcjc_value = array();
$cy_last	= $DB->fetch_one_assoc("SELECT id FROM `cy` WHERE site_type='".$site_type."' AND fzx_id='".$fzx_id."' ORDER BY id DESC LIMIT 1 ");
$sql_old_xcjc_value     = $DB->query("select vid from `assay_pay`  where fzx_id='".$fzx_id."' AND is_xcjc='1' AND cyd_id='".$cy_last['id']."' ");
while($rs_old_xcjc_value= $DB->fetch_assoc($sql_old_xcjc_value)){
	$old_xcjc_value[]       = $rs_old_xcjc_value['vid'];
}
//$xcjc_value_num	= count($old_xcjc_value);
#########取出本单位检测的所有的检测项目并对应显示 现场检测项目、全程空白项目、现场平行项目
$xcjc_value_checkbox1   = $xcjc_value_checkbox = $qckb_values = $xcpx_values = '';
$sql_xcjc_value	= $DB->query("SELECT xm.id,xm.value_C,xm.fenlei,xm.is_xcjc FROM `xmfa` AS fa LEFT JOIN `assay_value` AS xm ON fa.xmid=xm.id WHERE fa.fzx_id='$fzx_id' AND fa.act='1' AND fa.mr='1' GROUP BY fa.xmid");
$qckb_value_num	= $xcpx_value_num	= $xcjc_value_num	= 0;//可检测项目数量改成不检测项目数量
while($rs_xcjc_value = $DB->fetch_assoc($sql_xcjc_value)){
	//默认现场检测项目
	if($rs_xcjc_value['is_xcjc']=='1' && (empty($xcjc_value_arr) || in_array($rs_xcjc_value['id'],$xcjc_value_arr))){
		if(in_array($rs_xcjc_value['id'],$old_xcjc_value)){
			$xcjc_value_num++;
			$xcjc_value_checkbox1   .= "<label><input type='checkbox' name='xcjc_value[]' value='{$rs_xcjc_value['id']}' checked>{$rs_xcjc_value['value_C']}</label>";
		}else{
			$xcjc_value_checkbox    .= "<label><input type='checkbox' name='xcjc_value[]' value='{$rs_xcjc_value['id']}'>{$rs_xcjc_value['value_C']}</label>";
		}
	}
	//显示全程序空白项目
	if(!in_array($rs_xcjc_value['id'],$qckb_value_arr)){
		$qckb_value_num++;
		$qckb_values    .= $rs_xcjc_value['value_C'].'、';
	}
	//显示现场平行项目
	if(!in_array($rs_xcjc_value['id'],$xcpx_value_arr)){
		$xcpx_value_num++;
		$xcpx_values    .= $rs_xcjc_value['value_C'].'、';
	}
}
if($qckb_values == ''){
	if($qckb_value_all_num==0){
		$qckb_value_num = 0;
		$qckb_values    = '未设置项目';
	}else{
		$qckb_value_num = $qckb_value_all_num;
		$qckb_values    = '全部项目均可检测';
	}
}else{
	$qckb_values    = substr($qckb_values,0,-3)."(admin注意:此处显示的是不检测的项目，但设置时依然要设置检测哪些项目。)";//去除最后的“、”
}
if($xcpx_values == ''){
	if($xcpx_value_all_num==0){
		$xcpx_value_num = 0;
		$xcpx_values    = '未设置项目';
	}else{
		$xcpx_value_num = $xcpx_value_all_num;
		$xcpx_values    = '全部项目均可检测';
	}
}else{
	$xcpx_values    = substr($xcpx_values,0,-3)."(admin注意:此处显示的是不检测的项目，但设置时依然要设置检测哪些项目。)";//去除最后的“、”
}
$qckb_modify    = $xcpx_modify = '';
if($u['admin']=='1'){//全程序空白可检测项目设置 ，与现场平行样可检测项目设置 只有admin可以设置
	$qckb_modify    = "<tr>
			<td nowrap>
				全程序空白项目($qckb_value_num 项):
			</td>
			<td align=\"left\" colspan=\"4\" style=\"white-space: nowrap;text-overflow:ellipsis; overflow:hidden;\">
				$qckb_values
			</td>
			<td nowrap>
				<a href=\"values_modify.php?action=qckb_value&site_type=$site_type\" target=\"_blank\">修改</a>
			</td>
		</tr>";
	$xcpx_modify    = "<tr>
			<td nowrap>现场平行项目($xcpx_value_num 项):</td>
			<td align=\"left\" colspan=\"4\" style=\"white-space: nowrap;text-overflow:ellipsis; overflow:hidden;\">
				$xcpx_values
			</td>
			<td nowrap>
				<a href=\"values_modify.php?action=xcpx_value&site_type=$site_type\" target=\"_blank\">修改</a>
			</td>
		</tr>";
}
$xcjc_value_checkbox    = $xcjc_value_checkbox1.$xcjc_value_checkbox;//把默认选中的现场检测项目放到一起
########取出所有采样员
$sql_cy_user    = $DB->query("SELECT * FROM `users` WHERE fzx_id='$fzx_id' and `group`!='0' and `group`!='测试组' and `cy`='1' ORDER BY convert(`userid` using gb2312) asc");//order by userid DESC
//如果获得了采样单的id
$sid_arr=array();
$xcpx_sid_arr=array();
if($_GET['cyd_id']){
	$cy_sql="SELECT cy_user,cy_user2,cy_date,sites,snkb FROM `cy` WHERE id='".$_GET['cyd_id']."'";
	$cy_rs=$DB->fetch_one_assoc($cy_sql);
	$site_str='';
	$cy_user=$cy_rs['cy_user'];
	$cy_user2=$cy_rs['cy_user2'];
	$cy_date=$cy_rs['cy_date'];
	if(!empty($cy_rs['snkb'])){
		$snkb_checked="checked=checked";
	}
	$rec_rs=$DB->fetch_one_assoc("SELECT id FROM `cy_rec` WHERE cyd_id='".$_GET['cyd_id']."' AND sid=0");
	if(!empty($rec_rs)){
		$qckb_checked="checked=checked";
	}

	$rec_sql="SELECT * FROM `cy_rec` WHERE cyd_id='".$_GET['cyd_id']."' AND sid>0";
	$rec_query=$DB->query($rec_sql);
	while($rec_rs=$DB->fetch_assoc($rec_query)){
		if(!in_array($rec_rs['sid'],$sid_arr)){
			$sid_arr[]=$rec_rs['sid'];
		}
		if($rec_rs['sid']>0&&$rec_rs['zk_flag']<0){
			$xcpx_sid_arr[]=$rec_rs['sid'];
		}
	}
	if(!empty($sid_arr)){
		$group_name_checked="checked=checked";
		$disabled='';
	}
}
$option_user    = '';
while($rs_cy_user=$DB->fetch_assoc($sql_cy_user)){
	$selected       ='';
	$selected2      ='';
	if($cy_user==$rs_cy_user['userid']&&!empty($cy_user)){
		$selected       = "selected=selected";
	}
	if($cy_user2==$rs_cy_user['userid']&&!empty($cy_user2)){
		$selected2      = "selected=selected";
	}
	$option_user   .= "<option {$selected} value='{$rs_cy_user['userid']}'>{$rs_cy_user['userid']}</option>";
	$option_user2  .= "<option {$selected2} value='{$rs_cy_user['userid']}'>{$rs_cy_user['userid']}</option>";
}
#############取出站点及统计属性等信息
//获取站点有没有多个垂线和层面
$sql_site_line_vertical= array();
$sql_site_line_vertical     = $DB->query("SELECT * FROM `sites` WHERE fzx_id='$fzx_id' ORDER BY tjcs,site_name");
while ($rs_site_line_vertical= $DB->fetch_assoc($sql_site_line_vertical)) {
	$site_line_vertical[$rs_site_line_vertical['site_code']][$rs_site_line_vertical['water_type']][]    = 1;
	//	$site_line_vertical[$rs_site_line_vertical['site_code']][]    = $rs_site_line_vertical['site_code'];
}
$site_options	= '';
$sites_arr	= array();
$site_names_arr	= array();
$where_sql_sites	= '';
//流域的搜索条件
if(!empty($_GET['area']) && $_GET['area']!='全部'){
	if($_GET['area']=='未填写流域'){
		$where_sql_sites	.= " AND (si.area='' OR si.area is null) ";
	}else{
		$where_sql_sites	.= " AND si.area='{$_GET['area']}' ";
	}
}
//分中心的搜索条件
if((string)$fp_id=='全部'){
	$where_sql_sites	.= '';
}else{
	$where_sql_sites	.= " AND si.fp_id='$fp_id' ";
}
//统计参数的搜索条件
if(!empty($_GET['tjcs']) && $_GET['tjcs']!='全部'){
	if($_GET['tjcs']=='未分配统计参数'){
		$where_sql_sites	.= " AND (gr.group_name='' OR gr.group_name is null) ";
	}else{
		$where_sql_sites	.= " AND gr.group_name='{$_GET['tjcs']}' ";
	}
}
//站点名称的搜索条件
if(!empty($_GET['site_id']) && $_GET['site_id']!='全部'){
	$where_sql_sites	.= " AND gr.site_id='{$_GET['site_id']}' ";
}
$sql_sites	= $DB->query("SELECT si.*,gr.id as gr_id,gr.group_name,gr.assay_values,gr.xcpx_values,gr.sort as gr_sort FROM `sites` AS si LEFT JOIN `site_group` AS gr ON si.id=gr.site_id  WHERE gr.fzx_id='$fzx_id' AND gr.act='1' AND si.fzx_id='$fzx_id' $where_sql_sites ORDER BY si.tjcs,si.site_name");
while($rs_sites	= $DB->fetch_assoc($sql_sites)){
	//$sites_tjcs	= array_filter(explode(",",$rs_sites['tjcs']));
	//判断相同站码但水样类型不同的站点
    $line_vertical  = '';
    if(count($site_line_vertical[$rs_sites['site_code']])>1){
    	$line_vertical	.= "(".$water_type_arr[$rs_sites['water_type']].")";
    }
    //判断出该站点的垂线和层面
    if(count($site_line_vertical[$rs_sites['site_code']][$rs_sites['water_type']])>1){
		$str_site_line   = $global['site_line'][$rs_sites['site_line']];
		$str_site_vertical      = $global['site_vertical'][$rs_sites['site_vertical']];
		$line_vertical  .= "(".$str_site_line.$str_site_vertical.")";
    }
	//站点的水样类型
	$water_type_str	= $water_type_arr[$rs_sites['water_type']];
	//分不同统计类型 记录
	if(!empty($rs_sites['assay_values'])){//解决没选项目时，项目数量的判断失误问题
		$site_values_num= count(@explode(',',$rs_sites['assay_values']));
		$site_disabled	= '';
	}else{
		$site_values_num= 0;
		$site_disabled	= "disabled=disabled";//如果站点中没有检测项目，这个站点将不允许选择
	}
	//现场检测项目的数量
	$xcpx_value_zt	= '';
	if(!empty($rs_sites['xcpx_values'])){
		$xcpx_values_num= count(@explode(',',$rs_sites['xcpx_values']));
	}else{
		$xcpx_value_zt	= "xcpx_value_zt='no'";//在项目更改时，有这个标识的现场平行项目数也要相应更改
		$xcpx_values_num= $site_values_num;
		//$xcpx_values_num= 0;
	}
	//asort($sites_tjcs);
	//站点名称的下拉菜单
	if(!in_array($rs_sites['id'],$site_names_arr)){
		$site_names_arr[]	 = $rs_sites['id'];
		if(!empty($_GET['site_id']) && $rs_sites['id']==$_GET['site_id']){
			$site_options	.= "<option value='{$rs_sites['id']}' selected>{$rs_sites['site_name']}$line_vertical</option>";
		}else{
			$site_options	.= "<option value='{$rs_sites['id']}'>{$rs_sites['site_name']}$line_vertical</option>";
		}
	}
	//根据统计参数 分别存储信息
	/*if(empty($sites_tjcs)){
		$sites_tjcs[]	= '';
	}*/
	//foreach($sites_tjcs as $value){
		//$tjcs_id	= $value;
		$tjcs_id	= $rs_sites['group_name'];
		if($tjcs_id==''){
			$value	= '未分配统计参数';
		}else{
			if(!empty($tjcs_arr[$tjcs_id])){
				$value	= $tjcs_arr[$tjcs_id];
			}else{
				//continue;
				//这里的参数要么是分中心加的，要么就是被开发人员误删了。
				$value	= '系统未能识别该统计参数，请联系管理员';
			}
		}
		//统计参数名称及信息
		if(empty($sites_arr[$tjcs_id])){
			$sites_arr[$tjcs_id]	= "<tr style=\"font-weight:bold;height:25px;\"><td style=\"background-color:#99CCFF;\">$value</td></tr><tr><td>";
		}
		$sites_arr[$tjcs_id]	.= "<label title='{{$water_type_str}}{$rs_sites['river_name']}.{$rs_sites['site_name']}$line_vertical' tjcs='{$tjcs_id}' class='site_label'><input type='checkbox' class='check_sites' site_id='{$rs_sites['id']}' value='{$rs_sites['gr_id']}' site_old='{$rs_sites['id']}' site_value_num='$site_values_num' xcpx_value_num='$xcpx_values_num' />{$rs_sites['site_name']}<font color='#9B9898'>$line_vertical</font></label>"; 
	//}
}
##############显示每种统计类型的站点
$lines	= '';
foreach($sites_arr as $key=>$value){
	$lines	.= $value."</td></tr>";
}
if($_GET['action']=='ajax_site_old'){
	//$get_data	= array('lines'=>$lines,'fp_fzx_options'=>$fp_fzx_options,'site_area_options'=>$site_area_options,'tjcs_options'=>$tjcs_options,'site_options'=>$site_options);
	//echo JSON($get_data);//
	echo $lines;
}else{
	disp("xd_jdrw_index.html");
}
?>
