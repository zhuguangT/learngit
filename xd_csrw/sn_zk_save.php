<?php
/**
 * 功能：添加、修改室内质控(室内平行,加标回收)
 * 作者：zhengsen
 * 时间：2014-06-16
**/
include "../temp/config.php";
if(!$u['userid']){
	nologin();
}
//print_rr($_POST);
//exit();
if(empty($_POST['snpx'])){
	$_POST['snpx']=array();
}
if(empty($_POST['jbhs'])){
	$_POST['jbhs']=array();
}
//查询出现场项目
$pay_sql="SELECT * FROM assay_pay WHERE cyd_id='".$_POST['cyd_id']."' AND is_xcjc='1' GROUP BY vid";
$pay_query=$DB->query($pay_sql);
$xcjc_arr=array();
while($pay_rs=$DB->fetch_assoc($pay_query)){
	$xcjc_arr[]=$pay_rs['vid'];
}
$rec_query=$DB->query("SELECT * FROM `cy_rec` WHERE `cyd_id`='".$_POST['cyd_id']."' AND `status`='1'");
while($rs=$DB->fetch_assoc($rec_query)){
	$zk_flag=$rs['zk_flag'];
	if(in_array($rs['id'],$_POST['snpx'])){
		if(empty($rs['snpx_item'])){
			$vid_arr=explode(',',$rs['assay_values']);
			$snpx_arr=array_diff($vid_arr,$global['not_need_zk']);
			$snpx_arr=array_diff($snpx_arr,$xcjc_arr);
			if(!empty($snpx_arr)){
				if($zk_flag>=0){
					$zk_flag+=20;
				}
				$snpx_item=implode(',',$snpx_arr);
				$up_sql="UPDATE `cy_rec` SET zk_flag='".$zk_flag."',snpx_item='".$snpx_item."' WHERE id='".$rs['id']."'";
				$DB->query($up_sql);
			}
		}	
	}else{
		if(!empty($rs['snpx_item'])){
			if($zk_flag>=0){
				$zk_flag-=20;
			}
			$up_sql="UPDATE `cy_rec` SET zk_flag='".$zk_flag."',snpx_item='' WHERE id='".$rs['id']."'";
			$DB->query($up_sql);
		}
	}
	if(in_array($rs['id'],$_POST['jbhs'])){
		if(empty($rs['jbhs_item'])){
			$vid_arr=explode(',',$rs['assay_values']);
			$jbhs_arr=array_diff($vid_arr,$global['not_need_zk']);
			$jbhs_arr=array_diff($jbhs_arr,$xcjc_arr);
			if(!empty($jbhs_arr)){
				if($zk_flag>=0){
					$zk_flag+=40;
				}
				$jbhs_item=implode(',',$jbhs_arr);
				$up_sql="UPDATE `cy_rec` SET zk_flag='".$zk_flag."',jbhs_item='".$jbhs_item."' WHERE id='".$rs['id']."'";
				$DB->query($up_sql);
			}
		}	
	}else{
		if(!empty($rs['jbhs_item'])){
			if($zk_flag>=0){
				$zk_flag-=40;
			}
			$up_sql="UPDATE `cy_rec` SET zk_flag='".$zk_flag."',jbhs_item='' WHERE id='".$rs['id']."'";
			$DB->query($up_sql);
		}
	}
}


gotourl("fp_csrw.php?cyd_id=$_POST[cyd_id]");
?>
