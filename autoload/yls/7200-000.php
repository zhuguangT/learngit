<?php
/*
 *功能：叶绿素仪器导入页面
 *作者：zhengsen
 *时间：2014-11-24
 */
 $arr=array();
//include("../../temp/config.php");
//$lujing="../files/tlyls.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$xmArr=array("CHL-NA"=>'86');
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'µG/L'=>array('hs'=>'1','blws'=>'4'),'UG/L'=>array('hs'=>'1','blws'=>'4'));
$quzhi_zt=$get_bar='';
$get_xmzt=1;
$zhi     = array();
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
	$unit='';
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//echo $line."<br/>";
	//获取项目
	if($get_xmzt){
		$temp_xm=explode(" ",$line);
		foreach($temp_xm as $k=>$v){
			if($xmArr[$v]){
				$vid=$xmArr[$v];
				$get_xmzt='';
				$get_bar='1';
			}
		}
	}
	//取出编号
	if(preg_match("/^[A-Z]{2}\d{6}[-]\d{4}(PJ|P|J)?/",$line,$bianHao)&&$get_bar){
		$bar = $bianHao[0];
		/*if(isset($zhi[$bar])){
			$bar = $bar."P";
		}*/
		$quzhi_zt = "start";
	}
	//取出项目相应数值
	if($quzhi_zt=='start'){
		$temp_arr=explode(" ",$line);
		foreach($temp_arr as $key=>$value){
			if(stristr($value,".")){
				$last_sj=$value;
				if(stristr($value,'MG/L')){
					$unit='MG/L';
				}
				if(stristr($value,'µG/L')||stristr($value,'UG/L')){
					$unit='UG/L';
				}
				if($unit){
					if(empty($unit_arr[$unit]['blws'])){
					$zhi[$bar][$vid]=$last_sj*$unit_arr[$unit]['hs'];
					}else{
					$zhi[$bar][$vid]=number_format(($last_sj*$unit_arr[$unit]['hs']),$unit_arr[$unit]['blws']);
					}
					$quzhi_zt="stop";
				}
				continue;
			}
			if($unit_arr[$value]!=''){
				if(empty($unit_arr[$value]['blws'])){
					$zhi[$bar][$vid]=$last_sj*$unit_arr[$value]['hs'];
				}else{
					$zhi[$bar][$vid]=number_format(($last_sj*$unit_arr[$value]['hs']),$unit_arr[$value]['blws']);
				}
				continue;
				$quzhi_zt="stop";
			}
		}
	}
}
//print_rr($zhi);exit();
//if($u['userid']=='admin'){print_rr($zhi);}
if(count($zhi)){
	yqdaoru($zhi);
}
?>
