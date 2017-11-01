<?php
/*
 *功能：原子荧光仪器导入页面(砷、硒、汞)
 *作者：zhengsen
 *时间：2015-07-22
 */
//include("../temp/config.php");
//$lujing="files/yzyg_2.pdf";
$arr=array();
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$xmArr = array("AS"=>"5","SE"=>"7","HG"=>"6");

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$quzhi_zt='';
$zhi     = array();
$cishu=0;
$get_xmzt=1;//获得项目的状态
$xm=array();//图谱的化验项目
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//取出化验项目
	if($get_xmzt){
		$temp_xmArr = explode('：',$line);
		foreach($temp_xmArr as $key=>$value){
			if($xmArr[$value]&&$value!=''){
				$xm_vid=$xmArr[$value];
			}
		}
	}
	//取出编号
	if(preg_match("/[A-Z]{1}\d{4}(P|J)?/",$line,$bianHao)){
		$bar = $bianHao[0];
		if(isset($zhi[$bar])){
			$bar = $bar."P";
		}
		$quzhi_zt = "start";
		$get_xmzt=$cishu=0;
	}
	//取出项目相应数值
	if($quzhi_zt=='start'){
		if(stristr($line,".")){
			$cishu++;
		}
		if($cishu==2){
			$zhi[$bar][$xm_vid]=$line;
			$quzhi_zt='stop';
			$cishu=0;
		}
	}
}
//print_rr($zhi);exit();
//if($u['userid']=='admin'){print_rr($zhi);}
if(count($zhi)){
	yqdaoru($zhi,'vd1');
}

?>
