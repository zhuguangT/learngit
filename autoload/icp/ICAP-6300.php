<?php
/*
*功能：icp仪器载入页面（铜、锌、铅、镉）
*作者：zhengsen
*时间：2015-01-07
*/

$arr     = array();
//include("../../temp/config.php");
//$lujing="../files/slw_icp.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$xmArr   = array("CU3247"=>'159',"ZN2138"=>'161',"PB2203"=>'137',"CD2288"=>'133');
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'),'NG/ML'=>array('hs'=>'0.001','blws'=>'7'));
$quzhi_zt= $get_xmzt =$unit='';
$zhi	 = array();
$vid_arr=array();
$cishu=0;
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	if(match_bar($line)){
		$bar = match_bar($line);
		$get_xmzt = "start";
		$cishu=0;
		continue;
	}
	if($get_xmzt=='start'){
		if(!empty($xmArr[$line])){
			$vid_arr[]=$xmArr[$line];
		}
	}
	if(stristr($line,"NG/ML")||stristr($line,"PPT")){
		$unit='NG/ML';
	}
	if(stristr($line,"MG/L")||stristr($line,"PPM")){
		$unit='MG/L';
	}
	if(stristr($line,"µG/L")||stristr($line,"PPB")){
		$unit='µG/L';
	}
	if($get_xmzt=='start'&&$unit&&!empty($vid_arr)){
		$quzhi_zt="start";
	}
	if($quzhi_zt=="start"&&stristr($line,".")){
		$line=number_format(($line*$unit_arr[$unit]['hs']),$unit_arr[$unit]['blws']);
		$zhi[$bar][$vid_arr[$cishu]]=del0($line);
		$cishu++;
		if($cishu==count($vid_arr)){
			$get_xmzt="stop";
			$quzhi_zt="stop";
			$unit='';
			$vid_arr=array();
			$cishu=0;
		}
	}

}
//print_rr($zhi);exit();
//if($u['userid']=='admin'){print_rr($zhi);exit();};
if(count($zhi)){
	yqdaoru($zhi);
}

?>
