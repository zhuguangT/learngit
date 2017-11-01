<?php
/**
 * 功能：项目修改页面（包括全程空白、现场平行、站点项目的修改）
 * 作者：韩枫
 * 日期：2014-04-24
 * 描述
*/
include("../temp/config.php");

//登陆及权限判断
if($u['xd_cy_rw']!='1' && $u['xd_csrw']!='1'){
        //跳转到登陆页
        echo "没有权限";
        exit;
}
$fzx_id         = $u['fzx_id'];

//暂不能用于 站点项目修改
if($_POST['action']=='qckb_value'||$_POST['action']=='xcpx_value'){
	if(!empty($_POST['vid'])){
		$values_str	= implode(",",$_POST['vid']);
	}else{
		$values_str	= '';
	}
	if(!empty($_POST['id'])){
		$sql_value_modify	= $DB->query("UPDATE `n_set` SET module_value1='$values_str'  WHERE id='{$_POST['id']}'");
		echo "<script>alert('项目修改成功');location.href='values_modify.php?site_type={$_POST['site_type']}&action={$_POST['action']}'</script>";
	}else{//如果没有数据就直接到数据库里插入一条数据
		$DB->query("INSERT INTO `n_set` SET fzx_id='$fzx_id',`module_name`='{$_POST['action']}',module_value1='$values_str',module_value2='{$_POST['site_type']}'");
		echo "<script>alert('项目添加成功');location.href='values_modify.php?site_type={$_POST['site_type']}&action={$_POST['action']}'</script>";
	}
}else if($_POST['action']=='xdrw_xcpx'){//批次现场平行项目的修改
	$jieGuo = "no";
	if(!empty($_POST['id'])){
		if(!empty($_POST['vid'])){
			$xcpx_value_num	= count($_POST['vid']);
			if(is_array($_POST['vid'])){
                		$values_str     = implode(",",$_POST['vid']);
			}else{
				$values_str	= $_POST['vid'];
			}
        	}else{
			$xcpx_value_num	= 0;
                	$values_str     = '';
        	}
                $sql_value_modify       = $DB->query("UPDATE `site_group` SET `xcpx_values`='$values_str'  WHERE id='{$_POST['id']}'");
		$jieGuo	= "yes";
        }
	echo json_encode(array('jieGuo'=>$jieGuo,'action'=>$_POST['action'],'num'=>$xcpx_value_num,'gr_id'=>$_POST['id']));
}else if($_POST['action']=='site_value'){//站点项目修改
	$jieGuo = "no";
	if(!empty($_POST['id'])){
                if(!empty($_POST['vid'])){
                        if(is_array($_POST['vid'])){
                                $values_str     = implode(",",$_POST['vid']);
                        }else{
                                $values_str     = $_POST['vid'];
                        }
                }else{
                        $values_str     = '';
                }
                $sql_value_modify       = $DB->query("UPDATE `site_group` SET `assay_values`='$values_str'  WHERE id='{$_POST['id']}'");
                /*if($u['is_zz']=='1'){
                        //判断是不是分配给分中心了
                        $sql_site_fzx   = $DB->fetch_one_assoc("SELECT sites.id,sites.fp_id FROM `sites` INNER JOIN `site_group` on sites.id=site_group.site_id WHERE site_group.id='{$_POST['id']}' AND sites.fzx_id != sites.fp_id");
                        //修改分中心group表里的项目数量
                        $DB->query("UPDATE `site_group` SET `assay_values`='$values_str'  WHERE site_id='{$sql_site_fzx['id']}' AND fzx_id='{$sql_site_fzx['fp_id']}'");
                }*/
                $jieGuo = "yes";
        }
        echo json_encode(array('jieGuo'=>$jieGuo,'action'=>$_POST['action'],'num'=>count($_POST['vid']),'gr_id'=>$_POST['id']));
}else if($_POST['action']=='group_value'){//批次项目修改
	$jieGuo = "no";
	//监督任务修改“未分配统计参数”的项目的时候，是没有批次名称的
        if(!empty($_POST['id']) || $_POST['site_type']=='0'){
                if(!empty($_POST['vid'])){
                        if(is_array($_POST['vid'])){
                                $values_str     = implode(",",$_POST['vid']);
                        }else{
                                $values_str     = $_POST['vid'];
                        }
                }else{
                        $values_str     = '';
                }
		$where_group	= '';
		if(!empty($_POST['fp_sites'])){
			$where_group	= " AND site_id not in({$_POST['fp_sites']})";
		}
                $sql_value_modify       = $DB->query("UPDATE `site_group` SET `assay_values`='$values_str'  WHERE fzx_id='$fzx_id' AND act='1' AND `group_name`='{$_POST['id']}' $where_group");
                /*if($u['is_zz']=='1'){
                        //判断里面有多少个站点是分配给分中心的
                        $sql_sites_fzx  = $DB->query("SELECT si.id,si.fp_id FROM `sites` AS si INNER JOIN `site_group` AS gr ON si.id=gr.site_id where gr.fzx_id='$fzx_id' AND gr.act='1' AND gr.`group_name`='{$_POST['id']}' AND si.fzx_id!=si.fp_id");
                        while($rs_sites_fzx= $DB->fetch_assoc($sql_sites_fzx)){
                                //修改分中心group表里的项目数量
                                $DB->query("UPDATE `site_group` SET `assay_values`='$values_str'  WHERE site_id='{$rs_sites_fzx['id']}' AND fzx_id='{$rs_sites_fzx['fp_id']}'");
                        }
                }*/
                $jieGuo = "yes";
        }
        echo json_encode(array('jieGuo'=>$jieGuo,'action'=>$_POST['action'],'num'=>count($_POST['vid']),'gr_name'=>$_POST['id']));
}
?>
