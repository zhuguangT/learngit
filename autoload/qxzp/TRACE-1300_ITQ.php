<?php
/*
 *功能：气质联用仪器导入页面
 *作者：zhengsen
 *时间：2014-11-18
 */
 $arr=array();
//include("../../temp/config.php");
//$lujing="../files/zzqxzp4.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$xmArr = array("CHLOROFORM"=>"496","CARBONTETRACHLORIDE"=>"280","BROMOFORM"=>"497","DICHLOROMETHANE"=>"495","1,2-DICHLOROETHANE"=>"283","VINYLCHLORIDE"=>"302","1,1-DICHLOROETHYLENE"=>"303","1,2-DICHLOROETHYLENE"=>"304","TETRACHLOROETHYLENE"=>"308","CHLOROPRENE"=>"300","HEXACHLOROBUTADIENE"=>"301","STYRENE"=>"309","BENZENE"=>"315","TOLUENE"=>"316","ETHYLBENZENE"=>"323","CHLOROETHYLENE"=>"302","TRICHLOROETHYLENE"=>"307","P-XYLENE"=>"317","M-XYLENE"=>"317","O-XYLENE"=>"317","ISOPROPYLBENZENE"=>"324","CHLOROBENZENE"=>"335","1,2-DICHLOROBENZENE"=>"336","1,4-DICHLOROBENZENE"=>"337","TRICHLOROBENZENE"=>"339","TETRACHLOROBENZENE"=>'342',"HEXACHLOROBENZENE"=>"206","NITROBENZENE"=>"348","DINITROBENZENE"=>"558","2,4-DINITROTOLUENE"=>"359","2,4,6-TRINITROTOLUENE"=>"361","NITROCHLOROBENZENE"=>"353","2,4-DINITROPHENOL"=>"358","2,4-DICHLOROPHENOL"=>"559","2,4,6-TRICHLOROPHENOL"=>'561',"PENTACHLOROPHENOL"=>"205","ANILINE"=>"380","BIS(2-ETHYLHEXYL)PHTHALATE"=>"376","DDT"=>"225","PARATHION"=>'209',"METHYLPARATHION"=>'211',"MALATHION"=>"203","DIMETHOATE"=>"208","DDV"=>"222","DEMETON"=>'228',"DELTAMETHRIN"=>"224","CARBARYL"=>"229","666"=>"207");

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>","&GT;");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
//$unit=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'));
$quzhi_zt='';
$zhi     = array();
$cishu=0;
$get_xmzt=0;//获得项目的状态
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//取出编号
	//echo $line."<br/>";
	//数据和曲线一起打印时曲线也有项目名称，避免获取错误匹配到report时就证明是另外一页了就停止获取
	if(stristr($line,"report")){
		$get_xmzt='';
	}
	if(match_bar($line)){
		$bar = match_bar($line);
		if(isset($zhi[$bar])){
			$bar = $bar."P";
		}
		$get_xmzt='1';
		continue;
	}
	if($get_xmzt){
		$temp_arr=explode(" ",$line);
		foreach($temp_arr as $key=>$value){
			if($xmArr[$value]){
				$vid=$xmArr[$value];
				$get_xmzt='0';
				$quzhi_zt = "start";
				break;
			}
		}
	}
	//取出项目相应数值
	if($quzhi_zt=='start'){
		if($line!=''){
			$cishu++;
		}

		if($cishu==5){
			if(stristr($line,'N/A')){
				$zhi[$bar][$vid]=0;
			}else{
				$zhi[$bar][$vid]=$line;
			}
			$get_xmzt='1';
			$cishu=0;
			$quzhi_zt='';
		}
	}
}
//print_rr($zhi);exit();
//if($u['userid']=='admin'){print_rr($zhi);}
if(count($zhi)){
	yqdaoru($zhi);
}
?>
