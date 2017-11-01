<?php
/**
 * 功能：显示测试任务通知单的信息
 * 作者：zhengsen
 * 时间：2014-06-26
**/
require_once '../temp/config.php';
//ajax请求删除站点
if($_POST['action']=='del_site'){
	$rec_sql="SELECT * FROM	`cy_rec` WHERE id='".$_POST['rec_id']."'";
	$rec_rs=$DB->fetch_one_assoc($rec_sql);
	if(!empty($rec_rs)){
		if($rec_rs['sid']==0||($rec_rs['sid']>0&&$rec_rs['zk_flag']<0)){
			//删除全程序空白、现场平行样
			$query=$DB->query("DELETE FROM	`cy_rec` WHERE id='".$_POST['rec_id']."'");
			if($rec_rs['sid']>0){
				//删除现场平行时要把原样的zk_flag减5
				$DB->query("UPDATE `cy_rec` SET zk_flag=zk_flag-5 WHERE cyd_id='".$_POST['cyd_id']."' AND sid='".$rec_rs['sid']."' AND zk_flag>-1");
				//如果删除平行样的项目，要在assay_order表找到原样的这个项目然后hy_flag要减5
				$DB->query("DELETE FROM `assay_order` WHERE cyd_id='{$_POST['cyd_id']}' AND sid='{$rec_rs['sid']}' AND hy_flag='-6'");
				$DB->query("UPDATE `assay_order` SET hy_flag=hy_flag-5 WHERE cyd_id='".$_POST['cyd_id']."' AND sid='".$rec_rs['sid']."' AND hy_flag>-1");
			}
			if($rec_rs['sid']==0){
				//删除全程序空白要把cy表的snkb字段更新为0
				$DB->query("UPDATE `cy` SET snkb='0' WHERE id='".$_POST['cyd_id']."'");
				$DB->query("DELETE FROM `assay_order` WHERE cyd_id='{$_POST['cyd_id']}' AND sid='{$rec_rs['sid']}'");
			}
			$del=1;//cy表中yp_count要减少的数量
		}else{
			$px_rs=$DB->fetch_one_assoc("SELECT * FROM `cy_rec` WHERE cyd_id='".$_POST['cyd_id']."' AND sid='".$rec_rs['sid']."' AND zk_flag='-6'");
			if(!empty($px_rs)){
				$del=2;
			}else{
				$del=1;
			}
			//删除原样，如果有平行样也删除,如果有现场平行项目 也删除
			$DB->query("DELETE FROM `assay_order` WHERE cyd_id='".$_POST['cyd_id']."' AND sid='".$rec_rs['sid']."'");
			$query=$DB->query("DELETE FROM	`cy_rec` WHERE cyd_id='".$_POST['cyd_id']."' AND sid='".$rec_rs['sid']."'");
			//下面更新cy表里面的sites字段
			if($query>0){
				$cy_sql="SELECT * FROM `cy` WHERE id='".$_POST['cyd_id']."'";
				$cy_rs=$DB->fetch_one_assoc($cy_sql);
				if(!empty($cy_rs['sites'])){
					$site_arr=explode(',',$cy_rs['sites']);
					if(in_array($rec_rs['sid'],$site_arr)){
						$key=array_search($rec_rs['sid'],$site_arr);
						unset($site_arr[$key]);
						if(!empty($site_arr)){
							$sites=implode(',',$site_arr);
						}else{
							$sites='';
						}
						$DB->query("UPDATE `cy` SET sites='".$sites."' WHERE id='".$_POST['cyd_id']."'");
					}
				}
			}
			//当一个站点删除后，此时如果全程序空白样里有相应的项目，但其他站点里没有这个项目的时候，需要将全程序空白样里的这个项目去掉。如果这个项目是现场项目需要将pay表及order表的记录也删除掉（由于这种情况出现的概率极低，这里的处理又要循环又要判断的，做必要性不大，临时不做。如果需要再加入此功能。20150528）
		}
		if($query>0){
			$data['del']=1;
			$DB->query("UPDATE `cy` SET yp_count=yp_count-'".$del."' WHERE id='".$_POST['cyd_id']."'");
		}else{
			$data['del']=0;
		}
		//判断如果没有站点了就删除这批任务
		$rec_nums=array();
		$rec_nums=$DB->fetch_one_assoc("SELECT COUNT(*) as site_nums FROM cy_rec WHERE cyd_id='".$rec_rs['cyd_id']."' AND sid>0");
		if(empty($rec_nums['site_nums'])){
			$DB->query("delete from `cy` where id='".$rec_rs['cyd_id']."'");
			$DB->query("delete from `cy_rec` where cyd_id='".$rec_rs['cyd_id']."'");
			$DB->query("delete from `assay_order` where cyd_id='".$rec_rs['cyd_id']."'");
			$DB->query("delete from `assay_pay` where cyd_id='".$rec_rs['cyd_id']."'");
			$data=array();
			$data['no_site']='1';
			echo JSON($data);
			exit();
		}else{
			//如果该张化验单已经没有任何化验任务，删除这张化验单
			$cyd_id	= $_POST['cyd_id'];
			$rs_order	= $DB->fetch_one_assoc("SELECT group_concat(distinct tid) as tid FROM `assay_order` WHERE cyd_id='".$cyd_id."'");
			if(!empty($rs_order['tid'])) {
				$rs_pay	= $DB->fetch_one_assoc("SELECT group_concat(vid) as vid FROM `assay_pay` WHERE cyd_id='{$cyd_id}' AND id NOT IN ({$rs_order['tid']})");
				$DB->query("DELETE FROM assay_pay WHERE cyd_id='{$cyd_id}' AND id not in ({$rs_order['tid']})");
				//判断是不是现场检测项目，如果是就删除cy表的xc_exam_value记录
				$cy_rs	= $DB->fetch_one_assoc("SELECT * FROM `cy` WHERE id='".$_POST['cyd_id']."'");
				$xc_exam_arr	= array();
				if(!empty($cy_rs['xc_exam_value'])){
					$xc_exam_arr= explode(",",$cy_rs['xc_exam_value']);
					$pay_vid_arr= explode(",", $rs_pay['vid']);
					$xc_exam_str	= implode(",", array_diff($xc_exam_arr,$pay_vid_arr));
					if(!empty($xc_exam_str)){
						$DB->query("UPDATE `cy` SET `xc_exam_value`='{$xc_exam_str}'  WHERE id='{$cyd_id}'");
					}
				}
			}
		}
		//判断这批任务里是否还存在和删除站点同样的水样类型如果没有则要更新cy表的water_type
		if(!empty($rec_rs['water_type'])){
			$water_type_rs=array();
			$water_type_rs=$DB->fetch_one_assoc("SELECT * FROM `cy_rec` WHERE cyd_id='".$_POST['cyd_id']."' AND water_type='".$rec_rs['water_type']."'");
			if(empty($water_type_rs)){
				$cy_rs=$DB->fetch_one_assoc("SELECT * FROM `cy` WHERE id='".$_POST['cyd_id']."'");
				if(!empty($cy_rs['water_type'])){
					$water_type_arr=explode(',',$cy_rs['water_type']);
					$unset_key=array_search($rec_rs['water_type'],$water_type_arr);
					if(isset($unset_key)){
						unset($water_type_arr[$unset_key]);
					}
					$water_type_str=implode(',',$water_type_arr);
					$DB->query("UPDATE `cy` SET water_type='".$water_type_str."' WHERE id='".$_POST['cyd_id']."'");
				}
			}
		}
		//重新获取水样类型
		$cy_wt_rs=$DB->fetch_one_assoc("SELECT * FROM `cy` WHERE id='".$_POST['cyd_id']."'");
		//获取本批次的水样类型
		if(!empty($cy_wt_rs['water_type'])){
			$wt_sql="SELECT * FROM `leixing` where id IN (".$cy_wt_rs['water_type'].")";
			$wt_query=$DB->query($wt_sql);
			while($wt_rs=$DB->fetch_assoc($wt_query)){
			$water_type_arrs[]=$wt_rs['lname'];
			}
			$water_types=implode(',',$water_type_arrs);
			$data['water_types']=$water_types;
		}else{
			$data['water_types']='';
		}
		//获取样品数量
		$yp_rs=$DB->fetch_one_assoc("SELECT count(*) as yp_nums FROM `cy_rec` WHERE cyd_id='".$_POST['cyd_id']."' AND sid>-1");
		if(!empty($yp_rs)){
			$data['yp_nums']=$yp_rs['yp_nums'];
		}else{
			$data['yp_nums']=0;
		}
	}
	echo JSON($data);
	exit();
	
}
if(($_POST['cyd_id'])){
	$cyd_id = $_POST['cyd_id'];
}
//获取cy表里的数据
$cyd      = get_cyd( $cyd_id );
//包含这个文件是为了执行更新操作
include("../xd_csrw/csrw_tzd_save.php");
if(!empty($_POST['vid'])){
	//包含这个文件是为了执行删除站点项目的操作
	include("../xd_csrw/add_hy_item_save.php");
}
if($_POST['csrw_xdcy_user']){
	if($cyd['cy_flag']=='1'){
		gotourl("cy_tzd.php?cyd_id={$_POST[cyd_id]}");
	}else{
		gotourl("../xd_csrw/fp_csrw.php?cyd_id={$_POST[cyd_id]}");
	}	
}else{
	gotourl("modi_csrw_tzd.php?cyd_id={$_POST[cyd_id]}");
}
?>