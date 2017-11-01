<?php
/**
 * 功能：下达采样任务页面上点击 "添加新批次按钮"加载的页面
 * 作者：韩枫
 * 日期：2014-08-13
 * 描述
*/
include("../temp/config.php");
//导航
$trade_global['daohang'][]	= array('icon'=>'','html'=>'新增委托任务','href'=>"$rooturl/kehu/newkh.php");
$_SESSION['daohang']['newkh']	= $trade_global['daohang'];

$fzx_id	= $u['fzx_id'];
$i	= 0;
//获取站点有没有多个垂线和层面
$sql_site_line_vertical         = array();
$sql_site_line_vertical     = $DB->query("SELECT * FROM `sites` WHERE fzx_id='$fzx_id' OR fp_id='$fzx_id' ORDER BY tjcs,site_name");
while ($rs_site_line_vertical= $DB->fetch_assoc($sql_site_line_vertical)) {
        $site_line_vertical[$rs_site_line_vertical['site_code']][$rs_site_line_vertical['water_type']][]    = 1;
        //$site_line_vertical[$rs_site_line_vertical['site_code']][]    = $rs_site_line_vertical['site_code'];
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

$title	= '新增委托任务';
$button_str	= "确认添加";
//委托数据参数
if(!$_GET['kid']){
	$_GET['kid'] = '1';
}
$lxr = $lxtel = $dizhi = '';
$wtdw = "<select name='wtdw' id='wtdw' width='200px'>";
$allsql = $DB->query("select * from kehu where act = 1");
while($re1 = $DB->fetch_assoc($allsql)){
	if($_GET['kid'] == $re1['id']){
		$wtdw .= "<option value='$re1[id]' selected>$re1[name]";
		$lxr = $re1['lxr'];
		$lxtel = $re1['tel'];
		$dizhi = $re1['dizhi'];
	}else{
		$wtdw .= "<option value='$re1[id]'>$re1[name]";
	}
}
if($_GET['wtid']){
	$wt = $DB->fetch_one_assoc("select * from kehu_wt where id='".$_GET['wtid']."'");
	$button_str ='确认修改';
	$xiu_flag = "xiugai";
}
if($_GET['fuzhi']){	
	$button_str ='确认复制';
}
//不能写在上面的条件里
	if($wt['wt_type']=='长期委托'){
		$wt_leixing = "<input type=\"radio\" name=\"fenlei\" value=\"临时委托\" />临时委托&nbsp;&nbsp;<input type=\"radio\" name=\"fenlei\" value=\"长期委托\" checked/>长期委托";
	}else{
		$wt_leixing = "<input type=\"radio\" name=\"fenlei\" value=\"临时委托\" checked/>临时委托&nbsp;&nbsp;<input type=\"radio\" name=\"fenlei\" value=\"长期委托\"/>长期委托";
	}
	if($wt['ypcz']=='留样'){
		$chuzhi = '<input type="radio" name="ypcz" value="处理" />处理&nbsp;&nbsp;<input type="radio" name="ypcz" value="留样" checked/>留样';
	}else{
		$chuzhi = '<input type="radio" name="ypcz" value="处理" checked/>处理&nbsp;&nbsp;<input type="radio" name="ypcz" value="留样"/>留样';
	}
	if($wt['bglq']=='邮寄'){
		$jf = '<input type="radio" name="bglq" value="自取" />自取&nbsp;&nbsp;<input type="radio" name="bglq" value="邮寄" checked/>邮寄';
	}else{
		$jf = '<input type="radio" name="bglq" value="自取" checked/>自取&nbsp;&nbsp;<input type="radio" name="bglq" value="邮寄"/>邮寄';
	}
	if($wt['cyfs']=='采样'){
		$jffs = '<input type="radio" name="cyfs" value="采样" checked  />采样&nbsp;&nbsp;<input type="radio" name="cyfs" value="送样" />送样&nbsp;&nbsp;<input type="radio" name="cyfs" value="送样" />现场检测';
	}elseif($wt['cyfs']=='现场检测'){
		$jffs = '<input type="radio" name="cyfs" value="采样"/>采样&nbsp;&nbsp;<input type="radio" name="cyfs" value="送样"/>送样&nbsp;&nbsp;<input type="radio" name="cyfs" value="送样"  checked />现场检测';
	}else{
		$jffs = '<input type="radio" name="cyfs" value="采样" />采样&nbsp;&nbsp;<input type="radio" name="cyfs" value="送样" checked/>送样&nbsp;&nbsp;<input type="radio" name="cyfs" value="送样" />现场检测';
	}
	if($wt['wt_xz']=='机密'){
		$wt_xz = '<input type="radio" name="wz_xz" value="保密" />保密&nbsp;&nbsp;<input type="radio" name="wz_xz" value="机密" checked/>机密&nbsp;&nbsp;<input type="radio" name="wz_xz" value="绝密" />绝密&nbsp;&nbsp;<input type="radio" name="wz_xz" value="无要求" />无要求';
	}elseif($wt['wt_xz']=='绝密'){
		$wt_xz = '<input type="radio" name="wz_xz" value="保密" />保密&nbsp;&nbsp;<input type="radio" name="wz_xz" value="机密" />机密&nbsp;&nbsp;<input type="radio" name="wz_xz" value="绝密" checked/>绝密&nbsp;&nbsp;<input type="radio" name="wz_xz" value="无要求" />无要求';
	}elseif($wt['wt_xz']=='无要求'){
		$wt_xz = '<input type="radio" name="wz_xz" value="保密" />保密&nbsp;&nbsp;<input type="radio" name="wz_xz" value="机密" />机密&nbsp;&nbsp;<input type="radio" name="wz_xz" value="绝密" />绝密&nbsp;&nbsp;<input type="radio" name="wz_xz" value="无要求" checked />无要求';
	}else{
		$wt_xz = '<input type="radio" name="wz_xz" value="保密" checked/>保密&nbsp;&nbsp;<input type="radio" name="wz_xz" value="机密" />机密&nbsp;&nbsp;<input type="radio" name="wz_xz" value="绝密" />绝密&nbsp;&nbsp;<input type="radio" name="wz_xz" value="无要求" />无要求';
	}
if($wt['wt_date']==''){
	$wt['wt_date'] = date("Y-m-d");
}
$site_label = '';
if($wt['sites']!=''&&$wt['group_ids']){
	$s_arr = explode(',',$wt['sites']);
	foreach($s_arr as $svv){
		$svsql = $DB->fetch_one_assoc("select s.id,s.site_name,sg.id as gid,sg.group_name from sites as s left join site_group as sg on s.id=sg.site_id where s.id='$svv' and sg.id in (".$wt['group_ids'].") limit 1");
		$wt['group_name'] = $svsql['group_name'];//这里主要是为了避免批次名称在下达采样任务界面被修改了
		if($svsql['site_name']){
			$site_label .= '<label class="group_sites" title=".'.$svsql['site_name'].'{'.$wt['group_name'].'}"><input name="sites[]" site_id="'.$svv.'" value="'.$svv.'" value1="'.$svsql['gid'].'" checked="" type="checkbox"><input name="gids['.$svv.']" value="'.$svsql['gid'].'" type="hidden">'.$svsql['site_name'].'</label>';
		}
	}
}
$wtdw .= "</select>";
	
disp("kehu/wt_pi.html");
?>
