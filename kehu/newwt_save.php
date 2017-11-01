<?php

include("../temp/config.php");
require_once __SITE_ROOT . "inc/site_func.php";
$fzx_id         = $u['fzx_id'];
$site_type = '3';
if($_POST['wtdw']&&$_POST['sites']){
	$wt['kid'] = $_POST['wtdw'];
	$wt['wt_date'] = $_POST['wt_date'];//日期
	$wt['fenlei'] = $_POST['fenlei'];//分类
	$wt['wt_xz'] = $_POST['wt_xz'];//委托性质
	$wt['jcsx'] = $_POST['jcsx'];//检测时间限制
	$wt['cyfs'] = $_POST['cyfs'];//采样方式
	$wt['ypcz'] = $_POST['ypcz'];//样品处置
	$wt['jcyj'] = $_POST['jcyj'];//检测依据
	$wt['wt_note'] = $_POST['wt_note'];//其他说明
	$wt['yueding'] = $_POST['yueding'];//约定
	$wt['group_name'] = $_POST['group_name'];//批名
	$wt['sites'] =  implode(',',$_POST['sites']);
	$gids =  implode(',',$_POST['gids']);//每个站点对应的批次id
	$wt['group_ids'] = $gids;
	//sql组合
	if(($_POST['submit']=='确认修改')&&$_POST['wtid']&&($_POST['fuzhi']!='fuzhi')){
		$wtsql = "update kehu_wt set ";
	}else{
		$wtsql = "insert into kehu_wt set ";
	}
	foreach($wt as $kk=>$vv){
		$wtsql .= $kk."='".$vv."',";
	}
	$wtsql = substr($wtsql,0,strlen($wtsql)-1); 
	if(($_POST['submit']=='确认修改')&&$_POST['wtid']&&($_POST['fuzhi']!='fuzhi')){
		$wtsql .= " where id='".$_POST['wtid']."' ";
	}
	$re = $DB->query($wtsql);
	//站点添加

	//获取就批次的批次排序,新加入的站点要更新为就批次的排序
	$old_group_sort	= $DB->fetch_one_assoc("SELECT `sort` FROM `site_group` WHERE  fzx_id='$fzx_id' AND `group_name`='{$_POST['group_name']}' AND `sort`!='' AND `sort` is not null limit 1");
	if(empty($old_group_sort['sort'])){
		$old_group_sort['sort']	= 0;	
	}
	if(!empty($_POST['sites']) && is_array($_POST['sites'])){
		$gr_id	= $gids;
		$new_gr_ids	= $gr_id;
		$site_id_post	= array();
		$sql_gr_value	= $DB->query("SELECT id,fzx_id,site_id,group_name,assay_values FROM `site_group` where id in({$gr_id})");
		while($rs_gr_value = $DB->fetch_assoc($sql_gr_value)){
			if($rs_gr_value['group_name']!=$_POST['group_name']){
				if(empty($rs_gr_value['group_name']) && $rs_gr_value['fzx_id']==$fzx_id){//之前没有批名，这里更改就可以了
					$site_id_post[$rs_gr_value['site_id']]['assay_values']	= $rs_gr_value['assay_values'];
					$DB->query("UPDATE `site_group` SET fzx_id='$fzx_id',group_name='{$_POST['group_name']}',sort='{$old_group_sort['sort']}',act='1',ctime='".date('Y-m-d H:i:s')."',cuser='{$u['userid']}' WHERE id='{$rs_gr_value['id']}'");
					$yxrow=$DB->affected_rows();
					if((int)$yxrow>0){
						$jieGuo  = 'yes';
					}
				}else{//之前已经有分批了，这里就要重新插入新批次
					//批次里选择了多个来源于不同批次的相同站点，需要整个相同站点的化验项目
					if(array_key_exists($rs_gr_value['site_id'], $site_id_post)){
						//整合此站点的化验项目
						$rs_gr_value['assay_values']	.= ",".$site_id_post[$rs_gr_value['site_id']]['assay_values'];
						$rs_gr_value['assay_values']	 = implode(",",array_filter(array_unique(explode(",",$rs_gr_value['assay_values']))));
						//记录站点及整合后化验项目
						$site_id_post[$rs_gr_value['site_id']]['assay_values']	= $rs_gr_value['assay_values'];
						//更新到site_group表中
						$DB->query("UPDATE `site_group` SET `assay_values`='{$rs_gr_value['assay_values']}' WHERE id='".$site_id_post[$rs_gr_value['site_id']]['gr_id']."'");
						$yxrow=$DB->affected_rows();
						if((int)$yxrow>0){
							$jieGuo  = 'yes';
						}
					}else{
						//先判断group表中是不是已经有改site_id的记录，如果有就更新此记录，没有就插入新记录
						$gr_old_sites	 = $DB->fetch_one_assoc("SELECT id,site_id,assay_values FROM `site_group` WHERE fzx_id='$fzx_id' AND site_id='{$rs_gr_value['site_id']}' AND group_name='{$_POST['group_name']}' AND id in({$gr_id})");
						if(!empty($gr_old_sites['id'])){
							//整合此站点的化验项目
							$rs_gr_value['assay_values']	.= ",".$gr_old_sites['assay_values'];
							$rs_gr_value['assay_values']	 = implode(",",array_filter(array_unique(explode(",",$rs_gr_value['assay_values']))));
							//记录站点及整合后化验项目
							$site_id_post[$gr_old_sites['site_id']]['gr_id']	= $gr_old_sites['id'];
							$site_id_post[$gr_old_sites['site_id']]['assay_values']	= $rs_gr_value['assay_values'];
							//更新到site_group表中
							$DB->query("UPDATE `site_group` SET `assay_values`='{$rs_gr_value['assay_values']}' WHERE id='".$gr_old_sites['id']."'");
							$yxrow=$DB->affected_rows();
							if((int)$yxrow>0){
								$jieGuo  = 'yes';
							}
						}else{
							//插入新纪录到site_group表中
							$DB->query("INSERT INTO `site_group` SET fzx_id='$fzx_id',site_id='{$rs_gr_value['site_id']}',site_type='$site_type',group_name='{$_POST['group_name']}',sort='{$old_group_sort['sort']}',act='1',ctime='".date('Y-m-d H:i:s')."',cuser='{$u['userid']}',assay_values='{$rs_gr_value['assay_values']}'");
							$new_group_id	 = $DB->insert_id();
							if((int)$new_group_id>0){
								$jieGuo	 	 = 'yes';
								//记录站点及对应化验项目
								$site_id_post[$rs_gr_value['site_id']]['gr_id']	= $new_group_id;
								$site_id_post[$rs_gr_value['site_id']]['assay_values']	= $rs_gr_value['assay_values'];
								$new_gr_ids	.= ",".$new_group_id;
							}
						}
					}
				}
			}
		}
		//这里后期应该把相同sites的站点，去除掉，一个批次里不应有相同的sites
		$DB->query("UPDATE `site_group` SET act='0' WHERE `fzx_id`='{$fzx_id}' AND `group_name`='{$_POST['group_name']}' AND id not in ({$new_gr_ids})");
	}

}else{
	echo "<script>alert('添加失败，您没有添加任务站点！')</script>";
}
gotourl("$rooturl/kehu/kh_list.php?kid=".$wt['kid']);
?>
