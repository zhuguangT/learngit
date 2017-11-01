<?php
/**
 * 功能：用户组添加页面
 * 作者：韩枫
 * 日期：2014-04-04
 * 描述：$daohang数组内容为导航栏内容
*/
include("../temp/config.php");
include("./qx.php");
//#########导航
$daohang = array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'权限管理','href'=>'user_manage/user_manage_list.php'),
        array('icon'=>'','html'=>'新建用户组','href'=>'user_manage/user_manage_group_new.php')
);
$trade_global['daohang'] = $daohang;
##########取出 该分中心 所有的用户
$checkboxUser = $checkboxQx = '';
$fzx_id     = $u['fzx_id'];//分中心id
$sqlUser   = $DB->query("select * from `users` where `fzx_id`='".$fzx_id."' and `group`!='测试组' and `group`!='0' ");
while($rsUser = $DB->fetch_assoc($sqlUser)){
	//$checkboxUser .= "<label class='bianse' style='width:90px;white-space:nowrap;'><input type='checkbox' name='userid[]' value='".$rsUser['id']."' />".$rsUser['userid']."</label>";
	$checkboxUser	.= "<label class='bianse' style='float:left;margin-bottom:1px;margin-left:1px;height:43px;width:130px;border:0px #D7D7D7 solid;'><input type='checkbox' name='userid[]' value='".$rsUser['id']."' />".$rsUser['userid']."</label>";
}
########取出所有权限
foreach($qx as $key=>$qxArr){
	//判断是不是总中心，不然不显示 分中心管理权限
	$checkboxQx .= "<label style='display:block;background-color:#F5F5F5;font-weight:bold;'>".$key."</label>";//<input type='checkbox' name='fenzu' valur='' />".$key."</label>";
	foreach($qxArr as $qxValue=>$qxName){
		//$checkboxQx .= "<label class='bianse' style='min-width:123px;white-space:nowrap;margin:10px 10px 10px 30px'><input type='checkbox' name='qx[]' value='".$qxValue."' />".$qxName."</label>";
		$checkboxQx	.= "<label class='bianse' style='margin-bottom:1px;margin-left:1px;height:43px;line-height:43px;width:130px;border:0px #D7D7D7 solid;'><input type='checkbox' name='qx[]' value='".$qxValue."' />".$qxName."</label>";
	}
}
disp('user_manage_group_new.html');
?>
