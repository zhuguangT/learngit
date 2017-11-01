<?php
/*
	功能：用于厂部默认站点功能设置
	时间：2016-11-09
	作者：高龙
	描述：
	系统：兰州
*/
include '../temp/config.php';
require_once "$rootdir/inc/site_func.php";
if(empty($u['userid'])){
	nologin();
}
$fzx_id=$u['fzx_id'];
$water_type = $_POST['water_type'];//水样类型

//根据水样类型从n_set表中查询出与其相对应的站点
$siteid = $DB->fetch_one_assoc("SELECT `module_value1` from `n_set` where `module_value2`= '{$water_type}' and `module_name` = 'cbsite_id'");

if(!empty($siteid['module_value1'])){
	//根据站点id把批次id取出来
	$result = $DB->query("SELECT `id` from `site_group` where `site_id` in({$siteid['module_value1']})");
	while ($pcid = $DB->fetch_assoc($result)) {
		$default_site[] = $pcid['id'];
	}

	//获取已选中批次的批次名称集合
	$group_name_json_arr= array();
	$site_gr_id			= $siteid['module_value1'];
	if(!empty($site_gr_id)){
		$sql_gr_name		= $DB->query("SELECT `group_name` FROM `site_group` WHERE `site_id` in($site_gr_id) group by `group_name`");
		while ($rs_gr_name	= $DB->fetch_assoc($sql_gr_name)) {
			$group_name_json_arr[]	= $rs_gr_name['group_name'];
		}
	}
}


###############站点选择区域###########
$group_sql = "SELECT  sg.group_name,s.site_name,s.id,sg.id as gr_id FROM site_group sg LEFT JOIN sites s ON s.id = sg.site_id WHERE
    site_mark='fc_site' ORDER BY sg.group_name";
$group_query=$DB->query($group_sql);
while($group_rs=$DB->fetch_assoc($group_query)){
	$group_data[$group_rs['group_name']][$group_rs['id']]=$group_rs['site_name'];
	$group_id[$group_rs['group_name']][$group_rs['id']]=$group_rs['gr_id'];	
}
$group_site_str	= $checked_sites_str	= '';
$line_nums		= '8';//每行显示的个数
//遍历每个批次下的站点
$j=1;
if(!empty($group_data)){
	foreach($group_data as $key=>$value){
		$i=1;
		$g_checked='';
		/*if($_POST['action']=='alone_set'){
			$group_name_json_arr= $info['alone_group_name'];
			$site_json_arr		= $info['alone_sites'];
		}else if($_POST['action']=='merger_set'){
			$group_name_json_arr= $info['merger_group_name'];
			$site_json_arr		= $info['merger_sites'];
		}else{
			$group_name_json_arr= $info['group_name'];
			$site_json_arr		= $info['sites'];
		}*/
		//$group_name_json_arr= $info['alone_group_name'];
		$site_json_arr		= $default_site;
		if(@in_array($key,$group_name_json_arr)){
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
			if(@in_array($group_id[$key][$k],$site_json_arr)){
				$s_checked='checked="checked"';
			}
			if($count%$line_nums&&$i==$count){
				$add_tds=$line_nums-$count%$line_nums+1;
				$group_site_str.="<td colspan=".$add_tds."><span class=\"s_float\"><label><input name=\"sites[$key][]\" type=\"checkbox\" value=".$k." gr_id=".$group_id[$key][$k]." class=".$j." onclick=\"check_group(this)\" $s_checked />".$v."</label></span></td>";
				if($s_checked=='checked="checked"'){
					$checked_sites_str	.= "<td colspan=".$add_tds."><span class=\"s_float\"><label><input name=\"sites[$key][]\" type=\"checkbox\" value=".$k." gr_id=".$group_id[$key][$k]." class=".$j." onclick=\"check_group(this)\" $s_checked />".$v."</label></span></td>";
				}
			}else{
				$group_site_str.="<td><span class=\"s_float\"><label><input  name=\"sites[$key][]\" type=\"checkbox\" value=".$k." gr_id=".$group_id[$key][$k]." class=".$j." onclick=\"check_group(this)\" $s_checked/>".$v."</label></span></td>";
				if($s_checked=='checked="checked"'){
					$checked_sites_str	.= "<td><span class=\"s_float\"><label><input  name=\"sites[$key][]\" type=\"checkbox\" value=".$k." gr_id=".$group_id[$key][$k]." class=".$j." onclick=\"check_group(this)\" $s_checked/>".$v."</label></span></td>";
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


###############站点选择区域###########

disp("changbu/bg_site_list");
?>