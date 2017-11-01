<?php
/*
* 功能：z站点样品编号对照表
* 作者：zhengsen
* 时间：2014-11-14
*/

include "../temp/config.php";
//导航
$trade_global['daohang'] = array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'采样任务列表','href'=>'./cy/cyrw_list.php'),array('icon'=>'','html'=>'站点样品编号对照表','href'=>'./cy/site_code.php?cyd_id='.$_GET['cyd_id'])
);
if(empty($u['userid'])){
	nologin();
}
if(!$_GET['print']){
	$dayin="<a href=\"../cy/site_code.php?cyd_id={$_GET[cyd_id]}&print=1&ajax=1\" target=\"_blank\" class=\"btn btn-primary btn-sm\"><i class=\"icon-print bigger-160\"></i>打印</a>";
}
//如果是打印页面显示设置
if($_GET['print']){
	$print='';
	if(!empty($_GET['page_size'])){
		$page_size=$_GET['page_size'];
	}else{
		$page_size=20;//默认打印12行
	}
	$input_note="此处设置打印行数，默认20行";
	echo temp("cy_tzd_print_head");
}
$rec_query=$DB->query("SELECT * FROM `cy_rec` WHERE cyd_id='".$_GET['cyd_id']."' AND status='1' AND sid>-1 ORDER BY bar_code");
$i=0;
$data=array();
while($rec_rs=$DB->fetch_assoc($rec_query)){
	$data[]=$rec_rs;
}
$total=count($data);
$page=ceil($total/$page_size);
$add_trs=$page_size-($total-($page-1)*$page_size);
foreach($data as $key=>$value){
	$i++;
	if($value['sid']>0&&$value['zk_flag']<0){
		$value['site_name']=$value['site_name']."(平行)";
	}
	$site_code_lines.="<tr><td>".$i."</td><td>".$value['site_name']."</td><td>".$value['bar_code']."</td></tr>";
	if($_GET['print'])
	{
		if(($i==$total)||$i%$page_size==0){
			if($i==$total&&$add_trs>0){
				for($k=1;$k<=$add_trs;$k++){
					$site_code_lines.="<tr><td>&nbsp;</td><td></td><td></td></tr>";
				}
			}
			echo temp("site_code.html");
			$site_code_lines='';
		}
	}
}
if(!$_GET['print']){
	disp("site_code.html");
}
?>
