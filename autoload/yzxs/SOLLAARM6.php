<?php
/*
 *功能：原子吸收分光光度计
 *检测项目：铁、锰、铜、锌、镉、银、铅、铊、铅（Pb）的质量分数/ 、镉（Cd）的质量分数/ %、重金属（以Pb计)含量
 *时间：2016-05-06
 *作者：tangyongsheng
*/
header("Content-Type:text/html;charset=utf-8"); 
//include("../../temp/config.php");
$arr=array();
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$xmArr=array("FE"=>"154","MN"=>"157","CU"=>"159","ZN"=>"161","CD"=>"133","PB"=>"137","K"=>"172","NA"=>"162","AS"=>'166','HG'=>'138','CR'=>'135',"AG"=>'150',"TI"=>'151',"TL"=>'151');//CD质量分数的vid 613
$newsxmArr=array("FE"=>"Fe","MN"=>"Mn","CU"=>"Cu","ZN"=>"Zn","CD"=>"Cd","PB"=>"Pb","NA"=>"Na","AS"=>'As','HG'=>'Hg','CR'=>'Cr',"AG"=>'Ag','TI'=>'Ti',"TL"=>"Ti");
$zhi     = array();
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'5'),'μG/L'=>array('hs'=>'0.001','blws'=>'7'));
$quzhi_zt=$unit='';
$zhi =$jcxm_arr=$bar_code_arr=array();
$cishu=0;
//$get_xm=1;//开启获取项目的标识
for($i=0;$i<count($arr);$i++){
    //循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
    $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
    //echo $line."<br />";
    $temp_line=explode(' ',$line);
    foreach($temp_line as $key=>$value){
        //echo $value."<br/>";
        if(stristr($value,"ABS") ||stristr($value,"RSD")||stristr($value,"ID")){
            $get_xm=1;//开启获取项目的标识
        }
        //获取项目
        if(!empty($xmArr[$value])&&$get_xm){
            //echo $value.'<br />';
            $xm_vid=$xmArr[$value];
            /*//替换正常的字符串
            if(!empty($newsxmArr[$value])){
                $value=$newsxmArr[$value];
            }*/
            if(!in_array($value,$jcxm_arr)){
                $jcxm_arr[]=$value;
            }
        }
        //获取单位
        if(!$unit){
            if(stristr($value,'MG/L')||stristr($value,'UG/ML')||stristr($value,'μG/ML')){
               $unit='MG/L';
            }
            if(stristr($value,'μG/L')||stristr($value,'UG/L') || stristr($value,'μG/L')){
                $unit='μG/L';
            }
            if(stristr($value,'NG/UL')){
                $unit='MG/L';
            }
        }
        //获取样品编号
         if(match_bar($value)){
            $bar      = match_bar($value);
            $cishu=0;
            /*if($zhi[$bar]!=''){//编号相同的情况默认处理为平行样品
                $bar  = $bar."P";
            }*/
            //echo $bar."<br />";
            if(!in_array($bar,$bar_code_arr)){
                $bar_code_arr[]=$bar;
            }
            $quzhi_zt = "start";
            continue;
        }
        //获取第一个带小数点的数据
        if($quzhi_zt=="start" && stristr($value,".")){
            //echo $value.'<br/>';
            $cishu++;
            if($cishu==4){
                $zhi['vd27'][$xm_vid][$bar]=number_format($value,4);
                if($xm_vid=='154'){
                    $zhi['vd27'][153][$bar]=number_format($value,4);//总铁和铁的标示一样
                    $zhi['vd27'][155][$bar]=number_format($value,4);
                }
                //质量分数的检测项目:铅，镉
                //铅
                if($xm_vid=='137'){ //vd6  vd7
                   // echo $bar.'----'.$value.'<br/>';  //IT2016080128P
                    if(substr($bar,-1)=='P'){
                        $zhi['vd7'][611][substr($bar,0,strlen($bar)-1)]=number_format($value,4);
                    }else{
                        $zhi['vd6'][611][$bar]=number_format($value,4);
                    }
                }
                //镉
                if($xm_vid=='133'){ //vd6  vd7
                   // echo $bar.'----'.$value.'<br/>';  //IT2016080128P
                    if(substr($bar,-1)=='P'){
                        $zhi['vd7'][613][substr($bar,0,strlen($bar)-1)]=number_format($value,4);
                    }else{
                        $zhi['vd6'][613][$bar]=number_format($value,4);
                    }
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
	foreach($zhi as $zr_lie=>$lie_data){
            yqdaoru($lie_data,$zr_lie);
    }
}
?>