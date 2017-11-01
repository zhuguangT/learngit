<?php
/**
 * 功能：单项质控表列表页
 * 作者：Mr Zhou
 * 日期：2014-08-19
 * 描述：单项质控表列表页
*/
include ('../..//temp/config.php');
$fzx_id = FZX_ID;
//导航
$trade_global['daohang'] = array(
    array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
    array('icon'=>'','html'=>'单项质控表列表页','href'=>'./zkb/danxiang_zk/dx_zk_list.php')
);

if(!$_GET['year'])	$_GET['year']	=	date('Y');
if(!$_GET['month'])	$_GET['month']	=	date('m');
$sql_where=" AND YEAR(`cy`.`cy_date`) = '{$_GET['year']}'"; 
//如果年份是今年择取当前月 否则取12月
$maxmonth = ($_GET['year'] == date('Y')) ? (int)date('n') : 12; 
for($i=1;$i<=$maxmonth;$i++){
    $_month=($i<10) ? '0'.$i : $i;
    if($_month!=$_GET['month']) $month_list.="<option value='$_month'>$_month</option>";
}
if($_GET['month']!='全部'){
	$sql_where.=" and month(`cy`.`cy_date`)='{$_GET['month']}'";
	$month_list.='<option value="全部">全部</option>';
}
for($i=date('Y');$i>=$begin_year;$i--){
	if($i!=$_GET['year']) $year_list.="<option value='$i'>$i</option>";
}
if(!$_GET['status']){
	$_GET['status']='全部';
}

$_GET['site_type'] = trim($_GET['site_type']);
if(!$_GET['site_type'] ){
	$_GET['site_type'] = '全部';
}
$site_type = $global['site_type'];
$site_flag = array_flip($site_type);

if($_GET["site_type"] != '全部'){
	$site_type_num = $site_flag[$_GET['site_type']];
}
$_site_types = disp_options( $site_type );
if(trim($_GET['site_type'])!='全部'){
	$sql_where .= " and `cy`.`site_type`='$site_type_num'";
}

$sql="SELECT id,cy_date,status,group_name,cyd_bh  FROM `cy` WHERE `fzx_id` = '$fzx_id' $sql_where AND status>='5'";
$R = $DB->query($sql);
$arr =  array();
while($r=$DB->fetch_assoc($R)){
	$arr[$r['group_name']]['id'][] = $r['id'];
	$arr[$r['group_name']]['cy_date'] = $r['cy_date'];
	$arr[$r['group_name']]['group_name'] = $r['group_name'];
    $arr[$r['group_name']]['cyd_bh']   =  $r['cyd_bh']; //加一个采样单编号
}
foreach($arr as $valuex){
	$valuex['id'] = implode(',', $valuex['id']);
	$operation="<a align=center target=_self href='$rooturl/zkb/danxiang_zk/baogao_list.php?id=$valuex[id]&month=$_GET[month]&year=$_GET[year]&xun=$valuex[group_name]'>质控报告</a>";
	$lines.=temp('zkb/danxiang_zk/dx_zk_list_line.html');
}
disp('zkb/danxiang_zk/dx_zk_list');

?>