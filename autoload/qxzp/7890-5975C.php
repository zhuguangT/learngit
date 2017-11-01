<?php
/*
 *功能：气质联用仪器导入页面
 *作者：zhengsen
 *时间：2015-01-06
 */
 $arr=array();
//include("../../temp/config.php");
//$lujing="../files/slw_qxzp.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$xmArr = array("1,2,3-三氯苯"=>"340","1,2-二氯苯"=>"336","1,3-二氯苯"=>"338","1,4-二氯苯"=>"337","苯"=>"315","三氯甲烷"=>"496","四氯化碳"=>"280","甲苯"=>"316","氯苯"=>"335","二甲苯"=>"317","异丙苯"=>"324","氯乙烯"=>"302","丙烯醛"=>"275","1,2,3,4-四氯苯"=>"343","丙烯腈"=>"313","三氯乙醛"=>"503","苯乙烯"=>"309","1，2，3-三氯苯"=>"340","1，2-二氯苯"=>"336","1，3-二氯苯"=>"338","1，4-二氯苯"=>"337","苯"=>"315","三氯甲烷"=>"496","四氯化碳"=>"280","甲苯"=>"316","氯苯"=>"335","二甲苯"=>"317","异丙苯"=>"324","氯乙烯"=>"302","丙烯醛"=>"275","1，2，3，4-四氯苯"=>"343","丙烯腈"=>"313","三氯乙醛"=>"503","苯乙烯"=>"309");

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>","&GT;");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
//$unit=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'));
$quzhi_zt='';
$zhi     = array();
$cishu=0;
$get_xmzt=0;//获得项目的状态
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//取出编号
	//echo $line."<br/>";
	if(match_bar($line)){
		$bar = match_bar($line);
		if(isset($zhi[$bar])){
			$bar = $bar."P";
		}
		$get_xmzt='1';
		continue;
	}
	if($get_xmzt){
		$temp_arr=explode(" ",$line);
		foreach($temp_arr as $key=>$value){
			if($xmArr[$value]){
				$cishu=0;
				$vid=$xmArr[$value];
				$quzhi_zt = "start";
				continue;
			}
			if($quzhi_zt=='start'&&stristr($value,'.')){
				$cishu++;
				if($cishu==2){
					$zhi[$bar][$vid]=$value;
					if(stristr($value,'N.D.')){
						$zhi[$bar][$vid]=0;
					}
					$quzhi_zt='stop';
					$cishu=0;
				}
			}

		}
	}
	//取出项目相应数值
	if($quzhi_zt=='start'&&stristr($line,'.')){
		$cishu++;
		if($cishu==2){
			$zhi[$bar][$vid]=$line;
			if(stristr($line,'N.D.')){
				$zhi[$bar][$vid]=0;
			}
			$cishu=0;
			$quzhi_zt='';
		}
	}
}
//print_rr($zhi);exit();
//if($u['userid']=='admin'){print_rr($zhi);}
if(count($zhi)){
	yqdaoru($zhi,"vd27");
}
?>
