<?php
/*
*功能：显示任意查询的报告信息
*作者：zhengsen
*时间：2015-02-04
 */

include_once '../temp/config.php';
include INC_DIR . "cy_func.php";
if($u['userid']==''){
	nologin();
}
$fzx_id=$u['fzx_id'];
if(!empty($_POST['vid'])){
	$vid_arr=$_POST['vid'];
	$vid_str=implode(',',$vid_arr);
}else{
	echo "<script>alert('请先选择化验项目'); window.close();</script>";
}
//print_rr($btcs_arr);
//查询下化验单数据在什么状态下能显示到报告上
$show_shuju_arr	= array();
$show_shuju_old	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='show_shuju' ORDER BY id DESC LIMIT 1");
if(!empty($show_shuju_old['module_value1'])){
	$show_shuju_arr	= explode(",",$show_shuju_old['module_value1']);
}
//查询出每种水样类型下项目的名称
$jcbz_sql="SELECT aj.vid,n.module_value2 as water_type,aj.value_C FROM n_set n JOIN assay_jcbz aj ON n.id=aj.jcbz_bh_id WHERE module_name='jcbz_bh' AND module_value3='1' ";
$jcbz_query=$DB->query($jcbz_sql);
while($jcbz_rs=$DB->fetch_assoc($jcbz_query)){
	$jcbz[$jcbz_rs['water_type']][$jcbz_rs['vid']]=$jcbz_rs['value_C'];
}
//查询出所有的项目名称
$xm_sql="SELECT id,value_C FROM assay_value";
$xm_query=$DB->query($xm_sql);
while($xm_rs=$DB->fetch_assoc($xm_query)){
	$xm[$xm_rs['id']]=$xm_rs['value_C'];
}
//查询要输出的站点和项目数据
if(!empty($_GET['fzx_id'])&&$_GET['fzx_id']!="全部"){
	$fzx_str="AND c.fzx_id='".$_GET['fzx_id']."'";
}
$vid_arr=$site_arr=array();
foreach($_POST['sites'] as $group_name_key=>$sites_arr){
	$site_arr=array_unique(array_merge($site_arr,$sites_arr));
	$site_str=implode(',',$sites_arr);
	$sql="SELECT cr.*,ao.cid,ao.id as aid,ao.vd0,ao.ping_jun,ao.vid,ao.hy_flag,c.cy_date,s.st_type,s.site_name,ap.over,ap.unit,ap.jc_xz,s.sgnq_code,s.sgnq,s.xz_area,cr.water_height,cr.liu_l,cr.qi_wen
    FROM cy c LEFT JOIN cy_rec cr ON cr.cyd_id = c.id LEFT JOIN assay_order AS ao ON  ao.cid=cr.id LEFT JOIN sites AS s ON s.id = ao.sid  LEFT JOIN site_group sg ON s.id=sg.site_id LEFT JOIN assay_pay ap  ON ao.tid=ap.id WHERE c.cy_date BETWEEN '".$_POST['begin_date']."' AND '".$_POST['end_date']."' AND
    c.site_type = '".$_POST['site_type']."' AND s.id IN (".$site_str.") AND ao.vid IN (".$vid_str.") AND cr.zk_flag >= '0'	 AND ao.hy_flag>=0 AND sg.group_name='".$group_name_key."' group by aid  ORDER BY c.cy_date, cr.bar_code";
	$query=$DB->query($sql);
	while($rs=$DB->fetch_assoc($query)){
		//项目数组
		$max_water_type=get_water_type_max($rs['water_type'],$u['fzx_id']);
		if(empty($jcbz[$max_water_type][$rs['vid']])){
			$xm_arr[$rs['vid']]=$xm[$rs['vid']];//防止在assay_jcbz表里没有初始化某项目导致项目为空（初始化完成后应该去掉）
		}else{
			$xm_arr[$rs['vid']]=$jcbz[$max_water_type][$rs['vid']];
		}
		if(!in_array($rs['vid'],$vid_arr)){
			$vid_arr[$rs['vid']]=$rs['vid'];
		}		
		$jc_xz_arr[$rs['vid']]=$rs['jc_xz'];//项目的检测限值
		$unit_arr[$rs['vid']]=$rs['unit'];//项目单位
		//水样类型
		$result[$rs['id']]['water_type']=$rs['water_type'];
		//表头数据
		$result[$rs['id']]['sgnq_code']=$rs['sgnq_code'];//水功能区序号
		$result[$rs['id']]['sgnq']=$rs['sgnq'];//水功能区名称
		$result[$rs['id']]['site_name']=$rs['site_name'];//控制断面
		$result[$rs['id']]['xz_area']=$rs['xz_area'];//行政区
		$result[$rs['id']]['cy_date']=$rs['cy_date'];//采样日期
		$result[$rs['id']]['cy_time']=substr($rs['cy_time'],0,5);//采样时间
		$result[$rs['id']]['water_height']=$rs['water_height'];//水位
		$result[$rs['id']]['liu_l']=$rs['liu_l'];//流量
		$result[$rs['id']]['qi_wen']=$rs['qi_wen'];//流量/蓄水量
		//化验项目数据
		if(!empty($show_shuju_arr) && !in_array($rs['over'],$show_shuju_arr)){
			$vd0	= '';
		}else{
			if(!empty($rs['ping_jun'])&&$global['bg_pingjun']) {
				$vd0 = $rs['ping_jun'];
			}else{
				$vd0 =$rs['vd0'];
			}
			if(in_array($rs['vid'],$global['modi_data_vids'])&&$vd0<='0'){
				$vd0='未检出';
			}
			$vd0 = str_replace(" ","",$vd0);
		}
		$result[$rs['id']]['vid'][$rs['vid']]['vd0']=$vd0;
		$result[$rs['id']]['vid'][$rs['vid']]['unit']=$rs['unit'];

	}
}
//print_rr($site_arr);exit();
//print_rr($result);exit();
//成果表格最多23列
if(empty($_POST['row_max'])){
	define("COL_MAX",20);
}else{
	define("COL_MAX",$_POST['row_max']);
}
//所有的项目个数
ksort($vid_arr);//对数组进行重排序
sort($vid_arr);
//print_rr($xm_px_arr);
if(!empty($xm_px_arr)){
	$vid_arr_temp=array();
	foreach($xm_px_arr as $key=>$value){
		if(in_array($value,$vid_arr)){
			$temp_k=array_search($value,$vid_arr);
			$vid_arr_temp[]=$vid_arr[$temp_k];
			unset($vid_arr[$temp_k]);
		}
	}
	$vid_arr=$vid_arr_temp+$vid_arr;
}
$vid_total=count($vid_arr);
//print_rr($vid_arr);exit();
$line_size_first= COL_MAX;//第一页最多可容纳的化验项目数
$line_size_other= COL_MAX+count($_POST['cgb_bt_cs']);

