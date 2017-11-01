<?php
/**
 * 功能：采样任务列表
 * 作者：zhengsen
 * 时间：2014-04-15
**/
include '../temp/config.php';
//导航
$trade_global['daohang'][] = array('icon'=>'','html'=>'采样任务列表','href'=>'./cy/cyrw_list.php');
$_SESSION['daohang']['cyrw_list'] = $trade_global['daohang'];
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

$sql .= "  AND cy_date LIKE '{$_GET['cy_date']}%' GROUP BY cy.status,cy.id ORDER BY cy.status ASC ,cy.cy_user_qz_date DESC,cy.cy_date DESC, cyd_bh DESC  ";
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
    $row['status_text'] = $global['status'][$row['status']];
    if(!empty($cy_json['退回']) && empty($row['sh_user_qz'])){//这里是被退回的采样单
    	if(!empty($row['cy_user_qz']) || !empty($row['cy_user_qz2'])){
    		$row['status_text']     = '<font color=red>退回任务已签字</font>';
    	}else{
        	$row['status_text']     = '<font color=red>采样记录被退回</font>';
    	}
    }

    //正式运行后删除 系统管理员的删除全部的权限
	if($u['admin']||$u['system_admin']||( ($u['xd_cy_rw'] || $u['xd_csrw'])&&$row['status']<='2' )){
		$row["delete"] = "|<a class='red icon-remove bigger-130' title=\"删除\" href='javascript:if(confirm(\"一旦删除，无法恢复，你确实要删除吗？\")) location=\"modify_cyd.php?action=删除&cyd_id=".$row[id]."\"'></a>";
	}
  
    $result[] = $row;
}
foreach($result as $key=>$data)
{
	$cy_record_str='';
	if($data['status']>='1'){
		$cy_record_str="|<a href='cy_record.php?cyd_id={$data['id']}'>采样记录表</a>";
	}
	$modi_csrw_tzd_str='';
	if($data['status']<'5'){
		//样品接收人签字时候就不再显示修改及确认采样任务
		$modi_csrw_tzd_str = "|<a href='modi_csrw_tzd.php?cyd_id={$data['id']}' title='修改及确认采样任务'>修改</a>";
	}
	$i = $key+1;
	/*if($u['ypjs']&&$u['userid']==$data['sh_user_qz']||$u['admin']){
		$cyd_bh="<a href=\"site_code.php?cyd_id={$data[id]}\">{$data[cyd_bh]}</a>";
	}else{
		$cyd_bh=$data['cyd_bh'];
	}*/
	if(!empty($data['cy_user'])&&!empty($data['cy_user2'])){
		$cy_users=$data['cy_user'].' 、'.$data['cy_user2'];
	}else{
		$cy_users=$data['cy_user'].$data['cy_user2'];	
	}
	//委托任务不显示批名
	if($data['site_type']=='3'){
		$data['group_name'] = '委托任务（真实名称已隐藏）';
	}
	$cyd_bh="<a href=\"site_code.php?cyd_id={$data[id]}\">{$data[cyd_bh]}</a>";
	//让admin可以方便的看到cyd_id，方便维护
	$cyd_id	= '';
	if($u['admin'] == '1' && $show_zt != '演示'){
		$cyd_id	= "<font color='#D88376'>(id:{$data[id]})</font>";
	}
	$lines.=temp("cyrw_list_line.html");
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
disp("cyrw_list.html");
