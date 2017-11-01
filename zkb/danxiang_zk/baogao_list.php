<?php
/**
 * 功能：单项质控报告列表
 * 作者：Mr Zhou
 * 日期：2014-08-19
 * 描述：
*/
include ('../../temp/config.php');
//导航
$trade_global['daohang'] = array(
    array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
    array('icon'=>'','html'=>'单项质控报告列表','href'=>'./zkb/danxiang_zk/baogao_list.php')
);
$ids = ($_POST['id'])?implode(',',$_POST['id']):$_GET['id'];
if($ids =='')
echo "<script>alert('请选择批次');location.href='../zkyb_list.php';</script>";
$year = ($_POST['year'])?$_POST['year']:$_GET['year'];
$month = ($_POST['month'])?$_POST['month']:$_GET['month'];

$zk_type=array(
	'实验室两空白检测结果比较',
	'实验室与现场空白检测结果比较',
	'密码平行样检测结果',
	'密码平行样检测结果评定',
	'实验室平行样检测结果',
	'实验室平行样检测结果评定',
	'加标回收率检测结果',
	'加标回收率检测结果评定');
$zkcount = count($zk_type);
for($i=0;$i<$zkcount;$i++){
	$url = "$rooturl/zkb/danxiang_zk/baogao_zk.php?zk_type=$i&cyd_id=$ids&year=$year&month=$month&xun=$_GET[xun]";
	$divline.= '<tr align="center"><td>'.($i+1).'</td><td>'.$zk_type[$i].'</td><td><a href="'.$url.'" target="_blank">查看报告</a>    <a href="'.$url.'&xz=1" target="_blank">下载报告</a></td></tr>';
}
disp('zkb/danxiang_zk/baogao_list');
?>