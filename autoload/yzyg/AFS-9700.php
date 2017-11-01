<?php
/*
 *功能：原子荧光仪器导入页面(砷、硒、汞)
 *作者：zhengsen
 *时间：2014-10-22
 */
 $arr=array();
//$lujing="../files/zzyzyg.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$xmArr = array("AS"=>"166","SE"=>"141","HG"=>"138","NONE"=>"NONE");

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'5'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'));
$quzhi_zt='';
$zhi     = array();
$cishu=0;
$get_xmzt=1;//获得项目的状态
$xm=array();//图谱的化验项目
//print_rr($arr);
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//echo $line."<br/>";
	//取出化验项目
	if($get_xmzt){
		$temp_xmArr = explode(' ',$line);
		foreach($temp_xmArr as $key=>$value){
			if($xmArr[$value]&&$value!=''&&!in_array($value,$xm)){
				$xm[]=$value;
			}
		}
	}
	//取出编号
	if(match_bar($line)){
		$bar = match_bar($line);
		if(isset($zhi[$bar])){
			$bar = $bar."P";
		}
		$quzhi_zt = "start";
		$get_xmzt='0';
	}
	//取出项目相应数值
	if($quzhi_zt=='start'){
		if(stristr($line,".")){
			$last_sj=$line;
		}
		if($unit_arr[$line]!=''){
			if(empty($unit_arr[$line]['blws'])){
				$zhi[$bar][$xmArr[$xm[$cishu]]]=$last_sj*$unit_arr[$line]['hs'];
			}else{
				$zhi[$bar][$xmArr[$xm[$cishu]]]=number_format(($last_sj*$unit_arr[$line]['hs']),$unit_arr[$line]['blws']);
			}
			$cishu++;
		}
		if($cishu==count($xm)){
			$quzhi_zt='stop';
			$cishu=0;
		}
	}
}
//print_rr($zhi);exit();
//if($u['userid']=='admin'){print_rr($zhi);}
if(count($zhi)){
	yqdaoru($zhi);
}
?>
