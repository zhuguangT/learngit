<?php
/**
 * 功能： 超标项目列表弹出层显示
 * 作者：zhengsen
 * 日期：2015-4-10
 * 描述：
*/
include '../temp/config.php';
$fzx_id=$u['fzx_id'];
if(!empty($_GET['cyd_id'])){
	$sql="SELECT  ao.*,ap.assay_element FROM `assay_order` ao JOIN assay_pay ap ON ao.tid=ap.id WHERE ao.cyd_id ='".$_GET['cyd_id']."' AND chao_biao ='1'";
	$query=$DB->query($sql);
	while($rs=$DB->fetch_assoc($query)){
		$cb_xm_line.="<tr><td>".$rs['site_name']."</td><td><a title=\"点击查看化验单\" href=\"../huayan/assay_form.php?tid=".$rs['tid']."\">".$rs['assay_element']."</a></td><td>".$rs['vd0']."</td></tr>";
	}
}

//print_rr($_GET);exit();
disp("bg/cb_xm_list.html");
?>