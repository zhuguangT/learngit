<?php
/*
 *功能：原子荧光仪器导入页面(砷、硒、汞)
 *作者：zhengsen
 *时间：2015-04-28
 */
//include("../../temp/config.php");
$arr=array();
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$xmArr   = array("CA"=>'173',"MG"=>'174',"NA"=>'162',"CU"=>'159',"ZN"=>'161',"PB"=>'137',"CD"=>'133',"BA"=>'143',"CO"=>'167',"MO"=>'146',"NI"=>'148',"TI"=>'168',"SB"=>'142',"AS"=>'166',"SE"=>'141',"HG"=>"138","SN"=>'170');

$newsxmArr= array("CA"=>'Ca',"MG"=>'Mg',"NA"=>'Na',"CU"=>'Cu',"ZN"=>'Zn',"PB"=>'Pb',"CD"=>'Cd',"BA"=>'Ba',"CO"=>'Co',"MO"=>'Mo',"NI"=>'Ni',"TI"=>'Ti',"SB"=>'Sb',"AS"=>'As',"SE"=>'Se',"HG"=>"Hg","SN"=>'Sn');

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$unit=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'μG/L'=>array('hs'=>'0.001','blws'=>'7'));
$quzhi_zt='';
$zhi  =$jcxm_arr=$bar_code_arr   = array();
$cishu=0;
$get_xmZt=1;//获得项目的状态
$xm=array();//图谱的化验项目
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
    //循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
    $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
    //取出化验项目
    if($get_xmZt){
        $temp_xmArr = explode('：',$line);
        foreach($temp_xmArr as $key=>$value){
            if($xmArr[$value]&&$value!=''&&!in_array($value,$xm)){
                $xm[]=$value;
            }
            if(!in_array($value,$jcxm_arr)){
                $jcxm_arr[]=$value;
            }
        }
    }
    //取出编号
    if(match_bar($line)){
        $bar = match_bar($line);
        if(isset( $zhi['vd27'][$bar][$xmArr[$xm[$cishu]]])){
            $bar = $bar."P";
        }
        if(!in_array($bar,$bar_code_arr)){
            $bar_code_arr[]=$bar;
        }
        $quzhi_zt = "start";
        $get_xmZt='0';
    }
    //取出项目相应数值
    if($quzhi_zt=='start'){
        if(stristr($line,".")){
            $last_sj=$line;
        }
        if($unit[$line]!=''){
            $zhi['vd27'][$bar][$xmArr[$xm[$cishu]]]=$last_sj;
            $cishu++;
        }
        //质量分数的检测项目:砷166   617，汞138    619
        //铅
        if($xmArr[$xm[$cishu]]=='166'){ //vd6  vd7
           // echo $bar.'----'.$value.'<br/>';  //IT2016080128P
            if(substr($bar,-1)=='P'){
                $zhi['vd7'][617][substr($bar,0,strlen($bar)-1)]=$last_sj;
            }else{
                $zhi['vd6'][617][$bar]=$last_sj;
            }
        }
        if($xmArr[$xm[$cishu]]=='138'){ //vd6  vd7
           // echo $bar.'----'.$value.'<br/>';  //IT2016080128P
            if(substr($bar,-1)=='P'){
                $zhi['vd7'][619][substr($bar,0,strlen($bar)-1)]=$last_sj;
            }else{
                $zhi['vd6'][619][$bar]=$last_sj;
            }
        }     
        if($cishu==count($xm)){
            $quzhi_zt='stop';
            $cishu=0;
        }
    }
}
/*echo '<pre>';
echo $lujing;
print_r($zhi);
die();*/
if(count($zhi)){
    foreach ($jcxm_arr as $key => $value) {
         //替换正常的字符串
        $jcxm_arr2=array();
        if(!empty($newsxmArr[$value])){
            $jcxm_arr2[]=$newsxmArr[$value];
        }else{
            $jcxm_arr2[]=$value;
        }
    }
    //把编号和项目更新到pdf表的pdf_detail字段
    update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr2);
    foreach($zhi as $zr_lie=>$lie_data){
            yqdaoru($lie_data,$zr_lie);
    }
}
?>
