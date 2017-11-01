<?php
/*
*功能：报告统一获取数据的文件
*作者：feng
*时间：2016-03-08
 */
include_once '../temp/config.php';
include_once INC_DIR . "cy_func.php";//get_water_type_max函数
include_once '../baogao/bg_func.php';//is_chaobiao函数
if($u['userid']==''){
	nologin();
}
$fzx_id	= $u['fzx_id'];
##########传入参数
$begin_date	= $begin_date_t;//开始时间
$end_date	= $end_date_t;//结束时间
$cids_arr	= $cids_arr_t;//获取哪些站点的数据（cy表和cy_rec表的id）
$vids_arr	= $vids_arr_t;//项目
$export_element	= $export_element_t;//需要输出的结果，多个数组（结果+是否超标，检测次数，站点的分类,是几类水等）
$jcbz_bh_id	= '';//用哪套标准判定（用于特殊情况，为空按照水样类型来判断）
if(empty($begin_date) || empty($end_date) || empty($cids_arr) || empty($vids_arr) || empty($export_element)){
	//参数不全，返回失败状态
	goback("请刷新页面重试！");
	exit;
}

#############获取数据
//可以显示数据的条件
////查询下化验单数据在什么状态(已校核、已复核...)下能显示到报告上（系统设置->系统个性化配置->报告可以查看数据状态）
$show_shuju_arr	= array();
$show_shuju_old	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='show_shuju' ORDER BY id DESC LIMIT 1");
if(!empty($show_shuju_old['module_value1'])){
	$show_shuju_arr	= explode(",",$show_shuju_old['module_value1']);
}
//组织sql条件
$return_result_arr	= $return_site_arr	= $return_max_min	= $return_jc_cb_sum	= $jcx_arr	= array();
$sql_result	= "SELECT s.xz_area,s.site_mark,ao.*,ap.unit,ap.`over`,ap.is_xcjc,cr.`cy_date` FROM `sites` AS s RIGHT JOIN `assay_order` AS ao ON s.id=ao.sid LEFT JOIN `assay_pay` AS ap ON ao.tid=ap.id INNER JOIN `cy_rec` as cr ON ao.cid=cr.id WHERE cr.`cy_date` >='".$begin_date."' AND cr.`cy_date` <='".$end_date."' AND ao.cid in (".implode(',', $cids_arr).") AND ao.vid in (".implode(',', $vids_arr).") AND ao.`hy_flag`>=0 ANd ao.`sid`>0 ORDER BY ao.`water_type`,cr.`cy_date`,ao.`bar_code`";
$query_result	= $DB->query($sql_result);
while($rs_result = $DB->fetch_assoc($query_result)){
	$max_water_type=get_water_type_max($rs_result['water_type'],$fzx_id);
	if(empty($max_water_type)){
		$max_water_type	= '1';
	}
	//化验项目数据（有平均值且global中配置为获取平均值时，以平均值为准）
	if($rs_result['over'] == ''){//用来判断是否是各个分厂所录入的数据
		$rs_result['over'] = '已审核';
	}
	if(!empty($show_shuju_arr) && !in_array($rs_result['over'],$show_shuju_arr)){
		$vd0	= '';
	}else{
		if(!empty($rs_result['ping_jun'])&&$global['bg_pingjun']) {
		//if((!empty($rs_result['ping_jun']) && !in_array($rs_result['hy_flag'],array('6','26','46','66'))) || (in_array($rs_result['hy_flag'],array('6','26','46','66')) && $global['bg_pingjun'])) {
			$vd0= $rs_result['ping_jun'];
		}else{
			$vd0= $rs_result['vd0'];
		}
		//将部分项目小于0的数据改为“未检出”显示
		if(@in_array($rs_result['vid'],$global['modi_data_vids'])&&$vd0<='0'&&$vd0!=''){
			$vd0= '未检出';
		}
		$vd0		= str_replace(" ","",$vd0);//去掉结果中的空格
		$baoliu_num	= 0;
		if(stristr($vd0,'.')){
			$tmp_arr	= explode('.',$vd0);
			$baoliu_num	= strlen($tmp_arr[1]);
		}
		$float_vd0	= (float)$vd0;//转换下存储类型，方便后面做对比大小
		if($baoliu_num > 0){
			$float_vd0	= number_format($float_vd0, $baoliu_num);
		}
		if(($float_vd0 != '0' && $float_vd0==$vd0) || $vd0=='0'){
			$vd0	= $float_vd0;
		}
	}
	$pingjia	= array();
	if($vd0 != '' && $rs_result['site_mark'] != 'fc_site'){
		$pingjia= water_pingjia($max_water_type,$rs_result['vid'],$vd0,$rs_result['WQG']);//水质类别判断
	}
	$old_vd0	= $vd0;
	$vd0		= str_replace(array("<",">"),array("&lt;","&gt;"),$vd0);
	//结果、判定结果、单位、水质类别
	$return_result_arr[$rs_result['cyd_id']][$rs_result['cid']][$rs_result['vid']]['vd0']		= $vd0;//结果
	if($rs_result['is_xcjc'] !='1'){
		$return_result_arr[$rs_result['cyd_id']][$rs_result['cid']][$rs_result['vid']]['tid'] = $rs_result['tid'];//化验单id
	}else{
		$return_result_arr[$rs_result['cyd_id']][$rs_result['cid']][$rs_result['vid']]['cyd_id'] = $rs_result['cyd_id'];//化验单id
	}
	$return_result_arr[$rs_result['cyd_id']][$rs_result['cid']][$rs_result['vid']]['panding']	= $pingjia['status'];//判定结果0是符合，1是不符合
	$return_result_arr[$rs_result['cyd_id']][$rs_result['cid']][$rs_result['vid']]['pingjia']	= $pingjia['pingjia_result'];//评价结果：数字 1~5，分别代表I到 V类. 7以上为生活饮用水类的标准
	$return_result_arr[$rs_result['cyd_id']][$rs_result['cid']][$rs_result['vid']]['pingjia_name']	= $pingjia['pingjia_name'];//评价结果名称：I到 V类. 或“生活饮用水”等其他标准的名称
	$return_result_arr[$rs_result['cyd_id']][$rs_result['cid']][$rs_result['vid']]['beishu']	= $pingjia['beishu'];//超标倍数
	$return_result_arr[$rs_result['cyd_id']][$rs_result['cid']][$rs_result['vid']]['unit']	= $rs_result['unit'];//计量单位
	//sid，名称,水样类型，站点所属行政区
	$return_site_arr[$rs_result['cid']]['sid']			= $rs_result['sid'];
	$return_site_arr[$rs_result['cid']]['site_name']	= $rs_result['site_name'];
	$return_site_arr[$rs_result['cid']]['water_type']	= $rs_result['water_type'];
	$return_site_arr[$rs_result['cid']]['quyu']			= $rs_result['xz_area'];//行政区
	$return_site_arr[$rs_result['cid']]['cy_date']		= $rs_result['cy_date'];
	$tmp_month	= substr($rs_result['cy_date'],0,strrpos($rs_result['cy_date'],'-'));
	$tmp_year	= explode('-',$rs_result['cy_date'])[0];
	//##############//最大值、最小值、平均值（每项目+每站点分类，统计每月或全年）
	if(in_array('max_min_value',$export_element)){
		//将汉字转换为0，将检出限的<去掉,科学计数法转换成正常数字（×10^，get_round_yxws）
		//$vd0	= (float)str_replace(array("<",">","&lt;","&gt;"), '', $vd0);
		if(stristr($vd0,"×10^")){
			$vd0	= get_round_yxws($vd0);//将科学计数法转换成正常数字
		}
		////比较得出最大值、最小值、平均值
		//数组初始化
		if(count($return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]) =='0'){
			$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['max']	= $vd0;//每年最大值
			$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['min']	= $vd0;//每年最小值
			$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['avg']	= array();//每年平均值
		}
		if(count($return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]) =='0'){
			$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['max']= $vd0;//每月最大值
			$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['min']= $vd0;//每月最小值
			$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['avg']= array();//每月平均值
		}
		//对比获得最大值和最小值(0和字符串的判断时，可能会出问题（但用户的数据很少会同时出现0与字符串的情况，因为0一般都是小于检出限）。下面有测试代码)
		$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['max']= max($vd0,$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['max']);//月最大值
		$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['max']	= max($vd0,$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['max']);//年最大值
		$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['min']= min($vd0,$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['min']);//月最小值
		$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['min']	= min($vd0,$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['min']);//年最小值
		//对数据去掉特殊字符后在进行比较
		$tmp_vd0		= (float)str_replace(array("<",">","&lt;","&gt;"), '', $vd0);
		$tmp_month_max	= (float)str_replace(array("<",">","&lt;","&gt;"), '', $return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['max']);
		$tmp_month_min	= (float)str_replace(array("<",">","&lt;","&gt;"), '', $return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['min']);
		$tmp_year_max	= (float)str_replace(array("<",">","&lt;","&gt;"), '', $return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['max']);
		$tmp_year_min	= (float)str_replace(array("<",">","&lt;","&gt;"), '', $return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['min']);
		/*//判断最大值、最小值，并存储
		if($tmp_month_max<$tmp_vd0 || ($tmp_month_max==$tmp_vd0 && $tmp_vd0==$vd0)){//第二个处理的是（未检出或者小于检出限）与0对比的时候要把0记录进去
			$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['max']	= $vd0;//月最大值
			if($tmp_year_max<$tmp_vd0 || ($tmp_year_max==$tmp_vd0 && $tmp_vd0==$vd0)){
				$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['max']	= $vd0;//年最大值
			}
		}else if($tmp_month_min>$tmp_vd0 || ($tmp_month_min==$tmp_vd0 && stristr($old_vd0,"<"))){//第二个处理的是（未检出或者小于检出限）与0对比的时候要把0记录进去
			$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['min']	= $vd0;//月最小值
			if($tmp_year_min>$tmp_vd0 || ($tmp_year_min==$tmp_vd0 && stristr($old_vd0,"<"))){
				$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['min']	= $vd0;//年最小值
			}
		}*/
		//计算平均值时，小于检出限的数按照检出限的一半计算,_round四舍六入五单双修约
		$round_num	= 0;
		if(stristr($old_vd0,"<")){
			$avg_vd0	= $tmp_vd0/2;
			$round_num	= strlen(explode('.',$old_vd0)[1]);
			$jcx_arr[$rs_result['vid']]['jcx']	= str_replace(array("<",">","&lt;","&gt;"), '',$old_vd0);//获取项目的检出限
		}else{
			$avg_vd0	= $vd0;//汉字、正常数字都记录
			if($vd0!='0' && $tmp_vd0 == '0'){
				$jcx_arr[$rs_result['vid']]['china']	= "yes";//获取项目的用汉字情况
			}else{
				if(stristr($vd0,'.')){//获取保留位数
					$round_num	= strlen(explode('.',$vd0)[1]);
				}
			}
		}
		$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['avg']['sum'][]	= $avg_vd0;//月平均值的原始数据
		$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['avg']['sum'][]	= $avg_vd0;//年平均值的原始数据
		//用检出限一半计算的要进行修约
		if($round_num==0){
			$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['avg']['value']	= array_sum($return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['avg']['sum'])/count($return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['avg']['sum']);//月平均值
			$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['avg']['value']	= array_sum($return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['avg']['sum'])/count($return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['avg']['sum']);//年平均值
		}else{//需要四舍六入五单双修约
			$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['avg']['value']	= _round((array_sum($return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['avg']['sum'])/count($return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['avg']['sum'])),$round_num);//月平均值
			$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['avg']['value']	= _round((array_sum($return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['avg']['sum'])/count($return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['avg']['sum'])),$round_num);//年平均值
		}
		//判断检出限的情况
		if(!empty($jcx_arr[$rs_result['vid']]['jcx'])){
			$tmp_month_avg	= (float)str_replace(array("<",">","&lt;","&gt;"), '', $return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['avg']['value']);
			if($tmp_month_avg <$jcx_arr[$rs_result['vid']]['jcx']){
				$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['avg']['value']	= "&lt;".$jcx_arr[$rs_result['vid']]['jcx'];
			}
			$tmp_year_avg	= (float)str_replace(array("<",">","&lt;","&gt;"), '', $return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['avg']['value']);
			if($tmp_year_avg <$jcx_arr[$rs_result['vid']]['jcx']){
				$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['avg']['value']	= "&lt;".$jcx_arr[$rs_result['vid']]['jcx'];
			}
		}
		//判断汉字的情况
		if($jcx_arr[$rs_result['vid']]['china']=='yes'){
			if($return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['avg']['value']<=0){
				$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_month]['avg']['value']	= '未检出';//无、未检出
			}
			if($return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['avg']['value']<=0){
				$return_max_min[$rs_result['sid']][$rs_result['vid']][$tmp_year]['avg']['value']	= '未检出';
			}
		}
		//科学技术法的情况（以后出现在考虑什么时候转换）
	}
	//#############//检测次数、超标次数(按项目+站点分类统计，统计每月或全年)
	if(in_array('jc_cb_sum',$export_element)){
		//结果、判定结果、单位、水质类别
		//$return_result_arr[$rs_result['cid']][$rs_result['vid']]['vd0']		= $vd0;//结果
		//$return_result_arr[$rs_result['cid']][$rs_result['vid']]['panding']	= $return_data['status'];//判定结果0是符合，1是不符合
		if(empty($return_jc_cb_sum[$rs_result['sid']][$rs_result['vid']][$tmp_year])){
			$return_jc_cb_sum[$rs_result['sid']][$rs_result['vid']][$tmp_year]['jc_sum']	= 0;//每年检测次数
			$return_jc_cb_sum[$rs_result['sid']][$rs_result['vid']][$tmp_year]['cb_sum']	= 0;//每年超标次数
		}
		if(empty($return_jc_cb_sum[$rs_result['sid']][$rs_result['vid']][$tmp_month])){
			$return_jc_cb_sum[$rs_result['sid']][$rs_result['vid']][$tmp_month]['jc_sum']	= 0;//每月检测次数
			$return_jc_cb_sum[$rs_result['sid']][$rs_result['vid']][$tmp_month]['cb_sum']	= 0;//每月超标次数
		}
		$return_jc_cb_sum[$rs_result['sid']][$rs_result['vid']][$tmp_month]['jc_sum']++;//每月检测次数
		$return_jc_cb_sum[$rs_result['sid']][$rs_result['vid']][$tmp_year]['jc_sum']++;//每年检测次数
		if($pingjia['status']!='0'){
			$return_jc_cb_sum[$rs_result['sid']][$rs_result['vid']][$tmp_month]['cb_sum']++;//每月超标次数
			$return_jc_cb_sum[$rs_result['sid']][$rs_result['vid']][$tmp_year]['cb_sum']++;//每年超标次数
		}
	}

}
//print_rr($return_site_arr);
//print_rr($return_result_arr);
/*
//字符串和0的判断
$a	= 0;
//$a	= (float)$a;
echo max('bbb','aaa','未检出',$a,'<0.05');
echo min($a,'bbb','aaa','未检出',$a,'<0.05');
*/

//根据条件查询结果//按照采样时间排序
//将平均值、vd0等特殊情况处理。单位.检测方法。
//判断是否合格

//现场的一些数据改如何查看
//获取采样时间的范围
//组合成数组

###########输出（返回）结果
