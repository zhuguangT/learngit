<?php
/**
 * 功能：下达采样任务页面上点击 "添加新批次按钮"加载的页面
 * 作者：韩枫
 * 日期：2014-08-13
 * 描述
*/
include("../temp/config.php");
$fzx_id	= $u['fzx_id'];
$close  = '';
$i	= 0;
if($_GET['action']=='group_add' || $_GET['action']=='group_modify'){
	$_GET['group_name']	= urlsafe_b64decode($_GET['group_name']);//解密加密过的group_name
	//获取站点有没有多个垂线和层面
	$sql_site_line_vertical         = array();
	$sql_site_line_vertical     = $DB->query("SELECT * FROM `sites` WHERE fzx_id='$fzx_id' OR fp_id='$fzx_id' ORDER BY tjcs,site_name");
	while ($rs_site_line_vertical= $DB->fetch_assoc($sql_site_line_vertical)) {
		if(!empty($rs_site_line_vertical['site_code'])){
			$site_line_vertical[$rs_site_line_vertical['site_code']][$rs_site_line_vertical['water_type']][]    = 1;
		}
	}
	#######取出所有的水样类型并存到数组中
	$water_type_arr = array();
	$water_type_sql = $DB->query("SELECT * FROM `leixing` WHERE 1");
	while ($rs_water_type   = $DB->fetch_assoc($water_type_sql)) {
		$water_type_arr[$rs_water_type['id']]   = $rs_water_type['lname'];
	}
	$site_type      = get_str($_GET['site_type']);//temp/global.inc.php 中定义的站点类别
	$sql_group	= $DB->query("SELECT group_name,sort FROM `site_group` WHERE fzx_id='$fzx_id' AND `site_type`='{$site_type}' AND `group_name`!='' AND `group_name` IS NOT null GROUP BY `group_name` ORDER BY `sort` asc,`group_name`");
	$group_option	= '';
	$sort= 1;
	while($rs_group = $DB->fetch_assoc($sql_group)){
		if($rs_group['group_name']==$_GET['group_name']){
			$checked = 'selected';
		}else{
			$checked = '';
		}
		$group_option	.= "<option value='{$rs_group['sort']}' label='{$rs_group['group_name']}' $checked>{$rs_group['group_name']}</option>";
	}
	if($_GET['action']=='group_modify'){
		$title	= '采样批次修改';
		$button_str	= "确认修改批次";
		$this_group_name	= $_GET['group_name'];
		$sql_this_group	= $DB->query("SELECT gr.id as gr_id,si.id,si.site_name,si.river_name,si.site_code,si.water_type,si.site_line,si.site_vertical FROM `site_group` AS gr INNER JOIN `sites` AS si ON gr.site_id=si.id WHERE gr.fzx_id='$fzx_id' AND gr.`site_type`='{$site_type}' AND si.site_mark<>'fc_site' AND gr.`group_name`='{$_GET['group_name']}' AND gr.act='1' ORDER BY si.sort");
		$site_label	= '';
		while($rs_this_group = $DB->fetch_assoc($sql_this_group)){
			//判断相同站码但水样类型不同的站点
	        $line_vertical  = '';
	        if(count($site_line_vertical[$rs_this_group['site_code']])>1){
	        	$line_vertical  .= "(".$water_type_arr[$rs_this_group['water_type']].")";
	        }
	        //判断出该站点的垂线和层面
	        if(count($site_line_vertical[$rs_this_group['site_code']][$rs_this_group['water_type']])>1){
                $str_site_line   = $global['site_line'][$rs_this_group['site_line']];
                $str_site_vertical      = $global['site_vertical'][$rs_this_group['site_vertical']];
                $line_vertical  .= "(".$str_site_line.$str_site_vertical.")";
	        }
			$i++;
			//$site_label	.= "<label class='group_sites_old'><input type='checkbox' name='sites[]' site_id='{$rs_this_group['id']}' value='{$rs_this_group['gr_id']}' checked disabled />{$rs_this_group['site_name']}</label>";
			$site_label	.= "<label title='{$rs_this_group['river_name']}.{$rs_this_group['site_name']}{{$_GET['group_name']}}' class='group_sites'><input type='checkbox' name='sites[]' site_id='{$rs_this_group['id']}' value='{$rs_this_group['gr_id']}' checked />{$rs_this_group['site_name']}<font color='#9B9898'>$line_vertical</font></label>";
		}
	}else{
		$title  = "采样批次添加";
		$button_str	= "确认添加批次";
	}
	$close  = "<span id='close' style=\"position:fixed;top:0;right:10px;width:60px;height:60px;background-color:#C7C2BC;cursor: pointer;opacity:0.8;margin-right:60px;\"><img src=\"$rooturl/img/close.png\" width=\"60px\" height=\"60px\" title=\"点击关闭本页\" alt=\"关闭\" /></span>";
}
echo temp("group_add.html");
?>
