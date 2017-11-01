<?php
include '../temp/config.php';
$fzx_id	= $u['fzx_id'];
//检测监督任务分配的项目是否被配置。
if($_POST['action']=='peizhi'){
	$groups = $_POST['siteid']; 
	$re1 = $DB->query("select xmid,lxid from xmfa where fzx_id = ".$fzx_id." and fangfa<>0 and act<>'0'");
	while($xm = $DB->fetch_assoc($re1)){
		$idzong[] = $xm[xmid];
		$wy[$xm[xmid]][$xm[lxid]] = $xm[lxid];
	}
	$re2 = $DB->query("select id,value_C from assay_value");
	while($xm1 = $DB->fetch_assoc($re2)){
		$zong[$xm1[id]] = $xm1[value_C];
	}	
	$wrong =array();
	
	$re = $DB->query("select gr.id,gr.assay_values,s.water_type from site_group as gr left join sites as s on gr.site_id=s.id where gr.id in (".$groups.")");
	while($xinxi = $DB->fetch_assoc($re)){
		$zj ='';
		$xm = explode(',',$xinxi['assay_values']);
		if(!empty($xinxi['assay_values'])){
			foreach($xm as $x){
				if(!in_array($x,$idzong)){
					$zj .= ','.$zong[$x];
				}
				elseif(in_array($x,$idzong)&&(empty($wy[$x][$xinxi['water_type']]))){
					$zj .= ','.$zong[$x];
				}
			}
			if(!empty($zj)){
				$zj = substr($zj,1);
				$wrong[$xinxi[id]] = $zj;
			}
		}	
	}
	if(empty($wrong)){
		 echo 'all';
	}else{
		echo json_encode($wrong);
	}
}else if($_GET['action']=='get_cy_user'){
	$cy_user	= array();
	$rs_cy_user	= $DB->fetch_one_assoc("SELECT cy_user,cy_user2 FROM `cy` WHERE `cy_flag`!='0' AND `group_name`='{$_GET['group_name']}' AND `cy_user`!='' AND `cy_user` IS NOT NULL ORDER BY id desc LIMIT 1");
	$cy_user['cy_user1']	= $rs_cy_user['cy_user'];
	$cy_user['cy_user2']	= $rs_cy_user['cy_user2'];
	echo json_encode($cy_user);
}