<?php
/**
 * 功能：用户组修改页面
 * 作者：韩枫
 * 日期：2014-04-07
 * 描述：$daohang数组内容为导航栏内容
*/

include("../temp/config.php");
include("./qx.php");
//#########导航
$daohang = array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'权限管理','href'=>'user_manage/user_manage_list.php'),
        array('icon'=>'','html'=>"用户组:{{$_GET['group_name']}}信息修改",'href'=>"user_manage/user_manage_group_modify.php?uid={$_GET['uid']}&group_name={$_GET['group_name']}")
);
$trade_global['daohang'] = $daohang;

$fzx_id       = $u['fzx_id'];//分中心id
if(empty($_GET['uid'])){
	echo "<script>alert('系统未能识别该组，请刷新页面后重试');location.href='user_manage_list.php'</script>";
	exit;
}
$checkboxUser = $checkedUser = $checkboxQx = '';
###########取出该用户组的所有信息
$rs_group        = $DB->fetch_one_assoc("SELECT * FROM `users` WHERE id='".$_GET['uid']."'");
//如果除了这个分组内的其他成员都没有”权限管理“权限，则不允许删除这个分组
if($rs_group['user_manage']==1){
	$user_manage_users	= $DB->fetch_one_assoc("SELECT id FROM `users` WHERE fzx_id='".$fzx_id."' AND `user_manage`='1' AND `group`!='0' AND `group`!='测试组' AND `group` NOT LIKE '%|$rs_group[userid]|%' limit 1");
	$manage_modify_zt	= "yes";
	if(empty($user_manage_users)){
		$manage_modify_zt	= 'no';
	}
}
##########取出 该分中心 所有的用户
$sqlUser          = $DB->query("SELECT * FROM `users` WHERE `fzx_id`='".$fzx_id."' AND `group`!='测试组' AND `group`!='0' ");
while($rsUser = $DB->fetch_assoc($sqlUser)){
	$group_name_arr = explode("|",$rsUser['group']);
	if(in_array($rs_group['userid'],$group_name_arr)){
		//$checkedUser  .= "<label class='bianse' style='font-weight:bold;color:#008000;width:90px;white-space:nowrap;'><input type='checkbox' name='userid[]' value='".$rsUser['id']."' checked />".$rsUser['userid']."</label>";
		$checkedUser	.= "<label class='bianse' style='float:left;margin-bottom:1px;margin-left:1px;height:43px;width:100px;border:0px #D7D7D7 solid;font-weight:bold;color:#008000;'><input type='checkbox' name='userid[]' value='".$rsUser['id']."' checked />".$rsUser['userid']."</label>";
	}
	else{
		//$checkboxUser .= "<label class='bianse' style='width:90px;white-space:nowrap;'><input type='checkbox' name='userid[]' value='".$rsUser['id']."' />".$rsUser['userid']."</label>";
		$checkboxUser	.= "<label class='bianse' style='float:left;margin-bottom:1px;margin-left:1px;height:43px;width:90px;border:0px #D7D7D7 solid;'><input type='checkbox' name='userid[]' value='".$rsUser['id']."' />".$rsUser['userid']."</label>";
	}
}
if($checkedUser){
	$checkboxUser	= $checkedUser."<br /><br /><br />".$checkboxUser;//这里是为了把已选择的用户和未选择的用户分开显示，方便操作
}
########取出所有权限
$old_qx = '';
foreach($qx as $key=>$qxArr){
	//判断是不是总中心，不然不显示 分中心管理权限 ###未完成
	$checkboxQx .= "<label style='display:block;background-color:#F5F5F5;font-weight:bold;'>".$key."</label>";//<input type='checkbox' name='fenzu' valur='' />".$key."</label>";
	foreach($qxArr as $qxValue=>$qxName){
		if($rs_group[$qxValue]=='1'){
			$manage_modify_class	= "";
			if($qxValue=='user_manage' && $manage_modify_zt=='no'){
				$manage_modify_class	= 'no_modify';
			}
			//$checkboxQx .= "<label class='bianse' style='font-weight:bold;color:#008000;min-width:123px;white-space:nowrap;margin:10px 10px 10px 30px'><input type='checkbox' name='qx[]' value='".$qxValue."' checked/>".$qxName."</label>";
			$checkboxQx	.= "<label class='bianse' style='margin-bottom:1px;margin-left:1px;height:43px;line-height:43px;width:130px;border:0px #D7D7D7 solid;font-weight:bold;color:#008000;'><input type='checkbox' class='{$manage_modify_class}' name='qx[]' value='".$qxValue."' checked/>".$qxName."</label>";
			$old_qx	.= $qxValue."|";
		}
		else{
			//$checkboxQx .= "<label class='bianse' style='min-width:123px;white-space:nowrap;margin:10px 10px 10px 30px'><input type='checkbox' name='qx[]' value='".$qxValue."' />".$qxName."</label>";
			$checkboxQx	.= "<label class='bianse' style='margin-bottom:1px;margin-left:1px;height:43px;line-height:43px;width:130px;border:0px #D7D7D7 solid;'><input type='checkbox' name='qx[]' value='".$qxValue."' />".$qxName."</label>";
		}
	}
}
$old_qx = substr($old_qx,0,-1);
disp('user_manage_group_modify.html');
?>
