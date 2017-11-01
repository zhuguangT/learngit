<?php
/**
 * 功能：
 * 作者: Mr
 * 日期: 2015-11-01 
 * 描述:这里是需要特殊处理数据的化验单函数
*/
//'110,125,128,174,181,494,567,592,598,601,605,651,653,655,657'
/**
 * 功能：动植物油的化验单
 * 日期：2015-07-17
 * 参数：
 * 返回值：
 * 功能描述：动植物油浓度等于总油浓度加上石油浓度
*/
function getHydData_110($pay){
	global $DB,$global;
	if('lzzls'!=$global['hyd']['danwei']){
		return false;
	}
	//110 动植物油，603 总油，108石油类。
	$sql = "SELECT * FROM `assay_order` WHERE `cyd_id`='{$pay['cyd_id']}' AND `vid` IN(110,627,108) ORDER BY `id`";
	$query = $DB->query($sql);
	while ($row=$DB->fetch_assoc($query)) {
		$orders[$row['vid']][$row['bar_code']]=$row;
	}
	$sql = '';
	foreach ($orders['110'] as $key => $value) {
		$vd1=is_numeric($orders[627][$value['bar_code']]['_vd0'])?$orders[627][$value['bar_code']]['_vd0']:0;
		$vd2=is_numeric($orders[108][$value['bar_code']]['_vd0'])?$orders[108][$value['bar_code']]['_vd0']:0;
		$_vd0=$vd1-$vd2;
		$vd0=round_value($_vd0,$pay['id']);
		$sql = "UPDATE `assay_order` SET `vd4`='{$orders[627][$value['bar_code']]['_vd0']}',`vd5`='{$orders[108][$value['bar_code']]['_vd0']}',`vd0`='{$vd0}',`_vd0`='{$_vd0}' WHERE `id` = '{$value['id']}'";
		$DB->query($sql);
	}
}
/**
 * 功能：总碱度的化验单
 * 日期：2015-07-17 
 * 参数：
 * 返回值：
 * 功能描述：给其他项目加上质控数据
*/
function getHydData_125($pay){
	global $DB,$global;
	if('bjyth'!=$global['hyd']['danwei']){
		return false;
	}
	//125总碱度 188碳酸盐 189重碳酸盐 575氢氧化物
	$sql = "SELECT * FROM `assay_order` WHERE `cyd_id`='{$pay['cyd_id']}' AND `vid` IN(125,188,189) ORDER BY `id`";
	$query = $DB->query($sql);
	while ($row=$DB->fetch_assoc($query)) {
		$orders[$row['vid']][$row['bar_code']]=$row;
	}
	$sql = '';
	foreach ($orders['125'] as $key => $value) {
		$vd12 = round_value($value['vd12'],$orders['189'][$key]['tid']);
		$vd17 = round_value($value['vd17'],$orders['188'][$key]['tid']);
		$sql = "UPDATE `assay_order` SET
			`vd10`='{$orders[189][$value['bar_code']]['ping_jun']}',`vd11`='{$orders[189][$value['bar_code']]['xiang_dui_pian_cha']}',
			`vd12`='{$vd12}',
			`vd15`='{$orders[188][$value['bar_code']]['ping_jun']}',`vd16`='{$orders[188][$value['bar_code']]['xiang_dui_pian_cha']}',
			`vd17`='{$vd17}',
			`vd20`='{$orders[125][$value['bar_code']]['ping_jun']}',`vd21`='{$orders[125][$value['bar_code']]['xiang_dui_pian_cha']}'
			WHERE `id` = '{$value['id']}'";
		$DB->query($sql);
	}
}
/**
 * 功能：侵蚀性二氧化碳的化验单处理
 * 作者：Mr Zhou
 * 日期：2014-12-01 
 * 参数：
 * 返回值：
 * 功能描述：1、与总碱度有关联，只有总碱度=重碳酸盐碱度(P=0)时才滴定侵蚀性二氧化碳；
 *		   2、侵蚀性二氧化碳计算时要减去总碱度的用量
*/
function getHydData_128($pay){
	global $DB,$dhy_arr,$global;
	if('lnsw'!=$global['hyd']['danwei']){
		return false;
	}
	//125 总碱度 128 侵蚀性二氧化碳
	$sql = "SELECT * FROM `assay_order` WHERE `cyd_id`='{$pay['cyd_id']}' AND `vid` IN(125,128) ORDER BY `id`";
	$query = $DB->query($sql);
	while ($row=$DB->fetch_assoc($query)) {
		$orders[$row['vid']][$row['bar_code']]=$row;
	}
	$sql = '';
	foreach ($orders['128'] as $key => $value) {
		$P   = $orders[125][$value['bar_code']]['vd4'];
		$M   = $orders[125][$value['bar_code']]['vd7'];
		if(floatval($P)>0){
			$M='-';
		}
		$sql = "UPDATE `assay_order` SET `vd12`='{$M}' WHERE `id` = '{$value['id']}'";
		$DB->query($sql);
	}
}
/**
 * 功能：离子色谱的化验单
 * 日期：2015-09-02
 * 参数：
 * 返回值：
 * 功能描述：
*/
function getHydData_181($pay){
	global $DB,$global;
	if('bjyth'!=$global['hyd']['danwei']){
		return false;
	}
	//181氟化物氯化物182硫化物185硝酸盐氮186
	$sql = "SELECT * FROM `assay_order` WHERE `cyd_id`='{$pay['cyd_id']}' AND `vid` IN(181,182,185,186) ORDER BY `id`";
	$query = $DB->query($sql);
	while ($row=$DB->fetch_assoc($query)) {
		$orders[$row['vid']][$row['bar_code']]=$row;
	}
	$sql = '';
	foreach ($orders['181'] as $key => $value) {
		$sql = "UPDATE `assay_order` SET 
			`vd19`='{$orders[181][$value['bar_code']]['ping_jun']}',`vd20`='{$orders[181][$value['bar_code']]['xiang_dui_pian_cha']}',
			`vd21`='{$orders[182][$value['bar_code']]['ping_jun']}',`vd22`='{$orders[182][$value['bar_code']]['xiang_dui_pian_cha']}',
			`vd23`='{$orders[186][$value['bar_code']]['ping_jun']}',`vd24`='{$orders[186][$value['bar_code']]['xiang_dui_pian_cha']}',
			`vd25`='{$orders[185][$value['bar_code']]['ping_jun']}',`vd26`='{$orders[185][$value['bar_code']]['xiang_dui_pian_cha']}'
			WHERE `id` = '{$value['id']}'";
		$DB->query($sql);
	}
}
/**
 * 功能：镁的化验单
 * 作者：Mr Zhou
 * 日期：2014-11-30 
 * 参数：
 * 返回值：
 * 功能描述：镁是有减差法来测的，即总硬度的值减去钙的值
*/
function getHydData_174($pay){
	global $DB,$global;
	if('lnsw'!=$global['hyd']['danwei']){
		return false;
	}
	//vid 103总硬度  173钙 174镁
	//vd2总硬度标液用量V0(mL)
	//vd3总硬度空白用量V1(mL)
	//vd4总硬度取样体积V2(mL)
	//vd5钙标液用量V3(mL)
	//vd6钙取样体积V4(mL)
	//td26总硬度化验单id
	//td27钙化验单id
	$sql = "SELECT * FROM `assay_order` WHERE `cyd_id`='{$pay['cyd_id']}' AND `vid` IN(103,173,174) ORDER BY `id`";
	$query = $DB->query($sql);
	while ($row=$DB->fetch_assoc($query)) {
		$orders[$row['vid']][$row['bar_code']]=$row;
	}
	$sql = '';
	foreach ($orders['174'] as $key => $value) {
		$sql = "UPDATE `assay_order` SET `vd2`='{$orders[103][$value['bar_code']]['vd9']}',`vd3`='{$orders[103][$value['bar_code']]['vd10']}',`vd4`='{$orders[103][$value['bar_code']]['vd1']}',`vd5`='{$orders[173][$value['bar_code']]['vd9']}',`vd6`='{$orders[173][$value['bar_code']]['vd1']}' WHERE `id` = '{$value['id']}'";
		$DB->query($sql);
	}
}
/**
 * 功能：三卤甲烷
 * 作者：
 * 日期：2015-07-09
 * 参数：
 * 返回值：
 * 功能描述：
*/
function getHydData_494($pay){
	global $DB,$global;
	//兰州
	/*if('qdzls'!=$global['hyd']['danwei']){
		return false;
	}*/
	//三卤甲烷	  vid 494
	//三氯甲烷	  vid 496   限值  60
	//三溴甲烷	  vid 497   限值  100
	//一氯二溴甲烷  vid 498   限值  100
	//二氯一溴甲烷  vid 499   限值  60
	$sql = "SELECT * FROM `assay_order` WHERE `cyd_id` ='{$pay['cyd_id']}' AND `vid` IN ('494','496','497','498','499')";
	$query=$DB->query($sql);
	while($row=$DB->fetch_assoc($query))
	{
		$orders[$row['vid']][$row['bar_code']]=$row;
	}
	//将三卤甲烷分量的vid和限值放在一个数组里面
	$sljw_arr = array('496'=>'0.06','499'=>'0.06','498'=>'0.1','497'=>'0.1');
	foreach ($orders[494] as $key => $value) {
		$bar = $value['bar_code'];
		$zongLiang = 0.00;
		foreach ($sljw_arr as $vid => $xz) {
			if('<'==$orders[$vid][$bar]['vd0'][0]){
				//如果检测结果小于检出限使用检出限的一半除以限值得到该项目的分量值
				$fenLiang = floatval(str_replace('<', '', $orders[$vid][$bar]['vd0']))/2/$xz;
			}else{
				$fenLiang = floatval($orders[$vid][$bar]['vd0'])/$xz;
			}
			$zongLiang += $fenLiang;
		}
		$vd0 = round_value($zongLiang,$pay['id']);
		$sql = "UPDATE `assay_order` SET `vd27` = '$vd0' WHERE `id` = '{$value['id']}'";
		$sql = "UPDATE `assay_order` SET `_vd0`='$zongLiang',`vd0` = '$vd0',
				`vd3` ='{$orders['496'][$bar]['vd0']}',`vd4` ='{$orders['496'][$bar]['_vd0']}',`vd5` ='{$orders['496'][$bar]['tid']}',
				`vd6` ='{$orders['497'][$bar]['vd0']}',`vd7` ='{$orders['497'][$bar]['_vd0']}',`vd8` ='{$orders['497'][$bar]['tid']}',
				`vd9` ='{$orders['498'][$bar]['vd0']}',`vd10`='{$orders['498'][$bar]['_vd0']}',`vd11`='{$orders['498'][$bar]['tid']}',
				`vd12`='{$orders['499'][$bar]['vd0']}',`vd13`='{$orders['499'][$bar]['_vd0']}',`vd14`='{$orders['499'][$bar]['tid']}'
				WHERE `id` = '{$value['id']}'";
		$DB->query($sql);
	}
}
/**
 * 功能：矿化度的化验单
 * 作者：Mr Zhou
 * 日期：2015-05-07 
 * 参数：
 * 返回值：
 * 功能描述：矿化度的检测结果计算中要减去二分之一的重碳酸盐的含量
*/
function getHydData_567($pay){
	global $DB,$global;
	if('lnsw'!=$global['hyd']['danwei']){
		return false;
	}
	//vid 567矿化度  188重碳酸盐
	//vd9 存储重碳酸盐含量
	$sql = "SELECT * FROM `assay_order` WHERE `cyd_id`='{$pay['cyd_id']}' AND `vid`in(567,188) ORDER BY `id`";//查询出本项目和对应的项目
	$query = $DB->query($sql);
	while ($row=$DB->fetch_assoc($query)) {
		$orders[$row['vid']][$row['bar_code']]=$row;
	}
	$sql = '';
	foreach ($orders['567'] as $key => $value) {
		$sql = "UPDATE `assay_order` SET `vd9`='{$orders[188][$value['bar_code']]['vd0']}' WHERE `id` = '{$value['id']}'";//将对应项目的结果值赋给本项目的对应字段
		$DB->query($sql);
	}
}
/**
 * 功能：硬度的化验单
 * 作者：Mr Zhou
 * 日期：2015-04-20 
 * 参数：
 * 返回值：
 * 功能描述：硬度是有减差法来测的，即总硬度的值碳酸盐硬度的值
*/
function getHydData_592($pay){
	getHydData_598($pay);
}
function getHydData_598($pay){
	global $DB,$global;
	if('qdzls'!=$global['hyd']['danwei']){
		return false;
	}
	$sql = "SELECT * FROM `assay_order` WHERE `cyd_id`='{$pay['cyd_id']}' AND `vid` IN({$pay['vid']},103,595) ORDER BY `id`";
	$query = $DB->query($sql);
	while ($row=$DB->fetch_assoc($query)) {
		if(''!=$row['ping_jun']){
			$row['vd0']=$row['ping_jun'];
			//如果是平行样，将原样的vd0改为平均值
			$orders[$row['vid']][str_replace('P', '', $row['bar_code'])]['vd0']=$row['ping_jun'];
		}
		$orders[$row['vid']][$row['bar_code']]=$row;
	}
	$sql = '';
	foreach ($orders[$pay['vid']] as $key => $value) {
		$sql = "UPDATE `assay_order` SET `vd3`='{$orders[103][$value['bar_code']]['vd0']}',`vd4`='{$orders[595][$value['bar_code']]['vd0']}' WHERE `id` = '{$value['id']}'";
		$DB->query($sql);
	}
}
/**
 * 功能：三价铁的化验单
 * 作者：Mr Zhou
 * 日期：2015-05-14 
 * 参数：
 * 返回值：
 * 功能描述：
*/
function getHydData_601($pay){
	global $DB,$global;
	if('lnsw'!=$global['hyd']['danwei']){
		return false;
	}
	//vid 601 三价铁 599 二价铁 153 总铁 154 铁
	$sql = "SELECT * FROM `assay_order` WHERE `cyd_id`='{$pay['cyd_id']}' AND `vid`in(601,599,153,154) ORDER BY `id`";//查询出本项目和对应的项目
	$query = $DB->query($sql);
	$tid = array();
	while ($row=$DB->fetch_assoc($query)) {
		$tid[$row['vid']] = $row['tid'];
		$orders[$row['vid']][$row['bar_code']]=$row;
	}
	$td7=$tid[153]?$tid[153]:$tid[154];
	$td8 = $tid[599];
	$sql = "UPDATE `assay_pay` SET `td7`='$td7',`td8`='$td8' WHERE `cyd_id`='{$pay['cyd_id']}' AND `vid`=601";//将对应项目的结果值赋给本项目的对应字段
	   $DB->query($sql);
	foreach ($orders['601'] as $key => $value) {
		$Fe=$tid[153]?$orders[153][$value['bar_code']]['_vd0']:$orders[154][$value['bar_code']]['_vd0'];
		$vd0=$Fe-$orders[599][$value['bar_code']]['_vd0'];
		$sql = "UPDATE `assay_order` SET `vd3`='$Fe',`vd4`='{$orders[599][$value['bar_code']]['_vd0']}' WHERE `id` = '{$value['id']}'";//将对应项目的结果值赋给本项目的对应字段
		$DB->query($sql);
	}
}
/**
 * 功能：盐基度的化验单
 * 作者：Mr Zhou
 * 日期：2015-04-20 
 * 参数：
 * 返回值：
 * 功能描述：
*/
function getHydData_605($pay){
	global $DB,$global;
	if('qdzls'!=$global['hyd']['danwei']){
		return false;
	}
	$sql = "SELECT * FROM `assay_order` WHERE `cyd_id`='{$pay['cyd_id']}' AND `vid` IN({$pay['vid']},603) ORDER BY `id`";
	$query = $DB->query($sql);
	while ($row=$DB->fetch_assoc($query)) {
		if(''!=$row['ping_jun']){
			$row['vd0']=$row['ping_jun'];
			//如果是平行样，将原样的vd0改为平均值
			$orders[$row['vid']][str_replace('P', '', $row['bar_code'])]['vd0']=$row['ping_jun'];
		}
		$orders[$row['vid']][$row['bar_code']]=$row;
	}
	$sql = '';
	foreach ($orders[$pay['vid']] as $key => $value) {
		//将聚氯化铝项目Al₂O₃[vid:603]的检测结果赋值给盐基度的vd6，作为ω₁参与计算
		$sql = "UPDATE `assay_order` SET `vd6`='{$orders[603][$value['bar_code']]['vd0']}' WHERE `id` = '{$value['id']}'";
		$DB->query($sql);
	}
}
/**
 * 功能：地下水阴阳离子
 * 日期：2015-09-23
 * 参数：
 * 返回值：
 * 功能描述：
*/
function getHydData_651($pay){
	getHydData_657($pay);
}
function getHydData_653($pay){
	getHydData_657($pay);
}
function getHydData_655($pay){
	getHydData_657($pay);
}
function getHydData_657($pay){
	global $DB,$global;
	if('lnsw'!=$global['hyd']['danwei']){
		return false;
	}
	//NH₄⁺含量=氨氮含量*1.286
	//NO₂⁻含量=亚硝酸盐氮*3.285
	//NO₃⁻含量=硝酸盐氮*4.43
	//P₂O₅含量=磷酸盐*4.43
	//651 氨根离子NH₄⁺(198氨氮)，653 亚硝酸根离子NO₂⁻(187亚硝酸盐氮)，655 硝酸根离子NO₃⁻(186硝酸盐氮)，657五氧化二磷P₂O₅(563磷酸盐)
	$dxs_lz_dy = array(
		651=>array('vid'=>198,'k'=>1.286,'v'=>'NH₄⁺','v2'=>'氨氮'),
		653=>array('vid'=>187,'k'=>3.285,'v'=>'NO₂⁻','v2'=>'亚硝酸盐氮'),
		655=>array('vid'=>186,'k'=>4.43,'v'=>'NO₃⁻','v2'=>'硝酸盐氮'),
		657=>array('vid'=>563,'k'=>0.739454,'v'=>'P₂O₅','v2'=>'磷酸盐')
	);
	$vid = $pay['vid'];
	$DB->query("UPDATE `assay_pay` SET `td9`='{$dxs_lz_dy[$vid]['v']}',`td10`='{$dxs_lz_dy[$vid]['v2']}',`td11`='{$dxs_lz_dy[$vid]['k']}' WHERE `id`='{$pay['id']}'");
	$query = $DB->query("SELECT * FROM `assay_order` WHERE `cyd_id`='{$pay['cyd_id']}' AND `vid` IN('$vid','{$dxs_lz_dy[$vid]['vid']}') ORDER BY `id`");
	while ($row=$DB->fetch_assoc($query)) {
		$orders[$row['vid']][$row['bar_code']]=$row;
	}
	if(empty($orders[$dxs_lz_dy[$vid]['vid']])){
		return false;
	}
	$sql = '';
	foreach ($orders[$vid] as $key => $value) {
		$vd01   = $orders[$dxs_lz_dy[$vid]['vid']][$value['bar_code']]['vd0'];
		$_vd01  = $orders[$dxs_lz_dy[$vid]['vid']][$value['bar_code']]['_vd0'];
		$_vd02  = $_vd01*$dxs_lz_dy[$vid]['k'];
		$vd02   = round_value($_vd02,$pay['id']);
		$sql = "UPDATE `assay_order` SET `vd3`='{$_vd01}',`vd4`='{$vd01}',`vd0`='{$vd02}',`_vd0`='{$_vd02}' WHERE `id`='{$value['id']}' AND `tid`='{$pay['id']}'";
		$DB->query($sql);
	}
}
/**
 * 功能：兰州工艺粒径
 * 日期：2016-08-01
 * 参数：
 * 返回值：
 * 功能描述：
*/
function getHydData_660($pay){
	global $DB,$global;
	$td18 = array(
		$pay['td19'] => $pay['td20'],
		$pay['td21'] => $pay['td22'],
		$pay['td23'] => $pay['td24'],
		'd80' => $pay['td25'],
		'd10' => $pay['td26'],
		'k80' => $pay['td27']
	);
	$pay['td18'] = json_encode($td18);
	$sql = "UPDATE `assay_pay` SET `td18`='{$pay['td18']}' WHERE `id`='{$pay['id']}'";
	$DB->query($sql);
	$sql = "UPDATE `assay_order` SET `vd0`='{$pay['td24']}',`vd27`='{$pay['td18']}' WHERE `tid`='{$pay['id']}'";
	$DB->query($sql);
}