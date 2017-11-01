<?php
/*
 *功能：流动注射仪器导入页面
 *作者：tangyongsheng
 *时间：2016-04-15
 */
header("Content-Type:text/html;charset=utf-8"); 
$arr=array();
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";

$arr     = @file($lujing2);//把文件 读取成数组
$xmArr   =array("挥发酚"=>'105',"挥挥挥"=>'105',"-CN"=>"179","LAS"=>'107',"阴离子合成洗涤剂"=>'107');
$newsxmArr=array("挥挥挥"=>'挥发酚',"LAS"=>'阴离子合成洗涤剂');
$html    = array("<BR>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;","<BR/>");
$zhi    = $xm_arr=$bar_code_arr=$jcxm_arr=array();
$quzhi_zt=$cishu='';
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
    //循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
    $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
    //echo $line."<br />";
    $temp_line=explode(" ",$line);
    foreach($temp_line as $key=>$value){
       // echo $value."<br />";
        if(stristr($value,'METHOD')){
            $get_xm=1;
        }
        if(stristr($value,'UNIT')){
            $get_xm='';
        }
        //获取项目
        if(!empty($xmArr[$value])&&$get_xm){
            if(!in_array($xmArr[$value],$xm_arr)){
                $xm_arr[]=$xmArr[$value];
            }
            /*//替换正常的字符串
            if(!empty($newsxmArr[$value])){
                $value=$newsxmArr[$value];
            }*/
            if(!in_array($value,$jcxm_arr)){
                $jcxm_arr[]=$value;
            }
        }
        //匹配样品编号
        if(match_bar($value)){
            $get_xm='';//停止获取项目名称
            $bar = match_bar($value);
            if(!in_array($bar,$bar_code_arr)){
                $bar_code_arr[]=$bar;
            }
            $quzhi_zt = "start";
            $cishu=0;
            continue;
        }
        //取出数值
        if($quzhi_zt=='start'){
            if(stristr($value,".")){
                $cishu++;
                $zhi[$bar][$xm_arr[$cishu-1]]=$value;
            }
            if($cishu==count($xm_arr)){
                $cishu=0;
                $quzhi_zt='stop';
            }
        }
    }
}
/*echo '<pre>';
print_r($zhi);
die();*/
if(count($zhi)){
    foreach ($jcxm_arr as $key => $value) {
         //替换正常的字符串
        $jcxm_arr=array();
        if(!empty($newsxmArr[$value])){
            $jcxm_arr[]=$newsxmArr[$value];
        }
    }
    //把编号和项目更新到pdf表的pdf_detail字段
    update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);

    yqdaoru($zhi,"vd27");
}
?>
