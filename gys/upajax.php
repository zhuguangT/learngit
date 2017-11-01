<?php
include "../temp/config.php";
//站点上传处理
if(!empty($_FILES['file_data']['name'])){
	$gid = $_GET['gid'];
	//处理
		if(!empty($_FILES['file_data']['name'])){

			$xxx     = explode('.',$_FILES['file_data']['name']);
			$cnt     = count($xxx);
			$newname = $xxx[0].date(ymdhis).".".$xxx[$cnt-1];
			$path    = "./upfiles/".$newname;
			$miao    = date('s');
			if(file_exists($_FILES['file_data']['tmp_name'])){//判断上传的文件是否存在
				if(move_uploaded_file($_FILES['file_data']['tmp_name'],$path)){//把上传的文件重命名并移到系统upfile目录下
				   $lujing[] = $path;
				}
			}
	    }
	  //路径存入数据库
}
$json= '';
if($lujing){
	$lustr = implode('||',$lujing);
	$up = $DB->fetch_one_assoc("select * from gys_gl where id = '$gid'");
	if($up['json']){
		$json = json_decode($up['json'],true);
		$json['fujian'] .= "||".$lustr;
	}else{
		$json['fujian'] = $lustr;
	}
	$jsonstr = JSON($json);
	$upp = $DB->query("update gys_gl set json='$jsonstr' where id = '$gid'");
	if($upp){
		echo "update gys_gl set json='$jsonstr' where id = '$gid'";
		echo '上传成功！';
	}else{
		echo '上传失败，请重试！';
	}
}else{
	echo '没有上传文件！';
}



