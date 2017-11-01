<?php
include '../temp/config.php';
$fzx_id	= $u['fzx_id'];
if($_POST['action']=='zhuijia'){
	$gids = $_POST['groupid'];
	$xms = $_POST['xms'];
	$sql = "select id,assay_values from site_group where id in (".$gids.")";
	$re =$DB->query($sql);
	while($row = $DB->fetch_assoc($re)){
		$addvids = $addvid = '';
		$vids = array();
		$xmid = array();
		$vids = explode(',',$row[assay_values]);
		$xmid = explode(',',$xms);
		foreach($xmid as $v){
			if(!in_array($v,$vids)){
				$addvid .= ','.$v;
			}
		}
		if($addvid != ''){
			if($row[assay_values] !=''){
				$addvids = $row[assay_values].$addvid;
			}else{
				$addvids = substr($addvid,1);
			}
			$upre = $DB->query("update site_group set assay_values = '$addvids' where id =".$row[id]);
		}
		$tj = explode(',',$addvids);
		$num[$row[id]] = count($tj);
	}
	if(!empty($num)){
		echo json_encode($num);
	}else{
		echo 'wrong';
	}
}
if($_POST['action']=='shan'){
	$gids = $_POST['groupid'];
	$xms = $_POST['xms'];
	$sql = "select id,assay_values from site_group where id in (".$gids.")";
	$re =$DB->query($sql);
	while($row = $DB->fetch_assoc($re)){
		$addvids = $addvid = '';
		$vids = array();
		$xmid = array();
		$vids = explode(',',$row[assay_values]);
		$xmid = explode(',',$xms);
		foreach($xmid as $v){
			if(in_array($v,$vids)){
				$vids = array_flip($vids);
				unset($vids[$v]);
				$vids = array_flip($vids);
			}
		}
		$num[$row[id]] = count($vids);
		$addvids = implode(',',$vids);
		$upre = $DB->query("update site_group set assay_values = '$addvids' where id =".$row[id]);
	}
	if(!empty($num)){
		echo json_encode($num);
	}else{
		echo 'wrong';
	}
	
}