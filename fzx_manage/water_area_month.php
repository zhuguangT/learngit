<?php
/*
 *功能：原子荧光仪器导入页面(砷、硒、汞)
 *作者：zhengsen
 *时间：2014-10-22
 */
include("../temp/config.php");
if(empty($u['userid'])){
	nologin();
}
$fzx_id=$u['fzx_id'];
if( !$_GET['year'] ){
    $_GET["year"] = date( "Y" );
}
if( !$_GET['month'] ){
    $_GET["month"] = date( "m" );
}
if(!$_GET['fzx_id']){
	$_GET['fzx_id']=$fzx_id;
}

if($fzx_id=='1'&&$_GET['action']!='1'){
	$hub_sql="SELECT * FROM hub_info ";
	$hub_query=$DB->query($hub_sql);
	$fzx_select="各监测中心：<select name='fzx_id' id='fzx_id' onchange='redirect()'><option value='全部'>全部</option>";
	while($hub_rs=$DB->fetch_assoc($hub_query)){
		if($_GET['fzx_id']==$hub_rs['id']){
			$fzx_select.="<option value=".$hub_rs['id']." selected='selected'>".$hub_rs['hub_name']."</option>";
		}else{
			$fzx_select.="<option value=".$hub_rs['id'].">".$hub_rs['hub_name']."</option>";
		}
	}
	$fzx_select.="</select>";
}

//查询统计参数
$x=0;
if($_GET['action']=='1'&&$fzx_id!='1'){
	$fzx_str="	AND (fzx_id='1' OR fzx_id='".$fzx_id."')";
}else{
	$fzx_str="AND fzx_id='1'";
}
$cs_sql="SELECT id,module_value1 FROM n_set WHERE module_name='tjcs'";
$cs_query=$DB->query($cs_sql);
while($cs_rs=$DB->fetch_assoc($cs_query)){
	$x++;
	$water_area_lines.="<tr><td>".$x."</td><td>".$cs_rs['module_value1']."</td><td><a target='blank' href='water_area_month_export.php?year={$_GET[year]}&&month={$_GET[month]}&&action=view&&tjcs={$cs_rs[id]}&&fzx_id={$_GET[fzx_id]}'>查看</a> | <a target='blank' href='water_area_month_export.php?year={$_GET[year]}&&month={$_GET[month]}&&action=load&&tjcs={$cs_rs[id]}&&fzx_id={$_GET[fzx_id]}'>下载</a></td></tr>";
}

$year_data[] = $_GET["year"];
for( $i = date('Y'); $i >= 2014; $i-- )
    if( $i != $_GET['year'] ) 
        $year_data[] = $i;


$year_list = disp_options( $year_data );
//所有月
$month_data[] = $_GET["month"];

//所有月
$month_max = ( $_GET['year'] == date('Y') ) ? (int)date('n') : 12;
$month_data = array( $_GET["month"]);
for( $i = $month_max; $i >= 1; $i-- ) {
    $month_text = ( $i < 10 ) ? "0{$i}" : $i;
    if( $month_text != $_GET['month'] )
        $month_data[] = $month_text;
}
$month_list = disp_options( array_unique($month_data) );
disp("water_area_month");
?>