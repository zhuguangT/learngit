<?php
include "../temp/config.php";
include "../inc/cy_func.php";
$gid	= get_str($_GET["gid"]);//gys_gl表中的id
$nid	= get_str($_GET["nid"]);//n_set表中的id
//导航
$trade_global['daohang'][]	=	array('icon'=>'','html'=>'供应商评审表','href'=>"$rooturl/gys/pingshen.php?gid=$gid&nid=$nid")
$_SESSION['daohang']['pingshen']	=	$trade_global['daohang'];

$fzx_id	= $u['fzx_id'];
$ps		= $DB->fetch_one_assoc("select * from `gys_gl` where id='$gid'");
$json	= json_decode($ps['json'],true); 
if($_GET['action']=='更新'){
	$psdate		= $_POST['psda'];//评审日期
	$psaddres	= $_POST['psaddre'];//评审地点
	$psgrouper	= $_POST['psgroup'];
	$psteam		= $_POST['psteam'];
	$psadvi		= $_POST['psadvice'];
	$psjl		= $_POST['psjl'];
	$qz		= $_POST['qz'];
	$rq		= $_POST['rq'];
	$jsxx		= array("jl"=>$psjl,"qz"=>$qz,"rq"=>$rq);
	$json		= JSON($jsxx);
	$DB->query("update `gys_gl` set psdate='$psdate',psaddress='$psaddres',psgrouper='$psgrouper',psteam='$psteam',psadvice='$psadvi',json='$json' where id='$gid'");
	$y			= substr($ps['pjdate'],0,4);
	gotourl("$rooturl/gys/gys_list.php?riqiy=$y");
}
disp("pingshen");
?>
