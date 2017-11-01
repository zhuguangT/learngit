<?php
/*
 *功能:气象色谱仪器导入页面(需要修改)
 *作者：zhengsen
 *时间：2015-07-03
 */
$arr=array();
//include("../../temp/config.php");
//$lujing="../files/20151019080119_192.168.117.232.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组
//检测项目
$xmArr = array("三氯甲烷"=>'496',"四氯化碳"=>'280',"二氯一溴甲烷"=>'499',"一氯二溴甲烷"=>'498',"三溴甲烷"=>'497',"百菌清"=>'212',"六氯丁二烯"=>'301','1,4-二氯苯'=>'337',"1,2-二氯苯"=>'336',"1,3,5-三氯苯"=>'341',"1,2,4-三氯苯"=>'553',"1,2,3-三氯苯"=>'340',"六氯苯"=>'206',"α-六六六"=>'628',"A-666"=>'628',"β-六六六"=>'631',"B-666"=>'631',"γ-六六六"=>'634',"δ-六六六"=>'637',"D-666"=>'637',"ρ,ρ'-DDE"=>'640',"PP-DDE"=>'640',"ρ,ρ'-DDD"=>'646',"PP-DDD"=>'646',"ρ,ρ'-DDT"=>'649',"PP-DDT"=>'649',"敌敌畏"=>'222',"乐果"=>'208',"甲基对硫磷"=>'211',"马拉硫磷"=>'203',"对硫磷"=>'209',"敌百虫"=>'227',"内吸磷"=>'228',"毒死蜱"=>"220","七七"=>'204',"七氯"=>'204',"环氧七氯"=>'226',"环环环环"=>'226',"二氯乙酸"=>'510','三氯乙酸'=>'511',"丙丙丙丙"=>'386',"丙烯酰胺"=>'386',"三氯乙醛"=>'503',"苯"=>'315',"R-666"=>'219',"OP-DDT"=>'643');//207已处理 r-666 是林丹的项目

$newsxmArr=array('环环环环'=>'环氧七氯',"七七"=>'七氯',"丙丙丙丙"=>'丙烯酰胺','林林'=>'林丹');
//分量项目
$fl_xmArr=array("343"=>"342","344"=>"342","345"=>"342","628"=>"207","631"=>"207","219"=>"207","637"=>"207","640"=>"225","643"=>"225","646"=>"225","649"=>"225");


$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>","&GT;");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'5'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'));
$quzhi_zt=1;
$zhi     =$bar_code_arr=$jcxm_arr=$over_vid_arr=array();
$cishu=$p=0;
$get_xmzt=$get_bar=0;//获得项目的状态
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
    //循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
    $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
    //把中文括号和逗号替换为英文括号和逗号
    $line = str_replace(array("，","，"),array(",",","),$line);
    $line = str_replace(array("（","（ ","）"," ）","(",")")," ",$line);
    //取出编号
    //echo $line."<br/>";
	//获取仪器编号
	if(match_yqbar($line)){
		$yqbar=match_yqbar($line);
		continue;
	}

    if(match_bar($line)){
        $bar = match_bar($line);
        if(!in_array($bar,$bar_code_arr)){
            $bar_code_arr[]=$bar;
        }
        $get_bar=1;
        continue;
    }
    if($get_bar){
        $temp_arr=explode(" ",$line);
        foreach($temp_arr as $key=>$value){
            if(stristr($value,'HZ*S')||stristr($value,'PA*S')){
                $p++;
                if($p==1){
                    $get_xmzt=1;
                }else{
                    $get_xmzt='';
                    break;
                }
            }
            if($get_xmzt){
                if(!$unit){
                    if(stristr($value,'MG/L')||stristr($value,'UG/ML')||stristr($value,'µG/ML')){
                        $unit='MG/L';
                    }
                    if(stristr($value,'µG/L')||stristr($value,'UG/L')){
                        $unit='µG/L';
                    }
                    if(stristr($value,'NG/UL')){
                        $unit='MG/L';
                    }
                }
                if((stristr($value,'.')||stristr($value,'-'))&&!$xmArr[$value]){
                    $last_sj=$value;
                }
                if(stristr($value,"警外")||stristr($value,'警警')){
                    $quzhi_zt='';
                }
                if($xmArr[$value]&&$quzhi_zt){
                    if(!$unit){
                        $unit='MG/L';
                    }
                    $xm_vid=$xmArr[$value];
                    if(!in_array($value,$jcxm_arr)){
                        $jcxm_arr[]=$value;
                    }
                    if($fl_xmArr[$xm_vid]){
                        $fl_xm_vid=$xm_vid;
                        $xm_vid=$fl_xmArr[$xm_vid];
                    }
                    if(stristr($last_sj,'-')&&!stristr($last_sj,'E-')){
                        $value=0;
                    }else{
                        $value=number_format(($last_sj*$unit_arr[$unit]['hs']),$unit_arr[$unit]['blws']);
                        $value=del0($value);
                    }
                    //判断如果是分量的项目要进行存储在json里面
                    //判断如果是分量的项目要进行存储在json里面
                    if(in_array($xm_vid,$fl_xmArr)){
                        $json_arr=array();
                        if(empty($zhi['vd26'][$bar][$xm_vid])){
                            $json_arr[$fl_xm_vid]=$value;
                        }else{
                            $json_arr=json_decode($zhi['vd26'][$bar][$xm_vid],true);
                            $json_arr[$fl_xm_vid]=$value;
                        }
                        $zhi['vd26'][$bar][$xm_vid]=JSON($json_arr);
                        $zhi['vd4'][$bar][$xm_vid]=$yqbar;
                        $zhi['vd4'][$bar][$fl_xm_vid]=$yqbar;
                        $zhi['vd27'][$bar][$fl_xm_vid]=$value;
                        $zhi['vd27'][$bar][$xm_vid]=array_sum($json_arr);
                    }else{
                        $zhi['vd27'][$bar][$xm_vid]=$value;
                        $zhi['vd4'][$bar][$xm_vid]=$yqbar;
                    }
                }
            }

        }
    }
}
//if($u['userid']=='admin'){print_rr($arr);}
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
   /* print_rr($zhi);*/
    //更新获取的数据到yqdaoru表
    foreach($zhi as $zrlie=>$data){
        yqdaoru($data,$zrlie);
    }
}
?>