if($vid_total>$line_size_first){
	$other_vid_total=$vid_total-$line_size_first;
	$parts=1+ceil($other_vid_total/$line_size_other);//循环所有项目所需要的页数
	//需要增加的空白td
	$add_tds=($parts-1)*$line_size_other+$line_size_first-$vid_total;
}else{

	$parts=1;//循环所有项目所需要的页数
	//需要增加的空白td
	$add_tds=$parts*$line_size_first-$vid_total;
}

//标题的显示
//$title = $_POST['begin_date'] . "至" . $_POST["end_date"]. get_site_list($site_arr,3) . "等站点水质监测成果表";

$title=$_POST['cgb_title'];
$page_data = array();
$k=0;
for( $i = 1; $i<= $parts; $i++ ) {
	//每一个的标题显示
	if($parts==1){
		$page_data[$i]['title']="<h3 style='margin:0 auto;text-align:center'>" . $title . "</h1>";
	}else{
		$page_data[$i]['title']="<h3 style='margin:0 auto;text-align:center'>" . $title . $c_num[$i] . "</h1>";
	}
    //$table  = '<table class="single" style="width:26cm"><tr align="center"><td>序<br />号</td>';
	$table  = "<tr align='center'><td>序<br />号</td>";
	if($i==1){
		//只有第一页才显示表头参数
		foreach($_POST['cgb_bt_cs'] as $key=>$value){
				$table.="<td>".$value."</td>";//表头显示的参数（$bt）
		}
	}
	//下面获取每一页的项目
	if($i==$parts){
		$end_key=count($vid_arr);
	}else{
		if($i==1){
			$end_key=$line_size_first;
		}else{
			$end_key=$line_size_first+($i-1)*$line_size_other;
		}
	}

	for($k; $k < $end_key; $k++) {
		$table .= "<td>{$xm_arr[$vid_arr[$k]]}{$k}<br />{$unit_arr[$vid_arr[$k]]}</td>";
	}
	if($i==$parts&&$add_tds){
		for($a=1;$a<=$add_tds;$a++){
			$table.="<td>&nbsp;</td>";
		}
	}
	$table.= "</tr>";

    $page_data[$i]['table_header'] = $table;
}
if(empty($_POST['col_max'])){
	$page_size=15;//每页显示的行数
}else{
	$page_size=$_POST['col_max'];
}
//成果输出显示程序
$lines_total = count($result);
$page_total = ceil( $lines_total / $page_size );
//需要增加的tr
$add_trs=$page_total*$page_size-$lines_total;
$page = 1;
$xh = 0;
if(empty($result)){
	echo "<script>alert('没有查询任何数据'); window.close();</script>";
}
foreach($result as $key=>$value){
	$xh++;
	$k=0;
	for($i = 1; $i <= $parts; $i++) {
		if(!isset($body[$i])){
			$body[$i] = "";
		}
		$body[$i] .= "<tr><td align='center' width=\"20px\">{$xh}</td>";
		if($i==$parts){
			$end_key=count($vid_arr);
		}else{
			if($i==1){
				$end_key=$line_size_first;
			}else{
				$end_key=$line_size_first+($i-1)*$line_size_other;
			}
		}
		if($i==1){
			foreach($_POST['cgb_bt_cs'] as $k1=>$v1){
				$body[$i].="<td>{$value[$global['cgb_bt_cs'][$v1]]}</td>";
			}
		}
		for($k; $k<$end_key; $k++) {
			if(!isset($vid_arr[$k])){
				break;
			}
			$body[$i] .= "<td width=\"40px\" align=\"left\" style=\"mso-number-format:'\@'\">{$value[vid][$vid_arr[$k]][vd0]}</td>";
		}
		if($i==$parts&&$add_tds){
			for($a=1;$a<=$add_tds;$a++){
				$body[$i].="<td width=\"40px\">&nbsp;</td>";
			}
		}
		$body[$i] .= "</tr>";
		if( $xh % $page_size == 0 || $xh==$lines_total) {
			if($xh==$lines_total&&$add_trs){
				for($g=1;$g<=$add_trs;$g++){
					$body[$i].="<tr>";
					for($t=1;$t<=$line_size_other+1;$t++){
						$body[$i].="<td>&nbsp;</td>";
					}
					$body[$i].="</tr>";
				}
			}
			$table = "<table style='width:$bg_width;margin:0 auto;border-collapse:collapse'  border='1px'>".$page_data[$i]['title'] . $page_data[$i]['table_header'] . $body[$i] . "</table>";
			if($i==$parts){
				$table.=temp("any_data/any_sites_export_qz");
			}
			$table.="<div style=\"PAGE-BREAK-AFTER: always\"></div>";
			$page_data["pages"][$page][$i] = $table;
			$body[$i] = "";
			$page++;
		}
	}
}
if($_POST['sub']=='查看成果'){
	for($i=1; $i<=count($page_data["pages"]); $i++){
		for($j = 1; $j <= $parts; $j++ ){
			echo $page_data["pages"][$i][$j];
		}
	}
}else if($_POST['sub']=='下载成果'){
	header("Content-Type:   application/msexcel");        
    header("Content-Disposition:   attachment;   filename=监测成果表.xls");        
    header("Pragma:   no-cache");        
    header("Expires:   0");        
	for($i=1; $i<=count($page_data["pages"]); $i++){
		for($j = 1; $j <= $parts; $j++ ){
			echo $page_data["pages"][$i][$j];
		}
	} 
}
//echo temp("any_sites_result_export.html");
