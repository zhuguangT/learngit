<?php
/**
 * 功能：生成化验单
 * 作者：zhengsen
 * 时间：2016-04-10
**/
include '../temp/config.php';
include 'create_hyd_function.php';
include_once INC_DIR . 'cy_func.php';
$fzx_id = FZX_ID;
$cyd_id = intval($cyd_id);
// 执行生成化验单的开始时间
$_begin = microtime(true);
// 同一个批次，不同水样类型、是否有不同的方法都存储在此数组中
$vid_xmfa_arr = array();
if( $cyd_id ){
	// 获取采样单信息
	$cyd = get_cyd( $cyd_id );
	if( $cyd['status'] >= 6 ){
		goback("化验单已经生成！请刷新重试");
	}
	// 这批任务的现场项目
	$xc_exam_value_arr = empty($cyd['xc_exam_value']) ? array() : explode(',',$cyd['xc_exam_value']);
	// 项目分包的具体信息
	$xmfb_data = json_decode($cyd['xmfb'],true);
	// 所有检测项目信息
	$jcxm_vids_arr= array();
	// 化验单数与化验任务数
	$assay_pay_nums = $hyd_nums = 0;
	// 根据本批次cy_rec表所有任务数据来生成化验数据
	$query=$DB->query("SELECT * FROM `cy_rec` WHERE `cyd_id`='{$cyd_id}' AND `status`='1'");
	while($row=$DB->fetch_assoc($query)){
		// 现场平行 5,25,45,65
		if( !in_array( $row['zk_flag'], $global['xcpx_flag'] ) ){
			$xcpx_arr = array();
		}else{
			$xmpx_sql = "SELECT `assay_values` FROM `cy_rec` WHERE `cyd_id`='{$cyd_id}' AND `sid`='{$row['sid']}' AND `zk_flag`='-6'";
			$xcpx_value = $DB->fetch_one_assoc($xmpx_sql);
			$xcpx_arr = explode(',',$xcpx_value['assay_values']);
		}
		// 生成assay_order表数据
		$data = create_assay_order( $row, $xc_exam_value_arr, $xcpx_arr, $cyd['snkb'], $jcxm_vids_arr, $xmfb_data);
		// 这批任务所有水样类型下的项目
		$jcxm_vids_arr=$data['jcxm_vids_arr'];
		// 化验任务数
		$hyd_nums += $data['hyd_nums'];
	}
	// 错误提醒
	$error_msg = array();
	// 化验单数
	$assay_pay_nums+=create_assay_pay($cyd_id,$jcxm_vids_arr);//assay_pay表插入的条数
	// 生成化验单总耗时
	$cost_time = round(microtime(true) - $_begin, 2);//生成化验单总耗时
	// 更新采样任务状态
	$DB->query("UPDATE `cy` SET `status`=6 WHERE `id`='{$cyd_id}' AND fzx_id='{$fzx_id}'");
	// 返回状态提示
	prompt("共生成{$assay_pay_nums}张化验单,{$hyd_nums}个化验任务！耗时{$cost_time}秒。".implode('，', $error_msg));
}else{
	goback('采样单ID错误，请重试！');
}
gotourl("fp_csrw.php?cyd_id={$cyd_id}");    
