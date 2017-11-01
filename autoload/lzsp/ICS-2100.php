<?php
/*
*功能：离子色谱仪器载入页面（硝酸盐氮,硫酸盐,氯离子,氟,溴酸盐）
*作者：zhengsen
*时间：2015-03-24
*/
//include("../../temp/config.php");
$arr     = array();
//$lujing="../files/qd_lzsp2.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");


$xmArr= array("F-"=>'181',"F"=>'181',"CL-"=>'182',"CL"=>'182',"SO4-"=>'190',"SO4"=>'190',"NO3-"=>'186',"NO3"=>'186',"NO3-N"=>'186',"PO4"=>'563',"NO2-N"=>'187',"BRO3-"=>"524","CL02-"=>"493");
$quzhi_zt = $get_xmzt =$get_unit='';
$get_bar=1;
$zhi	 =$bar_code_arr=$jcxm_arr= array();
//print_rr($arr);exit();
//if($u['admin']){print_rr($arr);};

for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//开始获取编号
	if($get_bar&&match_bar($line)){
		$bar = match_bar($line);
		if(!in_array($bar,$bar_code_arr)){
			$bar_code_arr[]=$bar;
		}
		$get_unit='start';
	}
	//匹配单位名称
	if(stristr($line,'MG/L')&&$get_unit){
		$get_xmzt="start";
		$get_unit='';
		continue;
	}
	//开始获取项目名称
	if($get_xmzt&&empty($quzhi_zt)){
		$temp_xm_arr=explode(" ",$line);
		foreach($temp_xm_arr as $key=>$value){
			if(!empty($xmArr[$value])){
				if(!in_array($value,$jcxm_arr)){
					$jcxm_arr[]=$value;
				}
				$xm_vid	 = $xmArr[$value];
				$quzhi_zt= "start";
				break;
			}
		}
		if($quzhi_zt){
			continue;
		}
	}
	if($quzhi_zt=="start"&&stristr($line,".")){//开始获取数据 获取第4个带“.”的值包括“n.a.”
		$temp_zhi=explode(" ",$line);
		foreach($temp_zhi as $key=>$value){
			if(stristr($value,".")){
				$cishu++;
			}
			if($cishu==4){
				if($line=="N.A."){//如果结果为'n.a.'，则默认为0.0000
					$line="0.0000";
				}
				$zhi[$bar][$xm_vid]=$value;
				$quzhi_zt ="";
				$cishu = 0;
				break;
			}
		}
	}
}
//print_rr($zhi);exit();
//if($u['admin']){print_rr($zhi);exit();};
if(count($zhi)){

	//把编号和项目更新到pdf表的pdf_detail字段
	update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);

	yqdaoru($zhi,"vd27");
}
?>
