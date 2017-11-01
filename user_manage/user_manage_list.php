<?php
/**
 * 功能：用户列表页面
 * 作者：韩枫
 * 日期：2014-04-04
 * 描述：$daohang数组内容为导航栏内容
*/
include("../temp/config.php");
//导航
$daohang = array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'权限管理','href'=>'user_manage/user_manage_list.php')
);
$trade_global['daohang'] = $daohang;

$fzx_id	= $u['fzx_id'];
//选出该分中心所有用户组的名称
$no_group_id  = ''; 
$group	= $DB->query("select `id`,`px`,`userid`,`fzx_id` from `users` where `group`='0' and `fzx_id`='".$fzx_id."' order by `px`,`id`");
while($aGroup = $DB->fetch_assoc($group)){
	$px_arr[$aGroup['id']] = $aGroup['px'];
	$px_modify	= ' onclick="px(this,'.$aGroup['px'].','.$aGroup['id'].')"';
        //选出该组下所有组员  组两边加“|”是为了区分 “分析室”“分析室2”等名字
        $user = $DB->query("select `id`,`userid`,`nickname`,`fzx_id` from `users` where `fzx_id`='".$fzx_id."' and `group` like '%|".$aGroup['userid']."|%' order by `userid`,`id`");
	$line = '';
        while($aUser=$DB->fetch_assoc($user)){
	      	$no_group_id	.= $aUser['id'].",";
		$sql_chongfu	 = $DB->query("SELECT * FROM `users` WHERE `fzx_id`='".$fzx_id."' AND `group`!='测试组' AND `group`!='0' AND `userid`='{$aUser['userid']}'");
		if($DB->num_rows($sql_chongfu)>1){
			$aUser['userid']	= $aUser['userid']."(".$aUser['nickname'].")";
		}
              	$line	.= "<a style='white-space:nowrap;display:inline-block;min-width:60px;' href=user_manage_user_modify.php?uid=".$aUser['id']."&user_name=".$aUser['userid']."&fzx_id={$aUser['fzx_id']}>".$aUser['userid']."</a> ";
	}
	$lines.=temp("user_manage_list_lines.html");
}
$line   = '';
if($no_group_id){
	$where_group_id	= " and id not in (".substr($no_group_id,0,-1).")";
}
$sql_no_group  = $DB->query("select * from `users` where `fzx_id`='".$fzx_id."' and `group`!='0' and `group`!='测试组' $where_group_id order by `userid`,`id`");
while($aUser   = $DB->fetch_assoc($sql_no_group)){
	$sql_chongfu     = $DB->query("SELECT * FROM `users` WHERE `fzx_id`='".$fzx_id."' AND `group`!='测试组' AND `group`!='0' AND `userid`='{$aUser['userid']}'");
        if($DB->num_rows($sql_chongfu)>1){
        	$aUser['userid']        = $aUser['userid']."(".$aUser['nickname'].")";
        }
	$line .= "<a style='white-space:nowrap;display:inline-block;min-width:60px;' href=user_manage_user_modify.php?uid=".$aUser['id']."&user_name=".$aUser['userid']."&fzx_id={$aGroup['fzx_id']}>".$aUser['userid']."</a> ";
}
if($line!=''){
	$lines.= "<tr><td class='px_td'></td><td nowrap align='right'>未分组成员</td><td align='left'>$line</td></tr></tr>";
}

//如果第一次进来  所有排序都是一样的 那么就自动给他们排序
if(in_array('0', $px_arr)){
	$i=1;
	foreach($px_arr as $key=>$value){
		$sql = "UPDATE `users` SET `px` = '$i' WHERE `id` = '$key'";
		$DB->query($sql);
		$i++;
	}
	echo "<script> location.reload();</script>";
}
disp("user_manage_list.html");
?>
