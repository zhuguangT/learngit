<?php
/*
*功能：原子吸收仪器载入页面（铜、锌、铁、锰、铅、镉、钾、钠）
*作者：zhengsen
*时间：2015-3-30
*/
//include("../../temp/config.php");
$arr     = array();
//$lujing="../files/as_yzxs1.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$cishu   = 0;
$quzhi_zt ='';
$xmArr=array("FE"=>"154","MN"=>"157","CU"=>"159","ZN"=>"161","CD"=>"133","PB"=>"137","K"=>"172","NA"=>"162");
$get_xmzt=$dw_zt='1';
$get_bar='';
$zhi = array();
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'),'NG/ML'=>array('hs'=>'0.001','blws'=>'7'));
//if($u['admin']==1){print_rr($arr);}
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	if($get_xmzt){
		$temp_line=explode(":",$line);
		foreach($temp_line as $key=>$value){
			if($xmArr[$value]){
				$xmid=$xmArr[$line];
				$get_xmzt='';
				$get_bar='1';
			}
		}
	}
	//获取单位
	if(stristr($line,"NG/ML")&&$dw_zt){
		$unit='NG/ML';
		$dw_zt='';
	}
	if(stristr($line,"MG/L")||stristr($line,"µG/ML")&&$dw_zt){
		$unit='MG/L';
		$dw_zt='';
	}
	if(stristr($line,"µG/L")||stristr($line,"UG/L")&&$dw_zt){
		$unit='µG/L';
		$dw_zt='';
	}
	//匹配样品编号
	if(match_bar($line)&&$get_bar){
		$bar = match_bar($line);
		if(isset($zhi[$bar])){
			$bar = $bar."P";
		}
		$quzhi_zt = "start";
	}
	//获取数据并赋值
	if($quzhi_zt=="start"&&(stristr($line,".")||$line=="0")){
		$cishu++;
		if($cishu==3){
			if(stristr($line,"-")){
				$line='0';
			}
			$zhi[$bar][$xmid]=$line;
			if($line!='0'){
				if(empty($unit_arr[$unit]['blws'])){
					$zhi[$bar][$xmid]=$line*$unit_arr[$unit]['hs'];
				}else{
					$zhi[$bar][$xmid]=number_format(($line*$unit_arr[$unit]['hs']),$unit_arr[$unit]['blws']);
				}
			}
			$quzhi_zt = "stop";
			$cishu = 0;
		}
	}
}
//if($u['admin']==1){print_rr($zhi);}
//print_rr($zhi);exit();
if(count($zhi)){
	yqdaoru($zhi);
}
?>
