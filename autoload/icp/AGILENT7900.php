<?php
/*
*功能：ICP-MS仪器载入页面
*作者：zhaohongqi
*时间：2017-10-10
*/
header("Content-Type:text/html;charset=utf-8"); 
$arr     = array();
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$xmArr   = array('BE'=>'145', 'V'=>'169', 'NI'=>'148', 'AS'=>'166', 'SE'=>'141', 'MO'=>'146', 'AG'=>'150', 'CD'=>'133', 'SN'=>'170', 'SB'=>'142', 'TL'=>'151', 'BA'=>'143','SR'=>'164', 'ZN'=>'169', 'PB'=>'137', 'LI'=>'163', 'B'=>'195', 'AL'=>'152', 'TI'=>'168', 'MN'=>'157', 'FE'=>'154', 'CO'=>'167', 'CU'=>'159');
$html    = array("<BR>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>","<A NAME=1></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;","<BR/>"," ","　","");
$zhi = $bar_code_arr = $jcxm_arr = [];
$start = $tmp = $get_zhi = '';
$check_num  = $jiange = 1;
$unit = "MG/L"; //默认项目单位为mg/L

for($i=0;$i<count($arr);$i++){
    //判断开始抓取数据的起始行
    if(strpos($arr[$i], '待测元素表') !== false) $start = true;
    if(!$start) continue;

    //循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
    $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
    //获取项目的单位
    $unit_u = strpos($line, 'UG/L');
    if($unit_u) $unit = 'UG/L';

    $temp_line=explode(" ",$line);
    foreach($temp_line as $key=>$value){
        //判断图谱一行中连续项目的数量，记为$jiange
        if(array_key_exists($value, $xmArr)){
            $jcxm_arr[$value] = $xmArr[$value];
            if($tmp && $check_num){
                ($i - $tmp==1) ? $jiange++ : $check_num = false;
            }
            $tmp = $i;
        }
        //匹配编号，获取编号所在行的行号
        if(match_bar($value)){
            $bar = match_bar($value);
            $get_zhi = $i;
        }
        //$m与$jiange比较，判断是否为存在数据的行
        $m = $i - $get_zhi;
        if($m>=1 && $m<=$jiange){
            //判断是否存在小于检出限的情况
            if(strpos($value, '&LT;') !== false) $value = 0;
            if(strpos($value, '<') !== false) $value = 0;
            if($unit == 'UG/L') $value /= 1000;
            $data[$bar][] = $value;
        }     
    }
}

$xm_num = count($jcxm_arr);
foreach($data as $bar=>$val_arr){
    $val_arr = array_slice($val_arr, 0, $xm_num);
    $val_arr = array_combine($jcxm_arr, $val_arr);
    $zhi[$bar] = $val_arr;
}
// print_rr($zhi);
// print_rr($jcxm_arr);
if(count($zhi)){
    //把编号和项目更新到pdf表的pdf_detail字段
    $bar_code_arr = array_keys($zhi);
    update_pdf_detail($pdf_rs['id'],$bar_code_arr,array_flip($jcxm_arr));
    //把数据存储到数据库中去
    yqdaoru($zhi,'vd27');
}