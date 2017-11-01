<?php
/**
 * 功能：个人工作量统计
 * 作者: Mr Zhou
 * 日期: 2015-09-28 
 * 描述: 
*/
include '../temp/config.php';
$fzx_id=$_SESSION['u']['fzx_id'];

//导航
$daohang = array(
		array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
		array('icon'=>'','html'=>'工作量统计','href'=>$rooturl.'/user_manage/task_total.php'),
		array('icon'=>'','html'=>'【'.$_GET['uname'].'】工作量统计','href'=>$current_url)
);
$trade_global['daohang']	= $daohang;

//默认搜索 今年、全部任务性质、整个实验室的任务
if( !isset($_GET['site_type']) ){
	$_GET['site_type']	= '全部';
}
if( !$_GET['rw_type'] ){
	$_GET['rw_type']	= '检测任务';
}
if(!$_GET['year']){
	$_GET['year']		= date('Y');
}

//任务性质列表
$site_type_list	= '<option value="全部">全部</option>'.disp_options( $global['site_type'] ,1,$_GET['site_type']);
//年份列表
$year_data[]	= $_GET["year"];
$sql	= "SELECT DISTINCT year(cy_date) AS Y FROM cy where fzx_id='$fzx_id' ORDER BY year(cy_date) DESC";
$res	= $DB->query($sql);
while($row = $DB->fetch_assoc($res)) {
    if($row['Y'] != $_GET['year']) {
        $year_data[]	= $row['Y'];
    }
}
$year_list	= disp_options( $year_data );
//任务类型列表
$usarr	= array( $_GET['rw_type'], "检测任务" );
$rw_type_list	= disp_options( $usarr );
$site_type = '';
if($_GET['site_type'] != '全部'){
	$site_type = " AND cy.site_type = '{$_GET['site_type']}' ";
}
$uname = $_GET['uname'];
if($_GET['rw_type']	== '检测任务'){
	//AND hy_flag >= 0 AND ao.sid >= 0  需要问清指控样统不统计进总数里面
	$sql = "SELECT COUNT(ao.id) AS total, MONTH(cy.cy_date) month ,assay_element vname FROM assay_order ao LEFT JOIN assay_pay ap ON ap.id = ao.tid LEFT JOIN cy ON cy.id = ap.cyd_id
	WHERE cy.fzx_id='$fzx_id' AND YEAR(cy.cy_date) = '$_GET[year]' AND (sign_01='{$uname}' OR sign_012='{$uname}') $site_type GROUP BY MONTH(cy.cy_date) ,assay_element";
}else{
	//$sql = "SELECT COUNT(cy_rec.id) AS total FROM cy_rec LEFT JOIN cy ON cy.id = cy_rec.cyd_id WHERE cy.fzx_id='$fzx_id' AND YERA(cy.cy_date) = '$_GET[year]' AND cy_rec.sid >= 0  GROUP BY MONTH(cy.cy_date) ,cy.cy_user,cy.cy_user2";
}
$data = $total = array();
$query = $DB->query($sql);
while ($row=$DB->fetch_assoc($query)) {
	if(!empty($row['vname'])){
		//单项目总数统计
		$total[$row['vname']] += $row['total'];
		//月份总数统计
		$month_total_1[$row['month']] += $row['total'];
		//个人单月统计
		$data[$row['vname']][$row['month']] += $row['total'];
	}
}
if(!empty($month_total_1)){
	$month_total_1['total'] = array_sum($month_total_1);
}
//任务量排序
arsort ($total);
$line = '';
foreach ($total as $vname => $value) {
	$line .= '<tr><td>'.$vname.'</td>';
	for($i=1;$i<=12;$i++){
		$line .= '<td>'.$data[$vname][$i].'</td>';
	}
	$line .= '<td>'.$value.'</td></tr>';
}
$_GET['site_type']	= str_replace(' ','',$_GET['site_type']);//不知为何，变量前面有空格，这里给去掉
$title_site_type = '【'.$_GET['uname'].'】';
if($_GET['site_type'] != '全部'){
	$title_site_type .= $global['site_type'][$_GET['site_type']];//标题上的 任务性质提示
}
disp('user_manager/task_total');
?>
