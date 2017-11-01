<?php
/*
*功能：icp仪器载入页面（铜、锌、铅、镉）
*作者：zhengsen
*时间：2015-03-25
*/
$arr     = array();
//include("../../temp/config.php");
//$lujing="../files/qd_icp.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=1></A>","<A NAME=2></A>","<A NAME=3></A>","<A NAME=4></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$xmArr   = array("CU"=>'159',"ZN"=>'161',"PB"=>'137',"CD"=>'133',"LI"=>"163","BE"=>"145","AL"=>"152","MN"=>"157","CO"=>"167","NI"=>"148","MO"=>"146","AG"=>"150","BA"=>"143","PB"=>"137","SR"=>"164","CR"=>"135","AS"=>"166","SE"=>"141","TL"=>"151");
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'),'NG/ML'=>array('hs'=>'0.001','blws'=>'7'));
$unit='µG/L';
$quzhi_zt='';
$get_xmzt=1;
$zhi	 =$vid_arr=$bar_code_arr=$jcxm_arr= array();
$cishu=0;
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//匹配到<HR>时说明分页了要从新获取项目的数组
	if(stristr($line,"<HR>")){
		$vid_arr=array();
	}
	//获取项目数组
	if($get_xmzt){
		$temp_line=explode(" ",$line);
		foreach($temp_line as $key=>$value){
			if(!empty($xmArr[$value])){
				if(!in_array($value,$jcxm_arr)){
					$jcxm_arr[]=$value;
				}
				$vid_arr[]=$xmArr[$value];
			}
		}
	}
	//获取样品编号
	if(match_bar($line)){
		$bar = match_bar($line);
		if(!in_array($bar,$bar_code_arr)){
			$bar_code_arr[]=$bar;
		}
		$quzhi_zt="start";
		$cishu=0;
		continue;
	}
	//获取数据
	if($quzhi_zt=="start"&&stristr($line,".")){
		if(stristr($line,"-")){
			$line=0;
		}
		$zhi[$bar][$vid_arr[$cishu]]=$line;
		if($line!='0'){
			if(empty($unit_arr[$unit]['blws'])){
				$line=$line*$unit_arr[$unit]['hs'];
				$zhi[$bar][$vid_arr[$cishu]]=del0($line);
			}else{
				$line=number_format(($line*$unit_arr[$unit]['hs']),$unit_arr[$unit]['blws']);
				$zhi[$bar][$vid_arr[$cishu]]=del0($line);
			}
		}
		$cishu++;
		if($cishu==count($vid_arr)){
			$quzhi_zt="";
			$cishu=0;
		}
	}

}
//print_rr($zhi);exit();
//if($u['userid']=='admin'){print_rr($zhi);exit();};
if(count($zhi)){

	//把编号和项目更新到pdf表的pdf_detail字段
	update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);

	yqdaoru($zhi);
}
?>
