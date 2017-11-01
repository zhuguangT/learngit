<?php
/**
 * 功能：工作量统计
 * 作者: Mr Zhou
 * 日期: 2015-09-28 
 * 描述: 
*/
include '../temp/config.php';
$fzx_id=$_SESSION['u']['fzx_id'];

//导航
$daohang = array(
		array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
		array('icon'=>'','html'=>'工作量统计','href'=>$current_url),
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
$usarr	= array( $_GET['rw_type'], '检测任务', '采样任务' );
$rw_type_list	= disp_options( $usarr );
$site_type = '';
if($_GET['site_type'] != '全部'){
	$site_type = " AND cy.site_type = '{$_GET['site_type']}' ";
}
if($_GET['rw_type']	== '检测任务'){
	//化验员分组信息
	$hyy_fz_data['全部']  = "全部";
	$hyy_fz_data['理化室'] = "'谢文莉','郝媛','郑舰','杨慧贤','张宣','张昊坤','程名','齐凤','栾洪文'";
	$hyy_fz_data['生物室'] = "'于容梅','冷冰琦','姚琳'";
	$hyy_fz_data['仪器室'] = "'孙朝杰','张肇荔','杨泽芳','王晶','王玮','陈漪洁','陈瀚','吴婧'";
	$hyy_fz_key = array_keys($hyy_fz_data);
	$hyy_fz = in_array($_GET['hyy_fz'],$hyy_fz_key) ? $_GET['hyy_fz'] : '全部';
	if('全部' != $hyy_fz){
		$fz_users = "AND `sign_01` IN ({$hyy_fz_data[$hyy_fz]})";
	}
	$hyy_fz_list = '科室分组：<select id="hyy_fz" onchange="redirect()">';
	foreach ($hyy_fz_key as $keshi) {
		$selected = ($keshi==$hyy_fz) ? 'selected' : '';
		$hyy_fz_list .= '<option value="'.$keshi.'" '.$selected.'>'.$keshi.'</option>';
	}
	$hyy_fz_list .= '</select>';
	//AND hy_flag >= 0 AND ao.sid >= 0  需要问清质控样统不统计进总数里面
	$sql = "SELECT COUNT(ao.id) AS total, MONTH(cy.cy_date) month ,sign_01 user1,sign_012 user2 FROM assay_order ao LEFT JOIN assay_pay ap ON ap.id = ao.tid LEFT JOIN cy ON cy.id = ap.cyd_id
	WHERE cy.fzx_id='$fzx_id' AND YEAR(cy.cy_date) = '$_GET[year]' $site_type $fz_users GROUP BY MONTH(cy.cy_date) ,sign_01,sign_012";
}else{
	$sql = "SELECT COUNT(cy_rec.id) AS total, MONTH(cy.cy_date) month,cy.cy_user user1,cy.cy_user2 user2 FROM cy_rec LEFT JOIN cy ON cy.id = cy_rec.cyd_id
		WHERE cy.fzx_id='$fzx_id' AND YEAR(cy.cy_date) = '$_GET[year]' AND cy_rec.sid >= 0  GROUP BY MONTH(cy.cy_date) ,cy.cy_user,cy.cy_user2";
}
$data_1 = $data_2 = $data_3 = $total = array();
$query = $DB->query($sql);
while ($row=$DB->fetch_assoc($query)) {
	if(!empty($row['user1'])){
		//第一化验员个人总数统计
		$total[$row['user1']] += $row['total'];
		//第一化验员月份总数统计
		$month_total_1[$row['month']] += $row['total'];
		//第一化验员个人单月统计
		$data_1[$row['user1']][$row['month']] += $row['total'];
		//总数统计
		$month_total_3[$row['month']] += $row['total'];
		$data_3[$row['user1']][$row['month']] += $row['total'];
	}
	if(!empty($row['user2'])){
		//第二化验员个人总数统计
		$total[$row['user2']] += $row['total'];
		//第二化验员月份总数统计
		$month_total_2[$row['month']] += $row['total'];
		//第二化验员个人单月统计
		$data_2[$row['user2']][$row['month']] += $row['total'];
		//总数统计
		$month_total_3[$row['month']] += $row['total'];
		$data_3[$row['user2']][$row['month']] += $row['total'];
	}
}
if(!empty($month_total_1)){
	$month_total_1['total'] = array_sum($month_total_1);
}
//任务量排序
arsort ($total);
$line = '';
foreach ($total as $uname => $value) {
	if($_GET['rw_type']	== '检测任务'){
		$line .= '<tr><td><a href="task_total_person.php?uname='.$uname.'&year='.$_GET['year'].'&rw_type='.$_GET['rw_type'].'&site_type='.$_GET['site_type'].'">'.$uname.'</a></td>';
	}else{
		$line .= '<tr><td>'.$uname.'</td>';
	}
	for($i=1;$i<=12;$i++){
		if(empty($data_3[$uname][$i])){
			$line .= '<td>-</td>';
		}else{
			$fuce = '';
			if(!empty($data_2[$uname][$i])){
				$fuce = '('.$data_2[$uname][$i].')';
			}
			$line .= '<td>'.$data_3[$uname][$i].$fuce.'</td>';
		}
	}
	$line .= '<td>'.$value.'</td></tr>';
}



$_GET['site_type']	= str_replace(' ','',$_GET['site_type']);//不知为何，变量前面有空格，这里给去掉
if($_GET['site_type'] != '全部'){
	$title_site_type= $global['site_type'][$_GET['site_type']];//标题上的 任务性质提示
}

disp('user_manager/task_total');


?>
