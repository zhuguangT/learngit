<?php
/*
*功能：原子吸收仪器载入页面（铜、锌、铁、锰、铅、镉）
*作者：zhengsen
*时间：2015-03-11
*/
include("../../temp/config.php");
$arr     = array();
$lujing="../files/yzxs.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","&#160;","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;");
$cishu   = 0;
$quzhi_zt=$get_xmzt=$xmid='';
$xmArr   =array("PB"=>"137","PB%"=>"611","CD"=>"133","AL"=>"152","CU"=>"159","BA"=>"143","BE"=>"145","MO"=>"146","NI"=>"148","AG"=>"150","TL"=>"151");
$zhi	 = $bar_code_arr=$jcxm_arr= array();
$unit_arr=array('MG/L'=>array('hs'=>'1'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'),'NG/ML'=>array('hs'=>'0.001','blws'=>'7'));

//print_rr($arr);//exit();
if($u['admin']==1){print_rr($arr);}
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//根据空格切分字符串
	$temp_line_arr=explode(" ",$line);
	foreach($temp_line_arr as $key=>$value){
		if(match_bar($value)){
			$bar = match_bar($value);
			if(!in_array($bar,$bar_code_arr)){
				$bar_code_arr[]=$bar;
			}
			if(!$xmid){
				$get_xmzt=1;
			}
			$hy_data='';
			$cishu=0;
			$quzhi_zt = "start";
			continue;
		}
		if($get_xmzt){
			//兼容聚氯化铝项目，元素+%，增加了 %? 匹配
			if(preg_match("/^[A-Z]{2}%?\d{3}[.]\d{2}/",$value,$xm_str)){
				$xm_bs = stristr($xm_str[0], '%') ? substr($xm_str[0],0,3) : substr($xm_str[0],0,2) ;
				if($xmArr[$xm_bs]){
					$xmid=$xmArr[$xm_bs];
					$jcxm_arr[]=$xm_bs;
					$get_xmzt='';
				}
			}		
		}
		if($quzhi_zt=="start"){
			if(stristr($value,'.')){
				$cishu++;
				if($cishu==2){
					$hy_data=preg_replace("/[A-Z]|[µ\/]/","",$value);
				}
			}
			if(!$unit){
				if(stristr($value,'MG/L')||stristr($value,'UG/ML')||stristr($value,'µG/ML')||stristr($value,'NG/UL')){
					$unit='MG/L';
				}
				if(stristr($value,'µG/L')||stristr($value,'UG/L')||stristr($value,'微克/升')){
					$unit='µG/L';
				}
			}
			if($unit&&$hy_data!=''){
				if(stristr($hy_data,'-')){
					$zhi[$bar][$xmid]=0;
				}else{
					if(empty($unit_arr[$unit]['blws'])){
						$zhi[$bar][$xmid]=$hy_data;
					}else{
						$hy_data=number_format(($hy_data*$unit_arr[$unit]['hs']),$unit_arr[$unit]['blws']);
						$zhi[$bar][$xmid]=del0($hy_data);
					}
				}
				$quzhi_zt="stop";
				$cishu=0;
			}
		}
	}
}
print_rr($zhi);exit();
//if($u['admin']==1){print_rr($zhi);}
if(count($zhi)){
	//把编号和项目更新到pdf表的pdf_detail字段
	update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);

	yqdaoru($zhi);
}function match_bar($bar){
	if(preg_match("/[A-Z]{2}\d{6}[-]\d{4}(PJ|P|J|\+)?/",$bar,$bianHao)||preg_match("/KB\d{9}/",$bar,$bianHao)){
		return $bianHao[0];
	}else{
		return false;
	}
}//去除多余的0
function del0($s)  
{  
    $s = trim(strval($s));  
    if (preg_match('#^-?\d+?\.0+$#', $s)) {  
        return preg_replace('#^(-?\d+?)\.0+$#','$1',$s);  
    }   
    if (preg_match('#^-?\d+?\.[0-9]+?0+$#', $s)) {  
        return preg_replace('#^(-?\d+\.[0-9]+?)0+$#','$1',$s);  
    }  
    return $s;  
}
?>
