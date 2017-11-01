<?php
/**
 * 功能：用户信息修改页面
 * 作者：韩枫
 * 日期：2014-04-11
 * 描述：$daohang数组内容为导航栏内容
 *	$qx_one_arr// 来自qx.php-->权限一维数组array([group1]=>'站网维护',[site_manage]=>'站点管理',[group2]='任务下达',[xd_cy_rw]...) 
*/
include("../temp/config.php");
include("./qx.php");
//#########导航
$daohang = array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'权限管理','href'=>'user_manage/user_manage_list.php'),
        array('icon'=>'','html'=>"{{$_GET['user_name']}}用户信息修改",'href'=>"user_manage/user_manage_user_modify.php?uid={$_GET['uid']}&user_name={$_GET['user_name']}")
);
$trade_global['daohang'] = $daohang;
########必要条件验证
if(empty($_GET['uid'])){
        echo "<script>alert('系统不能识别该用户，请返回重试');location.href='user_manage_list.php'</script>";
}

$fzx_id    = $u['fzx_id'];//分中心id
########取出此用户的信息
$rs_users  = $DB->fetch_one_assoc("select * from `users` where id='{$_GET['uid']}'");
########判断此用户是不是最后一个拥有“权限管理”权限的人
$manage_user_sql	= $DB->query("SELECT id FROM `users` WHERE `fzx_id`='{$fzx_id}' AND `user_manage`='1' AND `group`!='测试组' AND `group`!='0' ");
$manage_user_num	= $DB->num_rows($manage_user_sql);
$manage_zt	= 'yes';//权限管理的修改状态
if($manage_user_num<=1 && $rs_users['user_manage']=='1'){
	$manage_zt	= 'no';
}
//把用户组转换为数组
$group_arr = array();
if(!empty($rs_users['group'])){
	$group_arr = explode("|",$rs_users['group']);
}
//默认用户性别
if($rs_users['sex']=='男'){
	$sex_option = "<option value='男' selected>男</option><option value='女'>女</option>";
}
elseif($rs_users['sex']=='女'){
	$sex_option = "<option value='男'>男</option><option value='女' selected>女</option>";
}
else{
	$sex_option = "<option value='男'>男</option><option value='女'>女</option>";
}

########取出所有用户组
$checkbox_group= $checked_group = '';
$group_qx      = array();
$group         = $DB->query("select * from `users` where `group`='0' and `fzx_id`='".$fzx_id."' order by `id`");
while($aGroup  = $DB->fetch_assoc($group)){
	$group_qx[$aGroup['id']] = $aGroup;
	if(in_array($aGroup['userid'],$group_arr)){//判断该用户是否属于该用户组，然后默认选中
		//$checked_group  .= "<label class='bianse' style='min-width:123px;font-weight:bold;color:#008000;white-space:nowrap;margin:10px 10px 0px 30px;'><input type='checkbox' name='group[]' group_id='{$aGroup['id']}' value='".$aGroup['userid']."' checked/>".$aGroup['userid']."</label>";
		$checked_group	.= "<label class='bianse' style='float:left;margin-bottom:1px;margin-left:1px;height:43px;width:130px;border:0px #D7D7D7 solid;font-weight:bold;color:#008000;'><input type='checkbox' name='group[]' group_id='{$aGroup['id']}' value='".$aGroup['userid']."' checked/>".$aGroup['userid']."</label>";
	}
	else{
		//$checkbox_group .= "<label class='bianse' style='min-width:123px;white-space:nowrap;margin:10px 10px 0px 30px;'><input type='checkbox' name='group[]' group_id='{$aGroup['id']}' value='".$aGroup['userid']."' />".$aGroup['userid']."</label>";
		$checkbox_group	.= "<label class='bianse' style='float:left;margin-bottom:1px;margin-left:1px;height:43px;width:130px;border:0px #D7D7D7 solid;'><input type='checkbox' name='group[]' group_id='{$aGroup['id']}' value='".$aGroup['userid']."' />".$aGroup['userid']."</label>";
	}
}
$checkbox_group = $checked_group."<br /><br /><br />".$checkbox_group; 

########取出所有权限
$checkboxQx = '';
foreach($qx_one_arr as $key=>$value){
        //判断是不是总中心，不然不显示 分中心管理权限！！！!!!未完成
        if(stristr($key,"group")){//权限按功能分组
		$checkboxQx .= "<label style='display:block;background-color:#F5F5F5;font-weight:bold;'>".$value."</label>";//<input type='checkbox' name='fenzu' valur='' />".$key."</label>";
	}
        else{
		//判断哪些数组已经拥有这个权限，记录下来id 。方便页面上选择用户组的同时 选中对应权限的功能
		$group_str = "|";
		foreach($group_qx as $group_id=>$group_arr){
               		if($group_arr[$key]=='1'){
				$group_str .= $group_id."|";
			}
		}
		//判断该用户是否有该权限，然后默认选中
		if($rs_users[$key]=='1'){
			$no_modify = "";
			if($key=='user_manage' && $manage_zt=='no'){
				$no_modify = " no_modify";
			}
			//$checkboxQx .= "<label class='bianse' style='min-width:123px;white-space:nowrap;margin:10px 10px 10px 30px;font-weight:bold;color:#008000;'><input type='checkbox' groupid$group_id='yes'group='$group_str' name='qx[]' value='".$key."' checked/>".$value."</label>";
			$checkboxQx	.= "<label class='bianse' style='margin-bottom:1px;margin-left:1px;height:43px;line-height:43px;width:130px;border:0px #D7D7D7 solid;font-weight:bold;color:#008000;'><input type='checkbox' class=' $no_modify' groupid$group_id='yes'group='$group_str' name='qx[]' value='".$key."' checked  />".$value."</label>";
		}else{
			//$checkboxQx .= "<label class='bianse' style='min-width:123px;white-space:nowrap;margin:10px 10px 10px 30px'><input type='checkbox' groupid$group_id='yes'group='$group_str' name='qx[]' value='".$key."' />".$value."</label>";
			$checkboxQx	.= "<label class='bianse' style='margin-bottom:1px;margin-left:1px;height:43px;line-height:43px;width:130px;border:0px #D7D7D7 solid;'><input type='checkbox' groupid$group_id='yes'group='$group_str' name='qx[]' value='".$key."' />".$value."</label>";
		}
	}
}

disp("user_manage_user_modify.html");
?>
