<?php
/**
 * 功能：获取更多化验单
 * 作者: Mr Zhou
 * 日期: 2015-05-17
 * 描述: 现在化验项目都根据方法走，去xmfa表中查看化验单的关联数据
*/
if(!in_array(trim($_GET['qz']),array('jh','fh','sh'))){
	prompt('请求错误！');
	gotourl($url[1]);
}
include ('../temp/config.php');
require ('./assay_form_func.php');

$tid = intval($_GET['tid']);
$list_type	 = trim($_GET['list_type']);
$limit_step	 = intval($_GET['limit_step']);
$limit_start = intval($_GET['limit_start']);

$list_type_set = array(
	'pc' => array(
		'group_key' => 'vid',
		'row_name'  => 'cyd_bh',
		'item_name' => 'assay_element',
		'order_by'	=> '`cyd_id`,a.`vid`,a.`id`'
		),
	'xm' => array(
		'group_key' => 'vid',
		'row_name'  => 'assay_element',
		'item_name' => 'cyd_bh',
		'order_by'	=> 'a.`vid`,`cyd_id`,a.`id`'
		)
);
!in_array($list_type, array('pc','xm')) && $list_type='xm';
$key_name	= $list_type_set[$list_type];

//查询表格模板名称
$sql = "SELECT `id`,`table_name` FROM `bt_muban` WHERE 1";
$query = $DB->query($sql);
while ($row = $DB->fetch_assoc($query)) {
	$bt_muban[$row['id']] = $row['table_name'];
}

$hyd_all	= array();
//获得请求动作
$action		= trim($_GET['qz']);
//只检索最近三个月内数据
$last_date	= date('Y-m-01',strtotime('-2 months'));
$sh_config	= $global['hyd']['sh_config'];//'sh_config'=> array('jh'=>array('v1','校核','已完成','02'))
$sql	= "SELECT a.`id`,a.`vid`,a.`cyd_id`,c.`cyd_bh` FROM `assay_pay` AS a LEFT JOIN `cy` AS c ON a.`cyd_id`=c.`id` WHERE a.`fzx_id`='$fzx_id' AND c.`cy_date` > '$last_date' AND a.`id` != '$tid'AND a.`over` = '{$sh_config[$action][2]}' AND `vid` IN('{$u['user_other'][$sh_config[$action][0]]}')  ORDER BY {$key_name['order_by']} LIMIT $limit_start ,$limit_step";
$query=$DB->query($sql);
while( $row = $DB->fetch_assoc( $query ) ){
	$hyd_all[$row[$key_name['group_key']]][] = $row['id'];
}
$rows_c		= 0;//row_count row(项目数)统计
$items_c	= 0;//每个row里面化验单个数
$step_rows	= '';//快速切换项目位置
$assay_form_hyds = array();//所有的化验单内容
$assay_form_hyds['error']	 = 0;
$assay_form_hyds['has_more'] = 1;
$assay_form_hyds['hyd_all']	 = $hyd_all;
if(!count($hyd_all)){
	$assay_form_hyds['error'] = 1;
	$assay_form_hyds['has_more'] = 0;
	die(json_encode($assay_form_hyds));
}else if(count($hyd_all)<=$step_rows){
	$assay_form_hyds['has_more'] = 0;
}
foreach ($hyd_all as $hydRow => $hydRow_hyds) {
	$count = 0;
	foreach ($hydRow_hyds as $key => $tid) {
		$arow = array();
		$arow = get_hyd_data($tid);
		//如果是多合一的化验单，查询主项目的化验单显示
		$z_vid	= intval($dhy_arr[$arow['vid']]);
		if( $z_vid && $z_vid != $arow['vid']){
			$row	= $DB->fetch_one_assoc("SELECT `id` FROM `assay_pay` WHERE `cyd_id`='{$arow['cyd_id']}' AND `vid`='$z_vid'");
			$arow	= get_hyd_data($row['id']);
		}
    	$assay_form = get_assay_form($arow);
		$assay_form_hyds[$hydRow][$tid]['items'] = temp('/hyd/assay_form_qz_items');
    	$row_name  = $arow[$key_name['row_name']];
		$item_name = $arow[$key_name['item_name']];
		$assay_form_hyds[$hydRow][$tid]['row_name']		= $row_name;
		$assay_form_hyds[$hydRow][$tid]['item_name']	= $item_name;
	}
	$assay_form_hyds[$hydRow]['hydRow']		= temp('/hyd/assay_form_qz_row');
	$assay_form_hyds[$hydRow]['cyd_bh']		= $arow['cyd_bh'];
	$assay_form_hyds[$hydRow]['group_name'] = $arow['group_name'];
	$assay_form_hyds[$hydRow]['value_name'] = $arow['assay_element'];
}
echo json_encode($assay_form_hyds);
