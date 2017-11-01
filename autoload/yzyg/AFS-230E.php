<?php
/*
*功能：原子荧光 AFS-230E
*作者：Mr Zhou
*时间：2016-02-15
*/
header("Content-Type:text/html;charset=utf-8"); 
$arr= array();
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$xmArr   = array("CA"=>'173',"MG"=>'174',"NA"=>'162',"CU"=>'159',"ZN"=>'161',"PB"=>'137',"CD"=>'133',"BA"=>'143',"CO"=>'167',"MO"=>'146',"NI"=>'148',"TI"=>'168',"SB"=>'142',"AS"=>'166',"SE"=>'141',"HG"=>"138","SN"=>'170');

$newsxmArr=array("CA"=>'Ca',"MG"=>'Mg',"NA"=>'Na',"CU"=>'Cu',"ZN"=>'Zn',"PB"=>'Pb',"CD"=>'Cd',"BA"=>'Ba',"CO"=>'Co',"MO"=>'Mo',"NI"=>'Ni',"TI"=>'Ti',"SB"=>'Sb',"AS"=>'As',"SE"=>'Se',"HG"=>"Hg","SN"=>'Sn');

$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'),'NG/ML'=>array('hs'=>'0.001','blws'=>'7'));
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$quzhi_zt='';
$zhi =$jcxm_arr=$bar_code_arr=array();
$cishu=0;
$get_xm=1; //开启获取项目的标识
//取出数组键数
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
        //循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
       $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
        //取出编号匹配有8个的 数字, 加0~1个的 "J"或"P"
        $temp_arr=explode(" ",$line);
        foreach($temp_arr as $key=>$value){
        	//echo $value."<br/>";
            //获取项目
            if(!empty($xmArr[$value])&&$get_xm){

                $xm_vid=$xmArr[$value];
                /*//替换正常的字符串
                if(!empty($newsxmArr[$value])){
                    $value=$newsxmArr[$value];
                }*/
                if(!in_array($value,$jcxm_arr)){
                    $jcxm_arr[]=$value;
                }
            }
            //获取样品编号
             if(match_bar($value)){
                $bar      = match_bar($value);
                $cishu=0;
                /*if($zhi[$bar]!=''){//编号相同的情况默认处理为平行样品
                    $bar  = $bar."P";
                }*/
                if(!in_array($bar,$bar_code_arr)){
                    $bar_code_arr[]=$bar;
                }
                $quzhi_zt = "start";
                continue;
            }
            //获取第一个带小数点的数据
            if($quzhi_zt=="start" && stristr($value,".")){
                $cishu++;
                if($cishu==2){
                    $zhi['vd27'][$bar][$xm_vid] = $value;
                    //砷的质量分数vid:617
                    if($xm_vid=='166'){
                        $zhi['vd4'][$bar][617] = $value;
                    }
                    //汞的质量分数vid:619
                    if($xm_vid=='138'){
                        $zhi['vd4'][$bar][619] = $value;
                    }
                    $quzhi_zt  = "stop";
                    $cishu=0;
                }
                continue;
            }
        }    
}
if(count($zhi)){
    foreach ($jcxm_arr as $key => $value) {
         //替换正常的字符串
        $jcxm_arr=array();
        if(!empty($newsxmArr[$value])){
            $jcxm_arr[]=$newsxmArr[$value];
        }
    }
	update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);
	//yqdaoru($zhi,'vd27');
	foreach($zhi as $zr_lie=>$lie_data){
            yqdaoru($lie_data,$zr_lie);
    }
}
?>
