<?php
include "../../temp/config.php";
//导航
$trade_global['daohang']	= array(array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),array('icon'=>'','html'=>'检测能力统计表','href'=>$current_url));
$trade_global['css']		= array('lims/main.css');
$fzx_id=intval($_GET['fzx_id']) ? intval($_GET['fzx_id']) : '1';
$hub_info = $DB->fetch_one_assoc("SELECT * FROM `hub_info` WHERE 1 ");
$sql	= "SELECT `type`,`lname`,`lxid`,`value_C`,`xmid`,`method_number`,`full_name` FROM `xmfa` f LEFT JOIN `assay_value` AS v ON f.`xmid`=v.`id` LEFT JOIN `assay_method` m ON f.`fangfa`=m.`id` LEFT JOIN `leixing` l ON f.`lxid`=l.`id`  WHERE f.`fzx_id`='$fzx_id' ORDER BY `lxid`,`xmid`";
$query	= $DB->query($sql);
$shuzi_arr = array('（一）','（二）','（三）','（四）','（五）','（六）','（七）','（八）','（九）');
$fangfa = array();
while ($row = $DB->fetch_assoc($query)) {
	$fangfa[$row['type']][$row['lname']]['count']++;
	$fangfa[$row['type']][$row['lname']]['data'][$row['xmid']][] = $row;
}
$pjbz_arr = array(
	'地表水'=>'地表水环境质量标准 GB3838-2002 农田灌溉水质标准 GB5084-2005 渔业用水标准 GB11607-1989 地表水资源质量标准 SL63-1994 生活饮用水水源水质标准 CJ3020-1993',
	'地下水'=>'地下水 质量标准',
	'生活饮用水'=>'生活饮用水',
	'污废水及再生水'=>'污废水及再生水 质量标准',
	'土壤与底质'=>'土壤与底质 质量标准'
);
$lines = '';
$last_vid = 0;
$fangfa2 = array(
	'一、水' => $fangfa['水'],
	'二、土壤' => $fangfa['土壤']
);
foreach ($fangfa2 as $type_name => $type) {
	$j=0;
	$lines .= '<tr><td colspan="6" align="left">'.$type_name.'</td></tr>';
	foreach ($type as $lname => $leixing) {
		$i = 0;
		$leixingCount = $leixing['count']+1;
		$lines .= '<tr><td rowspan="'.$leixingCount.'">'.$shuzi_arr[$j++].'</td><td rowspan="'.$leixingCount.'">'.$lname.'</td><td colspan="4" align="left">'.$pjbz_arr[$lname].'</td></tr>';
		foreach ($leixing['data'] as $vid => $rows) {
			$i++;
			foreach ($rows as $key => $value) {
				$vid_head=$vid_foot='';
				if($last_vid != $vid){
					$last_vid = $vid;
					$vidCount = count($rows);
					$vid_head = '<td rowspan="'.$vidCount.'">'.$i.'</td><td rowspan="'.$vidCount.'">'.$value['value_C'].'</td>';
					$vid_foot = '<td rowspan="'.$vidCount.'">&nbsp;</td>';
				}
				$lines .= '<tr>'.$vid_head.'<td>'.$value['full_name'].'&nbsp;'.$value['method_number'].'</td>'.$vid_foot.'</tr>';
			}
		}
	}
}
disp('jcnl_tj/jcnl_tj');