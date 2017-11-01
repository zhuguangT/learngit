<?php
/*
*功能：查看和下载常规月报统计表的信息
*作者：高龙
*时间：2016/5/6
 */
include '../temp/config.php';
include INC_DIR . "cy_func.php";
include '../baogao/bg_func.php';
if($u['userid']==''){
	nologin();
}
$fzx_id=$u['fzx_id'];//获取分中心的id
if($_POST['month_type']	== 'any_data'){//任意成果查询
	if($_POST['export_element'][0] == 'max_min_value'){
		$jzjz = 1;
	}
	$_POST['alone_vid']		= $_POST['vid'];
	unset($_POST['vid']);
	$_POST['time_start']	= $_POST['begin_date'];
	unset($_POST['begin_date']);
	$_POST['time_end']		= $_POST['end_date'];
	unset($_POST['end_date']);
	switch ($_POST['sub']) {
		case '查看成果':
			$_POST['action']	= "see";
			break;
		case '下载成果':
			$_POST['action']	= "xia";
			break;
		case '查看趋势图':
			$_POST['action']	= "chart";
			break;
		default:
			$_POST['action']	= "see";
			break;
	}
}
$action	= !empty($_GET['action'])?$_GET['action']:$_POST['action'];
//包含获取报告配置信息的文件
include './get_sites.php';

//包含统计报告所需要的各种条件
include './get_sj_tj.php';
//包含统一查询数据的文件
include './get_result.php';

//包含获取项目名称和对项目进行排序的文件
include './get_xmn_xmpx.php';

//包含月报中几个重要的函数
if(!empty($monthbgfunc)){
	include "./".$monthbgfunc."";
}
//获取计量单位
if(!empty($max_water_type)){
	$vid_arr		= array_keys($xm_arr);
	$vid_unit_arr	= array_keys($unit_arr);
	$diff_arr		= array_diff($vid_arr,$vid_unit_arr);
	if(!empty($diff_arr)){
		$diff_str	= implode(',',$diff_arr);
		$sql_unit		= $DB->query("SELECT xmid,unit FROM `xmfa` WHERE `act`='1' AND `mr`='1' AND `lxid`='{$max_water_type}' AND `xmid` in($diff_str) group BY xmid");
		while ($rs_unit = $DB->fetch_assoc($sql_unit)) {
			$unit_arr[$rs_unit['xmid']]	= $rs_unit['unit'];	
		}
	}
}
//获取项目名称和项目单位
$xm_name_td=$xz_td=$xm_danwei='';
foreach($xm_arr as $key=>$value){
	$unit_str='';
	if(!empty($unit_arr[$key])){
		$unit_str=$unit_arr[$key];
	}
	$xm_name_td.="<td>".$value."</td>";
	$xm_danwei.="<td>".$unit_str."</td>";
}
//标题需要合并的列
$cols1=count($xm_arr);
$z_cols=$mbjbls+$cols1;
if(empty($return_result_arr)){
	echo "<script>alert('没有查询任何数据'); window.close();</script>";exit();
}
$title_name = $_POST['cgb_title'];//报告名称
//标题的显示
$title	='兰州威立雅水务（集团）有限责任公司'.'&nbsp;'.$_POST['cgb_title'];//主表表头
$cy_time= $begin_ymr.'&nbsp;&raquo;&nbsp;'.$end_ymr;//采样时间范围
//$titlex =$_POST['cgb_title']."(续)";//续表表头
//报告的显示
$mintd_width = 50;//table每列最小长度
$week_bg_line='';
$cy_date_width=85;
$site_width=115;
$water_type_width=75;
$vid_width=((1000-$cy_date_width-$site_width-$water_type_width)/$cols1)-4;
$xmlieshu	= empty($row_max)?6:$row_max;//统计报告所要显示的列数
$hangshu	= empty($col_max)?14:$col_max; //统计报告所要显示的行数
//报告有另外的php单独显示
if(!empty($mb_arr['result_php_name'])){
	if(!stristr($mb_arr['result_php_name'],".php")){
		$mb_arr['result_php_name']	.= ".php";
	}
	include($mb_arr['result_php_name']);
}else{//else
	$i=0;
	foreach($return_result_arr as $ks=>$vs){//****
		$i++;
		$resultss[$ks] = $vs;
		$week_bg_line.= dqsj($resultss,$xm_arr,$site_inf,$pc_information,$mbtd);//调用读取数组数据的函数
		$week_bg_line.="<tr><td colspan='{$z_cols}'></td></tr>";//在每一个批次下加一个空行
		if($i == count($return_result_arr)){//计算该报告的模板
			$tjbg=temp("any_data/".$mbname);
		}
		$resultss = '';//赋空为了防止影响下一次循环
	}//****
}//else
if($action=='see'){
	echo "<script src='{$rooturl}/js/jquery-2.1.0.js'></script>
		<style>
			td.vd0_button:hover{
				color:blue;
				cursor: pointer;
				transform: scale(1.2);
				opacity: 1;
				border-color:black !important;
			}
		</style>
		<script>
			$(function(){
				$(\"td.vd0_button[tid]\").click(function(){
					var tid		= $(this).attr('tid');
					var cyd_id	= $(this).attr('cyd_id');
					if(tid){
						window.open('{$rooturl}/huayan/assay_form.php?tid='+tid);
					}else if(cyd_id){
						window.open('{$rooturl}/cy/cy_record.php?cyd_id='+cyd_id);
					}else{
						alert('无化验单');
					}
				});
			})
		</script>
		";
	echo $tjbg;
}else if($action=='xia'){
	header("Content-Type:   application/msexcel");        
	header("Content-Disposition:   attachment;   filename=".$title_name.".xls");        
	header("Pragma:   no-cache");        
	header("Expires:   0");
	echo "
		<html xmlns:v=\"urn:schemas-microsoft-com:vml\" xmlns:o=\"urn:schemas-microsoft-com:office:office\"
		xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\">
		<title></title>
		<head></head>
		<body>".$tjbg."</body>
		</html>
	";
}else if($action=='chart'){
	$_POST['time_start']	= $_POST['choose_date']['begin_date'];
	$_POST['time_end']		= $_POST['choose_date']['end_date'];
	include("../data_chart/custom_chart2.php");
    exit;
}

?>