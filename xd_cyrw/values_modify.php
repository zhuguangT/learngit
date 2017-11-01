<?php
/**
 * 功能：项目修改页面（包括全程空白、现场平行、站点项目,批次项目的修改）
 * 作者：韩枫
 * 日期：2014-04-24
 * 描述
*/
include("../temp/config.php");

//登陆及权限判断
if($u['xd_cy_rw']!='1' && $u['xd_csrw']!='1'){
        //跳转到登陆页
        echo "没有权限";
        exit;
}
if($_GET['site_name']){
	$_GET['site_name'] = urlsafe_b64decode($_GET['site_name']);
}
$fzx_id 	= $u['fzx_id'];
$site_type      = get_str($_GET['site_type']);//temp/global.inc.php 中定义的站点类别
$_GET['group_name']	= urlsafe_b64decode($_GET['group_name']);
$action		= "qckb_value";
$fp_sites	= '';
$close		= "<span id='close' style=\"position: absolute;float:right;top:0;right:0px;width:60px;height:60px;background-color:#C7C2BC;cursor: pointer;opacity:0.8;margin-right:60px;z-index: 999\"><img src=\"$rooturl/img/close.png\" width=\"60px\" height=\"60px\" title=\"点>击关闭本页\" alt=\"关闭\" /></span>";
if($_GET['action']=='qckb_value'){//全程序空白项目设置
	$action	= 'qckb_value';
	$title	= '全程序空白';
	$close	= '';
}else if($_GET['action']=='xcpx_value'){//现场平行项目设置
	$action = 'xcpx_value';
        $title  = '现场平行';
	$close	= '';
}else if($_GET['action']=='xdrw'){//批次全程序空白项目设置
	$title	= $_GET['group_name'].'全程序空白';
	$action	= 'xdrw';
}else if($_GET['action']=='xdrw_xcpx'){//批次现场平行项目设置
	$title	= $_GET['site_name'].'现场平行';
	$action	= 'xdrw_xcpx';
}else if($_GET['action']=='site_value' || $_GET['action']=='jdrw_site_value'){//站点项目设置
	$title	= $_GET['site_name'];
	$action	= $_GET['action'];
	$id_html= $_GET['gr_id'];
}else if($_GET['action']=='group_value' || $_GET['action']=='jdrw_group_value'){//批次项目设置
	$title  = $_GET['group_name']."批次";
    $action = $_GET['action'];
	if($_GET['action']=='jdrw_group_value'){
		$id_html= $_GET['jdrw_sites'];
	}else{
		$id_html= $_GET['group_name'];
	}
}
//默认任务类型为 0
if(!in_array($site_type,array_keys($global['site_type']))){
        $site_type      = '0';
}
#########获取本任务类型下全程序空白/现场平行所测的项目(取出相应默认项目)
$moren_value_arr	= array();
if(in_array($action,array('qckb_value','xcpx_value'))){
	$rs_qckb_value	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE fzx_id='$fzx_id' AND `module_name`='$action' AND `module_value2`='$site_type' order by id desc limit 1");
	$moren_value_arr= @explode(',',$rs_qckb_value['module_value1']);
	$id_html	= $rs_qckb_value['id'];
}else if(!empty($_GET['qckb'])){//批次全程序空白默认项目
	$moren_value_arr= @explode(',',$_GET['qckb']);
}else if($_GET['action']=='xdrw_xcpx'){//批次现场平行项目设置
	//该任务类型下，现场平行可检测的项目
    $rs_qckb_value  = $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE fzx_id='$fzx_id' AND `module_name`='xcpx_value' AND `module_value2`='$site_type' order by id desc limit 1");
    $xcpx_value_arr = @explode(',',$rs_qckb_value['module_value1']);
	//$rs_xcpx_value	= $DB->fetch_one_assoc("SELECT id,assay_values,xcpx_values FROM `site_group` WHERE fzx_id='$fzx_id' AND `site_id`='{$_GET['sites']}' AND `group_name`='{$_GET['group_name']}'");
	$rs_xcpx_value	= $DB->fetch_one_assoc("SELECT assay_values,xcpx_values FROM `site_group` WHERE id='{$_GET['gr_id']}'");
	//监督任务时，要根据页面上存储的项目来设置
	if(!empty($_GET['site_value'])){
		$rs_xcpx_value['assay_values']	= $_GET['site_value'];
	}
	//批次现场平行默认项目
	$moren_value_arr= @explode(',',$rs_xcpx_value['xcpx_values']);
	if(empty($rs_xcpx_value['xcpx_values'])){
		$moren_value_arr	= $xcpx_value_arr;
	}
	$id_html	= $_GET['gr_id'];//$rs_xcpx_value['id'];
	//array_filter 是为了去除数组中的空值
        $values_sites   = implode(",",array_filter(array_intersect(explode(",",$rs_xcpx_value['assay_values']),$xcpx_value_arr)));
	if(!empty($values_sites)){
        	$where  = " and xm.id in ($values_sites) ";
	}else{
		$lines  = "该任务类型下没有配置'现场平行'可检测项目，请联系系统管理员。";
		disp("values_modify.html");
                exit;
        //	$where  = '';
	}
}else if($action=='site_value' || $action=='jdrw_site_value'){//取出站点项目作为默认选中项目
	$rs_site_value	= $DB->fetch_one_assoc("SELECT gr.assay_values,si.water_type FROM `site_group` AS gr INNER JOIN `sites` AS si ON gr.site_id=si.id WHERE gr.id='{$_GET['gr_id']}'");
	if(!empty($_GET['site_value'])){
		$moren_value_arr= @explode(',',$_GET['site_value']);
	}else{
		$moren_value_arr= @explode(',',$rs_site_value['assay_values']);
	}
	$water_type	= $rs_site_value['water_type'];
}
#########取出所有的项目模板
$xmmb_options	= '';
$sql_xmmb	= $DB->query("SELECT * FROM `n_set` WHERE fzx_id='$fzx_id' and module_name='xmmb'");// and module_value3='$site_type'");
while($rs_xmmb	= $DB->fetch_assoc($sql_xmmb)){
	$xmmb_options	.= "<option value='{$rs_xmmb['module_value1']}'>{$rs_xmmb['module_value2']}</option>";
}
#########取出本实验室检测的所有项目及分类
$where_qckb	= '';
if($action=='xdrw'){//取出已选站点拥有的项目
	$values_sites	 = array();
	if(!empty($_GET['sites'])){
		$where_qckb	= " AND `site_id` IN ({$_GET['sites']}) ";
	}
	//监督任务没有批次名称
	if($_GET['group_name']!='jdrw'){
		$where_qckb	.= " AND `group_name`='{$_GET['group_name']}' AND `act`='1' ";
	}
	//全程序空白项目配置
	$rs_qckb_value  = $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE fzx_id='$fzx_id' AND `module_name`='qckb_value' AND `module_value2`='$site_type' order by id desc limit 1");
        $qckb_value_arr	= @explode(',',$rs_qckb_value['module_value1']);
	if(!empty($where_qckb)){
		$sql_values_sites= $DB->query("SELECT id,assay_values FROM `site_group` WHERE fzx_id='$fzx_id' $where_qckb");
		while($rs_values_sites=$DB->fetch_assoc($sql_values_sites)){
			if(!empty($_GET['site_value'][$rs_values_sites['id']])){
				$rs_values_sites['assay_values']	= $_GET['site_value'][$rs_values_sites['id']];
			}
			$rs_values_sites= explode(',',$rs_values_sites['assay_values']);
			$values_sites	= array_merge($values_sites,$rs_values_sites);
		}
		//array_filter 是为了去除数组中的空值
		$values_sites	= implode(',',array_filter(array_unique(array_intersect($values_sites,$qckb_value_arr))));
		if(empty($values_sites)){
			$lines	= "该任务类型下没有配置'全程序空白'可检测项目，请联系系统管理员。";
			disp("values_modify.html");
                	exit;
		}
		//如果用户没有设置项目就默认选中全部符合的项目
		if(empty($moren_value_arr)){
			$moren_value_arr	= $qckb_value_arr;
		}
	}else{
		$values_sites	= implode(',',$qckb_value_arr);
	}
}else if($action=='site_value' || $action=='jdrw_site_value' || $action=='group_value' || $action=='jdrw_group_value'){//选出改水样类型下有方法且有标准的项目
	$values_sites	= array();
	if($action=='group_value' || $action=='jdrw_group_value'){
		$fp_sites       = "<input type='hidden' name='fp_sites' value='".substr($_GET['fp_sites_id'],0,-1)."' />";
		//取出本批次里的所有水样类型
		$group_water_arr= '';
		$group_water	= array();
		$where_group_water	= '';
		if($action=='group_value'){
			$where_group_water	.= " AND gr.`group_name`='{$_GET['group_name']}' ";
		}else{
			$where_group_water	.= "AND gr.id in(".$_GET['jdrw_sites'].") ";
		}
		$sql_group_water= $DB->query("SELECT sites.id,sites.water_type,sites.fzx_id FROM `site_group` AS gr LEFT JOIN `sites` ON gr.site_id=sites.id WHERE gr.fzx_id='$fzx_id' AND sites.fzx_id='$fzx_id' AND gr.`act`='1' $where_group_water GROUP BY sites.`water_type`");
		while($rs_group_water=$DB->fetch_assoc($sql_group_water)){
			$group_water[]	= $rs_group_water['water_type'];
		}
 		$water_type	= implode(",",$group_water);
	}
	//取出所有水样类型的父类
	$sql_fater_water= $DB->query("SELECT * FROM `leixing` WHERE id IN($water_type)"); 
	while($rs_fater_water=$DB->fetch_assoc($sql_fater_water)){
		if($rs_fater_water['parent_id']!=0){
			$water_type	.= ",".$rs_fater_water['parent_id'];
			$fater_water[$rs_fater_water['id']]	= $rs_fater_water['parent_id'];
		}else{
			$fater_water[$rs_fater_water['id']]	= $rs_fater_water['id'];
		}
	}
	/*//该水样类型下有标准的化验项目集合
	//$sql_jcbz_value	= $DB->query("SELECT bz.vid FROM `assay_jcbz` AS bz inner join `n_set` AS `set` ON bz.jcbz_bh_id=set.id WHERE set.module_value3='1' AND set.module_value2 IN($water_type) GROUP BY bz.vid");
	while($rs_jcbz_value = $DB->fetch_assoc($sql_jcbz_value)){
		$values_sites[]	= $rs_jcbz_value['vid'];
	}
	$xmfa_value_where	= implode(",",$values_sites);
	$values_sites	= array();
	//如果该水样类型下，没有找到任何检测标准
	if(empty($xmfa_value_where)){
		$rs_water_type	= $DB->fetch_one_assoc("SELECT `lname` FROM `leixing` where id='$water_type'");
		$lines = "水样类型: {$rs_water_type['lname']} 没有配制任何“检测标准”，请先配制检测标准";
		disp("values_modify.html");
		exit;
	}*/
	//该水样类型下有方法的化验项目集合
	//$sql_xmfa_value	= $DB->query("SELECT lxid,xmid FROM `xmfa` WHERE fzx_id='$fzx_id' AND lxid IN($water_type) AND mr='1' AND xmid in($xmfa_value_where) AND act='1'");
	$sql_xmfa_value	= $DB->query("SELECT lxid,xmid FROM `xmfa` WHERE fzx_id='$fzx_id' AND lxid IN($water_type) AND mr='1' AND act='1'");
	while($rs_xmfa_value = $DB->fetch_assoc($sql_xmfa_value)){
		if(array_key_exists($rs_xmfa_value['lxid'],$fater_water)){
			$rs_xmfa_value['lxid']	= $fater_water[$rs_xmfa_value['lxid']];
		}
		$water_values[$rs_xmfa_value['lxid']][]	= $rs_xmfa_value['xmid'];
		//$values_sites[]	= $rs_xmfa_value['xmid'];
	}
	//如果该水样类型下，没有找到任何检测方法
	if(empty($water_values)){
		$rs_water_type  = $DB->fetch_one_assoc("SELECT `lname` FROM `leixing` where id='$water_type'");
		$lines = "水样类型: {$rs_water_type['lname']} 没有配制任何“检测方法”，请先配制检测方法";
		disp("values_modify.html");
                exit;
	}
	if(count($water_values)>1){
		//多种水样类型时取交集
		$values_sites	= @call_user_func_array("array_intersect",$water_values);
	}else{
		//一种水样类型时 转换为一维数组
		$values_sites	= @array_values($water_values)[0];
	}
	//将水样类型数组转换为逗号隔开的字符串,供sql使用
	$values_sites	= @implode(",",$values_sites);
}
if(!empty($values_sites)){
	$where	= " and xm.id in ($values_sites) ";
}else{
	$where	= '';
}
$value_checked	= $value_checkbox = $value_options = '';
$checked_num	= $checkbox_num	  = $checked_value_num 	 = 0;
$fenlei_arr	= array();
$sql_xcjc_value	= $DB->query("SELECT xm.id,xm.value_C,xm.fenlei,xm.is_xcjc FROM `xmfa` AS fa INNER JOIN `assay_value` AS xm ON fa.xmid=xm.id WHERE fa.fzx_id='$fzx_id' AND fa.act='1' AND fa.mr='1' $where GROUP BY fa.xmid");
while($rs_xcjc_value = $DB->fetch_assoc($sql_xcjc_value)){
	//已经选中的项目
	if(empty($rs_xcjc_value['fenlei'])){
		$rs_xcjc_value['fenlei']	= '未分类';
	}
	if(!in_array($rs_xcjc_value['fenlei'],$fenlei_arr)){//根据项目分类显示项目
		$fenlei_arr[]	 = $rs_xcjc_value['fenlei'];
		$fenlei_num	 = count($fenlei_arr);
	}
	$value_options  .= "<option value='{$rs_xcjc_value['id']}'>{$rs_xcjc_value['value_C']}</option>";
	//根据条件默认选中项目
	if(in_array($rs_xcjc_value['id'],$moren_value_arr)){
		$checked_value_num++;
		if($checked_num<$fenlei_num){
			$value_checked  .= "<div class='checked_fenlei' classs='no' style='clear:both;background-color:#99CCFF;text-align:center;font-weight:bold;height:30px;line-height:30px;margin-bottom:1px;'>{$rs_xcjc_value['fenlei']}</div>";
			$checked_num     = $fenlei_num;
		}
		$value_checked	.= "<label class='show' style='float:left;margin-bottom:1px;margin-left:1px;height:43px;width:130px;border:1px #D7D7D7 solid;'><input type='checkbox' name='vid[]' value='{$rs_xcjc_value['id']}'checked />{$rs_xcjc_value['value_C']}</label>";
	}else{
		if($checkbox_num<$fenlei_num){
			$value_checkbox .= "<div class='checkbox_fenlei' classs='no' style='clear:both;background-color:#99CCFF;text-align:center;font-weight:bold;height:30px;line-height:30px;margin-bottom:1px;'>{$rs_xcjc_value['fenlei']}</div>";
			$checkbox_num    = $fenlei_num;
		}
		$value_checkbox	.= "<label class='show' style='float:left;margin-bottom:1px;margin-left:1px;height:43px;width:130px;border:1px #D7D7D7 solid;'><input type='checkbox' name='vid[]' value='{$rs_xcjc_value['id']}' />{$rs_xcjc_value['value_C']}</label>";
	}
}
#######显示界面
if($checked_value_num==0){//如果一个选中的项目都没有，就直接显示成全屏选项目的格式
	$lines	= "<div id='checkbox'>
			<p style='background-color:#FFCC99;position:fixed;top:90px;width:100%;'>
				请选择以下项目<span style='color:red;'>&nbsp;&nbsp;&nbsp;(已选择：<span id='num_tishi'>0</span> 项)</span>
				<input type='button' class='all_check' value='全选' />
				<input type='button' class='all_check' value='反选' />
			</p>
			$value_checkbox
			<div class='fixed' id='checkbox_fixed' style='width:100%;background-color:#99CCFF;text-align:center;font-weight:bold;height:30px;line-height:30px;display:none;'></div>
		</div>";
}else if($value_checkbox==''){
	$lines  = "<div id='checked' style='width:100%;float:left;border:1px #56932C solid;'>
                        <p style='position:fixed;top:90px;width:100%;background-color:#90CA1F;'>
                                目前已经选择的项目：<span id='checked_num'>$checked_value_num</span> 个
				<input type='button' class='all_checked' value='全选' />
				<input type='button' class='all_checked' value='反选' />
                        </p>
                        <div class='fixed' id='checked_fixed' style='width:100%;background-color:#99CCFF;text-align:center;font-weight:bold;height:30px;line-height:30px;display:none;'></div>
                        $value_checked
                </div>";
}else{//已选项目和未选项目分屏显示
	$lines	= "<div id='checked' style='width:50%;float:left;border:1px #56932C solid;'>
			<p style='position:fixed;top:90px;width:50%;background-color:#90CA1F;'>
				目前已经选择的项目：<span id='checked_num'>$checked_value_num</span> 个
				<input type='button' class='all_checked' value='全选' />
				<input type='button' class='all_checked' value='反选' />
			</p>
			<div class='fixed' id='checked_fixed' style='background-color:#99CCFF;text-align:center;font-weight:bold;height:30px;line-height:30px;display:none;'></div>
			$value_checked
		</div>
		<div id='checkbox' style='width:50%;float:left;border:1px #FFCC99 solid;'>
			<p style='position:fixed;top:90px;width:50%;background-color:#FFCC99;z-index:100'>
				还可以选择以下项目<span style='color:red;'>&nbsp;&nbsp;&nbsp;(已选择：<span id='num_tishi'>0</span> 项)</span>
				<input type='button' class='all_check' value='全选' />
				<input type='button' class='all_check' value='反选' />
			</p>
			<div class='fixed' id='checkbox_fixed' style='background-color:#99CCFF;text-align:center;font-weight:bold;height:30px;line-height:30px;display:none;'></div>
			$value_checkbox
		</div>";
}
$dhy_obj_json = json_encode($dhy_arr);
disp("values_modify.html");
?>
