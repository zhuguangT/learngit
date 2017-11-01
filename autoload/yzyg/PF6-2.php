<?php
/*
 *功能：原子荧光仪器导入页面(砷、硒、汞)
 *作者：zhengsen
 *时间：2015-03-19
 */
//include("../../temp/config.php");
 $arr=array();
//$lujing="../files/bx_yzyg.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$xmArr = array("AS"=>"166","SE"=>"141","HG"=>"138","NONE"=>"NONE");

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'μG/L'=>array('hs'=>'0.001','blws'=>'7'),'UG/L'=>array('hs'=>'0.001','blws'=>'7'));
$quzhi_zt=$xm_vid='';
$zhi     = array();
$get_xmzt=1;//获得项目的状态
//print_rr($arr);exit();
//if($u[admin]){print_rr($arr);exit();}
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//取出化验项目
	//echo $line."<br/>";
	if($get_xmzt){
		$temp_xmArr = explode(':',$line);
		foreach($temp_xmArr as $key=>$value){
			$temp_xm=trim($value);
			if($xmArr[$temp_xm]){
				$xm_vid=$xmArr[$temp_xm];
				$get_xmzt='';
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
		$get_xmzt='';
		continue;
	}
	//取出项目相应数值
	if($quzhi_zt=='start'){
		if(stristr($line,".")){
			$last_sj=$line;
		}
		if($unit_arr[$line]!=''){
			//echo $line;exit();
			if(empty($unit_arr[$line]['blws'])){
				$zhi[$bar][$xm_vid]=$last_sj*$unit_arr[$line]['hs'];
			}else{
				$zhi[$bar][$xm_vid]=number_format(($last_sj*$unit_arr[$line]['hs']),$unit_arr[$line]['blws']);
			}
			$quzhi_zt='stop';
		}
	}
}
//print_rr($zhi);exit();
//if($u['userid']=='admin'){print_rr($zhi);}
if(count($zhi)){
	yqdaoru($zhi);
}

?>
