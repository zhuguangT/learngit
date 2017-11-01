<?php
/**
 * 功能：更新化验单
 * 作者：铁龙
 * 日期：2014-04-14
 * 描述：化验单的计算和保存
*/
$tid = intval($_POST['tid']);
if($tid){
	$arow = $DB->fetch_one_assoc("SELECT * FROM `assay_pay` WHERE `id`='{$tid}' AND ( `fp_id` = '{$fzx_id}' OR `fzx_id` = '{$fzx_id}' ) LIMIT 1");
}
//判断是否有权利修改化验单
if( !in_array($u['id'],array($arow['uid'],$arow['uid2'])) && !$u['admin'] ){
	error_msg('你不是化验员本人，不能进行修改操作！');
}
if( 'save_sign' != $_POST['submit_flag'] && !empty($arow['sign_01']) && !$u['admin'] ){
	error_msg('该化验单已被（<span class="green">'.$arow['sign_01'].'</span>）签字，不能进行修改，请刷新页面查看。');
}
//修改化验单表头内容
$key_val_arr = array();
for($i=0; $i <= 34; $i++){
	if(isset($_POST['td'.$i])){
		$key_val_arr[$i] = "`td$i` = '".trim($_POST['td'.$i])."'";
	}
}
$key_val_arr[] = "`btdata`='".JSON($_POST['btdata'])."'";
$key_val_str = implode(',', $key_val_arr);
if(!empty($key_val_str)) {
	$DB->query( "UPDATE `assay_pay` SET {$key_val_str} WHERE `id`='{$arow['id']}' ORDER BY `id` LIMIT 1" );
}
$task_id = array();
//对每一个化验任务进行处理
foreach($_POST['mission'] as $key => $missid){
	$vdx = array();
	$oid = $task_id[] = $_POST['mission'][$key];
	//处理通过js进行计算的原始结果
	if(!empty($_POST['_vd0'][$key])){
		$vdx['_vd0']=$_POST['_vd0'][$key];
	}
	//循环取出每一列的值
	for( $i = 0; $i <= 30; $i++ ) {
		if(isset($_POST['vd'.$i])) {
			$vdx['vd'.$i] = trim($_POST['vd'.$i][$key]);
		}
	}
	if($oid>1){
		calc_a_task($oid,$vdx,$arow['id'],$arow['vid']);
	}
}
// 化验结果计算结束,检查质控
foreach($task_id as $i => $oid){
	check_zhi_kong($oid,$arow['td3']);
}