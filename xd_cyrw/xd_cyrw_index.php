<?php
/**
 * 功能：下达采样任务页面
 * 作者：韩枫
 * 日期：2014-04-21
 * 描述
*/
include("../temp/config.php");
//跳转到监督任务页面
if($_GET['site_type']=='0'){
        gotourl("$rooturl/xd_cyrw/xd_jdrw_index.php?site_type={$_GET['site_type']}");
}
if(!empty($global['site_type'][$_GET['site_type']])){
	$xdcy_title	= '下达'.$global['site_type'][$_GET['site_type']];
}else{
	$xdcy_title	= '下达采样任务';
}
//导航
$trade_global['daohang'][] = array('icon'=>'','html'=>$xdcy_title,'href'=>$_SESSION['url_stack'][0]);
$_SESSION['daohang']['xd_cyrw_index'] = $trade_global['daohang'];

$trade_global['js'] = array('jquery.date_input.js');
$trade_global['css'] = array('date_input.css');
//登陆及权限判断
if($u['xd_cy_rw']!='1' && $u['xd_csrw']!='1'){
	//跳转到登陆页
	echo "没有权限";
	exit;
}
$fzx_id		= $u['fzx_id'];
$disabled='disabled=disabled';
//$xdcy_title="下达采样任务";
//如果是修改测试任务通知单调用下达采样页面
if($_GET['action']=='load'){
	//$xdcy_title="修改采样任务";
	$xdcy_title= str_replace('下达','修改',$xdcy_title);
	$disabled2='disabled=disabled';	
}
$site_type	= get_str($_GET['site_type']);//temp/global.inc.php 中定义的站点类别
if(!array_key_exists($site_type,$global['site_type'])){
	$site_type	= '1';
}
$cy_date	= date('Y-m-d');
#########取出本单位所有的站点类别/任务类型/任务性质
$site_type_options	= "";
foreach($global['site_type'] as $key=>$value){
	if($site_type == $key){
		$site_type_options .= "<option value='$key' selected>$value</option>";
	}else{
		$site_type_options .= "<option value='$key'>$value</option>";
	}
}

