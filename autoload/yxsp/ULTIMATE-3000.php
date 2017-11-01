<?php
/*
*功能：液相色谱仪器载入页面
*作者：zhengsen
*时间：2014-11-17
*/
$arr     = array();
//include("../../temp/config.php");
//$lujing="../files/zzyxsp.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$xmArr=array("CARBARYL"=>"229","甲甲甲"=>"229");
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");

$unit=array('PPM'=>array('hs'=>'1','blws'=>'4'),'MG/L'=>array('hs'=>'1','blws'=>'4'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'));
$get_xmzt=1;
$quzhi_zt ='';
$zhi	 = array();
//if($u['admin']){print_rr($arr);exit();};
//print_rr($arr);exit();
$dw='MG/L';
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	if(stristr($line,'Amount')){
		$get_unit='1';
		continue;
	}
	if($get_unit=='1'){
		if(stristr($line,'PPM')){
			$dw='PPM';
		}
		elseif(stristr($line,'MG/L')){
			$dw='MG/L';
		}elseif(stristr($line,'µG/L')){
			$dw='µG/L';
		}
	}
	if($get_xmzt){
		if($xmArr[$line]){
			$vid=$xmArr[$line];
			$get_xmzt='';
		}
		$get_bar='start';
	}
	//开始获取编号
	if(match_bar($line)&&$get_bar){
		$bar = match_bar($line);
		$get_unit='0';
		/*if(isset($zhi[$bar])){//如果碰到相同的编号默认第二个为平行样
			$bar = $bar."P";
		}*/
		$quzhi_zt = "start";
		continue;
	}
	if($quzhi_zt=="start"&&stristr($line,".")){//开始获取数据 获取第4个带“.”的值包括“n.a.”
		$cishu++;
		if($cishu==4){
			if(stristr($line,'-')){
				$zhi[$bar][$vid]='0';
			}else{
				if(empty($unit[$dw]['blws'])){
					$zhi[$bar][$vid]=$line*$unit[$dw]['hs'];
				}else{
					$zhi[$bar][$vid]=number_format(($line*$unit[$dw]['hs']),$unit[$dw]['blws']);
				}
			}
			$quzhi_zt = "stop";
			$cishu = 0;
		}
	}
}
//print_rr($zhi);exit();
//if($u['admin']){print_rr($zhi);exit();};
if(count($zhi)){
	yqdaoru($zhi);
}
?>
