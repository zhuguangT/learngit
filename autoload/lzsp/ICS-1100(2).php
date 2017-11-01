<?php
/*
*功能：离子色谱仪器载入页面（硝酸盐氮,硫酸盐,氯离子,氟）
*作者：zhengsen
*时间：2015-01-04
*/
//include("../../temp/config.php");
$arr     = array();
//$lujing="../files/slw_lzsp.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");


$xmArr= array("氟化物"=>'181',"氯化物"=>'182',"硝酸盐"=>'186',"硫酸盐"=>'190');
$quzhi_zt = $bhZt =$get_xm='';
$zhi	 =$xm= $bar_arr=array();
$cishu=0;
//if($u['admin']){print_rr($arr);};
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//匹配项目名称后开始获取样品编号
	if(stristr($line,"μS*")){
		$get_xm='1';
	}
	if(!empty($xmArr[$line])&&$get_xm){
		if(!in_array($xmArr[$line],$xm)){
			$xm[]=$xmArr[$line];
		}
		$bhZt	= "start";
	}
	//开始获取编号
	if($bhZt&&match_bar($line)){
		$bar = match_bar($line);
		if(in_array($bar,$bar_arr)){//如果碰到相同的编号默认第二个为平行样
			$bar = $bar."P";
		}
		$bar_arr[]=$bar;
		$quzhi_zt = "start";
		$get_xm='';
	}
	if($quzhi_zt=="start"&&stristr($line,".")){//开始获取数据 获取第6个带“.”的值包括“n.a.”
		if($cishu<=3){
			if($line=="N.A."){//如果结果为'n.a.'，则默认为0.0000
				$line="0.000";
			}
			$zhi[$bar][$xm[$cishu]]=$line;
		}
		$cishu++;
		if($cishu==4){
			$quzhi_zt="stop";
			$cishu=0;
		}
	}
}
//if($u['admin']){print_rr($zhi);exit();};
//print_rr($zhi);exit();
if(count($zhi)){
	yqdaoru($zhi);
}
?>
