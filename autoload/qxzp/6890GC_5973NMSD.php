<?php
/*
 *功能：气质联用仪器导入页面（不支持中文）
 *作者：zhengsen
 *时间：2015-03-25
 *检测项目：环氧氯丙烷、1，1-二氯乙烯、土臭素、甲基异莰醇-2、硝基苯、二硝基苯（总量）、
 			对-二硝基苯、间-二硝基苯、邻-二硝基苯、硝基氯苯（总量）、对硝基氯苯、间硝基氯苯、
 			邻硝基氯苯、2，4-二硝基氯苯、2，4-二硝基甲苯、2，4，6-三硝基甲苯、邻苯二甲酸二丁酯、
 			邻苯二甲酸二（2-乙基己基）酯、三氯乙醛
 */
$arr=array();
$pname   = basename($lujing);
//if($u['admin']){echo $lujing.'<br/>';}
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$xmArr = array("BINGXIJING"=>"313","JIABEN"=>"316","BEN"=>"315","SILVYIXI"=>"308","SANLVYIXI"=>"307","1,2-ERLVYIXI(FAN)"=>"306","1,2-ERLVYIXI(SHUN)"=>"305","1,1-ERLVYIXI"=>"303","LVYIXI"=>"302","LIULVDINGERXI"=>"301","LVDINGERXI"=>"300","1,2-ERLVYIWAN"=>"283","ERLVJIAWAN"=>"495","YILVERXIUJIAWAN"=>"498","SANXIUJIAWAN"=>"497","1,4-ERLVBEN"=>"337","1,2-ERLVBEN"=>"336","LVBEN"=>"335","YIBINGBEN"=>"324","YIBEN"=>"323","JIAN,DUI-ERJIABEN"=>"651","LIN-ERJIABEN"=>"320","GEOSMIN"=>"329","MIB"=>"330","BENYIXI"=>"309","2,4,6-SANXIAOJIJIABEN"=>'361',"2,4-ERXIAOJIJIABEN"=>'359',"2,4-ERXIAOJILVBEN"=>'358',"DUI,LIN-XIAOJILVBEN"=>'356',"LIN,DUI-XIAOJILVBEN"=>'356',"JIAN-XIAOJILVBEN"=>'355',"XIAOJIBEN"=>'348',"XJB"=>'348',"DIETHYLHEXYL PHTHALATE"=>'376',"DIETHYLHEXYL"=>'376',"ERLVYIXIUJIAWAN"=>'499',"1,1,1-SANLVYIWAN"=>'284',"DIBUTYL PHTHALATE"=>'374',"DIBUTYL"=>'374',"DIBUTYL"=>'374',"1,2,4-SANLVBEN"=>'553',"1,2,3-SANLVBEN"=>'340',"SILVHUATAN"=>'280',"SANLVJIAWAN"=>"496","1,3,5-SANLVBEN"=>'341',"HYLBW"=>'292',"SANLVYIQUAN"=>'503',"LIN-ERXIAOJIBEN"=>'352',"DUI-ERXIAOJIBEN"=>'350',"JIAN-ERXIAOJIBEN"=>'351',"苯"=>"315","甲苯"=>"316","间,对-二甲苯"=>"651","邻-二甲苯"=>"320","苯乙烯"=>"309","乙苯"=>"324");
//分量项目
$fl_xmArr=array("356"=>'353',"355"=>'353','352'=>'349','351'=>'349','350'=>'349');
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>","&GT;");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'2'),'μG/L'=>array('hs'=>'0.001','blws'=>'7'),"NG/L"=>array('hs'=>'0.000001','blws'=>'8'));
$quzhi_zt=$unit='';
$get_yqbar='';
$zhi  =$yqbh   = $bar_code_arr=$jcxm_arr=array();
$cishu=0;
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//把中文逗号替换为英文逗号
	$line = str_replace(array("，","，"),array(",",","),$line);
	//把中文括号替换为英文括号
	$line = str_replace(array("（","（ ","）"," ）"),array("(","(",")",")"),$line);
	$temp_arr=explode(" ",$line);
	foreach($temp_arr as $key=>$value){
		//echo $value.'<br/>';
		if(stristr($value,"样品")){
			$get_xmbar='1';
		}
		if(stristr($value,"化合物")){
			$get_xmbar='';
		}
		//取出编号
		if(match_bar($value)&&$get_xmbar){
			$get_xmbar='';
			$bar = match_bar($value);
			//echo $bar."<br/>";
			if(!in_array($bar,$bar_code_arr)){
				$bar_code_arr[]=$bar;
			}
			$get_bar="1";
			//continue;
		}/*else{
			break;
		}*/

		if(stristr($value,"数据文件")){
			$get_yqbar='start';
		}
		if(stristr($value,"样品")){
			$get_yqbar='';
		}
		//获取仪器编号
	    if(match_yqbar($value)&&$get_yqbar){
	          $yqbar=match_yqbar($value);
	          continue;
	    }
		if($get_bar){
			if(stristr($value,'目标化合物')){
				$get_xmzt=1;
			}
			if(stristr($value,'定量报告')){
				$get_xmzt='';
			}
			if($xmArr[$value]&&$get_xmzt){
				$cishu=0;
				if(!in_array($value,$jcxm_arr)){
					$jcxm_arr[]=$value;
				}
				$xm_vid=$xmArr[$value];
				if($fl_xmArr[$xm_vid]){
					$fl_xm_vid=$xm_vid;//分量的项目id
					$xm_vid=$fl_xmArr[$xm_vid];//总量的项目id
				}
				$quzhi_zt = "start";
				//continue;
			}
			if($quzhi_zt=='start'&&(stristr($value,'.')||stristr($value,'低于')||stristr($value,'计算'))){
				//获取单位 此处就这样写，不然匹配不到单位
		        if(stristr($line,'MG/L')||stristr($line,'UG/ML')||stristr($line,'μG/ML')){
		           $unit='MG/L';
		        }
		        if(stristr($line,'μG/L')||stristr($line,'UG/L') || stristr($line,'μG/L')){
		            $unit='μG/L';
		        }
		        if(stristr($line,'NG/L')){
		            $unit='NG/L';
		        }
				$cishu++;
				if($cishu==2){
					if(stristr($value,'N.D.')||stristr($value,'低于')||stristr($value,'计算')){
						$value=0;
					}else{
						$value=number_format(($value*$unit_arr[$unit]['hs']),$unit_arr[$unit]['blws']);
						$value=del0($value);
					}
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
					$quzhi_zt='stop';
					$cishu=0;
				}
			}
	   }
	}
}
if(count($zhi)&&!empty($bar_code_arr)){
	//把编号和项目更新到pdf表的pdf_detail字段
	update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);
		foreach($zhi as $zrlie=>$data){
			yqdaoru($data,$zrlie);
		}	
}
?>
