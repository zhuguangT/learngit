<?php
include ('./temp/config.php');
$fzx_id = FZX_ID;
//只检索最近两个月内数据
//$last_date = date('Y-m-01',strtotime('-2 months'));
//获取两个月内未检测的化验单所在的采样单
//$sql_cyd_id = $DB->query("SELECT cyd_id,vid FROM  `assay_pay` as ap  WHERE  ap.`fzx_id` = '$fzx_id' AND (ap.`userid` = '{$u['userid']}' OR ap.`userid2`='{$u['userid']}') AND (ap.`sign_01`!='{$u['userid']}' AND ap.`sign_012`!='{$u['userid']}') ");
//导航
$trade_global['daohang'][] = array('icon'=>'','html'=>'样品领用列表','href'=>"$rooturl/main_user.php?cyd_id={$_GET['cyd_id']}");
$_SESSION['daohang']['main_user']	= $trade_global['daohang'];
$last_date	= date('Y-m-01');
$wwc = '';
if(!$_GET['month']||($_GET['month']==date('m'))){
	$_GET['month'] = date('m');
	$pd_wwc	= $DB->fetch_one_assoc("SELECT cyd_id,vid FROM  `assay_pay` as ap  WHERE  ap.`fzx_id` = '$fzx_id' AND (ap.`userid` = '{$u['userid']}' OR ap.`userid2`='{$u['userid']}') AND (ap.`over`='未开始' or ap.`over`='已开始') AND `create_date` < $last_date");
	if(!empty($pd_wwc)){
		$wwc="<p align='center' style=''><span id='wcts'style=\"color:red;\">您其他月份有未完成任务，<a href='javascript:void(0)'  onclick='gt()' >点击查看</a><span></p>";
	}
}
$last_date	= date('Y').'-'.$_GET['month'].'-01';
$newmonth = $_GET['month']+1;
$last_date_da = date('Y').'-'.$newmonth.'-01';

$sql_cyd_id	= $DB->query("SELECT cyd_id,vid FROM  `assay_pay` as ap  WHERE  ap.`fzx_id` = '$fzx_id' AND (ap.`userid` = '{$u['userid']}' OR ap.`userid2`='{$u['userid']}')");
$str_cyd_id	= '';
$arr_finist_vid	= array();
while ($rs_cyd_id = $DB->fetch_assoc($sql_cyd_id)) {
	$str_cyd_id	.= $rs_cyd_id['cyd_id'].",";
	$arr_finist_vid[]	= $rs_cyd_id['vid'];
}
if(empty($str_cyd_id)){
	//$tishi = $u['userid']."全部的任务都已经检测完成!";
	$tishi = $u['userid']."没有任何检测任务!";
	echo "<script>alert('$tishi');window.close();</script>";
	exit;
}else{
	$str_cyd_id	= substr($str_cyd_id, 0,-1);
}
//获取样品编号 和 其检测的项目
$sql = "SELECT cy.`id` as cyd_id,cy.site_type,cy.`cy_date`, cy.`group_name`, cy.`jcwc_date` ,ap.`id` ,ap.`vid`, ap.`assay_element`,ap.`userid`, ap.`over`
        FROM `cy` LEFT JOIN `assay_pay` ap ON cy.`id`=ap.`cyd_id` LEFT JOIN `assay_value` av ON ap.`vid` = av.`id`
        WHERE cy.`cy_date` >= '$last_date' AND cy.`cy_date`< '$last_date_da' AND ap.`cyd_id` in ($str_cyd_id) AND (ap.`userid` = '{$u['userid']}' OR ap.`userid2`='{$u['userid']}')
        ORDER BY cy.`cy_date` desc,cy.`id` desc,ap.`vid`";
