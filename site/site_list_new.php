<?php
/**
 * 功能：下达采样任务页面
 * 作者：韩枫
 * 日期：2014-04-21
 * 描述
*/
include("../temp/config.php");
$_GET['site_type']='0';
if($_GET['site_type']!='0'){
	gotourl("$rooturl/xd_cyrw/xd_cyrw_index.php?site_type={$_GET['site_type']}");
}
//导航
$daohang= array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'分中心站点管理 ','href'=>$_SESSION['url_stack'][0])
);
$trade_global['daohang']= $daohang;
$trade_global['js']		= array('jquery.date_input.js');
$trade_global['css']	= array('date_input.css');
//登陆及权限判断
if($u['xd_cy_rw']!='1' && $u['xd_csrw']!='1'){
	//跳转到登陆页
	echo "没有权限";
	exit;
}
$fzx_id     = $u['fzx_id'];
$fp_id		= $_GET['fzx_id'];
$tjcs		= $_GET['tjcs'];
$disabled	= 'disabled=disabled';
$group_name	= '';
$site_type	= '0';
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
		$rs_area['area'] = '未填写流域';
	}
	if($_GET['area']==$rs_area['area']){
		$area_selected	 = 'selected';
	}else{
		$area_selected	 = '';
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
$sql_old_xcjc_value     = $DB->query("select vid from `assay_pay`  where fzx_id='".$fzx_id."' AND is_xcjc='1' AND cyd_id='{$cy_last['id']}' ");
while($rs_old_xcjc_value= $DB->fetch_assoc($sql_old_xcjc_value)){
	$old_xcjc_value[]       = $rs_old_xcjc_value['vid'];
}
//$xcjc_value_num	= count($old_xcjc_value);
#########取出本单位检测的所有的检测项目并对应显示 现场检测项目、全程空白项目、现场平行项目
$xcjc_value_checkbox1   = $xcjc_value_checkbox  = '';
$sql_xcjc_value	= $DB->query("SELECT xm.id,xm.value_C,xm.fenlei,xm.is_xcjc FROM `xmfa` AS fa LEFT JOIN `assay_value` AS xm ON fa.xmid=xm.id WHERE fa.fzx_id='$fzx_id' AND fa.act='1' AND fa.mr='1' GROUP BY fa.xmid");
$xcjc_value_num	= 0;//可检测项目数量改成不检测项目数量
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
}
$xcjc_value_checkbox    = $xcjc_value_checkbox1.$xcjc_value_checkbox;//把默认选中的现场检测项目放到一起

