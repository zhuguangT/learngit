<?php
//签字延迟趋势图 数据传递页面
include("../temp/config.php");
$name    = $_GET['name'];
$year    = $_GET['year'];
$qixian  = $_GET['qixian']==''?2:$_GET['qixian'];
$zhoumoZt= $_GET['zhoumoZt']==''?'yes':$_GET['zhoumoZt'];
$monthArr= array();
//取出这个人今年的所有签字记录
if($_GET['count_type'] == 'huayan'){
	$queQz	= $DB->query("SELECT ap.id,month(ap.create_date) as month,ap.sign_01,ap.sign_012,ap.sign_date_01,ap.sign_date_02,cy.jcwc_date FROM `assay_pay` as ap LEFT JOIN `cy` ON ap.cyd_id=cy.id WHERE ap.fzx_id='$fzx_id' and year(ap.create_date)='".$year."'  and (sign_01='".$name."' or sign_012='".$name."') and over in ('已完成','已校核','已复核','已审核') order by ap.create_date");
	while($rsQz	= $DB->fetch_assoc($queQz)){
		$hyd_wc_date	= '';
		if(!empty($rsQz['sign_date_01']) && $rsQz['sign_01']==$name){
			$hyd_wc_date	= $rsQz['sign_date_01'];
			$hyd_user		= $rsQz['sign_01'];
		}else if(!empty($rsQz['sign_date_012']) && $rsQz['sign_012']==$name){
			$hyd_wc_date	= $rsQz['sign_date_012'];
			$hyd_user		= $rsQz['sign_012'];
		}else{
			continue;
		}
		if(!count($monthArr)&&!array_key_exists($rsQz['month'],$monthArr)){
			$monthArr[$rsQz['month']]['zongshu']=0;
			$monthArr[$rsQz['month']]['yanchi'] =0;
		}
		$monthArr[$rsQz['month']]['zongshu']++;
		if($rsQz['jcwc_date'] < $hyd_wc_date){
			$monthArr[$rsQz['month']]['yanchi']++;
		}
	}
}else{
	$queQz= $DB->query("select id,month(create_date) as month,sign_01,sign_02,sign_03,sign_04,sign_date_01,sign_date_02,sign_date_03,sign_date_04 from `assay_pay` where year(create_date)='".$year."' and (sign_02='".$name."' or sign_03='".$name."') and over in ('已校核','已复核','已审核') order by create_date");
	while($rsQz=$DB->fetch_assoc($queQz)){
		//$rsQz['month'] = "\"".$rsQz['month']."月\"";
		if(!count($monthArr)&&!array_key_exists($rsQz['month'],$monthArr)){
			$monthArr[$rsQz['month']]['zongshu']=0;
			$monthArr[$rsQz['month']]['yanchi'] =0;
		}
		if($rsQz['sign_02']==$name){
			$starDate = $rsQz['sign_date_01'];
			$endDate  = $rsQz['sign_date_02'];
		}
		else{
			$starDate = $rsQz['sign_date_02'];
			$endDate  = $rsQz['sign_date_03'];
		}
		$monthArr[$rsQz['month']]['zongshu']++;
		$dayCha    = (strtotime($endDate."24:00:00")-strtotime($starDate."24:00:00"))/3600/24;//两个签字之间差的天数
		//是否去除周六日的判断
		if($zhoumoZt=='yes')$zhouMoshu = get_weekend_days($endDate,$starDate);//算出这些天数中有几天是周末
		else $zhouMoshu = 0;
		if(($dayCha-$zhouMoshu)>$qixian)$monthArr[$rsQz['month']]['yanchi']++;//看看是否延迟签字
	}
}
//ksort($monthArr);
$tstr = $vstr  = '';
$cha  = $maxy  = 0;
foreach($monthArr as $key=>$val){
	$tstr .= "\"".$key."月\",";
	if($val['yanchi']==0)$yanChiLv = "0";
	else $yanChiLv = number_format($val['yanchi']/$val['zongshu']*100,2);
	$vstr .= $yanChiLv.",";
	if($cha>$yanChiLv)$cha  = $yanChiLv;
	//if($maxy<$yanChiLv)$maxy=$yanChiLv;
}
$tstr  = substr($tstr,0,-1);
$vstr  = substr($vstr,0,-1);
$maxy  = 100;
$miny  = 0;
$w     = 0;
if($cha==0){//防止卡死
	$cha=1;
}
/*
| Author:  http://blog.csdn.net/yangyu112654374/article/details/4797093
| @param   char|int  $start_date 一个有效的日期格式，例如：20091016，2009-10-16
| @param   char|int  $end_date  同上
| @return  给定日期之间的周末天数
*/
function get_weekend_days($start_date,$end_date){
        if (strtotime($start_date) > strtotime($end_date)) list($start_date, $end_date) = array($end_date, $start_date);
	$start_reduce = $end_add = 0;
	$start_N = date('N',strtotime($start_date));
	$start_reduce = ($start_N == 7) ? 1 : 0;
	$end_N = date('N',strtotime($end_date));
	in_array($end_N,array(6,7)) && $end_add = ($end_N == 7) ? 2 : 1;
	$days = abs(strtotime($end_date) - strtotime($start_date))/86400 + 1;
	return floor(($days + $start_N - 1 - $end_N) / 7) * 2 - $start_reduce + $end_add;
}
//$tstr是横轴,$vstr趋势线,$w竖轴保留几位小数,$miny:$maxy:$cha分别为竖轴的最大最小(原点)出原点最小值
$title = $name.$year."年 签字延迟率 趋势图";
?>
{
"type":"line",
"title":{
    "text":"<?php echo $title; ?>"
},
"scale-x":{
	"values":[<?php echo $tstr; ?>],
	"zooming":1,
},
"scale-y":{
	"values":"<?php echo "$miny:$maxy:$cha"; ?>",
	"decimals":<?php echo $w;?>,
	"label":{
		"text":"延迟率(%)"
	}
},
"plot":{
	"tooltip-text":"延迟率:%v  "
},
"series":[
        {
		"values":[<?php echo $vstr; ?>],
	}
]
}
