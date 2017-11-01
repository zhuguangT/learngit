<?php
/*
 *功能：流动注射仪器导入页面
 *作者：zhengsen
 *时间：2014-11-18
 */
 $arr=array();
//include("../../temp/config.php");
//$lujing="../files/slw_ldzs.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组
$xmArr=array("TCN"=>"179","PHENOL"=>"105","MBAS"=>"107","CR"=>"135","TP"=>"120","TN"=>"121","NH3-N"=>"198","KMN04"=>"104","S"=>"185");
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'),'NG/ML'=>array('hs'=>'0.001','blws'=>'7'));
$zhi    = array();
$quzhi_zt='';
$get_xm=1;
$get_bar='';
$k=0;
//print_rr($arr);
//if($u['admin']){print_rr($arr);exit()};
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//echo $line."<br/>";
	if($get_xm){
		if(stristr($line,"-")){
			$temp_line=explode("-",$line);
		}else{
			$temp_line=explode(" ",$line);
		}
		foreach($temp_line as $key=>$value){
			if($xmArr[$value]){
			$vid=$xmArr[$value];
			$get_xm='';
			$get_bar='1';
			}
			continue;
		}
	}
	if(stristr($line,"NG/ML")){
		$unit='NG/ML';
	}
	if(stristr($line,"MG/L")){
		$unit='MG/L';
	}
	if(stristr($line,"µG/L")||stristr($line,"UG/L")){
		$unit='µG/L';
	}
	if($get_bar){
		if(match_bar($line)){//总站编号匹配
			$bar = match_bar($line);
			$quzhi_zt = "start";
			continue;
		}
	}
	//取出数值
	if($quzhi_zt=='start'){
		if(empty($unit)){
			$unit='MG/L';
		}
		$k++;
		if($k>=5){
			$zhi[$bar][$vid]='0';
			$k=0;
			$quzhi_zt='stop';
			$cishu='0';
		}
		if(stristr($line,".")){
			$cishu++;
		}
		if($cishu=='1'){
			if(stristr($line,"-")){
				$zhi[$bar][$vid]='0';
			}else{
				$data=number_format(($line*$unit_arr[$unit]['hs']),$unit_arr[$unit]['blws']);
				$zhi[$bar][$vid]=del0($data);
			}
			$k=0;
			$quzhi_zt='stop';
			$cishu=0;
		}
	}
}

//if($u['userid']=='admin')print_rr($zhi);
if(count($zhi)){
	yqdaoru($zhi);
}
 
?>
