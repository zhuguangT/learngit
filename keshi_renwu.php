<?php
include ('./temp/config.php');
$fzx_id = FZX_ID;
//if($_GET['action'] != 'ajax' && $_GET['action'] != 'print')echo "<p align='center' style='font-weight:bold;padding-top:60px;font-size:16px;'>工程师调试中...</p>";
//年月的下拉菜单
$_GET['year'] = empty($_GET['year'])?date('Y'):$_GET['year'];
$year_data = array($_GET["year"]);
$begin_year = empty($begin_year) ? 2005:$begin_year;
for( $i = date('Y'); $i >= $begin_year; $i-- ){
    if( $i != $_GET['year'] ) {
        $year_data[] = $i;
    }
}

$userid = $u['userid'];
$year_list = disp_options( $year_data );
//所有月
$_GET['month'] = empty($_GET['month'])?date('m'):$_GET['month'];
$month_list = '';
$month_max = 12;
for($i=$month_max;$i>=1;$i--){
    $select = ($i==intval($_GET['month']))? 'selected' : '';
    $month_list .= '<option '.$select.' value="'.($i<10?'0'.$i:$i).'">'.($i<10?'0'.$i:$i).'月</option>';
}
//获取n_set表中相应科室的人员配置(这个功能是临时使用的，这里就直接写死了)
if($_GET['keshi'] == 'shengwu'){
	$title	= '生物室';
	$keshi_users	= array("于容梅","冷冰琦","姚琳","测试1","测试2");
}else if($_GET['keshi'] == 'lihua'){
	$title	= '理化室';
	$keshi_users	= array("谢文莉","郝媛","郑舰","杨慧贤","张宣","张昊坤","程名","齐凤","栾洪文","测试1","测试2");
}else{
	$_GET['keshi'] = 'yiqi';
	$title	= '仪器室';
	$keshi_users	= array("孙朝杰","张肇荔","杨泽芳","王晶","王玮","陈漪洁","陈瀚","吴婧","测试1","测试2");
}
$keshi_users	= "'".implode("','",$keshi_users)."'";
//签字处理
$qzarr = array();
if($_POST['qzdate']==''){
	$_POST['qzdate'] = $_GET['year'].'-'.$_GET['month'];
}
$bzsql = $DB->query("select * from n_set where module_name='hzqz' and module_value2 ='".$_POST['qzdate']."'");
while($qzrow = $DB->fetch_assoc($bzsql)){
	$qzarr[$qzrow['module_value1']] = $qzrow['module_value3'];
}
$keshi = $_GET['keshi'];
if($keshi){
	$jy = $keshi.'jy';
	$jyren = $qzarr[$jy];
	$fy = $keshi.'fy';
	$fyren = $qzarr[$fy];
}
//到pay表中找到相应人员的tid
$cy_date	= '';
if(!empty($_GET['year'])){
	$cy_date	.= $_GET['year'];
}else{
	$cy_date	.= date('Y');
}
if(!empty($_GET['month'])){
	if(strlen($_GET['month']) == 1){
		$_GET['month']	= "0".$_GET['month'];
	}
	$cy_date	.= "-".$_GET['month']."-01";
}else{
	$cy_date	.= "-".date('m')."-01";
}

