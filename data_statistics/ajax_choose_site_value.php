<?php
/*
*功能：ajax站点选择页面
*作者：hanfeng
*时间：2014-08-21
*/
include '../temp/config.php';
require_once "$rootdir/inc/site_func.php";

if($u['userid'] == ''){
	nologin();
}
$fzx_id		= $u['fzx_id'];
#################//取出该报告的默认配置信息（ajax时也会用到）##############
$gx_set_json= array();
$checked4	= 'checked="checked"';
if($_POST['set_id']){
	$info	= array();
	$cg_rs	= $DB->fetch_one_assoc("SELECT * FROM `baogao_list` WHERE `id`='".$_POST['set_id']."'");
	if(!empty($cg_rs['result_set'])){
		$info	= json_decode($cg_rs['result_set'],true);
	}
	if(!empty($cg_rs['gx_set'])){
		$gx_set_json= json_decode($cg_rs['gx_set'],true);
	}
	//获取已选中批次的批次名称集合
	$group_name_json_arr= array();
	$site_gr_id			= @implode(',',$info['alone_sites']);
	if(!empty($site_gr_id)){
		$sql_gr_name		= $DB->query("SELECT `group_name` FROM `site_group` WHERE `id` in($site_gr_id) group by `group_name`");
		while ($rs_gr_name	= $DB->fetch_assoc($sql_gr_name)) {
			$group_name_json_arr[]	= $rs_gr_name['group_name'];
		}
	}
}
//根据参数决定显示什么参数
$site_switch	= $value_switch	= $field_name	= '';
$site_display	= $value_display= " display:none;";
if($_POST['action'] == 'site_choose'){
	$site_switch	= 'yes';
	$site_display	= '';
	$field_name		= 'alone_sites[]';
}else if($_POST['action'] == 'value_choose'){
	$value_switch	= 'yes';
	$value_display	= '';
	$field_name		= 'alone_vid[]';
}else{
	$site_switch	= $value_switch	= 'yes';
	$site_display	= $value_display= '';
}
###############站点选择区域###########
if($site_switch	== 'yes'){
	#############下拉菜单筛选区域##############
	//当前任务类型
	if(!isset($_POST['site_type'])){
		if(isset($info['site_type'])){
			$site_type =$info['site_type'];
		}else{
			$site_type ='全部';
		}
	}else{
		$site_type=$_POST['site_type'];
	}
	//获得任务类型的下拉菜单
	$site_type_str	= "<option value='全部'>全部</option>";
	foreach($global['site_type'] as $key=>$value){
		if($key==$site_type){
			$site_type_str.="<option  selected='selected' value=".$key.">".$value."</opion>";
		}else{
			$site_type_str.="<option value=".$key.">".$value."</opion>";
		}
	}

	//当前水样类型
	if(!isset($_POST['water_type'])){
		if(isset($info['water_type'])){
			$water_type = $info['water_type'];
		}else{
			$water_type = '全部';
		}
	}else{
		$water_type=$_POST['water_type'];
	}
	//获得本中心的水样类型
	$sql_leixing = $DB->query("SELECT id,lname FROM `leixing` WHERE parent_id='0' AND act='1'");
	while($lx = $DB->fetch_assoc($sql_leixing)){
		if($lx['id']==$water_type){
			$lxlist .= "<option selected=\"selected\"  value=\"$lx[id]\">$lx[lname]</option><optgroup style=\"padding-left:21px\">";
		}else{
			$lxlist.="<option value='$lx[id]'>$lx[lname]</option><optgroup style=\"padding-left:21px\">";
		}
		$sql_xleixing = $DB->query("SELECT id as xid,lname,parent_id FROM `leixing` WHERE  parent_id!='0' AND act='1'");
		while($xlx = $DB->fetch_assoc($sql_xleixing)){
			if($lx['id']==$xlx['parent_id']){
				if($xlx['xid']==$water_type){
					$lxlist.="<option value='$xlx[xid]' selected=\"selected\">$xlx[lname]</option>";
				}else{
					$lxlist.="<option value='$xlx[xid]'>$xlx[lname]</option>";
				}
			}
		}
		$lxlist.="</optgroup>";
	}
	//当前统计参数
	if(empty($_POST['tjcs'])){
		$tjcs='';
	}else{
		$tjcs=$_POST['tjcs'];
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
	
	//获取某任务类型和某水样类型下的所有批次
	if($gx_set_json['fc_site']	== 'yes'){//站点为分厂上传数据的时候走这里
		$line_nums		= '8';//每行显示的个数
		$group_name		= "分厂上传的站点";
		$g_checked		= '';
		if(!empty($info['alone_sites'][$group_name])){
			$g_checked	= 'checked';
		}
		$group_site_str	= "<tr><td colspan=".$line_nums."><label><input name=\"group_name[]\" type=\"checkbox\" value='{$group_name}' id='1' onclick=\"check_sites(this)\" $g_checked/><span class='pc_css'>{$group_name}</span></label></td></tr>";
		$site_sql		= $DB->query("SELECT * FROM `changbu_data` WHERE 1 GROUP BY `site_name`");
		while($site_rs	= $DB->fetch_assoc($site_sql)){
			$s_checked	= '';
			if(@in_array($site_rs['site_name'],$info['alone_sites'][$group_name])){
				$s_checked	= 'checked';
			}
			$group_site_str	.= "<td><span class=\"s_float\"><label><input  name=\"sites[$group_name][]\" type=\"checkbox\" value=".$site_rs['site_name']." class='1' onclick=\"check_group(this)\" $s_checked/>".$site_rs['site_name']."</label></span></td>";
		}
	}else{
		$group_name_arr	= get_group_names($site_type,$water_type,1);
		//当前批次名称
		if(!isset($_POST['group_name'])){
		    $group_name = '';
		}else{
			$group_name	= $_POST['group_name'];
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
		$sql_where_arr	= [];
		if($site_type != '全部'){
			$sql_where_arr[]	= " AND sg.site_type='".$site_type."'";
		}
		if(!empty($water_type)&&$water_type!='全部'){
			$sql_where_arr[]	= " AND s.water_type='{$water_type}'";
		}
		if(!empty($group_name)){
			$sql_where_arr[]	= "AND sg.group_name='{$group_name}'";
		}
		if(!empty($tjcs)){
			$sql_where_arr[]	= "AND (s.tjcs='{$tjcs}' or s.tjcs like '%{$tjcs},%' or s.tjcs like '%,{$tjcs}%') ";
		}
		$sql_where_str	= implode('', $sql_where_arr);
		$group_sql = "SELECT  sg.group_name,s.site_name,s.id,sg.id as gr_id FROM site_group sg LEFT JOIN sites s ON s.id = sg.site_id WHERE sg.group_name!='' AND sg.act='1' {$sql_where_str} ORDER BY sg.group_name";
		$group_query=$DB->query($group_sql);
		while($group_rs=$DB->fetch_assoc($group_query)){
			$group_data[$group_rs['group_name']][$group_rs['id']]=$group_rs['site_name'];
			$group_id[$group_rs['group_name']][$group_rs['id']]=$group_rs['gr_id'];	
		}
		$group_site_str	= $checked_sites_str	= '';
		$line_nums		= '7';//每行显示的个数
		//遍历每个批次下的站点
		$j=1;
		if(!empty($group_data)){
			foreach($group_data as $key=>$value){
				$i=1;
				$g_checked	= $g_class	= '';
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
				$site_json_arr		= $info['alone_sites'];
				if(@in_array($key,$group_name_json_arr)){
					$g_checked	=' checked="checked" is_checked="yes" ';
					$g_class	= " class='checked-label' ";
					$checked_sites_str	.= "<tr gr_tr_id='{$j}' site_group='yes'><td colspan=".$line_nums."><label {$g_class}><input name=\"group_name[]\" type=\"checkbox\" value='".$key."' id='".$j."' onclick=\"check_sites(this)\" $g_checked  /><span class='pc_css'>".$key."</span></label></td></tr>";
				}
				$group_site_str.="<tr gr_tr_id='{$j}' site_group='yes'><td colspan=".$line_nums."><label {$g_class}><input name=\"group_name[]\" type=\"checkbox\" value='".$key."' id='".$j."' onclick=\"check_sites(this)\" $g_checked/><span class='pc_css'>".$key."</span></label></td></tr>";
				$count=count($value);
				foreach($value as $k=>$v){
					if($i==1){
						$group_site_str		.= "<tr gr_tr_id='{$j}' site='yes'>";
						$checked_sites_str	.= "<tr gr_tr_id='{$j}' site='yes'>";
					}
					$s_checked	=	$s_class= '';
					if(@in_array($group_id[$key][$k],$site_json_arr)){
						$s_checked	= ' checked="checked" is_checked="yes" ';
						$s_class	= " class='checked-label' ";
					}
					if($count%$line_nums&&$i==$count){
						$add_tds=$line_nums-$count%$line_nums+1;
						$group_site_str.="<td colspan=".$add_tds."><span class=\"s_float\"><label {$s_class}><input name=\"sites[$key][]\" type=\"checkbox\" value=".$k." gr_id=".$group_id[$key][$k]." class=".$j." onclick=\"check_group(this)\" $s_checked />".$v."</label></span></td>";
						if(stristr($s_checked,'checked')){
							$checked_sites_str	.= "<td colspan=".$add_tds."><span class=\"s_float\"><label {$s_class}><input name=\"sites[$key][]\" type=\"checkbox\" value=".$k." gr_id=".$group_id[$key][$k]." class=".$j." onclick=\"check_group(this)\" $s_checked />".$v."</label></span></td>";
						}
					}else{
						$group_site_str.="<td><span class=\"s_float\"><label {$s_class}><input  name=\"sites[$key][]\" type=\"checkbox\" value=".$k." gr_id=".$group_id[$key][$k]." class=".$j." onclick=\"check_group(this)\" $s_checked/>".$v."</label></span></td>";
						if(stristr($s_checked,'checked')){
							$checked_sites_str	.= "<td><span class=\"s_float\"><label {$s_class}><input  name=\"sites[$key][]\" type=\"checkbox\" value=".$k." gr_id=".$group_id[$key][$k]." class=".$j." onclick=\"check_group(this)\" $s_checked />".$v."</label></span></td>";
						}
					}
					if($i%$line_nums==0||$i==$count){
						$group_site_str.="</tr>";
						$checked_sites_str	.= "</tr>";
					}
					if($i%$line_nums==0&&$count>$i){
						$group_site_str.="<tr gr_tr_id='{$j}' site='yes'>";
						$checked_sites_str	.= "</tr>";
					}
					$i++;
				}
				$j++;
			}
		}
	}
}
###############项目选择区域####################
if($value_switch== 'yes'){
	$line_nums		= '8';//每行显示的个数
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
		/*if($_POST['action']=='alone_set'){
			$vid_json_arr= $info['alone_vid'];
		}else if($_POST['action']=='merger_set'){
			$vid_json_arr= $info['merger_vid'];
		}else{
			$vid_json_arr= $info['vid'];
		}*/
		$vid_json_arr= $info['alone_vid'];
		if(@in_array($xm_rs['id'],$vid_json_arr)){
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

}
###################项目选择区域 结束###############
echo temp("any_data/ajax_choose_site_value.html");
?>
