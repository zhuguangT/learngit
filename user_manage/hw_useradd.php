<?
include "../temp/config.php";
$fzx_id=$_SESSION['u']['fzx_id'];
if(!empty($_POST['save'])){
	if(empty($_POST['user'])){
		echo '<div style="text-align:center;margin-top:200px;">人员不能为空</div>',"<script>setTimeout('location.href=\"hw_useradd.php?add=1\"',1500);</script>";
		die;
	}elseif(empty($_POST['assay_value'])){
		echo '<div style="text-align:center;margin-top:200px;">项目不能为空</div>',"<script>setTimeout('location.href=\"hw_useradd.php?add=1&user=".$_post['user']."\"',1500);</script>";
		die;
	}
	//检查人员是否存在
	$user_check = "select `userid` from users where fzx_id='$fzx_id' and  userid = '".$_POST['user'].'\' limit 1';
	$user_row = $DB->fetch_one_assoc($user_check);
	if(empty($user_row['userid']))
		echo '<div style="text-align:center;margin-top:200px;">输入的人员不存在</div>',"<script>setTimeout('location.href=\"hw_useradd.php?add=1\"',1500);</script>";
	$sql1="SELECT distinct(u.userid) FROM users u LEFT JOIN xmfa x ON ( u.id = x.`userid` OR u.id = x.userid2 ) LEFT JOIN assay_method m ON x.fangfa = m.id LEFT JOIN assay_value v ON x.xmid = v.id
where u.fzx_id='$fzx_id' and u.userid='$_POST[user]' and value_C !='' ";
	//$sql1="select `str3` from `n_set` where `fzx_id`='".$fzx_id."' and  `name`='worke' and `str3`='$_POST[user]' and `int1`=1";
	$rs = $DB->query($sql1);
	if(!mysql_num_rows($rs)){
		$sql="";
		$sql = "insert into `n_set`(`name`,`str1`,`str2`,`str3`,`str4`,`str5`,fzx_id) values('worke','$_POST[assay_value]','$_POST[method]','$_POST[user]','$_POST[expiry_remind]','$_POST[expiry_date]','".$fzx_id."')";
		if($DB->query($sql)){
			header("location:hw_userwoke.php");
		}
	}else{
		$sql = "update `n_set` set `int1`=0 where `fzx_id`='".$fzx_id."' and  `str3`='$_POST[user]'";
		if($DB->query($sql)){
			header("location:hw_userwoke.php");	
		}
	}
}
if(isset($_GET['del'])){
	$sql = "update `n_set` set `int1`=1 where `fzx_id`='".$fzx_id."' and  `str3`='$_GET[del]' and `name`='worke'";
	if($DB->query($sql)){
		header("location:hw_userwoke.php");
	}
}
//没有提交之前，映射到html页面显示
$method=$value_C='';
//查询检测方法
$method_sql="select id, method_number from assay_method";
$method_query=$DB->query($method_sql);
while($r = $DB->fetch_assoc($method_query))
{
	$method.="<option value='".$r['id']."'>".$r['method_number']."</option>";
}
//查询检测项目
$value_C_sql="select id,value_C from assay_value";
$value_C_query=$DB->query($value_C_sql);
while($r = $DB->fetch_assoc($value_C_query))
{
	$value_C.="<option value='".$r['id']."'>".$r['value_C']."</option>";
}
$user = $_GET['user'];
$uid=$_GET['uid'];

disp('user_manager/hw_useradd');
?>
