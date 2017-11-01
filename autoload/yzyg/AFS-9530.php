<?php
/*
 *功能：原子荧光仪器导入页面(砷、硒、汞)
 *作者：zhengsen
 *时间：2015-08-24
 */
$arr=array();
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$xmArr = array("AS"=>"166","SE"=>"141","HG"=>"138");

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=1></A>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$unit_arr=$global['unit_trans'];
$quzhi_zt=$get_bar=$unit=$get_data='';
$zhi     = $bar_code_arr=$jcxm_arr=array();
$cishu=0;
$get_xmzt=1;//获得项目的状态

//print_rr($arr);exit();

for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//取出化验项目
	if($get_xmzt){
		if($xmArr[$line]){
			if(!in_array($line,$jcxm_arr)){
				$jcxm_arr[]=$line;
			}
			$xm_vid=$xmArr[$line];
			$get_xmzt='';
			$get_bar=1;
		}
	}
	//取出编号
	if(match_bar($line)&&$get_bar){
		$bar = match_bar($line);
		if(!in_array($bar,$bar_code_arr)){
			$bar_code_arr[]=$bar;
		}
		$quzhi_zt = "start";
		$cishu=0;
	}
	//取出项目相应数值
	if($quzhi_zt=='start'){
		
		$temp_line=explode(" ",$line);
		foreach($temp_line as $key=>$value){
			if($unit_arr[$value]){
				if(!$unit){
					$unit=$value;
				}
				$get_data=1;
			}
		}
		if(stristr($line,".")||$line=='0'&&$get_data){
			$cishu++;
			if($cishu==3){
				$zhi[$bar][$xm_vid]=$value;
				$cishu=$get_data=0;

			}
		}
	}
}
//print_rr($zhi);exit();
if(count($zhi)&&$unit){
	//把编号和项目更新到pdf表的pdf_detail字段
	update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);

	yqdaoru($zhi,'vd0',$unit);
}
?>
