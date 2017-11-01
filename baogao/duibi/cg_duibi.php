<?php
/**
 * 功能：成果对比
 * 作者：Mr Zhou
 * 日期：2014-11-09
 * 描述：
*/
include '../../temp/config.php';
$fzx_id = FZX_ID;
$trade_global['css'] = array('lims/main.css');
$cyd_id = intval($_GET['cyd_id']);
if(intval($_GET['sid'])){
	$site_where = " AND s.`id`=".intval($_GET['sid']);
}
$sql = "SELECT
	s.`id` sid, s.`fp_id`, s.`site_name`,s.`sgnq`,h.`hub_name`,
	zcy.`cy_date`,zao.`vid`,zap.`assay_element`,

	zcy.`id` zcyd,			fcy.`id` fcyd,
	zao.`sid` zsid,			fao.`sid` fsid,
	zao.`vid` zvid,			fao.`vid` fvid,
	zao.`id` zaoid,			fao.`id` faoid,
	zao.`vd0` z_vd0,		fao.`vd0` f_vd0,
	zao.`_vd0` z__vd0,		fao.`_vd0` f__vd0, 
	zam.`method_number` zmb,fam.`method_number` fmb,
	zam.`method_name` zmn ,	fam.`method_name` fmn
	FROM `cy` zcy LEFT JOIN `assay_pay` zap ON zap.`cyd_id`=zcy.`id` 
	LEFT JOIN `assay_order` zao ON zap.`id`=zao.`tid` 
	LEFT JOIN `sites` s ON zao.`sid`=s.`id` 
	LEFT JOIN `xmfa` zxf ON zap.`fid`= zxf.`id` 
	LEFT JOIN `assay_method` zam ON zxf.`fangfa`=zam.`id` 

	LEFT JOIN `assay_order` fao ON fao.`sid`=zao.`sid` 
	LEFT JOIN `assay_pay` fap ON fap.`id`=fao.`tid`
	LEFT JOIN `cy` fcy ON fcy.`id`=fap.`cyd_id`
	LEFT JOIN `xmfa` fxf ON fap.`fid`= fxf.`id` 
	LEFT JOIN `assay_method` fam ON zxf.`fangfa`=fam.`id` 

	LEFT JOIN `hub_info` h ON s.`fp_id`=h.`id`

	WHERE zcy.`id`=$cyd_id $site_where AND s.`id`>0 
	AND fcy.`cy_date`=zcy.`cy_date` AND zao.id != fao.id AND zao.vid=fao.vid
	ORDER BY zao.`sid`";
//echo $sql;
$query = $DB->query($sql);
while ($row=$DB->fetch_assoc($query)) {
	$hub_name = explode('监测中心', $row['hub_name']);
	$row['hub_name'] = $hub_name[1];
	if(''!=$row['z_vd0'] && ''!=$row['f_vd0']){
		if($row['z_vd0'] != $row['f_vd0'])
			$row['xdpc'] = round(($row['z__vd0']-$row['f__vd0'])/($row['z__vd0']+$row['f__vd0']),2).'%';
	}
	$data[$row['sid']][$row['vid']]=$row;
}
//print_rr($data['10465']['166']);
foreach ($data as $sid => $site) {
	$line = '';
	foreach ($site as $vid => $row) {
		$line.=temp('dbsy/cg_duibi_line');
	}
	$plan .= temp('dbsy/cg_duibi_plan');
}
disp('dbsy/cg_duibi');