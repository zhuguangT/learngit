<?php
/*
 *功能：耶拿CONTRAA700原子吸收仪器导入页面
 *作者：zhengsen
 *时间：2015-06-30
 */
$arr=array();
//include("../../temp/config.php");
//$lujing="../files/yzxs_cs.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$xmArr   =array("ZN"=>'161',"MN"=>'157',"NA"=>'162',"LI"=>'163',"SR"=>'164');

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$zhi    =$bar_code_arr=$jcxm_arr= array();
$quzhi_zt=$xm_zt='';
$get_xmzt=1;
$cishu=0;
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	$line  =str_replace(array('-','－','－'),array('-','-','-'),$line);//把中文,中文全角,英文全角的横杠替换成英文半角的横杠
	$tempArr=explode(" ",$line);
	//样品和数据在一起的情况
	foreach($tempArr as $key=>$value){
		//匹配项目名称
		if($get_xmzt){
			$xm_temp_arr=explode('-',$value);
			if(!empty($xm_temp_arr)){
				foreach($xm_temp_arr as $key1=>$value1){
					if($xmArr[$value1]){
						if(!in_array($value1,$jcxm_arr)){
							$jcxm_arr[]=$value1;
						}
						$xm_vid=$xmArr[$value1];
						$get_xmzt='';
						$xm_zt=1;
					}
				}
			}
		}
		//匹配出样品编号
		if(match_bar($value)){
			$bar = match_bar($value);
			if(!in_array($bar,$bar_code_arr)){
				$bar_code_arr[]=$bar;
			}
			$cishu=0;
			$quzhi_zt = "start";
			continue;
		}
		//匹配项目名称
		if($quzhi_zt=='start'&&$get_xmzt){
			if(preg_match("/^[A-Z]{1,2}\d{3}$/",$value,$xm_temp)){
				if(preg_match("/^[A-Z]{1,2}/",$value,$xm_temp)){
					$xm_bs=$xm_temp[0];
					if(!in_array($xm_bs,$jcxm_arr)){
						$jcxm_arr[]=$xm_bs;
					}
					$xm_vid=$xmArr[$xm_bs];
					$get_xmzt='';
					$xm_zt=1;
					continue;
				}
			}
		}
		if($quzhi_zt=='start'&&$xm_zt==1){
			if(stristr($value,".")){
				$cishu++;
				$value=preg_replace("/[A-Z]*\/*/",'',$value);
				if($cishu==1){
					$zhi['vd3'][$bar][$xm_vid]=$value;
				}
				if($cishu==3){
					$zhi['vd4'][$bar][$xm_vid]=$value;
					$cishu=0;
					$quzhi_zt='stop';
				}
			}
		}
	}

}
//print_rr($zhi);exit();
//if($u['userid']=='admin')print_rr($zhi);
if(count($zhi)){

	//把编号和项目更新到pdf表的pdf_detail字段
	update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);

	foreach($zhi as $zrlie=>$data){
		yqdaoru($data,$zrlie);
	}
}

?>
