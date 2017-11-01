<?php
/*
*功能：icp仪器载入页面（硼、钡、钴、钼、镍、钛、钒）
*作者：zhengsen
*时间：2014-11-06
*/

$arr     = array();
//$lujing="../files/zzicp.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$xmArr   = array("K"=>'172',"CA"=>'173',"MG"=>'174',"NA"=>'162',"CU"=>'159',"ZN"=>'161',"PB"=>'137',"CD"=>'133',"B"=>'195',"BA"=>'143',"CO"=>'167',"MO"=>'146',"NI"=>'148',"TI"=>'168',"V"=>'169',"SB"=>'142');

$quzhi_zt= $get_xmzt ='';
$zhi	 = array();

//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//if(preg_match("/^[A-Z]{2}\d{6}[-]\d{4}(PJ|P|J)?/",$line,$bianHao)){
	if(match_bar($line)){
		$bar = match_bar($line);
		$get_xmzt = "start";
	}
	if($get_xmzt=='start'&&stristr($line,"R")){
		$xm='';
		$vid='';
		$temp_arr=explode(" ",$line);
		$temp_arr=array_filter($temp_arr);//去掉数组中的空元素
		$xm=$temp_arr[0];
		if(!empty($xmArr[$xm])){
			$vid=$xmArr[$xm];
		}
		if(!empty($vid)){
			$quzhi_zt="start";
		}

	}
	if($quzhi_zt=="start"&&stristr($line,".")&&!stristr($line,"R")){
		$zhi[$bar][$vid]=$line;
		$get_xmzt="stop";
		$quzhi_zt="stop";
	}

}
//print_rr($zhi);exit();
//if($u['userid']=='admin'){print_rr($zhi);exit();};
if(count($zhi)){
	yqdaoru($zhi);
}
?>
