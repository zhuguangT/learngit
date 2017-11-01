<?php
/*
*功能：离子色谱仪器载入页面（硝酸盐氮,硫酸盐,氯离子,氟）
*作者：zhengsen
*时间：2015-03-30
*/
//include("../../temp/config.php");
$arr     = array();
//$lujing="../files/fs_lzsp.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");


$xmArr= array("F-"=>'181',"F"=>'181',"CL-"=>'182',"CL"=>'182',"SO4-"=>'190',"SO4"=>'190',"NO3-"=>'186',"NO3"=>'186',"NO3-N"=>'186',"PO4"=>'563',"PO4-"=>'563',"NO2-N"=>'187');
$quzhi_zt = $bhZt ='';
$zhi	 = array();
//print_rr($arr);exit();
//if($u['admin']){print_rr($arr);};

for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//匹配项目名称后开始获取样品编号
	if(!empty($xmArr[$line])){
		$xm		= $xmArr[$line];
		$bhZt	= "start";
	}
	//匹配到AMOUNT时停止获取样品编号
	if(stristr($line,"AMOUNT")){
		$bhZt  = "stop";
	}
	//开始获取编号
	if($bhZt=="start"&&match_bar($line)){
		$bar = match_bar($line);
		if(isset($zhi[$xm][$bar])){//如果碰到相同的编号默认第二个为平行样
			$bar = $bar."P";
		}
		$quzhi_zt = "start";
	}
	if($quzhi_zt=="start"&&stristr($line,".")){//开始获取数据 获取第6个带“.”的值包括“n.a.”
		$cishu++;
		if($cishu==7){
			if($line=="N.A."){//如果结果为'n.a.'，则默认为0.0000
				$line="0.0000";
			}
			$zhi[$xm][$bar]=$line;
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
