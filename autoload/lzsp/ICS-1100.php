<?php
/*
*功能：离子色谱仪器载入页面（硝酸盐氮,硫酸盐,氯离子,氟）
*作者：zhengsen
*时间：2014-10-21
*/
header("Content-Type:text/html;charset=utf-8"); 
$arr   = array();
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$xmArr= array("F"=>'181',"CLO2"=>'493',"CL"=>'182',"CLO3"=>'492',"NO3"=>'186',"SO4"=>'190',"BRO3"=>'524');
$quzhi_zt = $bhZt=$quzhi_xm='';
$zhi=$bar_code_arr=$jcxm_arr=array();
$cishu=0;
//if($u['admin']){print_rr($arr);};
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//echo $line."<br />";
	//匹配项目名称后开始获取样品编号
	if(match_bar($line)){
		$bar = match_bar($line);
		/*if(isset($zhi[$bar][$vid])){//如果碰到相同的编号默认第二个为平行样
			$bar = $bar."P";
		}*/
		if(!in_array($bar,$bar_code_arr)){
			$bar_code_arr[]=$bar; //样品编号
		}
		$quzhi_xm = "start";
		continue;
	}
	if($quzhi_xm=='start'){
		if($line=="µS*MIN"){ //区分曲线和数据的数据
			$bhZt='start';
		}
	}
	if($bhZt=='start'){
		if(!empty($xmArr[$line])){
		$vid=$xmArr[$line];
		if(!in_array($line,$jcxm_arr)){
			$jcxm_arr[]=$line;//项目编号
		}
		$quzhi_zt	= "start";
		continue;
		}
	}
	if($quzhi_zt=="start"&&stristr($line,".")){//开始获取数据 获取第6个带“.”的值包括“n.a.”
		$cishu++;
		if($cishu==3){
			$zhi[$bar][$vid]=$line;
			$quzhi_zt	= "stop";
			$quzhi_xm = "stop";
			$cishu=0;
			continue;
		}
	}
}
if(count($zhi)){
	update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);
	yqdaoru($zhi,'vd27');
}
?>
