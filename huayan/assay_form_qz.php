<?php
/**
 * 功能：显示化验单
 * 作者: Mr Zhou
 * 日期: 2015-05-17
 * 描述:
*/
header("Pragma: no-cache");
if(!in_array(trim($_GET['qz']),array('jh','fh','sh'))){
	prompt('请求错误！');
	gotourl($url[1]);
}
include ('../temp/config.php');
require ('./assay_form_func.php');
//导航
$trade_global['daohang']	= array(array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'));
$trade_global['js']			= array('date-time/bootstrap-datepicker.min.js','date-time/bootstrap-timepicker.min.js','jquery.maskedinput.min.js');
$trade_global['css']		= array('lims/main.css','datepicker.css','bootstrap-timepicker.css');
$fzx_id=FZX_ID;
$tid = intval($_GET['tid']);
if(!$tid){
	prompt('未提供正确化验单编号');
	gotourl($url[1]);
}
$_SESSION['begin_url'] = $current_url;

//查询表格模板名称
$sql = "SELECT `id`,`table_name` FROM `bt_muban` WHERE 1";
$query = $DB->query($sql);
while ($row = $DB->fetch_assoc($query)) {
	$bt_muban[$row['id']] = $row['table_name'];
}

//获得请求动作
$action		= trim($_GET['qz']);
//只检索最近三个月内数据
$last_date	= date('Y-m-01',strtotime('-2 months'));
$sh_config	= $global['hyd']['sh_config'];//'sh_config'=> array('jh'=>array('v1','校核','已完成','02'))

$limit_step = 3;
$list_type	= 'xm';//xm|pc 项目分组还是批次分组
$list_type_set = array(
	'pc' => array(
		'group_key'	=> 'cyd_id',
		'row_name'	=> 'cyd_bh',
		'item_name'	=> 'assay_element',
		'order_by'	=> '`cyd_id`,a.`vid`,a.`id`'
		),
	'xm' => array(
		'group_key'	=> 'vid',
		'row_name'	=> 'assay_element',
		'item_name'	=> 'cyd_bh',
		'order_by'	=> 'a.`vid`,`cyd_id`,a.`id`'
		)
);
$key_name	= $list_type_set[$list_type];

$hyd_all = array();
//本张化验单优先显示
$this_assay = $DB->fetch_one_assoc("SELECT `id`,`vid`,`cyd_id`,`over` FROM `assay_pay` WHERE `id`='$tid' AND `fzx_id`='$fzx_id' AND `vid` IN('{$u['user_other'][$sh_config[$action][0]]}') ");
//判断本张化验单是否符合状态
/* 之所以不将条件放在SQL查询中是因为传递过来的化验单可能不符合状态，查询不出来，导致无法查询剩下的该批次或者该项目的任务 */
if($sh_config[$action][2] == $this_assay['over']){
	$hyd_all[$this_assay[$key_name['group_key']]] = array($tid);
}
$header_title	= $sh_config[$action][1].'任务列表';
$trade_global['daohang'][1]	= array('icon'=>'','html'=>$header_title,'href'=>$current_url);
$sql	= "SELECT a.`id`,a.`vid`,a.`cyd_id`,c.`cyd_bh` FROM `assay_pay` AS a LEFT JOIN `cy` AS c ON a.`cyd_id`=c.`id` WHERE a.`fzx_id`='$fzx_id' AND a.`is_xcjc`='0' AND c.`cy_date` > '$last_date' AND a.`id` != '$tid' AND a.`over` = '{$sh_config[$action][2]}' AND `vid` IN('{$u['user_other'][$sh_config[$action][0]]}') AND `{$key_name['group_key']}`='{$this_assay[$key_name['group_key']]}' ORDER BY {$key_name['order_by']}";
$query=$DB->query($sql);
while( $row = $DB->fetch_assoc( $query ) ){
	$hyd_all[$row[$key_name['group_key']]][] = $row['id'];
}
//如果该页面的化验单数少于3张则继续加载$limit_step(3)张
if(count($hyd_all[$this_assay[$key_name['group_key']]])<3){
	$hyd_all_str = count($hyd_all)>0 ? implode(',', $hyd_all[$this_assay[$key_name['group_key']]]) : '0';
	$sql = "SELECT a.`id`,a.`vid`,a.`cyd_id`,c.`cyd_bh` FROM `assay_pay` AS a LEFT JOIN `cy` AS c ON a.`cyd_id`=c.`id` WHERE a.`id` NOT IN ($hyd_all_str) AND a.`fzx_id`='$fzx_id' AND a.`is_xcjc`='0' AND c.`cy_date` > '$last_date' AND a.`over` = '{$sh_config[$action][2]}' AND `vid` IN('{$u['user_other'][$sh_config[$action][0]]}') ORDER BY {$key_name['order_by']} LIMIT $limit_step";
	$query=$DB->query($sql);
	while( $row = $DB->fetch_assoc( $query ) ){
		$hyd_all[$row[$key_name['group_key']]][] = $row['id'];
	}
}
$hyd_total	= 0; //化验单个数统计
$step_rows	= '';//快速切换项目位置
$hyd_id_arr	= array();//记录所有的化验单id
$assay_form_hyds= '';//所有的化验单内容
foreach ($hyd_all as $hydRow => $hydRow_hyds) {
	$nav_child = '';
	$count = count($hydRow_hyds);
	foreach ($hydRow_hyds as $key => $tid) {
		$hyd_total++;
		$arow = array();
		$hyd_id_arr[] = $tid;
		$arow = get_hyd_data($tid);
		//如果是多合一的化验单，查询主项目的化验单显示
		if(in_array($arow['vid'], explode(',',$dhy_arr['str2']))){
			$z_vid	= $dhy_arr[$arow['vid']];
			$row	= $DB->fetch_one_assoc("SELECT `id` FROM `assay_pay` WHERE `cyd_id`='{$arow['cyd_id']}' AND `vid`='$z_vid'");
			$arow	= get_hyd_data($row['id']);
		}
		$assay_form = get_assay_form($arow);
		$items .= temp('/hyd/assay_form_qz_items');
		$row_name = $arow[$key_name['row_name']];
		$item_name = $arow[$key_name['item_name']];
		$nav_child .= '<li><a href="#hydItem_'.$tid.'"><i class="icon-check-empty"></i>&nbsp;'.$item_name.'（'.$tid.'）</a></li>';
	}
	$assay_form_hyds .= temp('/hyd/assay_form_qz_row');
	$step_rows .= '<li id="nav_rows_'.$hydRow.'"><a href="#hydRow_'.$hydRow.'">'.$row_name.'（ 共 <span class="hydRowSign">0</span>/<span class="hydRowCount">'.$count.'</span> 张）</a><ul class="nav">'.$nav_child.'</ul></li>';
	$items = '';
}
disp('hyd/assay_form_qz');
?>