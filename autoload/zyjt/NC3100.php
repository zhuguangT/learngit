<?php
/*
*功能：总有机碳载入
*作者:tangyongsheng
*时间：2016-11-30
*数组格式:$zhi = array([编号1]=>值1,[编号2]=>值2)
*/
header("Content-Type:text/html;charset=utf-8");
$arr= array();
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$quzhi_zt=$quzhi_zt2=$unit='';
$zhi=$bar_code_arr=$shu=array();
$cishu=$p=0;
$jcxm_arr=array("总有机碳");
//取出数组键数
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
    //循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
    $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
    //获取样品编号
    if(preg_match('/[A-Z]{2}\d{10}(PJ|P|J|\+)?/',$line)||preg_match("/02C/",$line)||preg_match('/08C/',$line)){
		if($line=='02C'){
			$bar='0.2C';
		}elseif($line=='08C'){
			$bar='0.8C';
		}else{
			$bar      = $line;
		}
		if(!in_array($bar,$bar_code_arr)){
			$bar_code_arr[]=$bar; //样品编号
		}
		$quzhi_zt = "start";
        continue;
	}
	//获取检测结果
	if($quzhi_zt=='start'){
		//开启获取过程值的状态
		if(stristr($line,"REP")){
			$p++;
			if($p==1){
				$quzhi_zt2='start';
			}else{
				$quzhi_zt2='';
				break;
			}
		}
		//关闭获取过程值的状态
		if(stristr($line,"AU")){
			$quzhi_zt2="stop";
		}
		//获取单位
		if(!$unit&&$quzhi_zt2=='start'){
	        if(stristr($line,'MG/L')||stristr($line,'UG/ML')||stristr($line,'μG/ML')){
	           $unit='MG/L';
	        }
	        if(stristr($line,'μG/L')||stristr($line,'UG/L') || stristr($line,'μG/L')){
	            $unit='μG/L';
	        }
	        if(stristr($line,'NG/UL')){
	            $unit='MG/L';
	        }
	    }
		//进行比较大小，取小的值
		if($quzhi_zt2=='start' && stristr($line, ".")){
			$pattern = "/^[-+]?[0-9]+\.[0-9]+/";  
			if(preg_match($pattern,$line)){
				if($unit=="MG/L"){
					$shu[]=$line;
				}else{
					$shu[]=$line*0.001;
				}
			}
		}
	}       
}
//最终结果进行判断
/*sort($shu);//按照数字升序对数组中的元素进行排序,取2个值相对偏小的
$zhi['vd7'][111][$bar]=$shu[0];
$zhi['vd17'][111][$bar]=$shu[1];*/
if(count($shu)=='2'){
	$zhi['vd7'][111][$bar]=$shu[0];
	$zhi['vd17'][111][$bar]=$shu[1];
}else{
	$a=abs($shu[0]-$shu[1]);
	$b=abs($shu[0]-$shu[2]);
	$c=abs($shu[1]-$shu[2]);
	if($a<$b&&$a<$c){
		$zhi['vd7'][111][$bar]=$shu[0];
		$zhi['vd17'][111][$bar]=$shu[1];
	}elseif($b<$a&&$b<$c){
		$zhi['vd7'][111][$bar]=$shu[0];
		$zhi['vd17'][111][$bar]=$shu[2];
	}elseif($c<$a&&$c<$b){
		$zhi['vd7'][111][$bar]=$shu[1];
		$zhi['vd17'][111][$bar]=$shu[2];
	}
}
if(count($zhi)){
	update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);
	foreach($zhi as $zr_lie=>$lie_data){
    	yqdaoru($lie_data,$zr_lie);
    }
}
?>
