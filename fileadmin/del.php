<?php
include '../temp/config.php';
$fzx_id=$_SESSION['u']['fzx_id'];
$id = $_GET['id'];
if($_GET['handle']=='juan'){
	$sql = "SELECT * FROM `filemanage` WHERE `fzx_id` = '{$fzx_id}' AND `jid` = '{$id}'";
	$re = $DB->query($sql);
	$data = $DB->fetch_assoc($re);
	if($data){
		echo "<script>alert('此卷下还有类别！不能删除！');</script>";
	}else{
		$sql = "DELETE FROM `filemanage` WHERE `fzx_id` = '{$fzx_id}' AND `id` = '{$id}'";
		if($DB->query($sql)){
			echo "<script>alert('删除成功！');</script>";
		}
	}
}else if($_GET['handle']=='lx'){
	$sql = "SELECT * FROM `filemanage` WHERE `fzx_id` = '{$fzx_id}' AND `pid` = '$id'";
	$re = $DB->query($sql);
	$num = $DB->num_rows($re);
	if($num){
		echo "<script>alert('此类型下还有内容！不能删除！');</script>";
	}else{
		$sql = "DELETE FROM `filemanage` WHERE `fzx_id` = '{$fzx_id}' AND `id` = '{$id}'";
		if($DB->query($sql)){
			echo "<script>alert('删除成功！');</script>";
		}
	}
}else{
	$del = "delete from filemanage where fzx_id='$fzx_id' and id=$id";
	if($_GET['pid2']){
		$DB->query($del);
		echo "<script>location.href='show.php?id='+$_GET[pid2]</script>";
	}
	if($_GET['pid3']){
		alert(var_dump($_POST));
	}
	$sel = "select * from filemanage where fzx_id='$fzx_id' and pid=$_GET[pid]";
	if($_GET['pid']){
		$rss = $DB->query($sel);
		$num=mysql_num_rows($rss);
		$DB->query($del);
		if($num==1){
			$r=$DB->fetch_assoc($rss);
			 $id2 = $r['pid'];	
			$del2 = "delete from filemanage where fzx_id='$fzx_id' and id=$_GET[pid]";
			$sel2 = "select * from filemanage where fzx_id='$fzx_id' and id=$id2";
			
			$rss2 = $DB->query($sel2);
			$r2=$DB->fetch_assoc($rss2);
			$id3 = $r2['pid'];
			$sel3 = "select * from filemanage where fzx_id='$fzx_id' and pid=$id3";
			$rss3 = $DB->query($sel3);
			$num3=mysql_num_rows($rss3);
			//echo $sel2;exit;
			$DB->query($del2);
			if($num3==1){
			
				$del3 = "delete from filemanage where fzx_id='$fzx_id' and id=$id3";
				$DB->query($del3);
			}
			
			if($num==1 && $num3!=1){
				echo "<script>confirm('这是当前目录下的最后一条信息如果删除，当前目录也会被删除')</script>";
			}elseif($num3==1 && $num==1){
				echo "<script>confirm('这是当前目录下的最的全部信息都将删除')</script>";
			}
		}
	}
}

echo "<script>location.href='fileadmin.php'</script>";
?>
