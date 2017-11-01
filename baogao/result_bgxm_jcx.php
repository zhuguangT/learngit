<?php
/*
	功能：根据不同的模板返回该检测报告所需要的检出限
	作者：高龙
	时间：2016-7-19
*/
	//根据模板名称查询出该检测报告下的检出限
	$jcbz_sql="SELECT n.module_value4,aj.* FROM n_set n JOIN assay_jcbz aj ON n.id=aj.jcbz_bh_id WHERE module_name='jcbz_bh' AND module_value3='1' AND module_value1 like '%".$mbrows['te_name']."%'";
	$jcbz_jgj = $DB->query($jcbz_sql);
	if($DB->num_rows($jcbz_jgj) <= 0){
		$jcbz_sql="SELECT n.module_value4,aj.* FROM n_set n JOIN assay_jcbz aj ON n.id=aj.jcbz_bh_id WHERE module_name='jcbz_bh' AND module_value3='1' AND module_value2='".$pd_water_type."'";
		$jcbz_jgj = $DB->query($jcbz_sql);
	}
	while ($jcx_result = $DB->fetch_assoc($jcbz_jgj)) {
		$mb_jcx_arr[$jcx_result['vid']][$jcx_result['module_value4']] = $jcx_result['xz']?$jcx_result['xz']:'--';//读取项目不同类型下的检出限
	}
?>