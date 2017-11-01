<?php
include '../temp/config.php';
$fzx_id=$_SESSION['u']['fzx_id'];
$id=$_GET['id'];
$pid=$_GET['pid'];
$old_url=$_SERVER['HTTP_REFERER'];
if(!empty($_FILES['upfile']['name'])){
		foreach($_FILES['upfile']['name'] as $key=>$value ){
		if($_FILES['upfile']['size'][$key]<100000000){
			$xxx	= explode('.',$_FILES['upfile']['name'][$key]);
			$cnt	= count($xxx);
			$newname= date(ymdhis).$u['fzx_id']."_{$key}.".$xxx[$cnt-1]; 
			$path	= "./upfile/".$newname;
			//将文件移入指定文件夹
			if(move_uploaded_file($_FILES['upfile']['tmp_name'][$key],$path)){
				$name_new_arr[] = $newname;
				$name_old_arr[] = $_FILES['upfile']['name'][$key];
			}
			
		}else{
			echo "<script>alert('上传文件不能超过95M');location.href='show.php?id='+{$_POST['data']['pid']}+'&name={$_POST['data'][name]}'</script>";
		}
	}
	$sql_set	= array();
	$name_new_json = json_encode($name_new_arr , JSON_UNESCAPED_UNICODE);
	$name_old_json = json_encode($name_old_arr , JSON_UNESCAPED_UNICODE);
	$sql_set['file']	= "`file`='$name_new_json'";
	$sql_set['old_file_name']	= "`old_file_name`='{$name_old_json}'";
}

if($_POST[add1]){
	$pid = $_POST[pid];
	$name = $_POST[name]; 
	$namebak = $_POST[namebak];
	$xu = $_POST['xu']+0.001;
	$add = "INSERT into filemanage values(null,'$fzx_id','$pid','$name','$namebak','','$xu')";
	if($DB->query($add))
	header("location:fileadmin.php");
}
//新增或修改类下面的资料
if($_POST['add2']|| $_POST['upload']){
	if(!is_array($_FILES['upfile']['name'])){
		foreach($_FILES['upfile'] as $key => $value){
			$_FILES['upfile'][$key]	= array();
			$_FILES['upfile'][$key]['0']	= $value;
		}
	}
	$arr_file=$_FILES['upfile']['name'];
	$old_file_name=array();
	foreach ($_POST['data'] as $key => $value) {
		$sql_set[$key]	= "`$key`='$value'";
	}
	foreach($arr_file as $key=>$value){//遍历文件得到键名键值,value为文件名称
		$old_file_name[$key]= $value;
		$name_suffix= substr($value,0,strrpos($value,"."));
	}
	//将数据插入数据库
			if(!empty($sql_set)){
				//$sql_set['fzx_id']	= "`fzx_id`='{$fzx_id}'";
				$sql_set_str	= " ".implode(',', $sql_set)." ";
				if($_POST['upload']){
					$sql = "update `filemanage` SET $sql_set_str WHERE `id`='{$_POST['id']}'";
				}else{
					$sql = "insert into `filemanage` SET `fzx_id`='{$fzx_id}', $sql_set_str";
				}
				if($DB->query($sql)){
					if(!empty($_POST['old_url'])){
						echo "<script>location.href='{$_POST['old_url']}'</script>";
					}else{
						echo "<script>location.href='show.php?id='+{$_POST['data']['pid']}+'&name={$_POST['data'][name]}'</script>";
					}
				}
			}
}

//判断是否是ajax提交的修改
if($_POST['ajax']){
	$id		= $_POST['id'];
	$name	= $_POST['name'];
	$content= $_POST['content'];
	if(!empty($id) && !empty($name)){
		$add = "update filemanage set `{$name}`='{$content}' where fzx_id='{$fzx_id}' and id='{$id}'";
		if($DB->query($add)){
			echo "成功";exit;
		}else{
			echo "失败";exit;
		}
	}else{
		echo "失败";exit;
	}
}

if($_POST[fix]){
	$id = $_POST['id'];
	$pid = $_POST['pid'];
	$name = $_POST['name']; 
	$namebak = $_POST['namebak']; 
	$add = "update filemanage set name='{$name}',namebak='{$namebak}' where fzx_id='{$fzx_id}' and id={$id}";
	if($DB->query($add)){
		if($pid)
			echo "<script>alert('修改成功');location.href='show.php?id='+$pid</script>";
		else	
			echo "<script>alert('修改成功');location.href='fileadmin.php'</script>";
	}
}
if($_GET[id]){
	$id	= $_GET[id];
	$pid= $_GET[pid];
	$key = $_GET['key'];
	$sql = "SELECT file , old_file_name FROM `filemanage` WHERE `id`= {$id}";
	$re = $DB->query($sql);
	while($data = $DB->fetch_assoc($re)){
		$file_name_arr = json_decode($data['file'] , true);
		$old_file_name_arr = json_decode($data['old_file_name'] , true);
	}
	unset($file_name_arr[$key]);
	unset($old_file_name_arr[$key]);
	$file_name_json = json_encode($file_name_arr , JSON_UNESCAPED_UNICODE);
	$old_file_json = json_encode($old_file_name_arr , JSON_UNESCAPED_UNICODE);
	$DB->query("update filemanage set `file`='$file_name_json',old_file_name='$old_file_json' where fzx_id='$fzx_id' and id=$id");
	if($pid){
		echo "<script>alert('文件删除成功');location.href='show.php?id='+$pid</script>";
	}else{
		echo "<script>alert('文件删除成功');location.href='fileadmin.php'</script>";
	}
}

//判断是否有添加要素的提交
if(!empty($_GET['ins_ys'])){
	$xu=$_GET['xu']+1;
	$ins_sql="INSERT into filemanage (fzx_id,pid,namebak,xu) values ('$fzx_id',0,'$_GET[ins_ys]',$xu)";
	$ins_query=$DB->query($ins_sql);
	if($ins_query){
		echo "<script type='text/javascript'>alert('添加要素成功');location='fileadmin.php';</script>";
	}else{
		echo "<script type='text/javascript'>alert('添加要素失败');location='fileadmin.php';</script>";
	}
}
//判断是否要添加类的提交
if(!empty($_GET['ins_lei'])){
	$xu=$_GET['xu']+1;
	// print_rr($_GET);die;
	$ins_sql="INSERT INTO `filemanage` (fzx_id,pid,jid,name,xu) VALUES ('$fzx_id','{$_GET['pid']}','{$_GET['jid']}','{$_GET['ins_lei']}','$xu')";
	// echo $ins_sql;die;
	$ins_query=$DB->query($ins_sql);
	if($ins_query){
		echo "<script type='text/javascript'>alert('添加类成功');location='fileadmin.php';</script>";
	}else{
		echo "<script type='text/javascript'>alert('添加类失败');location='fileadmin.php'</script>";
	}

}
//判断是否要添加卷的提交
if(!empty($_GET['ins_juan'])){
	// print_rr($_GET);die;
	$xu=$_GET['xu']+1;
	$ins_sql="INSERT INTO `filemanage` (fzx_id,pid,name,xu,jid) VALUES ('$fzx_id','{$_GET['pid']}','{$_GET['ins_juan']}','$xu','0')";
	//echo $ins_sql;die;
	$ins_query=$DB->query($ins_sql);
	if($ins_query){
		echo "<script type='text/javascript'>alert('添加类成功');location='fileadmin.php';</script>";
	}else{
		echo "<script type='text/javascript'>alert('添加类失败');location='fileadmin.php'</script>";
	}

}
?>
