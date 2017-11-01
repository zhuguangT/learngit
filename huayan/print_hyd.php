<?php
/**
 * 功能：打印化验单
 * 作者: 铁龙
 * 日期: 2014-05-22
 * 描述: 
 */
include '../temp/config.php';
include './assay_form_func.php';
$fzx_id	= FZX_ID;
$arow	= array();
$hyd_id	= intval($_GET['tid']);
if($hyd_id){
	$arow = get_hyd_data( $hyd_id );
}
if(empty($arow['id'])){
	header('location:'.$rooturl.'/huayan/ahlims.php?ajax=1&app=public&act=reto&content=该化验单不存在&class=danger');
	die;
}
header('location:'.$rooturl.'/huayan/ahlims.php?app=print&act=print_hyd&ajax=1&tid='.$arow['id']);
// 若复核人员已签字, 则将已打印字段置 1
if ( $arow['sign_03'] ) {
    $DB->query( "UPDATE `assay_pay` SET `printed`='1' WHERE `id`={$arow['id']} AND ( `fp_id` = '{$fzx_id}' OR `fzx_id` = '{$fzx_id}' ) " );
}
// 表格纵横板式
$zongheng	= $arow['zongheng'].'_biao';
// 表格纵横板式的宽度
$zongheng	= $$zongheng;
// 使用模板的名称
$table_name = $arow['table_name'];
// 获取行数据
$lines_data	= get_assay_hyd_line($hyd_id,$table_name,1);
// 站点解码
$zhanming	= ($global['hyd']['code_jiema']['is_jiema']&&$arow[$global['hyd']['code_jiema']['sign']]) ? '站 名' : '样品编号';
// 化验单模板文件地址
$plan_file_path = $global['hyd']['plan_file_path'];
// 这里添加  环境条件 的表格头部
$hjtj_bt = temp($plan_file_path.'hjtj_bt');
// 获取化验单签字表单
$assay_sign_form = get_assay_form_sign($arow);
// 获取line模板
$line_template = temp($plan_file_path.'line_'.$table_name);
// 声明$aline数据变量
$aline = '';
// 获取plan内容
$plan = temp( $plan_file_path.'plan_'.$table_name);
// 获取表单内容
$plan_template = temp('hyd/assay_form_hyd');
// 提取extraxjs的内容
preg_match("/<script.*extrajs.*>(.*)<\/script>/isU", $plan_template, $extrajs);
// 清除表单内容中的js代码
$plan_template = preg_replace('/<script.*>(.*)<\/script>/isU','',$plan_template);
// 获取关联的PDF图谱
$hyd_pdf = array();
$query = $DB->query("SELECT `pid`  FROM `hydpdf` WHERE `tid` = '{$hyd_id}'");
while ($row = $DB->fetch_assoc($query)) {
	$hyd_pdf[] = $rooturl.'/huayan/view_pdf.php?ajax=1&handle=see&pid='.$row['pid'];
}
$hyd_pdf = json_encode($hyd_pdf);
$json_lines = json_encode($lines_data);
$trade_global = json_encode($trade_global);
echo temp('hyd/print_hyd');
?>