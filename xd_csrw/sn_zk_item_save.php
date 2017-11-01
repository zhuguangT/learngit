<?php
/**
 * 功能：保存室内质控的项目(室内平行,加标回收)
 * 作者：zhengsen
 * 时间：2014-06-17
**/
require_once "../temp/config.php";
if(!$u['userid']){
	nologin();
}
$rec_rs=$DB->fetch_one_assoc("SELECT * FROM `cy_rec` WHERE id='".$_POST['rec_id']."'");
$zk_flag=$rec_rs['zk_flag'];
if(empty($_POST['vid'])){
	if($_POST['action']=='snpx'){
		if(in_array($zk_flag,$global['snpx_flag'])){
				$zk_flag-=20;
		}
		$up_str="snpx_item";
	}else{
		if(in_array($zk_flag,$global['jbhs_flag'])){
			$zk_flag-=40;
		}
		$up_str="jbhs_item";
	}
	$up_sql="UPDATE `cy_rec` SET zk_flag='".$zk_flag."',".$up_str."='' WHERE id='".$_POST['rec_id']."'";
	$DB->query($up_sql);
}else{
	sort($_POST['vid']);
	if($_POST['action']=='snpx')
	{
		if(!in_array($zk_flag,$global['snpx_flag'])&&$zk_flag>=0){
				$zk_flag+=20;
		}
		$up_str="snpx_item";
	}else{
		if(!in_array($zk_flag,$global['jbhs_flag'])&&$zk_flag>=0){
			$zk_flag+=40;
		}
		$up_str="jbhs_item";
	}
	$vid_str=implode(',',$_POST['vid']);
	$up_sql="UPDATE	`cy_rec` SET zk_flag='".$zk_flag."',".$up_str."='".$vid_str."' WHERE id='".$_POST['rec_id']."'";
	$DB->query($up_sql);
}

gotourl( "fp_csrw.php?cyd_id={$rec_rs[cyd_id]}" );
?>
