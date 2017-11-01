<?php
include "../temp/config.php";
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="sites.csv"');
header('Cache-Control: max-age=0'); 
$sitesql = "select site_name,water_type,site_code,fzx_id,fp_id,site_type,tjcs,area,xz_area,site_address,water_system,sgnq,sgnq_code,sgnq_type,site_line,site_vertical,jingdu,weidu from sites where 1 order by water_type";
$water_type_arr = array();
$sql_water_type = $DB->query("SELECT * FROM `leixing` where (`fzx_id`='$fzx_id' OR `fzx_id`='0') AND `act`='1'");
while($rs_water_type= $DB->fetch_assoc($sql_water_type))
{
   $water_type_arr[$rs_water_type['id']] = $rs_water_type['lname'];
}
//分中心
$sql_fzx    = $DB->query("SELECT * FROM `hub_info` WHERE 1");
while($rs_fzx= $DB->fetch_assoc($sql_fzx))
{
   $fzx_arr[$rs_fzx['id']]  = $rs_fzx['hub_name'];
}
######任务类型对应id
$site_type_arr = array('0'=>'监督任务','1'=>'常规任务','2'=>'临时任务','3'=>'委托任务');
#####垂线 对应的编号
$site_line_arr = array('1'=>"左",'2'=>"中",'3'=>"右");

#####层面 对应的编号
$site_vertical_arr  = array('1'=>"上",'2'=>"中",'3'=>"下");

####统计参数的名称及id
$tjcs_arr   = array();
$sql_tjcs   = $DB->query("SELECT * FROM `n_set` WHERE module_name='tjcs'");
while ($rs_tjcs= $DB->fetch_assoc($sql_tjcs)) 
{
   $tjcs_arr[$rs_tjcs['id']] = $rs_tjcs['module_value1'];
}


// 从数据库中获取数据，为了节省内存，不要把数据一次性读到内存，从句柄中一行一行读即可
$stmt = $DB->query($sitesql );

// 打开PHP文件句柄，php://output 表示直接输出到浏览器
$fp = fopen('php://output', 'a');

// 输出Excel列名信息
$head = array('站点名称', '水样类型', '站点编码', '创建分中心', '分配分中心', '任务类型', '统计参数', '流域', '行政区', '站点地址', '水系', '水功能区', '水功能区编码', '水功能区类型', '垂线', '层面', '经度', '纬度');
foreach ($head as $i => $v) {
// CSV的Excel支持GBK编码，一定要转换，否则乱码
$head[$i] = iconv('utf-8', 'gbk', $v);
}

// 将数据通过fputcsv写到文件句柄
fputcsv($fp, $head);

// 计数器
$cnt = 0;
// 每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
$limit = 100000;

// 逐行取出数据，不浪费内存
while ($row = $DB->fetch_assoc($stmt)) {

$cnt ++;
if ($limit == $cnt) { //刷新一下输出buffer，防止由于数据过多造成问题
ob_flush();
flush();
$cnt = 0;
}

foreach ($row as $i => $v) {
if($i == 'water_type'){
	$v=$water_type_arr[$v];
}
if($i == 'site_type'){
	$v=$site_type_arr[$v];
}
if($i == 'site_line'){
	$v=$site_line_arr[$v];
}
if($i == 'site_vertical'){
	$v=$site_vertical_arr[$v];
}
if($i == 'fzx_id'){
	$v=$fzx_arr[$v];
}
if($i == 'fp_id'){
	$v=$fzx_arr[$v];
}
$v2 ='';
$varr =array();
if($i == 'tjcs'){
	$varr = explode(',',$v);
	foreach($varr as $v1){
		$v2.=','. $tjcs_arr[$v1];
	}
	$v = $v2;
}
$row[$i] = iconv('utf-8', 'gbk', $v);
}
fputcsv($fp, $row);
} 
?>