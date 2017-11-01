<?
/*
*功能：石油类仪器载入页面
*作者：zhengsen
*时间：2015-03-24
*数组格式:$zhi = array([编号1]=>值1,[编号2]=>值2)
*/
include("../../temp/config.php");
$arr     = array();
$lujing="../files/qd_syl2.pdf";
print_rr($arr);exit();
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$quzhi_zt='';
$zhi	 =array();
//取出数组键数
print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
        //循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
       $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
        //取出编号匹配有8个的 数字, 加0~1个的 "J"或"P"
		$temp_arr=explode(" ",$line);
		foreach($temp_arr as $key=>$value){
			if((match_bar($value)&&!stristr($value,"."))){
                $bar      = match_bar($value);
                /*if($zhi[$bar]!=''){//编号相同的情况默认处理为平行样品
					$bar  = $bar."P";
                }*/
                $quzhi_zt = "start";
				continue;
			}
		}
        //获取第一个带小数点的数据
        if($quzhi_zt=="start" && (stristr($line,".")||$line=="0")){
			$zhi[$bar] = $line;
			$quzhi_zt  = "stop";
        }
}

//if($u['userid']=='admin'){print_rr($zhi);}
//下面是吧 $zhi 数据写入数据库
if(count($zhi)){
	yqdaoru($zhi);
}
?>
