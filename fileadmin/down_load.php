<?php
include '../temp/config.php';
$file_rs	= $DB->fetch_one_assoc("SELECT `file`,`old_file_name` FROM `filemanage` WHERE `id`='$_GET[id]'");

$filename	= $file_rs['file'];
$old_name	= $file_rs['old_file_name'];

//取出文件对应格式
$a = file("aconten_type.php");
$b= array();
foreach($a as $key=>$value){
	$value	= trim(str_replace('	', ' ', $value));
	if(stristr($value,'.')){
		$tmp = explode(" ",$value);
		foreach ($tmp as $key_dian=>$type) {
			if(empty($type)){
				continue;
			}else{
				if(substr($type,0,1) == '.'){
					$b_key	= str_replace(".",'',$type);
					$b_value= $tmp[($key_dian+1)];
					$b[$b_key]	= $b_value;
				}
			}
		}
	}
}
//用以解决中文不能显示出来的问题 
$file_name	= $filename;//iconv("utf-8","gb2312",$filename); 
$file_sub_path="$rootdir/fileadmin/upfile/"; 
$file_path	= $file_sub_path.$file_name; 
//首先要判断给定的文件存在与否 
if(!file_exists($file_path)){ 
	echo "没有该文件文件"; 
	return ; 
} 
$fp=fopen($file_path,"r"); 
$file_size=filesize($file_path); 
//下载文件需要用到的头 
$extension	= @end(explode('.',$file_name));
if(array_key_exists($extension, $b)){
	$content_type	= $b[$extension];
}else{
	$content_type	= "application/octet-stream";
}

Header("Content-type: ".$content_type); //通过这句代码客户端浏览器就能知道服务端返回的文件形式 
Header("Accept-Ranges: bytes"); //告诉客户端浏览器返回的文件大小是按照字节进行计算的
Header("Accept-Length:".$file_size); //告诉浏览器返回的文件大小 
Header("Content-Disposition: attachment; filename=".$old_name); //指定下载文件的描述
//readfile($file);
$buffer	=1024; 
$file_count=0; 
//向浏览器返回数据 
while(!feof($fp) && $file_count<$file_size){ 
$file_con=fread($fp,$buffer); 
$file_count+=$buffer; 
echo $file_con; 
} 
fclose($fp); 

?>
