<?php
/*
 *功能：流动注射仪器导入页面
 *作者：zhengsen
 *时间：2014-11-18
 */
 $arr=array();
//include("../../temp/config.php");
//$lujing="../files/tlldzs3.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组
$xmArr=array("TCN"=>"179","PHENOL"=>"105","MBAS"=>"107","CR"=>"135","TP"=>"120","TN"=>"121","NH3-N"=>"198","KMN04"=>"104","S"=>"185");
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$zhi    = array();
$quzhi_zt='';
$get_xm=1;
$get_bar='';
$k=0;
//print_rr($arr);exit();
//if($u['admin']){print_rr($arr);exit()};
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//echo $line."<br/>";
	if($get_xm){
		if(stristr($line,"-")){
			$temp_line=explode("-",$line);
			$line=$temp_line[0];
		}
		if($xmArr[$line]){
			$vid=$xmArr[$line];
			$get_xm='';
			$get_bar='1';
		}
		continue;
	}
	if($get_bar){
		//if(preg_match("/^[A-Z]{2}\d{6}[-]\d{4}(PJ|P|J)?/",$line,$bianHao)){//总站编号匹配
		if(match_bar($line)){//总站编号匹配
			$bar = match_bar($line);
			$quzhi_zt = "start";
			continue;
		}
	}
	//取出数值
	if($quzhi_zt=='start'){
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
		if($cishu=='2'){
			if(stristr($line,"-")){
				$zhi[$bar][$vid]='0';
			}else{
				$zhi[$bar][$vid]=$line;
			}
			$k=0;
			$quzhi_zt='stop';
			$cishu=0;
		}
	}
}
//print_rr($zhi);exit();
//if($u['userid']=='admin')print_rr($zhi);
if(count($zhi)){
	yqdaoru($zhi);
}
?>
