<?
/*
*功能：石油类仪器载入页面
*作者：zhengsen
*时间：2016-02-16
*数组格式:$zhi = array([编号1]=>值1,[编号2]=>值2)
*/
header("Content-Type:text/html;charset=utf-8"); 
$arr     = array();
//$lujing="./files/2.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$quzhi_zt='';
$cishu=0;
$zhi	 =$bar_code_arr=$jcxm_arr=array();
//取出数组键数

//print_rr($arr);exit();
$jcxm_arr=array("石油类");
for($i=0;$i<count($arr);$i++){
        //循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
       $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
		$temp_arr=explode(" ",$line);
		foreach($temp_arr as $key=>$value){
			//匹配样品编号
			if(match_bar($value)){
                $bar      = match_bar($value);
				if(!in_array($bar,$bar_code_arr)){
					$bar_code_arr[]=$bar;
				}
				$quzhi_zt='stop';
				$cishu=0;
				continue;
			}
			if(stristr($value,'ABS')){
				$quzhi_zt='start';
				continue;
			}
			if($quzhi_zt=='start'&&stristr($value,'.')){
				$cishu++;
				if($cishu==2){
					$zhi['vd1'][$bar]=$value;
				}
				if($cishu==4){
					$zhi['vd2'][$bar]=$value;
				}
				if($cishu==6){
					$zhi['vd3'][$bar]=$value;
					$cishu=0;
					$quzhi_zt='stop';
				}
			}
		}
}
//if($u['userid']=='admin'){print_rr($zhi);}
//下面是吧 $zhi 数据写入数据库
if(count($zhi)){

	//把编号和项目更新到pdf表的pdf_detail字段
	update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);

	yqdaoru($zhi);
}
?>
