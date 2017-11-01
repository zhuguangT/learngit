<?
/*
*功能：紫外分光仪器载入页面
*作者：zhengsen
*时间：2015-04-29
*/
//include("../../temp/config.php");

$arr     = array();
//$lujing="../files/zz_zwfg2.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$xmArr=array("氨氮"=>"198","总氮"=>"121");
$kongGe  = array("&NBSP;","&#160;");
$quzhi_zt=$get_bar='';
$get_xmzt='1';
$zhi	 =array();
$cishu=0;
//取出数组键数
//print_rr($arr);
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
        //循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
       $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	   if($get_xmzt){
		   $temp_xm=array();
		   if(stristr($line,"：")){
				$temp_xm=explode("：",$line);
		   }
		   if(stristr($line,":")){
				$temp_xm=explode(":",$line);
		   } 
		   if(!empty($temp_xm)){
				$xm=trim($temp_xm[1]);
				if($xmArr[$xm]){
					$xm_vid=$xmArr[$xm];
					$get_bar='start';
					$get_xmzt='';
					continue;
				}
		   }
		   
	   }
		$line=str_replace("_","-",$line);
		if(match_bar($line)&&$get_bar){
			$bar      = match_bar($line);
			$quzhi_zt = "start";
			$cishu=0;
			continue;
		}
        //获取第一个带小数点的数据
        if($quzhi_zt=="start" && stristr($line,".")){
			$cishu++;
			if($cishu==2){
				$zhi[$bar][$xm_vid] = (float)$line;
				$quzhi_zt  = "stop";
				$cishu=0;
			}
        }
}
//print_rr($zhi);
//print_rr($zhi);exit();
//if($u['userid']=='admin'){print_rr($zhi);}
//下面是吧 $zhi 数据写入数据库
if(count($zhi)){
	yqdaoru($zhi,'vd27');
}
?>
