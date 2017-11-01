<?php
/**
* 
*  
*/
// B381,137,2,9,2,4,85,N,"CB201503-0001"
//A383,46,2,2,2,2,N,"CB201503-0001"
//P1
//<xpml></page></xpml><xpml><end/></xpml>

include "../temp/config.php";
if($u[userid] == '') nologin();
$fzx_id	= $u['fzx_id'];
$cyd_id	= $_POST['cyd_id'];
$bh_arr	= $_POST['bh'];//要打印的样品编号
//将样品编号按照“打印个数”合成一个数组，例：A0008打印2个标签，A009打印1个标签 合成后数组为array("A0008","A0008","A0009")
$dayin	= '';
$dayin_header	 = file_get_contents("./new_daijian.prn");//由于直接把prn文件的内容复制到php导致打印时汉字乱码，所以采用临时用函数读取的方式
foreach ($bh_arr as $key => $value) {
	if($value>0){
		$dayin .= $dayin_header.'B381,137,2,9,2,4,85,N,"'.$key.'"
A383,46,2,2,2,2,N,"'.$key.'"
P'.$value.'
<xpml></page></xpml><xpml><end/></xpml>';
	}
}
$stream = fopen("/tmp/dy.txt", "w+"); //保留最后一次打印数据
fwrite($stream, $dayin);
//判断 是否定义ip 地址（独立的打印服务器） 如果没有就是访问页面的机器
$hub_info	= $DB->fetch_one_assoc("SELECT `dayin_ip` FROM `hub_info` WHERE `id`='$fzx_id'");
//这里从数据库获取
$hub_info['dayin_ip']	= trim($hub_info['dayin_ip']);
if($hub_info['dayin_ip']!=''){
	if(stristr($hub_info['dayin_ip'],":")){
		$tmp_arr= explode(':',$hub_info['dayin_ip']);
		$ip		= $tmp_arr[0];
		$duankou= $tmp_arr[1];
	}else{
		$ip		= $hub_info['dayin_ip'];
		$duankou= '9100';
	}
}else{
	$ip		= $_SERVER['REMOTE_ADDR'];
	$duankou= 23128;
}
print_remote($dayin,$ip,$duankou);

function print_remote($str,$ip,$duankou){
	global $cyd_id,$rooturl;
	//利用fsockopen函数打开网络socket链接，建立TCP链接，使服务器与打印服务器能发送数据，更改打印服务器文件
	$fp=fsockopen($ip,$duankou, $errno, $errstr, 30);
	if(!$fp){   
		//gotourl("$rooturl/cy/dayin_biaoqian.php?cyd_id=$cyd_id","连接打印机失败");
		$jieGuo	= 'no';
		$html	= "连接打印机失败!请检查打印机及打印服务器的网线连接是否正常!";
	}else{
		//向打印服务器写入打印文件
		fwrite($fp,$str);
		//gotourl("$rooturl/cy/dayin_biaoqian.php?cyd_id=$cyd_id",'已经连接打印机正在打印，请等待！');
		$jieGuo	= 'yes';
		$html	= "已经连接打印机正在打印，请等待！";
		fclose($fp);
	}
	echo json_encode(array('jieGuo'=>$jieGuo,'html'=>$html));
}
?>