$sql_pay	= $DB->query($sql);
$arr_main	= $main_key	= array();
while($rs_pay	= $DB->fetch_assoc($sql_pay)){
	$sql_order	= $DB->query("SELECT bar_code FROM `assay_order` WHERE tid='{$rs_pay['id']}' ORDER BY cid");
	while($rs_order	= $DB->fetch_assoc($sql_order)){
		$bar_code = $rs_order['bar_code'];
		//这里只获取编号的后几位，是为了显示的时候能按照样品编号的流水号来排序
		$tmp_bar_num	= explode("-",$rs_order['bar_code']);
		if(!empty($tmp_bar_num[1])){
			$bar_code_num	= $tmp_bar_num[1];//$rs_order['bar_code'];//
		}else{
			$bar_code_num	= $tmp_bar_num[0];
		}
		/*if(array_key_exists($bar_code_num, $main_key)){
			$bar_code_num	.= "P";
		}*/
		 //委托任务不显示批名
	    if($rs_pay['site_type']=='3'){
	    	$rs_pay['group_name'] = '';
	       $rs_pay['group_name'] .= '委托任务（真实名称已隐藏）';
	    }
	    if(($rs_pay['over']=='未开始' ||$rs_pay['over']=='已开始') && ($_GET['month']!=date('m'))){
	    	$arr_main[$rs_pay['cyd_id']][$bar_code_num]['cyd']	= $rs_pay['group_name']." (采样日期：".$rs_pay['cy_date'].")<span style='color:red;'>未完成</span>";
	    }else{
	    	$arr_main[$rs_pay['cyd_id']][$bar_code_num]['cyd']	= $rs_pay['group_name']." (采样日期：".$rs_pay['cy_date'].")";
	    }
		
		if(!in_array($rs_pay['vid'], $arr_finist_vid)){
			$rs_pay['assay_element']	= "<font style='color:#2E5046'>".$rs_pay['assay_element']."</font>";
		}
		$main_key[$rs_pay['cyd_id']][$bar_code_num]= $rs_order['bar_code'];
		if($rs_pay['userid']==$u['userid']){
			$arr_main[$rs_pay['cyd_id']][$bar_code_num]['A']	.= $rs_pay['assay_element']."、";

		}else{
			$arr_main[$rs_pay['cyd_id']][$bar_code_num]['B']	.= $rs_pay['assay_element']."、";
		}
	}
}
//将项目按照统一排序
//区分出主测和辅测来
//显示到模板中
$first_group_name = $str_main	= "";
foreach ($arr_main as $cyd_id => $array) {
	ksort($array);
	//$str_main	.= "<tr><td colspan='2' style='font-weight:bold;'>{$value['cyd']}</td></tr><tr><th style='width:180px;'>样品编号</th><th>检测项目</th></tr>";
	foreach ($array as $key => $value) {
		$key	= $main_key[$cyd_id][$key];
		if($first_group_name!=$cyd_id){
			$str_main	.= "<tr><td colspan='2' style='font-weight:bold;'>{$value['cyd']}</td></tr><tr><th style='width:180px;'>样品编号</th><th>检测项目</th></tr>";
			$first_group_name	= $cyd_id;
		}
		if(empty($value['B'])){
			$value['B']	= "<font style='color:#ccc;'>没有辅测项目</font>";
		}else{
			$value['B']	= substr($value['B'],0,-3);
		}
		if(empty($value['A'])){
			$value['A']	= "<font style='color:#ccc;'>没有辅测项目</font>";
		}else{
			$value['A']	= substr($value['A'],0,-3);
		}
		$str_main	.= "<tr><td rowspan='2'>{$key}</td><td align=left>主测项目：".$value['A']."</td></tr><tr><td align=left>辅测项目：".$value['B']."</td></tr>";
	}
}

if($str_main == ''){
	$str_main	= "<tr><td rowspan='2' colspan='2'>".$u['userid']."本月还没有任何检测任务!</td></tr>";
}
//所有月
$_GET['month'] = empty($_GET['month'])?date('m'):$_GET['month'];
$month_list = '';
$month_max = 12;
for($i=$month_max;$i>=1;$i--){
    $select = ($i==intval($_GET['month']))? 'selected' : '';
    $month_list .= '<option '.$select.' value="'.($i<10?'0'.$i:$i).'">'.($i<10?'0'.$i:$i).'月</option>';
}
disp("mu");
?>
