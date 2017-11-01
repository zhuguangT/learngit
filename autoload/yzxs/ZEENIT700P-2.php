<?php
/*
 *功能：原子吸收仪器导入页面
 *作者：zhengsen
 *时间：2015-03-18
 */
 //include("../../temp/config.php");
$arr=array();
//$lujing="../files/bx_yzxs2.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$xmArr   =array("CU"=>'159',"ZN"=>'161',"PB"=>'137',"CD"=>'133',"FE"=>'154',"MN"=>'157',"K"=>'172',"NA"=>'162',"CA"=>'173',"MG"=>'174');

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'),'UG/L'=>array('hs'=>'0.001','blws'=>'7'));
$zhi    = array();
$quzhi_zt=$get_bar='';
$get_xmzt=$get_unit=1;

$cishu=0;
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//匹配出项目名称
	if($get_xmzt){
			if($xmArr[$line]){
				$xm_vid=$xmArr[$line];
				$get_bar="start";
				$get_xmzt='';
			}
	}
	//获取单位名称
	if($get_unit){
		$temp_unit_arr=explode(':',$line);
		foreach($temp_unit_arr as $key=>$value){
			if($unit_arr[$value]){
				$unit=$value;
				$get_unit='';
			}
		}
	}
	//匹配出样品编号
	if(match_bar($line)&&$get_bar){
		$bar = match_bar($line);
		$cishu=0;
		$quzhi_zt = "start";
		continue;
	}
	if($quzhi_zt=='start'&&stristr($line,".")){
		//取出编号相应数值
		if(stristr($line,"-")){
			$zhi[$bar][$xm_vid]='0';
		}else{
			if(empty($unit_arr[$unit]['blws'])){
				$zhi[$bar][$xm_vid]=$line*$unit_arr[$unit]['hs'];
			}else{
				$zhi[$bar][$xm_vid]=number_format(($line*$unit_arr[$unit]['hs']),$unit_arr[$unit]['blws']);
			}
		}
		$quzhi_zt='stop';
	}

}
//print_rr($zhi);exit();
//if($u['userid']=='admin')print_rr($zhi);
if(count($zhi)){
	yqdaoru($zhi);
}
?>
