<?
include "../temp/config.php";
if($_POST){
	if($_FILES[upfile][size]<1000000){
		$xxx = explode('.',$_FILES[upfile][name]);
		$newname =$xxx[0].date(ymdhis).".".$xxx[1]; 
		$path = "../files/".$newname;
		if(move_uploaded_file($_FILES[upfile][tmp_name],$path)){
			$sql="insert into n_set(name,str1,str2,str3,str4,`int1`) values('yiqi','$_POST[jdrq]','$_POST[jddw]','$_POST[sjr]','$newname','$_POST[id]')";
			$rs = $DB->query($sql);
			echo "<script>alert('上传成功');location.href='yq_jiandingjilu.php?id=$id'</script>";
		}
	}
}else{
disp('jd_upfile.html');
$id=$_GET[id];
}
?>
