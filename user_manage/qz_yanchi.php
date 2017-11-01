<?php //校核，复核延迟签字统计表页面
include("../temp/config.php");
$fzx_id=$_SESSION['u']['fzx_id'];

//#########导航
$daohang = array(
		array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
		array('icon'=>'','html'=>'签字延迟统计表','href'=>"user_manage/qz_yanchi.php?year={$_GET['year']}&month={$_GET['month']}&count_type={$_GET['count_type']}&qixian={$_GET['qixian']}&zhoumoZt={$_GET['zhoumoZt']}"),
);
$trade_global['daohang'] = $daohang;
if(empty($_GET['count_type']) || $_GET['count_type']=='huayan'){
	$shenhe_tiaojian	= " display:none;";
	$_GET['count_type']	= 'huayan';
	$huayan_checked		= ' selected ';
	$shenhe_checked		= '';
}else{
	$shenhe_tiaojian	= '';
	$huayan_checked		= '';
	$shenhe_checked		= ' selected ';
}
//规定天数的获取和存储
$moren_qixian	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `fzx_id`='$fzx_id' AND `module_name`='yanchi_qixian' LIMIT 1");
if((!empty($_GET['qixian']) && $_GET['qixian'] != $moren_qixian['module_value1']) || $_GET['qixian']==='0'){
	if(!empty($moren_qixian['id'])){
		$DB->query("UPDATE `n_set` SET `module_value1`='{$_GET['qixian']}' WHERE `id`='{$moren_qixian['id']}'");
	}else{
		$DB->query("INSERT INTO `n_set` SET `fzx_id`='$fzx_id',`module_value1`='{$_GET['qixian']}',`module_name`='yanchi_qixian'");
	}
}else{
	if(!empty($moren_qixian['module_value1']) || $moren_qixian['module_value1']==='0'){
		$_GET['qixian']	= $moren_qixian['module_value1'];
	}else{
		$_GET['qixian']	= "2";
	}
}
$userArr = array();
if($gx_qixian=='')$gx_qixian    = 2;//个性文件中的配置 如果没有配置默认2
if($gx_zhoumoZt=='')$gx_zhoumoZt= 'yes';//个性文件中的配置 如果没有配置默认 排除周六日
$year    = $_GET['year']==''?date("Y"):$_GET['year'];
if(strlen($year)>4||(int)$year==0)$year = date("Y");
$month   = $_GET['month']==''?date("m"):$_GET['month'];
$qixian  = $_GET['qixian']==''?$gx_qixian:$_GET['qixian'];
$zhoumoZt= $_GET['zhoumoZt']==''?$gx_zhoumoZt:$_GET['zhoumoZt'];
if($zhoumoZt=='yes')$checked='checked';
else $checked = '';
$optionY = $monthCz = $firstMonth = "";
$optionM = "<option value='all'>全部</option>";
//根据是统计化验签字还是校、复核来区分 sql的where条件
$sql_where	= '';
if($_GET['count_type']=='huayan'){
	$sql_where	.= " AND over in ('已完成','已校核','已复核','已审核') ";// AND ((`sign_date_01`!='' AND `sign_date_01` IS NOT NULL) OR (`sign_date_02`!='' AND `sign_date_02` IS NOT NULL))
}else{
	$sql_where	.= " and over in ('已校核','已复核','已审核') ";
}
//算出系统中所有的年份
$queYear = $DB->query("select distinct EXTRACT(year from create_date) as Cyear from `assay_pay` where fzx_id='$fzx_id' $sql_where order by create_date desc");
while($rsYear=$DB->fetch_assoc($queYear)){
		if($rsYear['Cyear']==$year)$selected="selected";
	else $selected='';
	$optionY .= "<option value=\"".$rsYear['Cyear']."\" ".$selected.">".$rsYear['Cyear']."</option>";
}
//算出系统中这一年中已有的月份
$queMonth = $DB->query("select distinct EXTRACT(month from create_date) as Cmonth from `assay_pay` where fzx_id='$fzx_id' $sql_where and year(create_date)='".$year."' order by create_date desc");
while($rsMonth=$DB->fetch_assoc($queMonth)){
	//if($firstMonth=="")$firstMonth=$rsMonth['Cmonth'];
	if($rsMonth['Cmonth']==$month){
		$selected="selected";
		$monthCz = "存在";//系统中存在这一年中这个月的签字记录
	}
	else $selected='';
	$optionM .= "<option value=\"".$rsMonth['Cmonth']."\" ".$selected.">".$rsMonth['Cmonth']."</option>";
}
$whereM = "";
if($monthCz!="存在")$month='all';//$firstMonth;//防止从其他年份换到这一年时，恰巧这一年没有这个月的数据的情况
if($month=='all')$whereM = "";
else $whereM = "and month(ap.create_date)='".$month."'";
//取出所有已经校核及以后状态的化验单
if($_GET['count_type']=='huayan'){
	$sqlHyd	= "SELECT ap.id,ap.sign_01,ap.sign_012,ap.sign_date_01,ap.sign_date_012,ap.sign_date_02,cy.jcwc_date FROM `assay_pay` as ap LEFT JOIN `cy` ON ap.cyd_id=cy.id WHERE ap.fzx_id='$fzx_id' and year(ap.create_date)='".$year."' ".$whereM." $sql_where";
}else{
	$sqlHyd	= "select id,sign_01,sign_02,sign_03,sign_04,sign_date_01,sign_date_02,sign_date_03,sign_date_04 from `assay_pay` AS ap where fzx_id='$fzx_id' and year(create_date)='".$year."' ".$whereM." $sql_where";
}
$queHyd  = $DB->query($sqlHyd);
while($rsHyd=$DB->fetch_assoc($queHyd)){
	if($_GET['count_type']=='huayan'){
		$hyd_wc_date	= $hyd_user	= $hyd_wc_date2	= $hyd_user2	= '';
		if(!empty($rsHyd['sign_date_01'])){
			$hyd_wc_date	= $rsHyd['sign_date_01'];
			$hyd_user		= $rsHyd['sign_01'];
		}
		//辅测化验员
		if(!empty($rsHyd['sign_date_012'])){
			$hyd_wc_date2	= $rsHyd['sign_date_012'];
			$hyd_user2		= $rsHyd['sign_012'];
		}
		if(empty($rsHyd['sign_date_01']) && empty($rsHyd['sign_date_012'])){
			continue;
		}
		if(!empty($hyd_user)){
			if(@!array_key_exists($hyd_user,$userArr)){
				$userArr[$hyd_user]['zongshu'] = 0;
				$userArr[$hyd_user]['yanchishu'] = 0;
			}
			$userArr[$hyd_user]['zongshu']++;
			if($userArr[$hyd_user]['all_tid']!=''){
				$userArr[$hyd_user]['all_tid'] = $userArr[$hyd_user]['all_tid'].",".$rsHyd['id'];
			}else{
				$userArr[$hyd_user]['all_tid']	= $rsHyd['id'];
			}
			if($rsHyd['jcwc_date'] < $hyd_wc_date){
				$userArr[$hyd_user]['yanchishu']++;
				if($userArr[$hyd_user]['yanchi_tid']!=''){
					$userArr[$hyd_user]['yanchi_tid'] = $userArr[$hyd_user]['yanchi_tid'].",".$rsHyd['id'];
				}else{
					$userArr[$hyd_user]['yanchi_tid']	= $rsHyd['id'];
				}
			}
		}
		//同时统计辅测化验员
		if(!empty($hyd_user2)){
			if(@!array_key_exists($hyd_user2,$userArr)){
				$userArr[$hyd_user2]['zongshu'] = 0;
				$userArr[$hyd_user2]['yanchishu'] = 0;
			}
			$userArr[$hyd_user2]['zongshu']++;
			if($userArr[$hyd_user2]['all_tid']!=''){
				$userArr[$hyd_user2]['all_tid'] = $userArr[$hyd_user2]['all_tid'].",".$rsHyd['id'];
			}else{
				$userArr[$hyd_user2]['all_tid']	= $rsHyd['id'];
			}
			if($rsHyd['jcwc_date'] < $hyd_wc_date){
				$userArr[$hyd_user2]['yanchishu']++;
				if($userArr[$hyd_user2]['yanchi_tid']!=''){
					$userArr[$hyd_user2]['yanchi_tid'] = $userArr[$hyd_user2]['yanchi_tid'].",".$rsHyd['id'];
				}else{
					$userArr[$hyd_user2]['yanchi_tid']	= $rsHyd['id'];
				}
			}
		}
	}else{
		if($rsHyd['sign_02']==''||$rsHyd['sign_01']==''||$rsHyd['sign_date_01']==''||$rsHyd['sign_date_02']=='')continue;
		//统计这个月所有校核人
		if(!count($userArr)||!array_key_exists($rsHyd['sign_02'],$userArr)){
			$userArr[$rsHyd['sign_02']]['zongshu'] = 0;
			$userArr[$rsHyd['sign_02']]['yanchishu'] = 0;
		}
		//统计这个月所有复核人
		if($rsHyd['sign_03']!=''&&!array_key_exists($rsHyd['sign_03'],$userArr)){
			$userArr[$rsHyd['sign_03']]['zongshu'] = 0;
			$userArr[$rsHyd['sign_03']]['yanchishu'] = 0;
		}
		if($userArr[$rsHyd['sign_02']]['jiaohe']!='')$userArr[$rsHyd['sign_02']]['jiaohe'] = $userArr[$rsHyd['sign_02']]['jiaohe'].",".$rsHyd['id'];
		else $userArr[$rsHyd['sign_02']]['jiaohe'] = $rsHyd['id'];
		//计算延迟校核数
		//if(@in_array($rsHyd['sign_01'],$userArr[$rsHyd['sign_02']]['jiaohe'])){
			$userArr[$rsHyd['sign_02']]['zongshu']++;
			$dayCha    = (strtotime($rsHyd['sign_date_02']."24:00:00")-strtotime($rsHyd['sign_date_01']."24:00:00"))/3600/24;//两个签字之间差的天数
			//是否去除周六日的判断
			if($zhoumoZt=='yes')$zhouMoshu = get_weekend_days($rsHyd['sign_date_02'],$rsHyd['sign_date_01']);//算出这些天数中有几天是周末
			else $zhouMoshu = 0;
			if(($dayCha-$zhouMoshu)>$qixian){
				$userArr[$rsHyd['sign_02']]['yanchishu']++;//看看是否延迟签字
				if($userArr[$rsHyd['sign_02']]['ycJhTid']!='')$userArr[$rsHyd['sign_02']]['ycJhTid']=$userArr[$rsHyd['sign_02']]['ycJhTid'].",".$rsHyd['id'];
				else $userArr[$rsHyd['sign_02']]['ycJhTid']=$rsHyd['id'];
			}
		//}
		//计算延迟复核数
		if($rsHyd['sign_03']!=''&&$rsHyd['sign_date_03']!=''){//&&@in_array($rsHyd['sign_02'],$userArr[$rsHyd['sign_03']]['fuhe'])){
			if($userArr[$rsHyd['sign_03']]['fuhe']!='')$userArr[$rsHyd['sign_03']]['fuhe'] = $userArr[$rsHyd['sign_03']]['fuhe'].",".$rsHyd['id'];
			else $userArr[$rsHyd['sign_03']]['fuhe'] = $rsHyd['id'];
			$userArr[$rsHyd['sign_03']]['zongshu']++;
			$dayCha2    = (strtotime($rsHyd['sign_date_03']."24:00:00")-strtotime($rsHyd['sign_date_02']."24:00:00"))/3600/24;
			if($zhoumoZt=='yes')$zhouMoshu2 = get_weekend_days($rsHyd['sign_date_03'],$rsHyd['sign_date_02']);
			else $zhouMoshu2 = 0;
			if(($dayCha2-$zhouMoshu2)>$qixian){
				$userArr[$rsHyd['sign_03']]['yanchishu']++;
				if($userArr[$rsHyd['sign_03']]['ycFhTid']!='')$userArr[$rsHyd['sign_03']]['ycFhTid']=$userArr[$rsHyd['sign_03']]['ycFhTid'].",".$rsHyd['id'];
				else $userArr[$rsHyd['sign_03']]['ycFhTid']=$rsHyd['id'];
			}
		}
	}
}
//每个人的签字及延迟签字的信息的显示
$lines = '';
$yanchilvArr = $linesArr = array();
foreach($userArr as $key=>$val){
	if($val['yanchishu']==0)$yanChiLv = "0";
	else $yanChiLv = number_format($val['yanchishu']/$val['zongshu']*100,2);
	$yanchilvArr[$key] = $yanChiLv;
	if($yanChiLv!=0)$baifenbi = "%";
	else $baifenbi = "";
	//这里get传值太大，改为seesion传值
	if(!empty($val['jiaohe'])){
			$_SESSION[$key]['jhtid']        = $val['jiaohe'];
			$val['jiaohe']  = '1';
	}
	if(!empty($val['fuhe'])){
			$_SESSION[$key]['fhTid']        = $val['fuhe'];
			$val['fuhe']    = '1';
	}
	if(!empty($val['ycJhTid'])){
			$_SESSION[$key]['ycJhTid']      = $val['ycJhTid'];
			$val['ycJhTid'] = '1';
	}
	if(!empty($val['ycFhTid'])){
			$_SESSION[$key]['ycFhTid']      = $val['ycFhTid'];
			$val['ycFhTid'] = '1';
	}
	//化验签字延迟统计
	if(!empty($val['all_tid'])){
		$_SESSION[$key]['huayan_tid']      = $val['all_tid'];
		$val['all_tid'] = '1';
	}
	if(!empty($val['yanchi_tid'])){
		$_SESSION[$key]['yanchi_tid']      = $val['yanchi_tid'];
		$val['yanchi_tid'] = '1';
	}
	if($val['yanchishu']!=0){
		//$hanshu = " onclick=\"window.open('qzHydList.php?user=$key&ycJhTid=".$val['ycJhTid']."&ycFhTid=".$val['ycFhTid']."&year=".$year."&month=".$month."');\"";
		$hanshu = " onclick=\"window.location.href='qzHydList.php?user=$key&huayan_yanchi_tid={$val['yanchi_tid']}&ycJhTid=".$val['ycJhTid']."&ycFhTid=".$val['ycFhTid']."&year=".$year."&month=".$month."';\"";
		$classN = "class='tdbian'";
	}
	else $hanshu = $classN = '';
	//延迟签字页面 行信息  //将每一行结果存到数组中
	//if($val['zongshu']!=0)$linesArr[$key]="<tr class='bian'><td>".$key."</td><td class='tdbian' title='点击查看".$key."这个月所有签字的化验单'  onclick=\"window.open('qzHydList.php?user=$key&jhTid=".$val['jiaohe']."&fhTid=".$val['fuhe']."&ycJhTid=".$val['ycJhTid']."&ycFhTid=".$val['ycFhTid']."&year=".$year."&month=".$month."');\">".$val['zongshu']."</td><td title='点击查看".$key."这个月延迟签字的化验单' ".$classN.$hanshu.">".$val['yanchishu']."</td><td>".$yanChiLv.$baifenbi."</td><td class='tdbian' onclick=\"newchart('".$key."','".$year."','".$qixian."','".$zhoumoZt."');\">年趋势图</td></tr>";
	if($val['zongshu']!=0)$linesArr[$key]="<tr class='bian'><td>".$key."</td><td class='tdbian' title='点击查看".$key."这个月所有签字的化验单'  onclick=\"window.location.href='qzHydList.php?user=$key&huayan_tid={$val['all_tid']}&huayan_yanchi_tid={$val['yanchi_tid']}&jhTid=".$val['jiaohe']."&fhTid=".$val['fuhe']."&ycJhTid=".$val['ycJhTid']."&ycFhTid=".$val['ycFhTid']."&year=".$year."&month=".$month."';\">".$val['zongshu']."</td><td title='点击查看".$key."这个月延迟签字的化验单' ".$classN.$hanshu.">".$val['yanchishu']."</td><td>".$yanChiLv.$baifenbi."</td><td class='tdbian' onclick=\"newchart('".$key."','".$year."','".$qixian."','".$zhoumoZt."','".$_GET['count_type']."');\">年趋势图</td></tr>";
}
@array_multisort($yanchilvArr,SORT_DESC,$linesArr);//根据延迟率排序
$lines = implode('',$linesArr);//将数组中的每一行结果转换成字符串(排序后)
disp('user_manager/qz_yanchi_1');
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
?>