################取出所有流域
$site_area_options	= "<option value='全部'>全部</option>";
$where_sql_area	= '';
$sql_area		= $DB->query("SELECT si.area FROM `sites` AS si  WHERE si.site_type='$site_type' AND si.fzx_id='$fzx_id' OR si.fp_id='$fzx_id' $where_sql_area GROUP BY si.area ORDER BY si.area");
while ($rs_area = $DB->fetch_assoc($sql_area)){
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
//采样单位默认为“委托方”的任务类型
if(@in_array($site_type,$global['cy_flag_site_type'])){
	$cy_checked	= '';
	$bcy_checked	= 'checked';
}else{
	$cy_checked	= 'checked';
	$bcy_checked	= '';
}
#########取出全程序空白项目
$qckb_value     = $DB->fetch_one_assoc("select module_value1 from `n_set` where fzx_id='$fzx_id' and module_name='qckb_value' and module_value2='$site_type' order by id desc limit 1");
$qckb_value_arr	= @explode(',',$qckb_value['module_value1']);
$qckb_value_all_num	= count($qckb_value_arr);
#######选择全程序空白时，是否默认选中室内空白
$checked_snkb	= '';
$create_snkb_old	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='xdcy'");
if(!empty($create_snkb_old['module_value1'])){
	$create_snkb_arr	= json_decode($create_snkb_old['module_value1'],true);
	if($create_snkb_arr['create_snkb'] == 'yes'){
		$checked_snkb	= 'checked';
	}
}
#########取出现场平行项目
$xcpx_value     = $DB->fetch_one_assoc("select module_value1 from `n_set` where fzx_id='$fzx_id' and module_name='xcpx_value' and module_value2='$site_type' order by id desc limit 1");
$xcpx_value_arr	= @explode(',',$xcpx_value['module_value1']);
$xcpx_value_all_num	= count($xcpx_value_arr);
#########各中心设置的现场检测项目
$xcjc_value	= $DB->fetch_one_assoc("SELECT  module_value1 FROM `n_set` WHERE fzx_id='$fzx_id' AND module_name='xcjc_value' order by id desc limit 1");
$xcjc_value_arr	= array_filter(@explode(',',$xcjc_value['module_value1']));
#########取出相同"任务类型"的上一批次任务所做的现场检测项目
$old_xcjc_value	= array();
$cy_last	= $DB->fetch_one_assoc("SELECT id FROM `cy` WHERE site_type='".$site_type."' AND fzx_id='".$fzx_id."' AND `xc_exam_value`!='' ORDER BY id DESC LIMIT 1 ");//这里现场检测的项目没有变化，但是由于有的批次不检测某项目而导致默认失败。临时这么加上，后期可以根据所选批次来默认现场检测项目
$sql_old_xcjc_value	= $DB->query("select vid from `assay_pay`  where fzx_id='".$fzx_id."' AND is_xcjc='1' AND cyd_id='".$cy_last['id']."' ");
while($rs_old_xcjc_value= $DB->fetch_assoc($sql_old_xcjc_value)){
	$old_xcjc_value[]	= $rs_old_xcjc_value['vid'];
}
//$xcjc_value_num	= count($old_xcjc_value);
#########取出本单位检测的所有的检测项目并对应显示 现场检测项目、全程空白项目、现场平行项目
$xcjc_value_checkbox1	= $xcjc_value_checkbox = $qckb_values = $xcpx_values = '';
$sql_xcjc_value	= $DB->query("SELECT xm.id,xm.value_C,xm.fenlei,xm.is_xcjc FROM `xmfa` AS fa LEFT JOIN `assay_value` AS xm ON fa.xmid=xm.id WHERE fa.fzx_id='$fzx_id' AND fa.act='1' AND fa.mr='1' GROUP BY fa.xmid");
$qckb_value_num	= $xcpx_value_num	= $xcjc_value_num	= 0;//可检测项目数量改成不检测项目数量
while($rs_xcjc_value = $DB->fetch_assoc($sql_xcjc_value)){
	//默认现场检测项目
	if($rs_xcjc_value['is_xcjc']=='1' && (empty($xcjc_value_arr) || in_array($rs_xcjc_value['id'],$xcjc_value_arr))){
		if(in_array($rs_xcjc_value['id'],$old_xcjc_value)){
			$xcjc_value_num++;
			$xcjc_value_checkbox1	.= "<label><input type='checkbox' name='xcjc_value[]' value='{$rs_xcjc_value['id']}' checked>{$rs_xcjc_value['value_C']}</label>";
		}else{
			$xcjc_value_checkbox	.= "<label><input type='checkbox' name='xcjc_value[]' value='{$rs_xcjc_value['id']}'>{$rs_xcjc_value['value_C']}</label>";
		}
	}
	//显示全程序空白项目
	if(!in_array($rs_xcjc_value['id'],$qckb_value_arr)){
		$qckb_value_num++;
		$qckb_values	.= $rs_xcjc_value['value_C'].'、';
	}
	//显示现场平行项目
	if(!in_array($rs_xcjc_value['id'],$xcpx_value_arr)){
		$xcpx_value_num++;
		$xcpx_values	.= $rs_xcjc_value['value_C'].'、';
	}
}
if($qckb_values == ''){
	if($qckb_value_all_num==0){
		$qckb_value_num = 0;
		$qckb_values	= '未设置项目';
	}else{
		$qckb_value_num	= $qckb_value_all_num;
		$qckb_values	= '全部项目均可检测';
	}
}else{
	$qckb_values	= substr($qckb_values,0,-3)."(admin注意:此处显示的是不检测的项目，但设置时依然要设置检测哪些项目。)";//去除最后的“、”
}
if($xcpx_values == ''){
	if($xcpx_value_all_num==0){
		$xcpx_value_num = 0;
		$xcpx_values	= '未设置项目';
	}else{
		$xcpx_value_num	= $xcpx_value_all_num;
		$xcpx_values	= '全部项目均可检测';
	}
}else{
	$xcpx_values	= substr($xcpx_values,0,-3)."(admin注意:此处显示的是不检测的项目，但设置时依然要设置检测哪些项目。)";//去除最后的“、”
}
$qckb_modify	= $xcpx_modify = '';
if($u['admin']=='1'){//全程序空白可检测项目设置 ，与现场平行样可检测项目设置 只有admin可以设置
	$qckb_modify	= "<tr>
                        <td colspan='6'>
                        	全程序空白项目($qckb_value_num 项)<a class='green icon-edit bigger-130' href=\"values_modify.php?action=qckb_value&site_type=$site_type\" target=\"_blank\" title='修改'></a>
                            现场平行项目($xcpx_value_num 项)<a class='green icon-edit bigger-130' href=\"values_modify.php?action=xcpx_value&site_type=$site_type\" target=\"_blank\" title='修改'></a>
                        </td>
                </tr>";
	$xcpx_modify	= '';
}
$xcjc_value_checkbox	= $xcjc_value_checkbox1.$xcjc_value_checkbox;//把默认选中的现场检测项目放到一起
########取出所有采样员
$sql_cy_user	= $DB->query("SELECT * FROM `users` WHERE fzx_id='$fzx_id' and `group`!='0' and `group`!='测试组' and `cy`='1' order by userid");
//如果获得了采样单的id
$sid_arr=array();
$xcpx_sid_arr=array();
if($_GET['cyd_id']){
	$cy_sql="SELECT cy_user,cy_user2,cy_date,sites,snkb FROM `cy` WHERE id='".$_GET['cyd_id']."'";
	$cy_rs=$DB->fetch_one_assoc($cy_sql);
	$_GET['group_name']     = $cy_rs['group_name'];//get传值有缺陷，这里直接用数据库取出来的值代替git传值
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
$option_user	= '';
while($rs_cy_user=$DB->fetch_assoc($sql_cy_user)){
	$selected	='';
	$selected2	='';
	if($cy_user==$rs_cy_user['userid']&&!empty($cy_user)){
		$selected	= "selected=selected";
	}
	if($cy_user2==$rs_cy_user['userid']&&!empty($cy_user2)){
		$selected2	= "selected=selected";
	}
	$option_user   .= "<option {$selected} value='{$rs_cy_user['userid']}'>{$rs_cy_user['userid']}</option>";
	$option_user2  .= "<option {$selected2} value='{$rs_cy_user['userid']}'>{$rs_cy_user['userid']}</option>";
}
########取出所有站点及批次信息
$group_options	= $site_lines	= '';
$old_group_num	= $i	= 0;
$site_num	= 1;
$group_sites_lines	= $group_lines_arr	= array();
//先计算每个批次有多少个站点
$sql_group_num	= $DB->query("SELECT group_name,count(site_id) AS `sites_num` FROM `site_group` WHERE fzx_id='$fzx_id' AND site_type='$site_type' AND act='1' AND `group_name`!='' AND `group_name` IS NOT null GROUP BY `group_name` ORDER BY ctime DESC");
while($rs_group_num= $DB->fetch_assoc($sql_group_num)){
	$group_sites_lines[$rs_group_num['group_name']]['nums']	= 0;
	$group_sites_lines[$rs_group_num['group_name']]['sites_num']	= $rs_group_num['sites_num'];
}
//获取站点有没有多个垂线和层面
$sql_site_line_vertical		= array();
$sql_site_line_vertical     = $DB->query("SELECT * FROM `sites` WHERE fzx_id='$fzx_id' OR fp_id='$fzx_id' AND (`site_code`!='' OR `site_code` is not NULL) ORDER BY tjcs,site_name");
while ($rs_site_line_vertical= $DB->fetch_assoc($sql_site_line_vertical)) {
		//奇怪，sql里面的为空限制不管用。这里只能再加一道关
		if($rs_site_line_vertical['site_code'] !=''){
			$site_line_vertical[$rs_site_line_vertical['site_code']][$rs_site_line_vertical['water_type']][]    = 1;//$rs_site_line_vertical['site_code'];
		}
}
#######取出所有的水样类型并存到数组中
$water_type_arr	= array();
$water_type_sql	= $DB->query("SELECT * FROM `leixing` WHERE 1");
while ($rs_water_type	= $DB->fetch_assoc($water_type_sql)) {
	$water_type_arr[$rs_water_type['id']]	= $rs_water_type['lname'];
}
//批次修改时，要加批次名称的条件（批名改动时？）
$group_name_str	= '';
if($_GET['group_name']){
	$group_name_str="AND gr.group_name='".$_GET['group_name']."'";
}
$where_sql_sites	= '';
if(!empty($_GET['area']) && $_GET['area']!='全部'){
	if($_GET['area']=='未填写流域'){
		$where_sql_sites	.= " AND (si.area='' OR si.area is null) ";
	}else{
		$where_sql_sites	.= " AND si.area='{$_GET['area']}' ";
	}
}
//以批次添加顺序来获取批次名称
$group_sort	= array();
if(in_array($global['site_type'][$site_type],array('委托任务','临时任务'))){
	$group_sort_sql	= $DB->query("SELECT `group_name`,max(`ctime`) AS px FROM `site_group` as gr WHERE gr.fzx_id='$fzx_id' and gr.site_type='$site_type' and gr.act='1' ".$group_name_str." AND gr.`group_name`!='' AND gr.`group_name` IS NOT null group by `group_name` ORDER BY gr.sort asc,px desc,gr.`group_name`");
	while($group_sort_rs = $DB->fetch_assoc($group_sort_sql)){
		$group_sort[]	= $group_sort_rs['group_name'];
	}
}
//获取批次及站点信息
$group_sites_waters	= array();
$sql_sites	= $DB->query("select gr.id as gr_id,gr.group_name,gr.assay_values,gr.xcpx_values,gr.sort as gr_sort,si.* from `site_group` as gr INNER JOIN `sites` as si on gr.site_id=si.id where si.site_mark != 'fc_site' and gr.fzx_id='$fzx_id' and gr.site_type='$site_type' and gr.act='1' ".$group_name_str." $where_sql_sites AND gr.`group_name`!='' AND gr.`group_name` IS NOT null order by gr.sort asc,gr.group_name,gr.site_sort,gr.ctime DESC");
while($rs_sites = $DB->fetch_assoc($sql_sites)){
	//判断相同站码但水样类型不同的站点
    $line_vertical  = '';
    if(count($site_line_vertical[$rs_sites['site_code']])>1){
    	$line_vertical	.= "(".$water_type_arr[$rs_sites['water_type']].")";
    }
    //判断出该站点的垂线和层面
    if(count($site_line_vertical[$rs_sites['site_code']][$rs_sites['water_type']])>1){
		$str_site_line   = $global['site_line'][$rs_sites['site_line']];
		$str_site_vertical      = $global['site_vertical'][$rs_sites['site_vertical']];
		if($rs_sites['site_type']>1){//非常规任务的站点不让显示层面垂线
			$line_vertical .= "";
		}else{
            $line_vertical .= "(".$str_site_line.$str_site_vertical.")";
		}
    }
	//站点所测的项目数量
	if(!empty($rs_sites['assay_values'])){//解决没选项目时，项目数量的判断失误问题
		$site_values_num= count(@explode(',',$rs_sites['assay_values']));
	}else{
		$site_values_num= 0;
	}
	//现场检测项目的数量
        if(!empty($rs_sites['xcpx_values'])){
                $xcpx_values_num= "(".count(@explode(',',$rs_sites['xcpx_values']))." 项)";
        }else{
		$xcpx_value_zt	= "xcpx_value_zt='no'";//在项目更改时，有这个标识的现场平行项目数也要相应更改
                $xcpx_values_num= "(".$site_values_num." 项)";//未设置现场平行项目时，默认显示站点项目的数量
		//$xcpx_values_num= '(默认)';
                //$xcpx_values_num= 0;
        }
	$sid_checked	= '';
	$xcpx_sid_checked	= '';
	if(in_array($rs_sites['id'],$sid_arr)){
		$sid_checked	= "checked=checked";
		$px_disabled	= '';
	}
	if(in_array($rs_sites['id'],$xcpx_sid_arr)){
		$xcpx_sid_checked	= "checked=checked";
	}
	if(!in_array($rs_sites['id'],$sid_arr)){
		$px_disabled	= "disabled=disabled";
	}
	//如果站点中没有检测项目，这个站点将不允许选择
	if($site_values_num==0){
		$site_disabled	= "disabled=disabled";
	}else{
		$site_disabled	= '';
	}
	//如果是总站分配的站点，那分中心没有更改其项目的权限
	$url_sn = '';
	$url_sn = urlsafe_b64encode($rs_sites['site_name']);
	$site_value_num_click	= "fp_sites_id='' style='color:blue;cursor:pointer;' onclick=\"qckb_value_modify('$url_sn','site_value','{$rs_sites['gr_id']}','');\"";
	//记录每个批次的水样类型
	if(!@in_array($rs_sites['water_type'],$group_sites_waters[$rs_sites['group_name']])){
		$group_sites_waters[$rs_sites['group_name']][]	= $rs_sites['water_type'];
	}
	$url_group_name = urlsafe_b64encode($rs_sites['group_name']);
	//记录批次下站点的数量及信息
	$group_sites_lines[$rs_sites['group_name']]['nums']++;
	$group_sites_lines[$rs_sites['group_name']]['lines']	.= "<tr gr_name='{$rs_sites['group_name']}'>
                        <td align='left' style='padding-left:30px;min-width:175px;max-width:20%;' title='{$rs_sites['river_name']}.{$rs_sites['site_name']}$line_vertical'><label><input type='checkbox' name='{$rs_sites['group_name']}[sites][]' group_id='{$rs_sites['gr_id']}' {$sid_checked} value='{$rs_sites['id']}' group_name='{$rs_sites['group_name']}' $site_disabled />{$rs_sites['site_name']}<font color='#9B9898'>$line_vertical</font></label></td>
                        <td style='min-width:50px;'><span class='tishi_site_value_num' water_type='{$rs_sites['water_type']}' id='{$rs_sites['gr_id']}' gr_id='{$rs_sites['gr_id']}' $site_value_num_click>$site_values_num</span></td>
                        <td style='min-width:130px;'><label><input type='checkbox' {$px_disabled} name='{$rs_sites['group_name']}[xcpx][]' value='{$rs_sites['id']}' {$xcpx_sid_checked} />现场平行<span style=\"color:blue;cursor:pointer;\" onclick=\"qckb_value_modify('{$rs_sites['group_name']}','xcpx','{$rs_sites['gr_id']}','{$url_group_name}');\" class='xcpx_site' $xcpx_value_zt xcpx_group_name='{$rs_sites['group_name']}' xcpx_num_id='{$rs_sites['gr_id']}'>{$xcpx_values_num}</span></label></td>
                        <td align='center' class='action-buttons' style='min-width:80px;'>
                        	<a class='green' href='$rooturl/site/site_info.php?action=xdrw&site_type={$site_type}&site_id={$rs_sites['id']}&group_name={$rs_sites['group_name']}' title='修改站点“{$rs_sites['site_name']}”的信息'><i class='icon-pencil bigger-130'></i></a>
                        	<a class='red' href='#' title='删除 {$rs_sites['site_name']}' onclick=\"gotoif('$rooturl/site/site_delete.php?action=xd_cyrw&sid={$rs_sites['id']}&sgname={$rs_sites['group_name']}&fid={$rs_sites['fzx_id']}&site_type={$site_type}','确定删除 {$rs_sites['site_name']} 吗?');\"><i class='icon-remove bigger-130'></i></a>
                        </td>
                   </tr>";
	//记录批次信息（表格需要有合并行，所以要放到最后一个站点的时候才知道合并多少行）
	if($group_sites_lines[$rs_sites['group_name']]['nums']==$group_sites_lines[$rs_sites['group_name']]['sites_num']){
		$group_water_type= implode(",",$group_sites_waters[$rs_sites['group_name']]);
		$site_num	 = $group_sites_lines[$rs_sites['group_name']]['nums']+1;
		$group_name	 = $rs_sites['group_name'];
		$site_lines	 = $group_sites_lines[$rs_sites['group_name']]['lines'];
		//批次排序的标识
		$group_px	= array_search($rs_sites['group_name'],$group_sort);
		if(!$group_px && $group_px!==0){
			$group_px	= $rs_sites['group_name'];
		}
		$group_lines_arr[$group_px]	= temp("xd_cyrw_index_lines.html");
		$group_options	.= "<option value='{$rs_sites['group_name']}'>{$rs_sites['group_name']}</option>";//站点分批的下拉菜单
	}
}
if(!empty($group_sort)){
	ksort($group_lines_arr);
}
//循环显示批次及站点信息(防止新添加的站点导致排序问题，最后再处理显示)
$group_lines	= '';
foreach($group_lines_arr as $value){
	$group_lines	.= $value;
}
if($_GET['site_type']!='3'){
	$groupadd = "<span class=\"tianjia_button\" onclick=\"qckb_value_modify('','group_add','0')\">添加新批次</span>";
}else{
	$groupadd = "<a href='$rooturl/kehu/newwt.php?site_type=3' class=\"tianjia_button\">添加委托任务</a>";
}
disp("xd_cyrw_index.html");
?>
