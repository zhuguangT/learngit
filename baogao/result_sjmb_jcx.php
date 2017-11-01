<?php
/*
	功能：选择项目检出限
	作者：高龙
	时间：2016-7-19
*/
	if($mbrows['te_name'] == '硫酸铝' || $mbrows['te_name'] == '氯化铁'){
		$jcx1g = $mb_jcx_arr[$xmid]['I类固体'];
		$jcx1y = $mb_jcx_arr[$xmid]['I类液体'];
		$jcx2g = $mb_jcx_arr[$xmid]['II类固体'];
		$jcx2y = $mb_jcx_arr[$xmid]['II类液体'];
	}
	if($mbrows['te_name'] == '活性炭'){
		$jcxk = $mb_jcx_arr[$xmid]['颗粒活性炭'];
		$jcxf = $mb_jcx_arr[$xmid]['粉末活性炭'];
	}
	if($mbrows['te_name'] == '聚氯化铝'){
		$jcxy = $mb_jcx_arr[$xmid]['液体'];
		$jcxg = $mb_jcx_arr[$xmid]['固体'];
	}
?>