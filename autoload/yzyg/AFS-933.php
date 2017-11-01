<?php
/*
 *功能：原子荧光仪器导入页面(砷、硒、汞)
 *作者：zhengsen
 *时间：2015-03-30
 */
include("../../temp/config.php");
 $arr=array();
$lujing="../files/yzyg25827.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$xmArr = array("AS"=>"166","AS%"=>"617","SE"=>"141","HG"=>"138","HG%"=>"619","SB"=>"142","NONE"=>"NONE");

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'μG/L'=>array('hs'=>'0.001','blws'=>'7'),'%'=>array('hs'=>'100','blws'=>'3'));
$quzhi_zt='';
$zhi     =$bar_code_arr=$jcxm_arr= array();
$cishu=$j=0;
$get_xmZt=1;//获得项目的状态
$xm=array();//图谱的化验项目
$unit = '';//聚合氯化铝的项目是会赋值%为单位
//print_rr($arr);exit();
//if($u[admin]){print_rr($arr);exit();}
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//取出化验项目
	//echo $line."<br/>";
	if($get_xmZt){
		$temp_xmArr = explode('：',$line);
		foreach($temp_xmArr as $key=>$value){
			if($xmArr[$value]&&!in_array($value,$xm)){
				$xm[]=$value;
				$jcxm_arr[]=$value;
				//兼容聚合氯化铝项目
				if(stristr($line,'%')){
					$unit = '%';
					$xm =  $jcxm_arr =array($value);
					$get_xmZt='0';
				}
			}
		}
	}
	//取出编号
	if(match_bar($line)){
		$bar = match_bar($line);
		if(isset($zhi[$bar])){
			$bar = $bar."P";
		}
		if(!in_array($bar,$bar_code_arr)){
			$bar_code_arr[]=$bar;
		}
		$quzhi_zt = "start";
		$get_xmZt='0';
		continue;
	}
	//取出项目相应数值
	if($quzhi_zt=='start'){
		if(stristr($line,".")){
			$j++;
			if($j==1){
				$yg_zhi=$line;
			}
			$last_sj=$line;
		}
		if($unit_arr[$line]!=''){
			!empty($unit) && $line = $unit;//如果是聚合氯化铝项目，则使用特定单位
			$zhi['vd4'][$bar][$xmArr[$xm[$cishu]]]=$yg_zhi;//获取的荧光值赋值给$zhi
			if(empty($unit_arr[$line]['blws'])){
				$result=$last_sj*$unit_arr[$line]['hs'];
				$zhi['vd0'][$bar][$xmArr[$xm[$cishu]]]=del0($result);
			}else{
				$result=number_format(($last_sj*$unit_arr[$line]['hs']),$unit_arr[$line]['blws']);
				$zhi['vd0'][$bar][$xmArr[$xm[$cishu]]]=del0($result);
			}
			$cishu++;
			$j=0;
		}
		if($cishu==count($xm)){
			$quzhi_zt='stop';
			$cishu=0;
			$j=0;
		}
	}
}
print_rr($jcxm_arr);
print_rr($zhi);exit();
//if($u['userid']=='admin'){print_rr($zhi);}
if(count($zhi)){

	//把编号和项目更新到pdf表的pdf_detail字段
	update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);

	foreach($zhi as $zr_lie=>$lie_data){
			yqdaoru($lie_data,$zr_lie);
	}
}

function match_bar($bar){
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
