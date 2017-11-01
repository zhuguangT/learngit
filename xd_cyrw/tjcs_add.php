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
if($_GET['action']=='tjcs_add' || $_GET['action']=='group_modify'){
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
		$this_group_name	= $_GET['group_name'];
		$sql_this_group	= $DB->query("SELECT gr.id as gr_id,si.id,si.site_name FROM `site_group` AS gr INNER JOIN `sites` AS si ON gr.site_id=si.id WHERE gr.fzx_id='$fzx_id' AND gr.`site_type`='{$site_type}' AND gr.`group_name`='{$_GET['group_name']}' AND gr.act='1' ORDER BY si.sort");
		$site_label	= '';
		while($rs_this_group = $DB->fetch_assoc($sql_this_group)){
			$i++;
			$site_label	.= "<label class='group_sites_old'><input type='checkbox' name='sites[]' site_id='{$rs_this_group['id']}' value='{$rs_this_group['gr_id']}' checked disabled />{$rs_this_group['site_name']}</label>";
		}
	}else{
		$title  = "采样批次添加";
	}
	$close  = "<span id='close' style=\"position:fixed;top:0;right:10px;width:60px;height:60px;background-color:#C7C2BC;cursor: pointer;opacity:0.8;margin-right:60px;\"><img src=\"$rooturl/img/close.png\" width=\"60px\" height=\"60px\" title=\"点击关闭本页\" alt=\"关闭\" /></span>";
}
echo temp("group_add.html");
?>
