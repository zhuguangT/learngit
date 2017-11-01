<?php
/**
 * 功能：下达采样任务页面上点击 "添加新批次按钮"加载的页面
 * 作者：韩枫
 * 日期：2014-08-14
 * 描述
*/
include("../temp/config.php");
$fzx_id = $u['fzx_id'];
$site_type	= get_str($_GET['site_type']);//temp/global.inc.php 中定义的站点类别
if(!array_key_exists($site_type,$global['site_type'])){
        $site_type      = '0';
};
########取出所有的统计参数
$tjcs_name_arr	= array();
$sql_tjcs       = $DB->query("SELECT * FROM `n_set` WHERE (fzx_id='$fzx_id' OR 1) AND module_name='tjcs'");
while($rs_tjcs  = $DB->fetch_assoc($sql_tjcs)){
        $tjcs_name_arr[$rs_tjcs['id']]       = $rs_tjcs['module_value1'];
}
//获取站点有没有多个垂线和层面
$sql_site_line_vertical         = array();
$sql_site_line_vertical     = $DB->query("SELECT * FROM `sites` WHERE fzx_id='$fzx_id' OR fp_id='$fzx_id'  AND (`site_code`!='' OR `site_code` is not NULL) ORDER BY tjcs,site_name");
while ($rs_site_line_vertical= $DB->fetch_assoc($sql_site_line_vertical)) {
        //奇怪，sql里面的为空限制不管用。这里只能再加一道关
        if($rs_site_line_vertical['site_code'] !=''){
                $site_line_vertical[$rs_site_line_vertical['site_code']][$rs_site_line_vertical['water_type']][]    = 1;
                //$site_line_vertical[$rs_site_line_vertical['site_code']][]    = $rs_site_line_vertical['site_code'];
        }
}
#######取出所有的水样类型并存到数组中
$water_type_arr = array();
$water_type_sql = $DB->query("SELECT * FROM `leixing` WHERE 1");
while ($rs_water_type   = $DB->fetch_assoc($water_type_sql)) {
        $water_type_arr[$rs_water_type['id']]   = $rs_water_type['lname'];
}
#####看有没有传过来group_name名称，如果传过来不查询此 group_name的记录
$sql_sites_where        = '';
/*if(!empty($_GET['group_name'])){
        $sql_sites_where        = " AND gr.group_name!='{$_GET['group_name']}' ";
}*/
########分中心自己添加的站点
$sql_sites      = $DB->query("select gr.id as gr_id,gr.group_name,si.site_name,si.id,si.tjcs from `site_group` as gr left join `sites` as si on gr.site_id=si.id where gr.fzx_id='$fzx_id' and gr.site_type='$site_type' AND si.site_mark<>'fc_site' and gr.act='1' AND site_name!='未添加' $sql_sites_where order by gr.sort asc,gr.group_name,si.sort,si.id");
$group_arr	= $tjcs_arr = $site_tjcs_arr	= $site_id_arr	= array();
$tjcs_option	= $group_name_option = $lines	= '';
while($rs_sites = $DB->fetch_assoc($sql_sites)){
        //判断相同站码但水样类型不同的站点
        $line_vertical  = '';
        if(count($site_line_vertical[$rs_sites['site_code']])>1){
        $line_vertical  .= "(".$water_type_arr[$rs_sites['water_type']].")";
        }
        //判断出该站点的垂线和层面
        if(count($site_line_vertical[$rs_sites['site_code']][$rs_sites['water_type']])>1){
                $str_site_line   = $global['site_line'][$rs_sites['site_line']];
                $str_site_vertical      = $global['site_vertical'][$rs_sites['site_vertical']];
                $line_vertical  .= "(".$str_site_line.$str_site_vertical.")";
        }
	$site_id_arr[]	= $rs_sites['id'];
	//记录批次名称
	if($rs_sites['group_name']==''){
		$rs_sites['group_name']	= '未分配批次的站点';
	}
	if($rs_sites['tjcs']==''){
		$rs_sites['tjcs']	= '未分配属性的站点';
	}
        if(!in_array($rs_sites['group_name'],$group_arr)){
                $group_arr[]     = $rs_sites['group_name'];
		$group_name_option .= "<option value='{$rs_sites['group_name']}'>{$rs_sites['group_name']}</option>";
		$lines  .= "<div class='old_sites' style='clear:left;font-weight:bold;text-align:center;background-color:#99CCFF;' tjcs='{$rs_sites['tjcs']}' group_id='{$rs_sites['group_name']}'>{$rs_sites['group_name']}</div>";
        }
	if(!in_array($rs_sites['tjcs'],$site_tjcs_arr)){
		$sites_tjcs      = array_filter(explode(",",$rs_sites['tjcs']));
        	asort($sites_tjcs);
		foreach($sites_tjcs as $value){
			$tjcs_id	= $value;
			if($tjcs_id==''){
                        	$value  = '未分配统计参数';
                	}else{
                        	if(!empty($tjcs_name_arr[$tjcs_id])){
                        	        $value   = $tjcs_name_arr[$tjcs_id];
                        	}else{
                        	        continue;
                        	        //这里的参数要么是分中心加的，要么就是被开发人员误删了。
                        	        $value   = '系统未能识别该统计参数，请联系管理员';
                        	}
                	}
			if(!in_array($tjcs_id,$tjcs_arr)){
				$tjcs_arr[]      = $tjcs_id;
                        	$tjcs_option    .= "<option value=',{$tjcs_id},'>{$value}</option>";
			}
			$site_tjcs_arr[]	 = $rs_sites['tjcs'];
		}
	}
        if($_GET['group_name']==$rs_sites['group_name']){
                $lines  .= "<label style='background-color:#C9F2D1;' title='{$rs_sites['river_name']}.{$rs_sites['site_name']}$line_vertical' class='old_sites site_label' tjcs='{$rs_sites['tjcs']}' group_id='{$rs_sites['group_name']}'><input type='checkbox' site_id='{$rs_sites['id']}' value='{$rs_sites['gr_id']}' tishi='{$rs_sites['group_name']}' checked />{$rs_sites['site_name']}<font color='#9B9898'>$line_vertical</font></label>";
        }else{
	       $lines	.= "<label title='{$rs_sites['river_name']}.{$rs_sites['site_name']}$line_vertical' class='old_sites site_label' tjcs='{$rs_sites['tjcs']}' group_id='{$rs_sites['group_name']}'><input type='checkbox' site_id='{$rs_sites['id']}' value='{$rs_sites['gr_id']}' tishi='{$rs_sites['group_name']}' />{$rs_sites['site_name']}<font color='#9B9898'>$line_vertical</font></label>";
        }
}
//总站分配给分中心的站点
$site_names_arr	= $sites_arr	= array();
$fp_lines	= $site_options	= $tjcs_fp_options	= '';
$sql_fp_sites   = $DB->query("SELECT si.*,gr.id AS gr_id,gr.group_name FROM `sites` AS si LEFT JOIN `site_group` as gr ON si.id=gr.site_id AND si.fzx_id=gr.fzx_id WHERE si.fzx_id!='$fzx_id' AND si.fp_id='$fzx_id' AND gr.act='1' ORDER BY gr.group_name");
while($rs_fp_sites= $DB->fetch_assoc($sql_fp_sites)){
        //判断相同站码但水样类型不同的站点
        $line_vertical  = '';
        if(count($site_line_vertical[$rs_fp_sites['site_code']])>1){
        $line_vertical  .= "(".$water_type_arr[$rs_fp_sites['water_type']].")";
        }
        //判断出该站点的垂线和层面
        if(count($site_line_vertical[$rs_fp_sites['site_code']][$rs_fp_sites['water_type']])>1){
                $str_site_line   = $global['site_line'][$rs_fp_sites['site_line']];
                $str_site_vertical      = $global['site_vertical'][$rs_fp_sites['site_vertical']];
                $line_vertical  .= "(".$str_site_line.$str_site_vertical.")";
        }
	$new_sites	= '';
	if(!in_array($rs_fp_sites['id'],$site_id_arr)){
		$new_sites	= "new_sites";
	}
	//$sites_tjcs     = array_filter(explode(",",$rs_fp_sites['tjcs']));
	//asort($sites_tjcs);
	//站点名称的下拉菜单
        if(!in_array($rs_fp_sites['id'],$site_names_arr)){
                $site_names_arr[]        = $rs_fp_sites['id'];
                $site_options   .= "<option value='{$rs_fp_sites['id']}' new_sites='$new_sites' site_tjcs='{$rs_fp_sites['tjcs']}'>{$rs_fp_sites['site_name']}$line_vertical</option>";
        }
	//根据统计参数 分别存储信息
        /*if(empty($sites_tjcs)){
                $sites_tjcs[]   = '';
        }*/
        //foreach($sites_tjcs as $value){
                //$tjcs_id      = $value;
		$tjcs_id	= $rs_fp_sites['group_name'];
                if($tjcs_id==''){
                        $value  = '未分配统计参数';
                }else{
                        if(!empty($tjcs_name_arr[$tjcs_id])){
                                $value  = $tjcs_name_arr[$tjcs_id];
                        }else{
                                //continue;
                                //这里的参数要么是分中心加的，要么就是被开发人员误删了。
                                $value  = '系统未能识别该统计参数，请联系管理员';
                        }
                }
		//统计参数名称及信息
                if(empty($sites_arr[$tjcs_id])){
			$tjcs_fp_options	.= "<option new_sites='$new_sites' value='{$tjcs_id}'>{$value}</option>";
			$sites_arr[$tjcs_id]	 = "<div class='fp_sites' new_sites='$new_sites' style='clear:left;font-weight:bold;text-align:center;background-color:#99CCFF;' tjcs='{$tjcs_id}'>{$value}</div>";
		}
		//站点信息
		$sites_arr[$tjcs_id]	.= "<label title='{$rs_fp_sites['river_name']}.{$rs_fp_sites['site_name']}$line_vertical' class='fp_sites site_label' new_sites='$new_sites' tjcs='{$tjcs_id}'><input type='checkbox' site_id='{$rs_fp_sites['id']}' value='{$rs_fp_sites['gr_id']}' tishi='{$value}' />{$rs_fp_sites['site_name']}<font color='#9B9898'>$line_vertical</font></label>";
	//}
}
##############显示每种统计类型的站点
$fp_lines  = '';
foreach($sites_arr as $key=>$value){
        $fp_lines  .= $value;
}
$fp_sites       = $fp_sites_div = '';
//这里可以改成 判断 hub表，是不是总中心的fzx_id???
if(!empty($sites_arr)){
        $fp_sites       = "<li><a href=\"#tabs-3\" id=\"k3\">总站分配的站点</a></li>";
        $fp_sites_div   = "<div id='tabs-3'>
                                <label><input type='checkbox' value='' id='fp_new_sites' />未分配批次的站点</label>
                                统计属性:<select class='chosen' id='fp_tjcs'><option value='全部'>全部</option>$tjcs_fp_options</select>
                                站点名称:<select class='chosen' id='fp_site_name'><option value='全部'>全部</option>$site_options</select>
                                <div style='text-align:left;margin-top:20px;'>
                                        $fp_lines
                                        <div style='clear:both;'></div>
                                </div>
                        </div>";
}
echo temp("site_add.html");
?>