#############取出站点及统计属性等信息
//获取站点有没有多个垂线和层面
$sql_site_line_vertical= array();
$sql_site_line_vertical     = $DB->query("SELECT * FROM `sites` WHERE fzx_id='$fzx_id' ORDER BY tjcs,site_name");
while ($rs_site_line_vertical= $DB->fetch_assoc($sql_site_line_vertical)) {
	$site_line_vertical[$rs_site_line_vertical['site_code']][]    = $rs_site_line_vertical['site_code'];
}
$site_options	= '';
$sites_arr	= array();
$site_names_arr	= array();
$where_sql_sites	= '';
if(!empty($_GET['area']) && $_GET['area']!='全部'){
	if($_GET['area']=='未填写流域'){
		$where_sql_sites	.= " AND (si.area='' OR si.area is null) ";
	}else{
		$where_sql_sites	.= " AND si.area='{$_GET['area']}' ";
	}
}
if((string)$fp_id=='全部'){
	$where_sql_sites	.= '';
}else{
	$where_sql_sites	.= " AND si.fp_id='$fp_id' ";
}
$sql_sites	= $DB->query("SELECT si.*,gr.id as gr_id,gr.group_name,gr.assay_values,gr.xcpx_values,gr.sort as gr_sort FROM `sites` AS si LEFT JOIN `site_group` AS gr ON si.id=gr.site_id  WHERE gr.fzx_id='$fzx_id' AND gr.act='1' AND si.fzx_id='$fzx_id' $where_sql_sites ORDER BY si.tjcs,si.site_name");
while($rs_sites	= $DB->fetch_assoc($sql_sites)){
	//$sites_tjcs	= array_filter(explode(",",$rs_sites['tjcs']));
	//判断出该站点的垂线和层面
	$line_vertical  = '';
	if(count($site_line_vertical[$rs_sites['site_code']])>1){
		$str_site_line  = $global['site_line'][$rs_sites['site_line']];
		$str_site_vertical      = $global['site_vertical'][$rs_sites['site_vertical']];
		$line_vertical  = "(".$str_site_line.$str_site_vertical.")";
	}
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
				//这里的参数要么是分中心加的，要么就是被开发人员误删了。#A7A7A7
				$value	= '系统未能识别该统计参数，请联系管理员';
			}
		}
		//统计参数名称及信息  #DDDDD	#9A9A9A跨列colspan='2'
		if(empty($sites_arr[$tjcs_id])){
		      $sites_arr[$tjcs_id]	= "<thead><tr tjcs='$tjcs_id' style=\"background-color:#ACACAC !important;font-weight:bold;\">
			<td style=\"background-color:#ACACAC !important;width:30%;\" align=\"left\" >$value</td>
			<td style=\"background-color:#ACACAC !important;width:25%;\"><span style=\"font-weight:bold;color:blue;cursor:pointer;\" onclick=\"qckb_value_modify('{$rs_sites['group_name']}','group_value','{$rs_sites['gr_sort']}')\">检测项目设定</span></td>
			<!-- <td style=\"background-color:#ACACAC !important;font-weight:bold;\">现场平行样</td>	-->
			<td style='background-color:#ACACAC !important;text-align:center;width:25%;text-align:center;' class='action-buttons'>操作
				<!-- <span class=\"widget-toolbar\" style=\"width:100%;height:100%;line-height:45px;clear:both;text-align:center;padding-right:0px;\"> -->
				<!--修改批次--><!--
				<a class='green' href='#' onclick=\"qckb_value_modify('$group_name','group_modify','{$rs_sites['gr_sort']}')\" title='修改批次“{$group_name}”的信息及站点'><i class='icon-edit bigger-130'></i></a>  -->
				<!--删除批次--> <!--
				<a class='red' href='#' onclick=\"gotoif('$rooturl/site/site_delete.php?action=xd_cyrw&pi=1&site_type={$_GET['site_type']}&sgname={$group_name}&fid={$fzx_id}','确定删除 {$group_name} 中全部的站点吗?');\" title=\"删除 {$group_name} \"><i class='icon-remove bigger-130'></i></a>  -->
				<!--添加批次--><!--
				<a class='ui-icon icon-plus-sign purple bigger-130' href='#' onclick=\"qckb_value_modify('','tjcs_add')\" style=\"cursor:pointer;\" title=\"添加新批次\"></a> -->
				<!-- 折叠    -->
				<!-- <a class=\"zheDie\" tjcs=\"$tjcs_id\" href=\"#\" style=\"height:100%;line-height:45px;vertical-align:middle;color:#E2E2E2;float:right;height:100%;border-width:1px;border-style:none none none solid;\">&nbsp;&nbsp;&nbsp;<i class=\"1 icon-chevron-up bigger-125\"></i>&nbsp;&nbsp;&nbsp;</a>
				</span> -->
			</td></tr></thead>";
		}
		//站点信息 
		$sites_arr[$tjcs_id]	.= "<tr tjcs='$tjcs_id' align=left><td title='{$rs_sites['river_name']}{$rs_sites['site_name']}$line_vertical' style='padding-left:30px;' class='action-button'><label> <input type='hidden' name='jdrw[sites][]' value='{$rs_sites['id']}' group_id='{$rs_sites['gr_id']}' group_name='jdrw' $site_disabled />{$rs_sites['site_name']}$line_vertical</label></td>
			<td align=center class='action-button'><span class='tishi_site_value_num' id='{$rs_sites['gr_id']}' gr_id='{$rs_sites['gr_id']}' style='color:blue;cursor:pointer;' onclick=\"qckb_value_modify('{$rs_sites['site_name']}','site_value','{$rs_sites['gr_id']}');\">{$site_values_num}</span></td>
			<!--<td align=center><label><input type='checkbox' name='jdrw[xcpx][]' value='{$rs_sites['id']}' $disabled />现场平行<span style=\"color:blue;cursor:pointer;\" onclick=\"qckb_value_modify('{$rs_sites['group_name']}','xcpx','{$rs_sites['gr_id']}');\" class='xcpx_site' $xcpx_value_zt xcpx_group_name='{$rs_sites['group_name']}' xcpx_num_id='{$rs_sites['gr_id']}'>({$xcpx_values_num} 项)</span></label></td> -->
			<td align=center class='action-button'>

				<a class='green icon-edit bigger-130' href='$rooturl/site/site_info.php?action=xdjdrw&site_type={$site_type}&site_id={$rs_sites['id']}&group_name={$rs_sites['group_name']}&fzx_id={$rs_sites['fp_id']}' title='修改站点“{$rs_sites['site_name']}”的信息'></a>
				<a class='red icon-remove bigger-130' href='#' onclick=\"gotoif('$rooturl/site/site_delete.php?action=xd_cyrw&sid={$rs_sites['id']}&sgname={$rs_sites['group_name']}&fid={$rs_sites['fzx_id']}&site_type={$site_type}','确定删除 {$rs_sites['site_name']} 吗?');\" title=\"删除 {$rs_sites['site_name']}\"></a>
			</td>
		</tr>";
		
	//}
}
##############显示每种统计类型的站点
$lines	= '';
foreach($sites_arr as $key=>$value){
	$lines	.= $value."</td></tr>";
}
disp("site_list_new.html");
?>
