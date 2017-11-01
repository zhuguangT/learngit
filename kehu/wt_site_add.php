<?php

include("../temp/config.php");
$fzx_id = $u['fzx_id'];
$site_type	= get_str($_GET['site_type']);//temp/global.inc.php 中定义的站点类别
if(!array_key_exists($site_type,$global['site_type'])){
        $site_type      = '3';
};
########取出所有的统计参数
$tjcs_name_arr	= array();
$sql_tjcs       = $DB->query("SELECT * FROM `n_set` WHERE (fzx_id='$fzx_id' OR 1) AND module_name='tjcs'");
while($rs_tjcs  = $DB->fetch_assoc($sql_tjcs)){
        $tjcs_name_arr[$rs_tjcs['id']]       = $rs_tjcs['module_value1'];
}
################取出所有水样的类型
$water_type_options = $kong = '';
$lei = $pid = array();
$sy = $DB->query("select * from leixing where fzx_id = 1 or fzx_id = 0 order by parent_id"); 
        $i = 0;
        while($sylx = $DB->fetch_assoc($sy)){
                
                if($sylx[parent_id]!=0){
                        $pid[$sylx[parent_id]] .= ','.$sylx[id];        
                        $lei[$sylx[parent_id]][$i][id] = $sylx[id];
                        $lei[$sylx[parent_id]][$i][lname] = '&nbsp;&nbsp;'.$sylx[lname];
                }else{
                        $lei[$sylx[id]][$i][id] = $sylx[id];
                        $lei[$sylx[id]][$i][lname] = $sylx[lname];
                }
                ++$i;
        }
        foreach($lei as $lk=>$lv){
                foreach($lv as $v){
                        if(!empty($pid[$v[id]])){
                                $va = $lk.$pid[$lk];
                                $water_type_options .= "<option value=$va>".$v[lname]."</option>";
                        }else{
                                $water_type_options .= "<option value=$v[id]>".$v[lname]."</option>";           
                        }
                }
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
$sql_sites      = $DB->query("select gr.id as gr_id,gr.group_name,si.site_name,si.id,si.tjcs,si.water_type from `site_group` as gr left join `sites` as si on gr.site_id=si.id where gr.fzx_id='$fzx_id' and gr.site_type='3' and gr.act='1' AND site_name!='未添加' $sql_sites_where order by gr.sort asc,gr.group_name,si.sort,si.id");
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
                $lines  .= "<div class='old_sites' style='clear:left;font-weight:bold;text-align:center;background-color:#99CCFF;' tjcs='{$rs_sites['tjcs']}' group_id='{$rs_sites['group_name']}' >{$rs_sites['group_name']}</div>";
          }
        if(!in_array($rs_sites['tjcs'],$site_tjcs_arr)){
                $sites_tjcs      = array_filter(explode(",",$rs_sites['tjcs']));
                asort($sites_tjcs);
                foreach($sites_tjcs as $value){
                        $tjcs_id        = $value;
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
                        $site_tjcs_arr[]         = $rs_sites['tjcs'];
                }
        }
          if($_GET['group_name']==$rs_sites['group_name']){
                    $lines  .= "<label style='background-color:#C9F2D1;' title='{$rs_sites['river_name']}.{$rs_sites['site_name']}$line_vertical' wy = '{$rs_sites['water_type']}' class='old_sites site_label' tjcs='{$rs_sites['tjcs']}' group_id='{$rs_sites['group_name']}'><input type='checkbox' site_id='{$rs_sites['id']}' value='{$rs_sites['gr_id']}' tishi='{$rs_sites['group_name']}' checked />{$rs_sites['site_name']}$line_vertical</label>";
          }else{
                 $lines .= "<label title='{$rs_sites['river_name']}.{$rs_sites['site_name']}$line_vertical' class='old_sites site_label' tjcs='{$rs_sites['tjcs']}' wy = '{$rs_sites['water_type']}' group_id='{$rs_sites['group_name']}'><input type='checkbox' site_id='{$rs_sites['id']}' value='{$rs_sites['gr_id']}' tishi='{$rs_sites['group_name']}' />{$rs_sites['site_name']}$line_vertical</label>";
          }
}
echo temp("kehu/wt_site_add.html");
?>
