<?php
/**
 * 功能：用户的添加、修改、删除 数据处理页面
 * 作者：韩枫
 * 日期：2014-04-11
 * 描述：$qx_keys 数组中只存储这数据库qx字段/来自 qx.php
*/
include("../temp/config.php");
include("./qx.php");

$fzx_id  = $u['fzx_id'];//分中心id
if($u['fzx_id']){
        $fzx_id = $u['fzx_id'];
}
else{
        echo "<script>alert('系统无法识别你的身份，请重新登陆');location.href='user_manage_group_new.php'</script>";
}
if($_GET['action']=='ajax'){
	if(!empty($_GET['nick_name'])){
		$submit       = '';
		if(!empty($_GET['uid'])){
			$where= " and id !='{$_GET['uid']}'";
		}
		else{
			$where= '';
		}
                $sql_nickname = $DB->query("select * from `users` where nickname='{$_GET['nick_name']}' and `group`!='0' $where");
                $num_nickname = $DB->num_rows($sql_nickname);
                if($num_nickname>0){
			$tixing = ' *该用户名已存在！请更改';
			$color  = 'red';
                }
		else{
			if($_GET['nick_name']!=$_GET['user_name']){
				$tixing = ' *登陆时，请使用此用户名!';
				$color  = 'blue';
			}
			else{
				$tixing = '';
				$color  = 'red';
			}
			$submit = 'yes';
		}
	}
	else{
		$tixing = ' *用户名不能为空！';
		$color  = 'red';
	}
	$json_arr = array("tixing"=>$tixing,"color"=>$color,"submit"=>$submit);
	echo json_encode($json_arr);
	exit;
}
elseif($_POST['action']=='add'){//添加用户信息
	######验证 用户名 密码 等信息
	if(!empty($_POST['user_name'])&&!empty($_POST['nickname'])){
		if(empty($_POST['user_pwd'])){
			$_POST['user_pwd'] = 'lims123';//如果真有跳过js，密码为空提交过来的情况，这里再给默认上
		}
		$password   = md5($_POST['user_pwd']);
		######组织sql 条件|要插入的数据
		$user_group = $set_qx= '';
		if(!empty($_POST['group'])){//用户组
			$user_group  = "|".implode('|',$_POST['group'])."|";
		}
		if(!empty($_POST['qx'])){//用户权限
			$set_qx      = ','.implode("='1',",$_POST['qx'])."='1'";
		}
		//插入数据库
		$DB->query("insert into `users` set fzx_id='{$fzx_id}',userid='{$_POST['user_name']}',`nickname`='{$_POST['nickname']}',`password`='$password',`sex`='{$_POST['sex']}',`group`='$user_group'$set_qx");
		$id=$DB->insert_id();
		if($id>0){
			echo "<script>alert('用户{".$_POST['user_name']."}已添加');location.href='user_manage_list.php'</script>";
		}
	}
	else{
		echo "<script>alert('系统未能识别该用户，请重试');location.href='user_manage_list.php'</script>";
	}
}
elseif($_POST['action']=='modify'){//修改用户信息
	if(!empty($_POST['uid'])&&!empty($_POST['user_name'])){
		######组织sql 条件|要更改的数据
		$set = "`userid`='{$_POST['user_name']}',`nickname`='{$_POST['nickname']}',`sex`='{$_POST['sex']}',";
		if(!empty($_POST['user_pwd'])){//判断是否修改密码
			$set .= "`password`='".md5($_POST['user_pwd'])."',";
		}
                if(!empty($_POST['group'])){//用户组
                        $set .= "`group`='|".implode('|',$_POST['group'])."|',";
                }
		else{
			$set .= "`group`='',";
		}
                if(!empty($_POST['qx'])){//用户选中的权限
                        $set .= implode("='1',",$_POST['qx'])."='1',";
                }
		else{
			$_POST['qx']	= array();
		}
		//用户需要去掉的权限
		$qx_keys = array_diff($qx_keys,$_POST['qx']);
		if(!empty($qx_keys)){
			$set	.= implode("='0',",$qx_keys)."='0',";
		}
		$set	 = substr($set,0,-1);
		//更改数据库
		$DB->query("update `users` set $set where id='{$_POST['uid']}'");
		$yxrow=$DB->affected_rows();
		if($yxrow>0){
			echo "<script>alert('用户{".$_POST['user_name']."}的信息已修改完成');location.href='user_manage_list.php'</script>";
		}
		else{
			echo "<script>alert('用户{".$_POST['user_name']."}的信息未修改');location.href='user_manage_list.php'</script>";
		}
	}
	else{
		echo "<script>alert('系统未能识别该用户，请重试');location.href='user_manage_list.php'</script>";
	}
}
elseif($_GET['action']=='delete'){//删除用户
	if(!empty($_GET['uid'])){
		//先判断下此用户是不是最后一个拥有“权限管理”权限的用户。如果是就不允许删除，并给予提示。
		$manage_user_sql	= $DB->query("SELECT id FROM `users` WHERE `fzx_id`='{$fzx_id}' AND `user_manage`='1' AND `group`!='测试组' AND `group`!='0' ");
		$manage_user_num	= $DB->num_rows($manage_user_sql);
		if($manage_user_num>1){
			$DB->query("delete from `users` where id='{$_GET['uid']}'");
			echo "<script>alert('用户{".$_GET['user_name']."}已删除');location.href='user_manage_list.php'</script>";
		}else{
			$user_qx	= $DB->fetch_one_assoc("SELECT user_manage FROM `users` WHERE `id`='{$_GET['uid']}'");
			if($user_qx['user_manage']=='1'){
				echo "<script>alert('用户{".$_GET['user_name']."}为最后一个拥有\"权限管理\"权限的用户，不允许删除');location.href='user_manage_list.php'</script>";
			}else{
				$DB->query("delete from `users` where id='{$_GET['uid']}'");
				echo "<script>alert('用户{".$_GET['user_name']."}已删除');location.href='user_manage_list.php'</script>";
			}
		}
	}
	else{
                echo "<script>alert('系统未能识别该用户，请重试');location.href='user_manage_list.php'</script>";
        }
}
?>
