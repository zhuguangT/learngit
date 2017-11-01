<?
include "../temp/config.php";

if($_POST){
	if($_FILES[upfile][size]<1000000){
		$xxx = explode('.',$_FILES[upfile][name]);
		$newname =$xxx[0].date(ymdhis).".".$xxx[1]; 
		$path = "../files/".$newname;
		if(move_uploaded_file($_FILES[upfile][tmp_name],$path)){
			$sql="update `yiqi` set yq_caozuo='$newname' where id = $id";
			$rs = $DB->query($sql);
			echo "<script>alert('上传成功');location.href='hn_yiqiguanli.php?id=$id'</script>";
		}
	}
}else{
disp('yiqi_upfile.html');
$id=$_GET[id];
}
?>
