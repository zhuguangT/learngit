<?php
/*
 *功能:气象色谱仪器7890B导入页面
 *作者：zhengsen
 *时间：2015-03-25
 */
header("Content-Type:text/html;charset=utf-8");
$arr=array();
//include("../../temp/config.php");
//$lujing="./files/1.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组
$xmArr = array("三氯甲烷"=>"496","三溴甲烷"=>"497","一氯二溴甲烷"=>"498","二氯一溴甲烷"=>"499","硝基苯"=>"348","2,4-二硝基甲苯"=>"359",
"2,4,6-三硝基甲苯"=>"361","2,4-二硝基氯苯"=>"358","环氧七氯"=>"226","敌百虫"=>"227","内吸磷"=>"228","苦味酸"=>"392","毒死蜱"=>"220",
"1,2,3,4-四氯苯"=>"343","1,2,3,5-四氯苯"=>"344","1,2,4,5-四氯苯"=>"345","邻-硝基氯苯"=>"356","间-硝基氯苯"=>"355","对-硝基氯苯"=>"354",
"邻-二硝基苯"=>"352","间-二硝基苯"=>"351","对-二硝基苯"=>"350","对-二甲苯"=>"318","多氯联苯(总量)"=>"364","四氯化碳"=>"280","邻-硝基甲苯"=>"362",
"间-硝基甲苯"=>"621","对-硝基甲苯"=>"363","2,6-二硝基甲苯"=>"623","3,4-二硝基甲苯"=>"625","六氯苯"=>"206","乐果"=>"208","对硫磷"=>"209",
"甲基对硫磷"=>"211","敌敌畏"=>"222","三卤甲烷"=>"494","三氯乙醛"=>"503","七氯"=>"204","马拉硫磷"=>"203","三氯乙烯"=>"307",
"四氯乙烯"=>"308","邻苯二甲酸二丁酯"=>"374","松节油"=>"408","环氧氯丙烷"=>"292","邻苯二甲酸二(2-乙基己基)酯"=>"376","丙烯酰胺"=>"386","丙丙丙丙"=>'386',"2，4-滴"=>"247",
"溴氰菊酯"=>"224","二氯乙酸"=>"510","三氯乙酸"=>"511","百菌清"=>"212","灭草松"=>"210","α-666"=>"628","β-666"=>"631","γ-666"=>"219","δ-666"=>"637",
"PP'-DDE"=>"640","OP'-DDT"=>"643","PP'-DDD"=>"646","PP'-DDT"=>"649","黄磷"=>"199");
//分量项目
/*$fl_xmArr=array("343"=>"342","344"=>"342","345"=>"342","628"=>"207","631"=>"207","219"=>"207","637"=>"207","640"=>"225","643"=>"225","646"=>"225","649"=>"225","356"=>"353","355"=>"353","354"=>"353","350"=>"349","351"=>"349","352"=>"349","496"=>"494","497"=>"494","498"=>"494","499"=>"494");*/
$newsxmArr=array("丙丙丙丙"=>'丙烯酰胺');
$fl_xmArr=array();
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>","&GT;");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'10'),'µG/L'=>array('hs'=>'0.001','blws'=>'10'));
$quzhi_zt='';
$zhi  =$bar_code_arr =$jcxm_arr=$yqbh=array();
$cishu=0;
$get_xmzt=$get_bar=0;//获得项目的状态
$get_yqbh=1;//获取仪器编号状态
$json_arr=array();
for($i=0;$i<count($arr);$i++){
    //循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
    $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
    //$line = str_replace(array("（","（ ","）"," ）","，","，"),array("(","(",")",")",",",","),$line);
    $temp_arr=explode(" ",$line);
    foreach ($temp_arr as $key => $value){
        //echo $value."<br />";
        //取出编号
        if(match_bar($value)){
            $bar = match_bar($value);
            if(isset($zhi[$bar])){
                $bar = $bar."P";
            }
            //echo $bar."<br />";
            if(!in_array($bar,$bar_code_arr)){
                $bar_code_arr[]=$bar; //样品编号
            }
            $get_bar=1;
            continue;
        }

        //获取仪器编号
        if($get_yqbh){
            if(match_yqbar($value)){
                $yqbh2=match_yqbar($value);
                $get_yqbh='';
                continue;
            }
        }
        if($get_bar){
            //获取取值状态
            if(stristr($value,'HZ*S')||stristr($value,'PA*S')){
                    $get_xmzt=1;
                }
            //获取检测的项目
            if($get_xmzt){
                if(!$unit){
                    if(stristr($value,'MG/L')){
                        $unit='MG/L';
                    }
                    if(stristr($value,'µG/L')||stristr($value,'UG/L')){
                        $unit='µG/L';
                    }
                    if(stristr($value,'NG/UL')){
                        $unit='MG/L';
                    }
                }
                if(stristr($value,'.')||stristr($value,'-')&&!$xmArr[$value]){
                    $last_sj=$value;
                    //echo $last_sj."<br />";
                }
                if($xmArr[$value]&&!isset($zhi[$bar][$xmArr[$value]])){
                    if(!in_array($value,$jcxm_arr)){
                        $jcxm_arr[]=$value;
                    }
                    if(!$unit){
                        $unit='MG/L';
                    }
                    $xm_vid=$xmArr[$value];
                    if($fl_xmArr[$xm_vid]){
                        $fl_xm_vid=$xm_vid;
                        $xm_vid=$fl_xmArr[$xm_vid];
                    }
                    if(stristr($last_sj,'-')&&!stristr($last_sj,'E-')){
                        $value=0;
                    }else{
                        $value=number_format(($last_sj*$unit_arr[$unit]['hs']),$unit_arr[$unit]['blws']);
                        //$value=del0($value);
                    }
                    //判断如果是分量的项目要进行存储在json里面
                    if(in_array($xm_vid,$fl_xmArr)){
                        if(empty($zhi['vd27'][$bar][$xm_vid])){
                            $json_arr[$fl_xm_vid]=$value;
                        }else{
                            $json_arr=json_decode($zhi['vd27'][$bar][$xm_vid],true);
                            $json_arr[$fl_xm_vid]=$value;
                        }
                        $zhi['vd27'][$bar][$xm_vid]=array_sum($json_arr);
                        $zhi['vd4'][$bar][$xm_vid]=$yqbh2;
                    }elseif($xm_vid==503){
                        if(stristr($bar,'加碱')){
                            if(strlen($bar)==18){
                                $bar2=substr($bar,0,-6);
                                $zhi['vd26'][$bar2][$xm_vid]=$value;
                                $zhi['vd5'][$bar2][$xm_vid]=$yqbh2; 
                                $zhi['vd27'][$bar2."J"][$xm_vid]=$value;
                                $zhi['vd4'][$bar2."J"][$xm_vid]=$yqbh2; 
                            }else{
                              $bar2=substr($bar,0,-6);
                              $zhi['vd26'][$bar2][$xm_vid]=$value;
                              $zhi['vd5'][$bar2][$xm_vid]=$yqbh2;  
                            } 
                        }elseif(stristr($bar,"J")){
                            $zhi['vd26'][$bar][$xm_vid]=$value;
                            $zhi['vd5'][$bar][$xm_vid]=$yqbh2;
                        }elseif(strlen($bar<"5")){
                            $zhi['vd27'][$bar][$xm_vid]="/";
                            $zhi['vd4'][$bar][$xm_vid]="/";
                            $zhi['vd26'][$bar][$xm_vid]=$value;
                            $zhi['vd5'][$bar][$xm_vid]=$yqbh2; 
                        }else{
                            $zhi['vd27'][$bar][$xm_vid]=$value;
                            $zhi['vd4'][$bar][$xm_vid]=$yqbh2;
                        }
                    }else{
                        $zhi['vd27'][$bar][$xm_vid]=$value;
                        $zhi['vd4'][$bar][$xm_vid]=$yqbh2;
                    }
                }
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
    update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr2);
    foreach($zhi as $zrlie=>$data){
        yqdaoru($data,$zrlie);
    }  
}
?>

