<?php
/*
 *功能：原子吸收仪器导入页面
 *作者：zhengsen
 *时间：2014-10-22
 */
$arr=array();
//$lujing="../files/zzyzxs_cd.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$xmArr   =array("CU"=>'159',"ZN"=>'161',"PB"=>'137',"CD"=>'133',"FE"=>'154',"MN"=>'157',"K"=>'172',"NA"=>'162',"CA"=>'173',"MG"=>'174');

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$unit=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'),'UG/L'=>array('hs'=>'0.001','blws'=>'7'));
$zhi    = array();
$quzhi_zt='';
$get_xmzt=1;
$cishu=0;
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//匹配出项目名称
	if($get_xmzt){
		if(preg_match("/^[A-Z]{1,2}\d{3}/",$line,$xm_temp)){
			if(preg_match("/^[A-Z]{1,2}/",$line,$xm_temp)){
				$xm=$xm_temp[0];
				$vid=$xmArr[$xm];
				$get_xmzt='';
			}
		}
	}
	$tempArr=explode(" ",$line);
	//样品和数据在一起的情况
	foreach($tempArr as $key=>$value){
		//匹配出样品编号
		if(match_bar($value)){
			$bar = match_bar($value);
			$cishu=0;
			$quzhi_zt = "start";
			continue;
		}
		if($quzhi_zt=='start'){
			if(stristr($value,".")){
				$cishu++;
				if($cishu=='2'){
					$last_sj=$value;
				}
			}
				//取出编号相应数值
			if($unit[$value]!=''&&$cishu=='2'){
				if(stristr($last_sj,"-")){
					$zhi[$bar][$vid]='0';
				}else{
					if(empty($unit[$value]['blws'])){
						$zhi[$bar][$vid]=$last_sj*$unit[$value]['hs'];
					}else{
						$zhi[$bar][$vid]=number_format(($last_sj*$unit[$value]['hs']),$unit[$value]['blws']);
					}
				}
				$cishu=0;
				$quzhi_zt='stop';
			}
		}
	}

}
//print_rr($zhi);exit();
//if($u['userid']=='admin')print_rr($zhi);
if(count($zhi)){
	yqdaoru($zhi);
}
?>
