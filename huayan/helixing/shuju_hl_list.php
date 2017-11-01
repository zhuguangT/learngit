<?php
/**
 * 功能：分配测试任务列表
 * 作者：zhengsen
 * 时间：2014-06-15
**/
include '../../temp/config.php';
require_once '../../inc/cy_func.php';
//导航
$trade_global['daohang'][]	= array('icon'=>'','html'=>'数据合理性分析列表','href'=>"$rooturl/huayan/helixing/shuju_hl_list.php?site_type={$_GET['site_type']}&cy_date={$_GET['cy_date']}&year={$_GET['year']}&month={$_GET['month']}");
$_SESSION['daohang']['shuju_hl_list']	= $trade_global['daohang'];
if(!$u['userid']){
	nologin();
}
$fzx_id=$u['fzx_id'];
$sql = "SELECT * FROM cy WHERE status > '5' ";
if( $_GET['site_type']=='' ){
	$_GET['site_type'] = "全部" ;
}
if( $_GET['site_type'] != "全部" ){
	$sql .= " AND site_type = '".$_GET['site_type']."' ";
}
if( !$_GET['cy_date'] ){
	$_GET['cy_date'] = date( "Y-m" );
}
if( !$_GET['year'] ){
	$_GET["year"] = date( "Y" );
}
if( !$_GET['month'] ||($_GET['year'] == date('Y')&&$_GET['month']>=date('m'))){
	$_GET["month"] = date( "m" );
}

$sql .= " AND cy_date LIKE '{$_GET[cy_date]}%'  AND fzx_id='".$fzx_id."' ORDER BY `cy_date`,`id` DESC ";
$res = $DB->query( $sql );
$result = array();
$i= 0;
while( $row = $DB->fetch_assoc($res) ) {
	$tid_sql	= $DB->fetch_one_assoc("SELECT group_concat(`vid`) AS vid FROM `assay_pay` WHERE `cyd_id`='{$row['id']}' AND `vid` in (121,187,186,198,114,104,118,119,'172','162','173','174','182','190','665','189','188')");
	$tid_arr	= array();
	if(!empty($tid_sql['vid'])){
		$tid_arr	= explode(',',$tid_sql['vid']);
	}
	//判断改批次是否有可判断的条件，检测结果是否齐全
	//总氮为空121  186,187,198三氮全为空 五日118、化学119有一个为空   186/198/114均有值
	$show_yes	= '';
	if(in_array('121',$tid_arr) && (in_array('186',$tid_arr) || in_array('187',$tid_arr) || in_array('198',$tid_arr))){
		$show_yes	= 'yes';
	}else if(in_array('118',$tid_arr) && in_array('119',$tid_arr)){
		$show_yes	= 'yes';
	}else if(in_array('114',$tid_arr) && in_array('186',$tid_arr) && in_array('189',$tid_arr)){
		$show_yes	= 'yes';
	}
	$ion_arr	= array(172,162,173,174,182,190,665,189,188);
	$ion_arr	= array_intersect($ion_arr,$tid_arr);
	if(count($ion_arr) >=5){
		$show_yes       = 'yes';
	}
	if($show_yes != 'yes'){
			$operation="<div>检测值不足</div>";
	}else{  //已生成化验单
		$operation="<div><a href='$rooturl/huayan/helixing/shuju_hl.php?cyd_id={$row['id']}'>查看合理性</a></div>";
		$i++;
		$csrw_list_lines.=temp("helixing/shuju_hl_list_line.html");
	}
}
if(empty($csrw_list_lines)){
	$csrw_list_lines	= "<tr><td colspan='6' title='合理性分析条件：\n(1)采样批次已经生成化验单\n(2)合理性分析所需要的项目，已化验出结果值'>本月暂无批次可以进行合理性分析</td></tr>";
}
//获得任务类型
$site_type_list="<option value='全部'>全部</option>";
foreach($global['site_type'] as $key=>$value){
	if($_GET['site_type']=="$key"){
		$site_type_list.="<option value=".$key." selected='selected'>".$value."</option>";
	}else{
		$site_type_list.="<option value=".$key.">".$value."</option>";
	}
}

$year_data[] = $_GET["year"];
$begin_year = empty($begin_year) ? 2005:$begin_year;
for( $i = date('Y'); $i >= $begin_year; $i-- )
	if( $i != $_GET['year'] ) 
		$year_data[] = $i;


//$month_data[] = $_GET["month"];

$year_list = disp_options( $year_data );
//所有月
$month_max = ( $_GET['year'] == date('Y') ) ? (int)date('n') : 12;

$month_data = array( $_GET["month"]);
for( $i = $month_max; $i >= 1; $i-- ) {
	$month_text = ( $i < 10 ) ? "0{$i}" : $i;
	if( $month_text != $_GET['month']){
		$month_data[] = $month_text;
	}
}
$month_list = disp_options( array_unique($month_data) );
disp("helixing/shuju_hl_list.html");
?>

