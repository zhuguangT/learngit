<?php
/*
*功能：ICP-MS仪器载入页面
*作者：tangyongsheng
*时间：2016-07-26
*/
header("Content-Type:text/html;charset=utf-8"); 
$arr     = array();
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

//检测项目
$xmArr   = array("CU3247"=>'159',"ZN2138"=>'161',"PB2203"=>'137',"CD2288"=>'133',"AL3961"=>'152',"FE2382"=>'154',"MN2576"=>'157',"CA3179"=>'173',"CA_3179"=>'173',"K7664"=>'172',"K_7664"=>'172',"MG2795"=>'174',"NA5895"=>'162',"NA_5895"=>'162',"MG_2795"=>'174',"CR2061"=>"727","CR_2061"=>"727","CO2286"=>'167',"CO_2286"=>'167',"LI6707"=>'163',"LI_6707"=>'163',"SR4077"=>'164',"SR_4077"=>'164',"TI3349"=>'168',"TI_3349"=>'168',"V_3102"=>"169","V3102"=>'169',"B_2496"=>'195',"B2496"=>'195',"BA2335"=>'143',"BA_2335"=>'143',"BE2348"=>"145","BE_2348"=>"145","MO2038"=>"146","MO_2038"=>"146","NI2216"=>'148',"NI_2216"=>'148');

$newsxmArr=array("CU3247"=>'Cu',"ZN2138"=>'Zn',"PB2203"=>'Pb',"CD2288"=>'Cd',"AL3961"=>'Al',"FE2382"=>'Fe',"MN2576"=>'Mn',"CA3179"=>'Ca',"CA_3179"=>'Ca',"K7664"=>'K',"K_7664"=>'K',"MG2795"=>'Mg',"NA5895"=>'Na',"NA_5895"=>'Na',"MG_2795"=>'Mg',"CR2061"=>"Cr","CR_2061"=>"Cr","CO2286"=>'Co',"CO_2286"=>'Co',"LI6707"=>'Li',"LI_6707"=>'Li',"SR4077"=>'Sr',"SR_4077"=>'Sr',"TI3349"=>'Ti',"TI_3349"=>'Ti',"V_3102"=>"v","V3102"=>'v',"B_2496"=>'B',"B2496"=>'B',"BA2335"=>'Ba',"BA_2335"=>'Ba',"BE2348"=>"Be","BE_2348"=>"Be","MO2038"=>"Mo","MO_2038"=>"Mo","NI2216"=>'Ni',"NI_2216"=>'Ni');
$html    = array("<BR>","<BR />","<I>","</I>","<B>","</B>","<A NAME=1></A>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;","<BR/>");
$get_xm=$get_bar=$quzhi_zt='';
$zhi=$xm_arr=$jcxm_arr=$bar_code_arr=array();
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
    //循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
    $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
    $line  = trim(str_replace('&LT;',"0",$line));
    //echo $line."<br />";
    $temp_line=explode(" ",$line);
    foreach($temp_line as $key=>$value){
    	//echo $value."<br />";
        if(stristr($value,'SAMPLE')){
         $get_xm="start";
    	}
         //获取项目
    if(!empty($xmArr[$value])&&$get_xm=='start'){
        if(!in_array($xmArr[$value],$xm_arr)){
            $xm_arr[]=$xmArr[$value];
            //echo $value."<br />";
        }
        if(!in_array($value,$jcxm_arr)){
            $jcxm_arr[]=$value;
        }
        $get_bar='start';
    }
     //匹配样品编号
    if(match_bar($value)&&$get_bar=='start'){
        $get_xm='';//停止获取项目名称
        $bar = match_bar($value);
       // echo $bar."<br />";
        if(!in_array($bar,$bar_code_arr)){
            $bar_code_arr[]=$bar;
        }
        $quzhi_zt = "start";
        $cishu=0;
        continue;
    }
    //取出数值
    if($quzhi_zt=='start' && stristr($value,".")){
        $cishu++;
        //六价铬质量分数vid=615,铬质量分数vid=727
        if($xm_arr[$cishu-1]==727){
            $zhi['vd4'][$bar][$xm_arr[$cishu-1]]=number_format($value,4);//结果值为铬质量分数的结果
            $zhi['vd4'][$bar][615]=number_format($value,4);              //结果值为六价铬质量分数的结果
        }else{
             $zhi['vd27'][$bar][$xm_arr[$cishu-1]]=number_format($value,4);
        }
        if($cishu==count($xm_arr)){
            $cishu=0;
            $quzhi_zt='stop';
        }
    }
   }
}
if(count($zhi)){
    $jcxm_arr2=array();
    foreach ($jcxm_arr as $key => $value) {
         //替换正常的字符串
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
