<?php
//基本变量设置
include("$rootdir/user_manage/qx.php");
$menu		= array();
$qxArr		= array();
$qx	= $qx_keys;
$selectqx 	= "`qx` IN (''";
if($u['test']==1) $selectqx .=",'test'";
$n=count($qx);
for($i=0;$i<$n;$i++){
	if($u[$qx[$i]]==1){
		$selectqx .=",'$qx[$i]'";
		$qxArr[] = $qx[$i];
	}
}
$selectqx.= ')';
$selectqx = " `qx` != 'admin'";
//提取一级菜单
//$parent_qx	= array();
$p_query  = $DB->query("SELECT * FROM `menu` WHERE `parent_id` = '0' ORDER BY `sort`");
while($parent = $DB->fetch_assoc($p_query)){
	$parent_qx	= explode(",",$parent['qx']);
	$parent_qx	= array_intersect($parent_qx,$qxArr);
	if(!empty($parent_qx) || ($u['admin']=='1' && stristr($parent['qx'],'admin')) || empty($parent['qx'])){
		$menu[$parent['id']]['p']=$parent;
	}

	//下达采样任务 的 二级菜单 统一用global.inc.php 的$global['site_type']配置
	if($parent['name']=='下达采样任务' && !empty($global['site_type'])){
		//判断有没有 下达采样任务的权限
		if(!empty($menu[$parent['id']])){
			foreach($global['site_type'] as $key=>$val){
				$menu[$parent['id']]['c'][]	= array('id'=>'','parent_id'=>'','name'=>$val,'url'=>"xd_cyrw/xd_cyrw_index.php?site_type=$key target=main",'sort'=>'','title'=>"下达".$val,'qx'=>'','icon'=>'');
			}
		}
		continue;
	}
	$sql 	= "SELECT * FROM `menu` WHERE `parent_id` = $parent[id] ORDER BY `sort`";
	$c_query= $DB->query($sql);
	$child_qx	= array();
	$child_qx_num	= 0;
	while($child = $DB->fetch_assoc($c_query)){
		$child_qx_num++;
		$child_qx	= explode(",",$child['qx']);
		$child_qx	= array_intersect($child_qx,$qxArr);
		if(!empty($child_qx) || ($u['admin']=='1' && stristr($child['qx'],'admin')) || empty($child['qx'])){
			//如果用户有二级菜单的权限，那应该同时把以及菜单显示出来
			if(empty($menu[$parent['id']]['p'])){
				$menu[$parent['id']]['p']=$parent;
			}
			$menu[$parent['id']]['c'][]=$child;
		}
	}
	//如果系统一级菜单内存在二级菜单，但该用户一个二级菜单的权限都没有。那么默认将一级菜单隐藏
	if($child_qx_num>0 && empty($menu[$parent['id']]['c'])){
		unset($menu[$parent['id']]);
	}
}
$bg_color = array('danger','warning','success','info','primary','default','purple','pink','inverse','grey','light','yellow');
?>