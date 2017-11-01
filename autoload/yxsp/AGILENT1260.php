<?php
/*
 *功能:安捷伦1260液相色谱仪器导入页面
 *作者：tangyongsheng
 *时间：2016-10-09
 */
/*error_reporting(E_ALL);
ini_set('display_errors', '1'); */
header("Content-Type:text/html;charset=utf-8"); 
$arr=array();
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组
$xmArr = array("甲萘威"=>"229","微微微微微"=>'410',"微囊藻毒素-LR"=>"410","2,4,6-三三酚"=>'523',"2,4,6-三氯酚"=>"523","五氯酚"=>"205","五三酚"=>'205',"2,4-二氯酚"=>'559',"2,4-二氯苯酚"=>"559","2-氯酚"=>"518","呋喃丹"=>"218","草甘膦"=>"221","草草草"=>'221',"莠去津"=>"223","苯苯(A)芘"=>'394',"苯并(A)芘"=>"394","萘"=>"404","荧荧"=>'399',"荧蒽"=>"399","苯苯(B)荧荧"=>'395',"苯并(B)荧蒽"=>"395","苯并(K)荧蒽"=>"396","苯苯(K)荧荧"=>'396',"苯并(GHI)苝"=>"398","苯苯(G.H.I)苝"=>'398',"茚苯(1.2.3-CD)芘"=>'397',"茚并(1,2,3-CD)芘"=>"397","2,4-滴"=>'247',"灭草松"=>'210',"溴氰菊酯"=>'224',"呋呋呋"=>"218","甲甲甲"=>'229',"莠莠莠"=>'223',"溴溴溴溴"=>'224',"灭灭灭"=>'210');
$newsxmArr=array("苯苯(A)芘"=>'苯并(A)芘',"荧荧"=>"荧蒽","苯苯(B)荧荧"=>'苯并(B)荧蒽',"苯苯(K)荧荧"=>"苯并(K)荧蒽","微微微微微"=>'微囊藻毒素-LR',"草草草"=>'草甘膦',"2,4,6-三三酚"=>'2,4,6-三氯酚',"五三酚"=>'五氯酚',"2,4-二氯酚"=>'2,4-二氯苯酚',"呋呋呋"=>"呋喃丹","甲甲甲"=>'甲萘威',"莠莠莠"=>'莠去津',"溴溴溴溴"=>'溴氰菊酯',"灭灭灭"=>'灭草松');
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>","&GT;");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
//$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'5'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'));
$quzhi_zt='';
$zhi= $bar_code_arr=$jcxm_arr=array();
$cishu=$p=0;
$get_xmzt=$get_bar=0;//获得项目的状态
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//把中文括号和逗号替换为英文括号和逗号
	$line = str_replace(array("（","（ ","）"," ）","，","，","-","－"),array("(","(",")",")",",",",","-","-"),$line);
	//取出编号
	if(match_bar($line)){
	    $bar = match_bar($line);
		if(!in_array($bar,$bar_code_arr)){
			$bar_code_arr[]=$bar;
		}
		$get_bar=1;
		continue;
	}
    //获取仪器编号
	if(match_yqbar($line)){
		$yqbar=match_yqbar($line);
		continue;
	}
	if($get_bar){
		$temp_arr=explode(" ",$line);
		foreach($temp_arr as $key=>$value){
			//if($u['admin']){echo $value.'<br/>';}
			if(stristr($value,'MAU*S')||stristr($value,'LU*S')){
				$p++;
				if($p==1){
					$get_xmzt=1;
				}else{
					$get_xmzt='';
					break;
				}
			}
			if($get_xmzt){
				if(!$xmArr[$value]&&(stristr($value,'.')||stristr($value,'-'))){
					$last_sj=$value;
				}
				if($xmArr[$value]){
					$xm_vid=$xmArr[$value];
					if(!in_array($value,$jcxm_arr)){
						$jcxm_arr[]=$value;
					}
					if(stristr($last_sj,'E-')){
						$value=NumToStr($last_sj);
					}
					elseif(stristr($last_sj,'-')){
						$value=0;
					}else{
						$value=$last_sj;
					}
					$zhi['vd27'][$bar][$xm_vid]=$value;
					$zhi['vd4'][$bar][$xm_vid]=$yqbar;
				}
			}
		}
	}
}
if(count($zhi)){ 
	$jcxm_arr2=array();
    foreach ($jcxm_arr as $key2 => $value2) {
         //替换正常的字符串
        if(!empty($newsxmArr[$value2])){
            $jcxm_arr2[]=$newsxmArr[$value2];
        }else{
             $jcxm_arr2[]=$value;
        }
    }
	//把编号和项目更新到pdf表的pdf_detail字段
	update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr2);

	foreach($zhi as $zrlie=>$data){
		yqdaoru($data,$zrlie);
	}
}
?>
