<?php
/**
 * 功能：改变指定化验单的化验人员
 * 作者: Mr Zhou
 * 日期: 2014-10-31 
 * 描述: 
*/
include '../temp/config.php';
$fzx_id = FZX_ID;

if($_POST['action'] == '改变化验员') {
	if(empty($_POST['s'])) gotourl($url[$_u_][1]);
	$hyd_id_str = join(",", $_POST['s']);
	$fx_user = $DB->fetch_one_assoc("SELECT `id`,`userid` FROM `users` WHERE `userid`='{$_POST['fx_user']}';");
	$sql = "UPDATE `assay_pay` SET `uid`='{$fx_user['id']}', `userid`= '{$fx_user['userid']}' WHERE `id` IN ({$hyd_id_str})  AND `fzx_id`='$fzx_id' ";
	$DB->query( $sql );
	alert('修改化验员'.$DB->affected_rows().'个化验单',$url[$_u_][1]);
}
else if($_POST['action'] == '删除化验单')
{
	foreach($_POST['s'] as $key => $_GET['hyd_id'])
	{
		$r=$DB->fetch_one_assoc("SELECT `cyd_id` FROM `assay_pay` WHERE `id`='$_GET[hyd_id]' AND `fzx_id`='$fzx_id'");
		$DB->query("UPDATE `cy` set `hyd_count`=`hyd_count`-1 WHERE `id`='$r[cyd_id]' AND `fzx_id`='$fzx_id'");
		$R=$DB->query("SELECT `vid`,`cid` FROM `assay_order` WHERE `tid`='$_GET[hyd_id]' ");
		while($r=$DB->fetch_assoc($R)){
			$a=$DB->fetch_one_assoc("SELECT `assay_values` from `cy_rec` where `id`='$r[cid]'");
			$a=elementsToArray($a['assay_values']);
			$a=implode(',',array_diff($a,array($r['vid'])));
			$DB->query("UPDATE `cy_rec` set `assay_values`='$a' where `id`='$r[cid]'");
		}
		$DB->query("DELETE from `assay_pay` where `id`='$_GET[hyd_id]' AND `fzx_id`='$fzx_id'");
		$DB->query("DELETE from `assay_order` where `tid`='$_GET[hyd_id]'");
	}
	gotourl($url[$_u_][1]);
}elseif($_POST['action']=='化验单载入'){
	if(!empty($_POST['s'])){
		foreach($_POST['s'] as $key=>$value){
			$rs=array();
			$rs=$DB->fetch_one_assoc("SELECT ap.id,ap.vid,x.fangfa FROM assay_pay ap JOIN xmfa x ON ap.fid=x.id  WHERE ap.id='".$value."' AND x.fzx_id='".$u['fzx_id']."'");
			if(!empty($rs)){
				$_GET['tid']=$rs['id'];
				$_GET['vid']=$rs['vid'];
				$_GET['fid']=$rs['fangfa'];
				include("../autoload/loadtable.php");
				$counts+=$count;
			}
		}
		if($counts>0){
			$msg="自动载入数据{$counts}个!";
		}
		gotourl($url[$_u_][1],$msg);
	}
	gotourl($url[$_u_][1]);
}
else {
	if($_GET['lx'] == 'houtui')
	{
		$DB->query("UPDATE `cy`  set `status`='5' where `id`='$_GET[did]' AND fzx_id='$fzx_id' AND `fzx_id`='$fzx_id'");
		$DB->query("DELETE from `assay_pay` where  `cyd_id`='$_GET[did]' AND fzx_id='$fzx_id' AND `fzx_id`='$fzx_id'");
		$DB->query("DELETE from `assay_order` where `cyd_id`='$_GET[did]' AND fzx_id='$fzx_id'");
		gotourl($url[$_u_][1]);
	}
}
?>