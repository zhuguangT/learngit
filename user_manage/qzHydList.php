<?php
//这是某某人校核，复核签字的化验单列表页面
/*$_GET['jhTid'] 规定条件下 筛选出来的 该人该时间段 所有校核的化验单id
*$_GET['fhTid']  规定条件下 筛选出来的 该人该时间段 所有复核的化验单id
$_GET['ycFhTid'] 规定条件下 筛选出来的 该人该时间段 所有延迟复核的化验单id
$_GET['ycJhTid'] 规定条件下 筛选出来的 该人该时间段 所有延迟校核的化验单id
*/
include("../temp/config.php");
if($_GET['month'] == 'all'){
	$daohang_html	= $_GET['user'].$_GET['year'].'全部月所有签字化验单';
}else{
	$daohang_html	= $_GET['user'].$_GET['year'].$_GET['month'].'月所有签字化验单';
}
$daohang = array(
		array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
		array('icon'=>'','html'=>'签字延迟统计表','href'=>'user_manage/qz_yanchi.php'),
		array('icon'=>'','html'=>$daohang_html,'href'=>'user_manage/qzHydList.php?user='.$_GET['user'].'&huayan_tid='.$_GET['huayan_tid'].'&huayan_yanchi_tid='.$_GET['huayan_yanchi_tid'].'&jhTid='.$_GET['jhTid'].'&fhTid='.$_GET['fhTid'].'&ycJhTid='.$_GET['ycJhTid'].'&ycFhTid='.$_GET['ycFhTid'].'&year='.$_GET['year'].'&month='.$_GET['month']),
);
$trade_global['daohang'] = $daohang;
$where = $lines = "";
$jhTid = $fhTid = $ycJhTid = $ycFhTid = array();
//数据量太大get传值不太好，改为session传值
if(!empty($_GET['jhTid'])){
	$_GET['jhTid']	= $_SESSION[$_GET['user']]['jhtid'];
}
if(!empty($_GET['fhTid'])){
	$_GET['fhTid']	= $_SESSION[$_GET['user']]['fhTid'];
}
if(!empty($_GET['ycJhTid'])){
	$_GET['ycJhTid']= $_SESSION[$_GET['user']]['ycJhTid'];
}
if(!empty($_GET['ycFhTid'])){
	$_GET['ycFhTid']= $_SESSION[$_GET['user']]['ycFhTid'];
}
if(!empty($_GET['huayan_tid'])){
	$_GET['huayan_tid']	= $_SESSION[$_GET['user']]['huayan_tid'];
}
if(!empty($_GET['huayan_yanchi_tid'])){
	$_GET['huayan_yanchi_tid']	= $_SESSION[$_GET['user']]['yanchi_tid'];
}
if(!empty($_GET['jhTid'])&&!empty($_GET['fhTid']))$where    = "a.id in (".$_GET['jhTid'].",".$_GET['fhTid'].")";
elseif(!empty($_GET['jhTid'])&&empty($_GET['fhTid']))$where = "a.id in (".$_GET['jhTid'].")";
elseif(empty($_GET['jhTid'])&&!empty($_GET['fhTid']))$where = "a.id in (".$_GET['fhTid'].")";
elseif(!empty($_GET['ycFhTid'])&&!empty($_GET['ycJhTid']))$where="a.id in (".$_GET['ycFhTid'].",".$_GET['ycJhTid'].")";
elseif(!empty($_GET['ycJhTid'])&&empty($_GET['ycFhTid']))$where = "a.id in (".$_GET['ycJhTid'].")";
elseif(empty($_GET['ycJhTid'])&&!empty($_GET['ycFhTid']))$where = "a.id in (".$_GET['ycFhTid'].")";
if(!empty($_GET['huayan_tid'])){
	$where	= " a.id in (".$_GET['huayan_tid'].") ";
}else if(!empty($_GET['huayan_yanchi_tid'])){
	$where	= " a.id in (".$_GET['huayan_yanchi_tid'].") ";
}
$huayan_yanchi_arr	= array();
if(!empty($_GET['huayan_yanchi_tid'])){
	$huayan_yanchi_arr	= @explode(',',$_GET['huayan_yanchi_tid']);
}
if(!empty($_GET['jhTid']))$jhTid  = @explode(",",$_GET['jhTid']);
if(!empty($_GET['fhTid']))$fhTid  = @explode(",",$_GET['fhTid']);
if(!empty($_GET['ycJhTid']))$ycJhTid = @explode(",",$_GET['ycJhTid']);
if(!empty($_GET['ycFhTid']))$ycFhTid = @explode(",",$_GET['ycFhTid']);
if($_GET['month']=='all')$_GET['month']='全部';
if(!empty($_GET['jhTid'])||!empty($_GET['fhTid'])||!empty($_GET['huayan_tid'])){//总的此人签字的化验单
	/*if(!empty($_GET['jhTid'])&&!empty($_GET['fhTid']))$where = "a.id in (".$_GET['jhTid'].",".$_GET['fhTid'].")";
	elseif(!empty($_GET['jhTid'])&&empty($_GET['fhTid']))$where = "a.id in (".$_GET['jhTid'].")";
	elseif(empty($_GET['jhTid'])&&!empty($_GET['fhTid']))$where = "a.id in (".$_GET['fhTid'].")";
	if(!empty($_GET['jhTid']))$jhTid  = @explode(",",$_GET['jhTid']);
	if(!empty($_GET['fhTid']))$fhTid  = @explode(",",$_GET['fhTid']);
	if(!empty($_GET['ycJhTid']))$ycJhTid = @explode(",",$_GET['ycJhTid']);
	if(!empty($_GET['ycFhTid']))$ycFhTid = @explode(",",$_GET['ycFhTid']);*/
	/*$queHyd = $DB->query("select a.id,a.sign_01,a.sign_02,a.sign_03,a.sign_date_01,a.sign_date_02,a.sign_date_03,a.assay_element,cy.cy_date from `assay_pay` as a inner join `cy` on cy.id=a.cyd_id where ".$where);
	while($rsHyd=$DB->fetch_assoc($queHyd)){
		//print_rr($rsHyd);
		if(@in_array($rsHyd['id'],$ycJhTid)||@in_array($rsHyd['id'],$ycFhTid))$riRed = "style=\"color:red;\"";
		else $riRed = '';
		if(@in_array($rsHyd['id'],$jhTid)){
			$name     = $rsHyd['sign_02'];
			$jhLines .= "<tr><td>".$rsHyd['id']."</td><td>".$rsHyd['assay_element']."</td><td>".$rsHyd['cy_date']."</td><td>".$rsHyd['sign_01']."</td><td>".$rsHyd['sign_date_01']."</td><td ".$riRed.">".$rsHyd['sign_date_02']."</td></tr>";
		}
		if(@in_array($rsHyd['id'],$fhTid)){
			$name     = $rsHyd['sign_03'];
			$fhLines .= "<tr><td>".$rsHyd['id']."</td><td>".$rsHyd['assay_element']."</td><td>".$rsHyd['cy_date']."</td><td>".$rsHyd['sign_02']."</td><td>".$rsHyd['sign_date_02']."</td><td ".$riRed.">".$rsHyd['sign_date_03']."</td></tr>";
		}
	}*/
	$leibie = "所有";
}
else if((empty($_GET['jhTid'])&&empty($_GET['fhTid'])&&empty($_GET['huayan_tid']))&&(!empty($_GET['ycJhTid'])||!empty($_GET['ycFhTid'])||!empty($_GET['huayan_yanchi_tid']))){//延迟此人签字的化验单
	$leibie = "延迟签字";
}
else{
	$leibie = "";
}
if($leibie!=""&&$where!=''){
	$huayan_style	= $shenhe_style	= 'display:none;';
	$jhXuHao = $fhXuHao = $huayan_xuhao	= 0;
	$queHyd  = $DB->query("select a.id,a.sign_01,a.sign_012,a.sign_02,a.sign_03,a.sign_date_01,a.sign_date_012,a.sign_date_02,a.sign_date_03,a.assay_element,cy.cy_date,cy.jcwc_date from `assay_pay` as a inner join `cy` on cy.id=a.cyd_id where a.fzx_id='$fzx_id' and cy.fzx_id='$fzx_id' and ".$where);
	while($rsHyd=$DB->fetch_assoc($queHyd)){
		//print_rr($rsHyd);
		if($leibie=="所有"&&(@in_array($rsHyd['id'],$ycJhTid)||@in_array($rsHyd['id'],$ycFhTid)||@in_array($rsHyd['id'],$huayan_yanchi_arr)))$riRed = "style=\"color:red;\"";
		else $riRed = '';
		if(!empty($_GET['huayan_tid']) || !empty($_GET['huayan_yanchi_tid'])){
			$huayan_style	= "";
			if(!empty($rsHyd['sign_date_01'])){
				$hyd_wc_date	= $rsHyd['sign_date_01'];
				$hyd_user		= $rsHyd['sign_01'];
			}
			if(!empty($rsHyd['sign_date_012']) && $rsHyd['sign_012']==$_GET['user']){
				$hyd_wc_date	= "(主测)".$hyd_wc_date."<br>（辅测）".$rsHyd['sign_date_012'];
				$hyd_user		= "(主测)".$hyd_user."<br>（辅测）".$rsHyd['sign_012'];
			}
			$huayan_xuhao++;
			$name     = $_GET['user'];//$hyd_user;
			$hyLines .= "<tr><td>".$huayan_xuhao."</td><td class='lianjie' onclick=\"tiaozhuan(".$rsHyd['id'].");\">".$rsHyd['id']."</td><td>".$rsHyd['assay_element']."</td><td>".$rsHyd['cy_date']."</td><td nowrap>".$hyd_user."</td><td nowrap>".$rsHyd['jcwc_date']."</td><td ".$riRed.">".$hyd_wc_date."</td></tr>";
		}
		if(@in_array($rsHyd['id'],$jhTid)||@in_array($rsHyd['id'],$ycJhTid)){
			$shenhe_style	= "";
			$jhXuHao++;
			$name     = $rsHyd['sign_02'];
			$jhLines .= "<tr><td>".$jhXuHao."</td><td class='lianjie' onclick=\"tiaozhuan(".$rsHyd['id'].");\">".$rsHyd['id']."</td><td>".$rsHyd['assay_element']."</td><td>".$rsHyd['cy_date']."</td><td nowrap>".$rsHyd['sign_01']."</td><td nowrap>".$rsHyd['sign_date_01']."</td><td ".$riRed.">".$rsHyd['sign_date_02']."</td></tr>";
		}
		if(@in_array($rsHyd['id'],$fhTid)||@in_array($rsHyd['id'],$ycFhTid)){
			$shenhe_style	= "";
			$fhXuHao++;
			$name     = $rsHyd['sign_03'];
			$fhLines .= "<tr><td>".$fhXuHao."</td><td class='lianjie' onclick=\"tiaozhuan(".$rsHyd['id'].");\">".$rsHyd['id']."</td><td>".$rsHyd['assay_element']."</td><td>".$rsHyd['cy_date']."</td><td>".$rsHyd['sign_01']."</td><td>".$rsHyd['sign_02']."</td><td>".$rsHyd['sign_date_02']."</td><td ".$riRed.">".$rsHyd['sign_date_03']."</td></tr>";
		}
	}
if($_GET['month']=='全部')$_GET['month'] = "全年";
else $_GET['month'] = $_GET['month']."月";
disp('user_manager/qz_hydlist');
}
?>
