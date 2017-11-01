<?php
/**
 * 功能：采样记录表信息的填写、查看、打印
 * 作者：zhengsen
 * 时间：2014-04-15
*/
include '../temp/config.php';
//导航
$trade_global['daohang'][]	= array('icon'=>'','html'=>'样品接收','href'=>"./cy/cy_ys_list.php?site_type={$_GET['site_type']}&cy_date={$_GET['cy_date']}&year={$_GET['year']}&month={$_GET['month']}&jie={$_GET['jie']}");
$_SESSION['daohang']['cy_ys_list']	= $trade_global['daohang'];
if(empty($u['userid'])){
	nologin();
}
$fzx_id=$u['fzx_id'];//分中心id
$sql = "SELECT *  FROM cy  where fzx_id='".$fzx_id."'";
if( !isset($_GET['site_type']) )
    $_GET['site_type'] = "全部" ;
if( $_GET["site_type"] != "全部" ) {
    $sql .= " AND site_type ={$_GET['site_type']}";
}
//print_rr($_GET);
if( !$_GET['cy_date'] ){
    $_GET['cy_date'] = date( "Y-m" );
}
if( !$_GET['year'] ){
    $_GET["year"] = date( "Y" );
}
if( !$_GET['month'] ){
    $_GET["month"] = date( "m" );
}
if(!$_GET['jie']){
	$yjs = "AND cy.status='4'";
	$bwjs = 'selected';
	$byjs = '';
}elseif($_GET['jie'] == '已接收'){
	$yjs = "AND cy.status>'4'";
	$bwjs = '';
	$byjs = 'selected';
}else{
	$yjs = "AND cy.status='4'";
	$bwjs = 'selected';
	$byjs = '';
}
$sql .= "  AND cy_date LIKE '{$_GET['cy_date']}%' $yjs GROUP BY cy.status,cy.id ORDER BY cy.status ASC ,cy.cy_user_qz_date DESC,cy.cy_date DESC, cyd_bh DESC ";
//echo $sql;
$res = $DB->query( $sql );
$result = array();
while( $row = $DB->fetch_assoc($res) )
{
	if($row['json']!=''){
        $cy_json   = json_decode($row['json'],true);
    }else{
        $cy_json   = array();
    }
    $row["site_total"] = count( elementsToArray( $row["sites"] ) );
    $result[] = $row;
}
foreach($result as $key=>$data)
{
	$cy_ys_str='';
	if($data['status']>='4'){
		$cy_ys_str="<a href='dayin_biaoqian.php?cyd_id={$data['id']}'>设置编号</a>|<a href='cy_ys.php?cyd_id={$data['id']}'>采样验收记录表</a>";
	}
	$i = $key+1;
	if(!empty($data['cy_user'])&&!empty($data['cy_user2'])){
		$cy_users=$data['cy_user'].' 、'.$data['cy_user2'];
	}else{
		$cy_users=$data['cy_user'].$data['cy_user2'];	
	}
	$cyd_bh="<a href=\"dayin_biaoqian.php?cyd_id={$data[id]}\">{$data[cyd_bh]}</a>";
	//让admin可以方便的看到cyd_id，方便维护
	$cyd_id	= '';
	if($u['admin'] == '1' && $show_zt != '演示'){
		$cyd_id	= "<font color='#D88376'>(id:{$data[id]})</font>";
	}
	$lines.=temp("ys_list_line.html");
}
//获得任务类型
$site_type_list="<option value='全部' >全部</option>";
foreach($global['site_type'] as $key=>$value){
	if($_GET['site_type']=="$key"){
		$site_type_list.="<option selected='selected' value=".$key.">".$value."</option>";
	}else{
		$site_type_list.="<option value=".$key.">".$value."</option>";
	}
}
//所有年
$year_data[] = $_GET["year"];
for( $i = date('Y'); $i >= 2005; $i-- )
    if( $i != $_GET['year'] ) 
        $year_data[] = $i;

$month_data[] = $_GET["month"];

$year_list = disp_options( $year_data );
//所有月
$rs_month = $DB->fetch_one_assoc("SELECT month(cy_date) as m FROM `cy` WHERE `fzx_id`='$fzx_id' AND year(cy_date)='{$_GET['year']}' AND month(cy_date)>'".date('m')."' GROUP BY month(cy_date) ORDER BY month(cy_date) DESC LIMIT 1");
if($rs_month['m']){
	$month_max	= $rs_month['m'];
}else{
	$month_max = ( $_GET['year'] == date('Y') ) ? (int)date('n') : 12;
}
$month_data = array( $_GET["month"]);
for( $i = $month_max; $i >= 1; $i-- ) {
    $month_text = ( $i < 10 ) ? "0{$i}" : $i;
    if( $month_text != $_GET['month'] )
        $month_data[] = $month_text;
}
$month_list = disp_options( array_unique($month_data) );
disp("ys_list.html");

