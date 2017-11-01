<?php
/*
功能：用户组添加,修改、删除 数据处理页面
作者：韩枫
日期：2014-04-07
描述：
*/
include("../temp/config.php");
include("./qx.php");
if($u['fzx_id']){
	$fzx_id = $u['fzx_id'];
}else{
	echo "<script>alert('系统无法识别你的身份，请重新登陆');location.href='user_manage_group_new.php'</script>";
}
//取出系统中，每个用户组的权限
$group_qx_arr = array();
$sql_group_qx = $DB->query("select * from `users` where fzx_id='".$fzx_id."' and `group`='0'");
while($rs_group_qx = $DB->fetch_assoc($sql_group_qx)){
	foreach($qx_keys as $value){
		if(empty($group_qx_arr[$rs_group_qx['userid']])){
			$group_qx_arr[$rs_group_qx['userid']]=array();
		}
		if($rs_group_qx[$value]=='1'){
			$group_qx_arr[$rs_group_qx['userid']][] = $value;
		}
	}
}
$set = $user_set = '';
if($_GET['action']=='ajax'){
	if(!empty($_GET['gr_id'])){
        	$where	= " and id !='{$_GET['gr_id']}'";
        }else{
        	$where	= '';
        }
	$sql_gr_name	= $DB->query("select * from `users` where fzx_id='$fzx_id' and `userid`='{$_GET['gr_name']}' and `group`='0' $where");
	$num_gr_name	= $DB->num_rows($sql_gr_name);
	if($num_gr_name>0){
		$tixing = ' *该用户组已存在！请更改';
	}else{
		$tixing	= '';
	}
	$json_arr = array("tixing"=>$tixing,"submit"=>$submit);
        echo json_encode($json_arr);
        exit;
	
}else if($_POST['submit']=='添加'){//添加用户组信息
	//组名不能为空
	if(!empty($_POST['g_name'])){
		$sql_g_name = $DB->query("select id from `users` where `userid`='".$_POST['g_name']."' and `group`='0' and fzx_id='".$fzx_id."'");
		$num_g_name = $DB->num_rows($sql_g_name);
		if($num_g_name>=1){//判断重名
			echo "<script>alert('{{$_POST['g_name']}}已经存在,请勿重复添加');location.href='user_manage_group_new.php'</script>";
		}else{
			if(!empty($_POST['qx'])){
				$set   = implode("='1',",$_POST['qx'])."='1',";
			}
			//插入数据
			$insert_g_name = $DB->query("insert into `users` set $set`userid`='".$_POST['g_name']."',`group`='0',`fzx_id`='".$fzx_id."'");
			$id=$DB->insert_id();
			//添加组内成员
			if($id>0&&!empty($_POST['userid'])){
				$sql_user = $DB->query("select id,`group` from `users` where id in (".implode(',',$_POST['userid']).")");
				while($rs_user = $DB->fetch_assoc($sql_user)){
					if(!empty($rs_user['group']))$group_name = $rs_user['group'].$_POST['g_name']."|";
					else $group_name = "|".$_POST['g_name']."|";
					$update_user= $DB->query("update `users` set ".$set."`group`='".$group_name."' where id='".$rs_user['id']."'");
				}
			}
			echo "<script>alert('用户组{{$_POST['g_name']}}已添加');location.href='user_manage_list.php'</script>";
		}
	}
	else{
		echo "<script>alert('组名不能为空');location.href='user_manage_group_new.php'</script>";
	}
	
}else if($_POST['submit']=='修改'){//修改用户组信息
	if(!empty($_POST['g_name'])&&!empty($_POST['uid'])){//组名 和原页面 组名的uid 不能为空
		$sql_g_name = $DB->query("select id from `users` where `userid`='".$_POST['g_name']."' and `group`='0' and fzx_id='".$fzx_id."' and id!='".$_POST['uid']."'");//查找id当id不等于提交过来的uid，分中心相等，分组为0，userid等于提交过来的用户组名，即查找没有提交过来的
               	$num_g_name = $DB->num_rows($sql_g_name);
                	if($num_g_name>=1){//判断重名
                     		echo "<script>alert('{{$_POST['g_name']}}已经存在');location.href='user_manage_group_modify.php?uid=".$_POST['uid']."&group_name={$_POST['old_name']}'</script>";
			exit;
		}else{######权限的判断
			if(!empty($_POST['qx'])){
                                	$set         = implode("='1',",$_POST['qx'])."='1',";
				$user_set = $set;
                        	}
			if(!empty($_POST['old_qx'])){
				$update_qx = explode("|",$_POST['old_qx']);
				if(!empty($_POST['qx'])){
                                          		$update_qx = array_diff($update_qx,$_POST['qx']);//去除选择的权限
                                	}
				if(!empty($update_qx))$set .= implode("='0',",$update_qx)."='0',";
                        	}
			######更新 用户组的信息(权限、组名)
			$DB->query("update `users` set ".$set."`userid`='".$_POST['g_name']."' where id='".$_POST['uid']."'");
			$affected_rows = $DB->affected_rows();
			######更新组内成员数据里的组名
			if($_POST['old_name']!=$_POST['g_name']){
				$DB->query("update `users` set `group`=replace(`group`,'$_POST[old_name]','$_POST[g_name]') where `group` like '%$_POST[old_name]%'");
			}
			######修改组内成员及其权限
			if(!empty($_POST['userid'])){//组内成员不为空
				$sql_user = $DB->query("select * from `users` where fzx_id='".$fzx_id."' and (id in (".implode(',',$_POST['userid']).") or `group` like '%$_POST[g_name]%')");
			}else{//这里是组内成员被清空的情况
				$sql_user = $DB->query("select * from `users` where fzx_id='".$fzx_id."' and `group` like '%|$_POST[g_name]|%'");
			}
			while($rs_user = $DB->fetch_assoc($sql_user)){
				$user_group_arr  = @explode('|',$rs_user['group']);
                                	if(!empty($_POST['userid'])&&in_array($rs_user['id'],$_POST['userid'])){//组内现有成员
					$user_update_set = '';
					//筛选掉该用户所在其他组里的权限（这些权限不作更改）
					if(!empty($update_qx)){//这个数组存的是该组去掉的权限(人减少的情况！！！)
						foreach($user_group_arr as $value){
							if(empty($value)){
								continue;
							}
							if($value!=$_POST['g_name']){
								$update_qx = array_diff($update_qx,$group_qx_arr[$value]);
							}
							if(empty($update_qx)){
								break;
							}
						}
					}
					if(!empty($update_qx)){
						$user_update_set  = implode("='0',",$update_qx)."='0',";
					}
					$user_update_set = $user_set.$user_update_set;
	                                        	if(empty($rs_user['group'])){
	                                        		$user_group = "|".$_POST['g_name']."|";
					}else{
						 $rs_user['group'] .= $_POST['g_name'];
						 $user_group_arr = array_unique(explode('|',$rs_user['group']));
						 $user_group = implode('|', $user_group_arr)."|";
					}
	                                	$DB->query("update `users` set $user_update_set`group`='".$user_group."' where id='".$rs_user['id']."'");
                                	}else{//组内去掉的成员
					$user_delete_set   = '';
					if(!empty($_POST['old_qx'])){//这个数组存的是该组修改之前的所有权限
						$delete_qx = explode("|",$_POST['old_qx']);
						foreach($user_group_arr as $value){
							if(empty($value)){
								continue;
							}
			                                           if($value!=$_POST['g_name']){
			                                                     $delete_qx = array_diff($delete_qx,$group_qx_arr[$value]);
			                                          }
			                                           if(empty($delete_qx)){
			                                                     break;
			                                          }
			                                }
						if(!empty($delete_qx)){
							$user_delete_set = implode("='0',",$delete_qx)."='0',";
						}
					}
	                                        $user_group = str_replace("|".$_POST['g_name']."|","|",$rs_user['group']);
	                                        $DB->query("update `users` set $user_delete_set`group`='".$user_group."' where id='".$rs_user['id']."'");
	                                }
			}
			echo "<script>alert('{{$_POST['g_name']}}信息修改已完成');location.href='user_manage_list.php'</script>";
		}
	}else{
                echo "<script>alert('组名不能为空');location.href='user_manage_group_modify.php'</script>";
        }
}else if($_GET['zt']=='delete'){//删除组
	if(!empty($_GET['uid'])){
		$rs_group = $DB->fetch_one_assoc("select * from `users` where id='".$_GET['uid']."'");
		if(!empty($rs_group)){######删除组内成员及其权限
			//如果除了这个分组内的其他成员都没有”权限管理“权限，则不允许删除这个分组
			if($rs_group['user_manage']==1){
				$user_manage_users	= $DB->fetch_one_assoc("SELECT id FROM `users` WHERE fzx_id='".$fzx_id."' AND `user_manage`='1' AND `group`!='0' AND `group`!='测试组' AND `group` NOT LIKE '%|$rs_group[userid]|%' limit 1");
				if(empty($user_manage_users)){
					echo "<script>alert('用户组{{$rs_group['userid']}}的组内成员为最后拥有“权限管理”权限的人，本组不允许删除!');location.href='user_manage_list.php'</script>";
					exit;
				}
			}
			$sql_user = $DB->query("select * from `users` where fzx_id='".$fzx_id."' and `group` like '%|$rs_group[userid]|%'");
			while($rs_user = $DB->fetch_assoc($sql_user)){
				$user_group_arr  = @explode('|',$rs_user['group']);
				$user_delete_set = '';
                                	if(!empty($_GET['old_qx'])){//这个数组存的是该组修改之前的所有权限
	                                	$delete_qx = explode("|",$_GET['old_qx']);
	                                           foreach($user_group_arr as $value){//筛选掉该用户所在其他组里的权限（这些权限不作更改）
	                                        		if(empty($value)){
	                                        			continue;
	                                        		}
	                                                	if($value!=$rs_group['userid']){
	                                                		$delete_qx = @array_diff($delete_qx,$group_qx_arr[$value]);
	                                                	}
	                                                	if(empty($delete_qx)){
	                                                		break;
	                                                	}
	                                           }
                                                     if(!empty($delete_qx)){
                                                     		$user_delete_set = implode("='0',",$delete_qx)."='0',";
                                		}
                                	}
                                	$user_group = str_replace("|".$rs_group['userid']."|","|",$rs_user['group']);//去掉当前的组名
                                	$DB->query("update `users` set $user_delete_set`group`='".$user_group."' where id='".$rs_user['id']."'");
			}
			######删除用户组信息
			$DB->query("delete from `users` where id='".$rs_group['id']."'");
			if($DB->affected_rows())echo "<script>alert('用户组{{$rs_group['userid']}}已删除');location.href='user_manage_list.php'</script>";
		}
		
	}
	else echo "<script>alert('系统未能识别用户组，请重试');location.href='user_manage_list.php'</script>";
}
?>