$where_date	= date('Y-m-01',strtotime($cy_date));
$where_month= " AND  cy.cy_date<='".date("Y-m-d",strtotime("$where_date +1 month -1 day"))."'";
$sql_tid	= $DB->query("SELECT cy.group_name,ap.id,ap.cyd_id,cy.jcwc_date,cy.cy_date,cy.site_type FROM  `assay_pay` as ap INNER JOIN `cy` ON ap.cyd_id=cy.id WHERE  ap.`fzx_id` = '$fzx_id' AND cy.cy_date>='$where_date' $where_month AND (ap.`userid` in ($keshi_users) OR ap.`userid2` in ($keshi_users) )");
$tids		= '';
$cyd_name	= $cyd_wc = $cyd_cy = $cyd_flag = array();
$site_type_str=array(1=>'常规',2=>'临时',3=>'委托');
while($rs_tid = $DB->fetch_assoc($sql_tid)){
	$tids	.= $rs_tid['id'].",";
	$cyd_name[$rs_tid['cyd_id']]	= $rs_tid['group_name'];
	$cyd_wc[$rs_tid['cyd_id']]      = $rs_tid['jcwc_date'];
	$cyd_cy[$rs_tid['cyd_id']]      = $rs_tid['cy_date'];
	$cyd_type[$rs_tid['cyd_id']]      = $site_type_str[$rs_tid['site_type']];
}
if(!empty($tids)){
	$tids	= substr($tids,0,-1);
}else{
	$json	= array();
	$str_main	= "<tr><td></td><td></td><td></td></tr>
	<tr><td></td><td>{$_GET['year']}年{$_GET['month']}月没有任何化验任务</td><td></td></tr>
	<tr><td></td><td></td><td></td></tr>";
	if($_GET['action'] == 'ajax'){
		$json['tb'] = $str_main;
        	$json['mc'] = '';
        	echo json_encode($json);
	}else{
		disp("keshi_renwu.html");
	}
	exit;
}
//根据tid到order表中查找出相应的一个编号检测的项目 hy_flag>=0
$order_str	= array();
$sql_order	= $DB->query("SELECT * FROM `assay_order` WHERE tid in ({$tids}) AND hy_flag>=0 ORDER BY cyd_id,SUBSTR(bar_code,-4 ,4),vid");
while($rs_order = $DB->fetch_assoc($sql_order)){
	if(stristr($rs_order['bar_code'],"KB")){
		$rs_order['bar_code']	= str_replace('KB', '空白样', $rs_order['bar_code']);
	}
	if(empty($order_str[$rs_order['cyd_id']][$rs_order['bar_code']]['num'])){
		$order_str[$rs_order['cyd_id']][$rs_order['bar_code']]['num']	= 0;
	}
	$order_str[$rs_order['cyd_id']][$rs_order['bar_code']]['num']++;
	$order_str[$rs_order['cyd_id']][$rs_order['bar_code']]['name']	.= $_SESSION['assayvalueC'][$rs_order['vid']]."、";
}
//print_rr($cyd_name);
//print_rr($order_str);
$str_main	= '';
$pistr = '<tr><td>序号</td><td>批名</td><td>任务性质</td></tr>';
$xuhao = '1';
foreach ($order_str as $cyd_id => $value) {
	if($_GET['action'] == 'print'){
		$dayin = "";
		if($_GET['xianshi']){
			$xianshiid = explode(',',$_GET['xianshi']);
			if(!in_array($cyd_id,$xianshiid)){
				continue;
			}
		}
	}else{
		$dayin = "<input type='checkbox' name='cypi[]' id='cypi' value='$cyd_id' checked>";
	}
	if($first_group_name!=$cyd_id){
		$str_main	.= "<tr><td colspan='3' style='font-weight:bold;word-spacing:233px;text-align:right;'>{$cyd_name[$cyd_id]} 采样日期：{$cyd_cy[$cyd_id]}<p/>检测完成日期：{$cyd_wc[$cyd_id]}</td></tr><tr><th style='width:135px;'>样品编号</th><th>检测项目</th><th nowrap>数量</th></tr>";
		$pistr .= "<tr><td>$xuhao</td><td><input type='checkbox' name='cypi[]' id='cypi' value='$cyd_id' keshi='".$_GET['keshi']."' st='{$cyd_type[$cyd_id]}' onclick='jishu()' >{$cyd_name[$cyd_id]}</td><td>$cyd_type[$cyd_id]</td></tr>";
		$first_group_name	= $cyd_id;
	}
	foreach($value as $bar_code => $valueC){
		if(!empty($valueC)){
			$valueC['name']	= substr($valueC['name'],0,-3);
		}
		$str_main	.= "<tr><td>{$bar_code}</td><td align=left>{$valueC['name']}</td><td>{$valueC['num']}</td></tr>";
	}
	$xuhao++;
}
$json = array();
if($_GET['action'] == 'ajax'){
	$json['tb'] = $str_main;
	$json['mc'] = $pistr;
	echo json_encode($json);
}else if($_GET['action'] == 'print'){
	disp("keshi_renwu_print.html");
}else{
	disp("keshi_renwu.html");
}
?>
