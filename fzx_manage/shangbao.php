<?php
/*
*功能：将月报的数据改为上报至总中心的状态
*作者：hanfeng
*时间：2015-05-19
 */
include '../temp/config.php';
if($u['userid']==''){
	nologin();
}
$fzx_id=$u['fzx_id'];
//判断是月报状态
if($_GET['action']=='month_export'){
	$cyd_ids_str	= $sites_str	= '';
	if(!empty($_GET['year']) && !empty($_GET['month'])){
		$cy_sql	= $DB->query("SELECT id,sites FROM `cy` WHERE `cy_date` LIKE '".$_GET['year']."-".$_GET['month']."%' AND `fzx_id`='$fzx_id'");
		while($cy_arr	= $DB->fetch_assoc($cy_sql)){
			$cyd_ids_str	.= $cy_arr['id'].',';
			if(!empty($cy_arr['sites'])){
				$sites_str	.= $cy_arr['sites'].",";
			}
		}
		if(!empty($cyd_ids_str)){
			$cyd_ids_str= substr($cyd_ids_str, 0,-1);
		}
		if(!empty($sites_str)){
			$sites_str	= substr($sites_str, 0,-1);
		}
	}
	if(!empty($_GET['tjcs'])){
		$site_where	= '';
		if(!empty($sites_str)){
			$site_where	= " AND `id` in ({$sites_str}) ";
		}
		$sites_str	= '';
		$sites_sql	= $DB->query("SELECT id FROM `sites` WHERE (`fzx_id`='$fzx_id' OR `fp_id`='$fzx_id') AND tjcs LIKE '%,{$_GET['tjcs']},%' $site_where");
		while($sites_arr	= $DB->fetch_assoc($sites_sql)){
			$sites_str	.= $sites_arr['id'].",";
		}
		if(!empty($sites_str)){
			$sites_str	= substr($sites_str, 0,-1);
		}
	}
	if(!empty($cyd_ids_str)){
		$order_where	= '';
		if(!empty($sites_str)){
			$order_where	= " AND `sid` in ({$sites_str}) ";
		}
		echo "UPDATE `assay_order` SET `assay_over`='over' WHERE cyd_id in ($cyd_ids_str) $order_where";
		//将对应时间下的对应站点对应项目的assay_order表的记录的assay_over状态都改为over
		$DB->query("UPDATE `assay_order` SET `assay_over`='over' WHERE cyd_id in ($cyd_ids_str) $order_where");
		//向n_set表插入一条可识别此报告以上报的记录
		$DB->query("INSERT INTO `n_set` SET fzx_id='$fzx_id',module_name='month_export_shangbao',module_value1='finish',module_value2='".$_GET['year']."-".$_GET['month']."',module_value3='{$_GET['tjcs']}'");
		if($DB->insert_id()>0){
			//更改成功
			gotourl("$rooturl/fzx_manage/water_area_month_export.php?year={$_GET['year']}&&month={$_GET['month']}&&action=view&&tjcs={$_GET['tjcs']}&&fzx_id=$fzx_id");
			exit;
		}
		echo "<script>alert('数据上报失败，请联系管理员');</scirpt>";
		gotourl("$rooturl/fzx_manage/water_area_month_export.php?year={$_GET['year']}&&month={$_GET['month']}&&action=view&&tjcs={$_GET['tjcs']}&&fzx_id=$fzx_id");
	}
}

?>