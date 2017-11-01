<?php
/**
 * 功能：系统权限名称配置页面
 * 作者：韩枫
 * 日期：2014-04-04
 * 描述：
*/
$qx = array();
$qx['管理者功能界面']['jindu_manage'] = '任务进度管理';
$qx['管理者功能界面']['static'] = '采样到位查看';
//$qx['站网维护']['site_manage'] = '站点管理';
$qx['任务下达']['xd_cy_rw'] = '下达检测任务';
$qx['任务下达']['xd_csrw'] = '检测任务列表';

$qx['样品采集及验收']['cy'] ='样品采集';
$qx['样品采集及验收']['ypjs'] = '样品接收';
$qx['样品采集及验收']['serach_bar'] = '样品搜索';

$qx['基础试验']['quxian_zz'] = '标准曲线制作';
$qx['基础试验']['quxian_jh'] = '标准曲线校核';
$qx['基础试验']['quxian_fh'] = '标准曲线复核';
$qx['基础试验']['quxian_sh'] = '标准曲线审核';

$qx['样品检测及审核']['hua_yan'] = '样品检测';
$qx['样品检测及审核']['jh'] = '数据校核';
$qx['样品检测及审核']['fh'] = '数据复核';
$qx['样品检测及审核']['sh'] = '数据审核';

//$qx['质量控制'][zhikong_tb] = '质控月报表填表';
$qx['质控审核']['zhikong_jh'] = '质控月报表校核';
$qx['质控审核']['zhikong_sh'] = '质控月报表审核';
$qx['质控审核']['zhikong_hlx_fx'] = '数据合理性分析';

$qx['检测报告']['jcbg_tb'] = '检测报告填表';
$qx['检测报告']['jcbg_sh'] = '检测报告审核';
$qx['检测报告']['jcbg_qf'] = '检测报告签发';

$qx['数据统计']['tongji_bg1'] = '水质公报';
$qx['数据统计']['tongji_bg2'] = '常规月报';
$qx['数据统计']['any_search'] = '任意成果查询';
$qx['数据统计']['site_map'] = '站点地图';

$qx['综合管理']['yiqi_manage'] = '仪器管理';
$qx['综合管理']['bzwz_manage'] = '标准物质';
$qx['综合管理']['kufang_manage'] = '库房管理';
$qx['综合管理']['wenjian_manage'] = '文件管理';
$qx['综合管理']['gys_manage'] = '供应商管理';

$qx['人员管理']['user_file_manage'] = '人员档案管理';
$qx['人员管理']['user_work_manage'] = '上岗项目统计';
$qx['人员管理']['task_total'] = '工作量统计';
$qx['人员管理']['qz_yanchi'] = '延迟签字统计表';

//当`hub_info`.is_zz = '1',且 $fzx_id = `hub_info`.id时，执行 $qx['分中心管理']['baogao'] = '分中心报告查看';
$ab = $DB->query("select id from `hub_info` where is_zz = '1'");
$fzx_id_arr = mysql_fetch_array($ab);
$fzx_count_sql	= $DB->query("SELECT `id` FROM `hub_info` WHERE `parent_id`='{$fzx_id}'");
$fzx_count	= $DB->num_rows($fzx_count_sql);
//var_dump($fzx_id_arr);
//die;
if($fzx_count>0 && @in_array($fzx_id, $fzx_id_arr)){
	$qx['分中心管理']['baogao'] = '分中心报告查看';
	$qx['分中心管理']['fp_site'] = '分中心站点管理';
}
$qx['系统设置']['user_manage'] = '权限管理';
$qx['系统设置']['xmfa'] = '检验方法配置';
//$qx['系统设置']['assay_value'] = '化验项目设置';
$qx['系统设置']['assay_jcbz'] = '检测标准限值设置';
$qx['系统设置']['zk_set'] = '质控范围设置';
$qx['系统设置']['hua_sh_set'] = '审核设置';
$qx['系统设置']['set_value'] = '自定义项目排序';
$qx['系统设置']['cyrq_set'] = '采样容器设置';
//$qx['系统设置']['system_gx'] = '系统个性化配置';

$qx['系统管理员']['system_admin'] = '系统管理员';

$i = 0;
$qx_keys = $qx_name = $qx_one_arr = array();
foreach($qx as $key=>$value){
	$i++;
	$qx_keys = array_merge($qx_keys,array_keys($value)); //仅有权限 的数据库字段名称
	$qx_name = array_merge($qx_name,array_values($value));//仅有权限 的汉字名称
	//这个数组是为了减少其他地方的循环  原数组很多地方都正用着暂时不改
	$qx_one_arr["group".$i] = $key;
          $qx_one_arr = array_merge($qx_one_arr,$value);//权限一维数组array([group1]=>'站网维护',[site_manage]=>'站点管理',[group2]='任务下达',[xd_cy_rw]...)
	
}
#下面两个数组都是以数字为索引，一个值为汉字权限名，一个值为数据库中所保存的英文权限名
//$qxname=array_values($qx);
//$qxKey =array_keys($qx);

?>
