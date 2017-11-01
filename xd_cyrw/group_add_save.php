<?php
/**
 * 功能：项目修改页面（包括全程空白、现场平行、站点项目的修改）
 * 作者：韩枫
 * 日期：2014-08-13
 * 描述
*/
include("../temp/config.php");
$fzx_id         = $u['fzx_id'];
$jieGuo = "no";
if($_POST['action']=='group_add' || $_POST['action']=='group_modify'){
	//如果没有选站点就添加一条 默认的记录
	/*if(empty($_POST['sites'])){
		$DB->query("INSERT INTO `sites` SET fzx_id='$fzx_id',water_type='1',site_type='{$_POST['site_type']}',site_name='未添加'");
                $new_sites_id = $DB->insert_id();
                if((int)$new_sites_id>0){
			$_POST['sites'][]	= $new_sites_id;
		}
	}*/
	//往批次表里添加数据
	if($_POST['action']=='group_modify'){
		if($_POST['group_name']!=$_POST['group_name_old']){
        		$DB->query("UPDATE `site_group` SET `group_name`='{$_POST['group_name']}' WHERE fzx_id='$fzx_id' AND `group_name`='{$_POST['group_name_old']}'");
			$jieGuo = 'yes';
        	}
        }
	//获取就批次的批次排序,新加入的站点要更新为就批次的排序
	$old_group_sort	= $DB->fetch_one_assoc("SELECT `sort` FROM `site_group` WHERE  fzx_id='$fzx_id' AND `group_name`='{$_POST['group_name']}' AND `sort`!='' AND `sort` is not null limit 1");
	if(empty($old_group_sort['sort'])){
		$old_group_sort['sort']	= 0;	
	}
	if(!empty($_POST['sites']) && is_array($_POST['sites'])){
		$gr_id	= implode(",",$_POST['sites']);
		$new_gr_ids	= $gr_id;
		$site_id_post	= array();
		$sql_gr_value	= $DB->query("SELECT id,fzx_id,site_id,group_name,assay_values FROM `site_group` where id in({$gr_id})");
		//foreach($_POST['sites'] as $site_id){
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
							$DB->query("INSERT INTO `site_group` SET fzx_id='$fzx_id',site_id='{$rs_gr_value['site_id']}',site_type='{$_POST['site_type']}',group_name='{$_POST['group_name']}',sort='{$old_group_sort['sort']}',act='1',ctime='".date('Y-m-d H:i:s')."',cuser='{$u['userid']}',assay_values='{$rs_gr_value['assay_values']}'");
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
			}else{//在添加新站点页面，已经添加到该批次的站点
				$jieGuo = 'yes';
			}
		}
		//这里后期应该把相同sites的站点，去除掉，一个批次里不应有相同的sites
		$DB->query("UPDATE `site_group` SET act='0' WHERE `fzx_id`='{$fzx_id}' AND `group_name`='{$_POST['group_name']}' AND id not in ({$new_gr_ids})");
	}
}//else if($_POST['action']=='group_modify'){
	
//}
echo json_encode(array('jieGuo'=>$jieGuo));
?>
