<?php
/**
 * 功能：用户添加页面
 * 作者：韩枫
 * 日期：2014-04-10
 * 描述：$daohang数组内容为导航栏内容
	$qx_one_arr// 来自qx.php-->权限一维数组array([group1]=>'站网维护',[site_manage]=>'站点管理',[group2]='任务下达',[xd_cy_rw]...) 
*/
include("../temp/config.php");
include("./qx.php");
//#########导航
$daohang = array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'权限管理','href'=>'user_manage/user_manage_list.php'),
        array('icon'=>'','html'=>'新建用户','href'=>'user_manage/user_manage_user_new.php')
);
$trade_global['daohang'] = $daohang;

$fzx_id  = $u['fzx_id'];//分中心id
########取出所有用户组
$checkbox_group = '';
$group_qx= array();
$group   = $DB->query("select * from `users` where `group`='0' and `fzx_id`='".$fzx_id."' order by `id`");
while($aGroup = $DB->fetch_assoc($group)){
	$group_qx[$aGroup['id']] = $aGroup;
	//$checkbox_group .= "<label class='bianse' style='min-width:123px;white-space:nowrap;margin:10px 10px 10px 30px'><input type='checkbox' name='group[]' group_id='$aGroup[id]' value='".$aGroup['userid']."' />".$aGroup['userid']."</label>";
	$checkbox_group	.= "<label class='bianse' style='float:left;margin-bottom:1px;margin-left:1px;height:43px;width:130px;border:0px #D7D7D7 solid;'><input type='checkbox' name='group[]' group_id='$aGroup[id]' value='".$aGroup['userid']."' />".$aGroup['userid']."</label>";
}
 
########取出所有权限
$checkboxQx = '';
foreach($qx_one_arr as $key=>$value){
        if(stristr($key,"group")){//权限按功能分组
		$checkboxQx .= "<label style='display:block;background-color:#F5F5F5;font-weight:bold;'>".$value."</label>";//<input type='checkbox' name='fenzu' valur='' />".$key."</label>";
	}
        else{
		$group_str = "|";
		//判断哪些数组已经拥有这个权限，记录下来id 。方便页面上选择用户组的同时 选中对应权限的功能
		foreach($group_qx as $group_id=>$group_arr){
               		if($group_arr[$key]=='1'){
				$group_str .= $group_id."|";
			}
		}
		//$checkboxQx .= "<label class='bianse' style='min-width:123px;white-space:nowrap;margin:10px 10px 10px 30px'><input type='checkbox' groupid$group_id='yes'group='$group_str' name='qx[]' value='".$key."' />".$value."</label>";
		$checkboxQx	.= "<label class='bianse' style='margin-bottom:1px;margin-left:1px;height:43px;line-height:43px;width:130px;border:0px #D7D7D7 solid;'><input type='checkbox' groupid$group_id='yes'group='$group_str' name='qx[]' value='".$key."' />".$value."</label>";
	}
}

disp("user_manage_user_new.html");
?>
