<?php
//强化检测报表
include_once '../temp/config.php';
include INC_DIR . "cy_func.php";
include '../baogao/bg_func.php';
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
$btcs_arr=array('cy_date'=>array('采样日期','年月日'),'cy_time'=>array('采样时间','时分'),'water_height'=>array('水位','m'),'liu_l'=>array('流量/蓄水量','m³/s/亿m³'),'qi_wen'=>array('气温','℃'));
//print_rr($btcs_arr);
//查询下化验单数据在什么状态下能显示到报告上
$show_shuju_arr	= array();
$show_shuju_old	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='show_shuju' ORDER BY id DESC LIMIT 1");
if(!empty($show_shuju_old['module_value1'])){
	$show_shuju_arr	= explode(",",$show_shuju_old['module_value1']);
}
//查询出每种水样类型下项目的名称
$jcbz_sql="SELECT aj.vid,n.module_value2 as water_type,n.module_value1 as jcbz_name,aj.value_C,aj.xz,aj.panduanyiju FROM n_set n JOIN assay_jcbz aj ON n.id=aj.jcbz_bh_id WHERE module_name='jcbz_bh' AND module_value3='1' ";
$jcbz_query=$DB->query($jcbz_sql);
$jcbz_name_arr	= '';
while($jcbz_rs=$DB->fetch_assoc($jcbz_query)){
	$jcbz_name_arr[$jcbz_rs['water_type']]	= $jcbz_rs['jcbz_name'];
	$jcbz[$jcbz_rs['water_type']][$jcbz_rs['vid']]=$jcbz_rs['value_C'];
	if(!empty($jcbz_rs['panduanyiju'])){
		$pdyj_arr=json_decode($jcbz_rs['panduanyiju'],true);
		if(!empty($pdyj_arr)){
			$pd_jcxz_arr[$jcbz_rs['water_type']][$jcbz_rs['vid']]	= $pdyj_arr;
		}else{
			$pd_jcxz_arr[$jcbz_rs['water_type']][$jcbz_rs['vid']]	= $jcbz_rs['panduanyiju'];
		}
	}else{
		$pd_jcxz_arr[$jcbz_rs['water_type']][$jcbz_rs['vid']]=$jcbz_rs['xz'];
	}
	$jcxz_arr[$jcbz_rs['water_type']][$jcbz_rs['vid']]=$jcbz_rs['xz'];
}
//查询出所有的项目名称
$xm_sql="SELECT id,value_C FROM assay_value";
$xm_query=$DB->query($xm_sql);
while($xm_rs=$DB->fetch_assoc($xm_query)){
	$xm[$xm_rs['id']]=$xm_rs['value_C'];
}
$vid_arr=$site_arr=$cy_date_arr=$xm_arr=array();
$now_vids	= $jiance_fangfa	= array();
$no_ok_arr	= array();//不合格项目统计
$jcbz_name	= '';//检测标准的名称
foreach($_POST['sites'] as $group_name_key=>$sites_arr){
	$site_arr=array_unique(array_merge($site_arr,$sites_arr));
	$site_str=implode(',',$sites_arr);
	$sql="SELECT cr.*,ao.cid,ao.id as aid,ao.vd0,ao.ping_jun,ao.vid,ao.hy_flag,c.cy_date as c_date,s.syxz,s.st_type,s.site_name,ap.td2,ap.over,ap.unit,ap.jc_xz,s.sgnq_code,s.sgnq,s.xz_area,cr.water_height,cr.liu_l,
	cr.qi_wen FROM cy c LEFT JOIN cy_rec cr ON cr.cyd_id = c.id LEFT JOIN assay_order AS ao ON  ao.cid=cr.id LEFT JOIN sites AS s ON s.id = ao.sid  LEFT JOIN assay_pay ap  ON ao.tid=ap.id WHERE c.cy_date BETWEEN '".$_POST['begin_date']."' AND '".$_POST['end_date']."' AND
    c.site_type = '".$_POST['site_type']."' AND s.id IN (".$site_str.") AND ao.vid IN (".$vid_str.") AND cr.zk_flag >= '0'	 AND ao.hy_flag>=0 AND c.group_name='".$group_name_key."' group by aid,cr.water_type  ORDER BY cr.water_type,c.cy_date, cr.bar_code";
	$query=$DB->query($sql);
	while($rs=$DB->fetch_assoc($query)){
		$jcfa_str	= trim($rs['td2']);
		//记录下检测方法，报告上需要体现
		$jcfa_str	= substr($jcfa_str,0,strripos($jcfa_str,' '));
		if(!in_array($jcfa_str,$jiance_fangfa)){
			$jiance_fangfa[]	= $jcfa_str;
		}
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
		if(empty($unit_arr[$rs['vid']])){
			$unit_arr[$rs['vid']]=$rs['unit'];//项目单位
		}
		//采样时间
		if(!empty($rs['c_date'])&&$rs['c_date']!='0000-00-00'&&!in_array($rs['c_date'],$cy_date_arr)){
			$cy_date_arr[] = $rs['c_date'];//结束日期
		}
		//水样类型
		$result[$rs['water_type']][$rs['id']]['water_type']=$rs['water_type'];
		//表头数据
		$result[$rs['water_type']][$rs['id']][bt]['sgnq_code']=$rs['sgnq_code'];//水功能区序号
		$result[$rs['water_type']][$rs['id']][bt]['sgnq']=$rs['sgnq'];//水功能区名称
		$result[$rs['water_type']][$rs['id']][bt]['site_name']=$rs['site_name'];//控制断面
		$result[$rs['water_type']][$rs['id']][bt]['xz_area']=$rs['xz_area'];//行政区
		$result[$rs['water_type']][$rs['id']][bt]['cy_date']=$rs['c_date'];//采样日期
		$result[$rs['water_type']][$rs['id']][bt]['cy_time']=substr($rs['cy_time'],0,5);//采样时间
		$result[$rs['water_type']][$rs['id']][bt]['water_height']=$rs['water_height'];//水位
		$result[$rs['water_type']][$rs['id']][bt]['liu_l']=$rs['liu_l'];//流量
		$result[$rs['water_type']][$rs['id']][bt]['qi_wen']=$rs['qi_wen'];//流量/蓄水量
		//化验项目数据
		if(!empty($show_shuju_arr) && !in_array($rs['over'],$show_shuju_arr)){
			$vd0	= '';
		}else{
			if(!empty($rs['ping_jun'])&&$global['bg_pingjun']){
				$vd0 = $rs['ping_jun'];
			}else{
				$vd0 =$rs['vd0'];
			}
			if(in_array($rs['vid'],$global['modi_data_vids'])&&$vd0<='0'&&$vd0!=''){
				$vd0='未检出';
			}
			$vd0 = str_replace(" ","",$vd0);
		}
		$jcbz_name	= $jcbz_name_arr[$max_water_type];
		//站点特殊标准限值判断
		if(!empty($rs['syxz'])){
			$rs['syxz']	= explode(',',$rs['syxz']);
		}
		//匹配标准限值
		$jcxz	= '';
		if(is_array($pd_jcxz_arr[$max_water_type][$rs['vid']])){
			foreach((array)$rs['syxz']	as $value_syxz){
				//按照站点特殊限制区分
				if(!empty($pd_jcxz_arr[$max_water_type][$rs['vid']][$value_syxz])){
					$jcxz	= $pd_jcxz_arr[$max_water_type][$rs['vid']][$value_syxz];
					continue;
				}
			}
			//按照水样类型区分
			if(empty($jcxz)){
				if($pd_jcxz_arr[$max_water_type][$rs['vid']][$rs['water_type']]){
					$jcxz	= $pd_jcxz_arr[$max_water_type][$rs['vid']][$rs['water_type']];
				}else{
					if(!empty($pd_jcxz_arr[$max_water_type][$rs['vid']][$max_water_type])){
						$jcxz	= $pd_jcxz_arr[$max_water_type][$rs['vid']][$max_water_type];
					}else{
						$jcxz	= $pd_jcxz_arr[$max_water_type][$rs['vid']]['其他'];
					}
				}
			}
		}else{
			$jcxz	= $pd_jcxz_arr[$max_water_type][$rs['vid']];
		}
		$return_data=is_chaobiao($rs['vid'],$max_water_type,$jcxz,$vd0);
		$vd0=str_replace(array("<",">"),array("&lt;","&gt;"),$vd0);
		if($return_data['status']){
			$vd0='<span style="color:red">'.$vd0.'*</span>';
			$no_ok_arr[]	= $xm_arr[$rs['vid']];
		}
		$result[$rs['water_type']][$rs['id']]['vid'][$rs['vid']]['vd0']=$vd0;
		if(!in_array($rs['vid'],$now_vids)){
			$now_vids[]	= $rs['vid'];
		}
		//$result[$rs['id']]['vid'][$rs['vid']]['unit']=$rs['unit'];
	}
}
//获取采样时间的范围
if(!empty($cy_date_arr)){
	if(min($cy_date_arr)==max($cy_date_arr)){
		$cy_date_fw=$cy_date_arr[0];
	}else{
		$cy_date_fw = min($cy_date_arr)."～".max($cy_date_arr);
	}
}
if(empty($result)){
	echo "<script>alert('没有查询任何数据'); window.close();</script>";exit();
}
//print_rr($result);exit();
//ksort($vid_arr);

if(!empty($xm_px_arr)){
	$xm_arr_temp=array();
	foreach($xm_px_arr as $key=>$value){
		if(!empty($xm_arr[$value])){
			$xm_arr_temp[$value]=$xm_arr[$value];
			unset($xm_arr[$value]);
		}
	}
	$xm_arr=$xm_arr_temp+$xm_arr;
}
//print_rr($vid_arr);
//定义每页站点列数
if(empty($_POST['col_max'])){
	define("COL_MAX",5);
}else{
	define("COL_MAX",$_POST['col_max']);
}
$col_max=COL_MAX;
$xu_width=40;
$vid_width=130;
$unit_width=70;
$site_width=(1000-$xu_width-$vid_width-$unit_width)/COL_MAX;
$z_col_max=$col_max+3;
$col_one=intval($z_col_max/3);
$col_two=$z_col_max-(2*$col_one);
//定义每页项目行数
if(empty($_POST['row_max'])){
	define("ROW_MAX",16);
}else{
	define("ROW_MAX",$_POST['row_max']);
}
$site_nums=count($result);
$bz_colspan=COL_MAX+1;
//显示水功能去的信息
$col_nums=0;
$row_nums=0;
$i=0;

//标题的显示
$date_str	= '';
if($_GET['set_id'] && $_GET['year']&&$_GET['month']){
	$year_month=date('Y年n月',strtotime($_GET['year'].'-'.$_GET['month']));
	$date_str='('.$year_month.')';
}
$title =$_POST['cgb_title'].$date_str;
//根据水样类型升序排列
@ksort($result);

$result_temp=array();
//重新构建结果数组
$water_type_id	= array();
$water_type_num	= array();
foreach($result as $key=>$value){
	$water_type_num[$key]	= count($value);
	$water_type_id[]	= $key;
	$result_temp	+=$value;
}
$water_type_str	= implode(',', $water_type_id);
$water_type_name	= array();
$leixing_sql	= $DB->query("SELECT id,lname FROM `leixing` WHERE id in ($water_type_str)");
while ($leixing_rs	= $DB->fetch_assoc($leixing_sql)) {
	$water_type_name[$leixing_rs['id']]	= $leixing_rs['lname'];
}
$td_num	= COL_MAX;//列数(站点数)
$tr_num	= ROW_MAX;//行数
//html中的合并行计算
$max_colspan	= $td_num+3;
$max_colspan_ban	= $max_colspan/2;
$beizhu_num	= $max_colspan-3;
$last_three	= intval($max_colspan/3);
$last_one	= $last_two	= ($max_colspan-$col_one)/2;

$result	= $result_temp;
$lines	= $site_names	= $jcbz_values	= '';
$bt_water_type	= $bt_site	= $content	= array();
$befor_td_num	= 3;
$yeshu	= 1;
$site_num	= 0;
$old_water_type	= $old_water_type_ye	= array();
foreach ($result as $key_sid => $value_site_arr) {
	$site_num++;
	//print_rr($value_site_arr);
	//表头信息
	if(empty($bt_site)){
		$last_num	= $td_num;//当前页剩余列数
		$bt_water_type[$yeshu]	.= "<td rowspan='2' style='width:60px'>序号</td><td rowspan='2' nowrap>项目</td><td rowspan='2' nowrap style='width:60px'>单位</td>";
	}
	if($fenye	== 'yes'){
		$yeshu++;
		$site_num	= 1;
		$bt_water_type[$yeshu]	.= "<td rowspan='2' style='width:60px'>序号</td><td rowspan='2' nowrap>项目</td><td rowspan='2' nowrap style='width:60px'>单位</td>";
		$fenye	= '';
		$last_num	= $td_num;
	}
	if($site_num!=1 && $site_num%$td_num==0){
		$fenye	= "yes";
	}
	if(!in_array($value_site_arr['water_type'],$old_water_type) || (@in_array($value_site_arr['water_type'],$old_water_type) && !in_array($yeshu,$old_water_type_ye))){
		$old_water_type[]	= $value_site_arr['water_type'];
		$old_water_type_ye[]= $yeshu;
		if($last_num>=$water_type_num[$value_site_arr['water_type']]){
			$last_num	= $last_num-$water_type_num[$value_site_arr['water_type']];
			$bt_water_type[$yeshu]	.= "<td colspan='{$water_type_num[$value_site_arr['water_type']]}'>{$water_type_name[$value_site_arr['water_type']]}</td>";
		}else{
			$water_type_num[$value_site_arr['water_type']]	= $water_type_num[$value_site_arr['water_type']]-$last_num;
			$bt_water_type[$yeshu]	.= "<td colspan='{$last_num}'>{$water_type_name[$value_site_arr['water_type']]}</td>";
		}
	}
	$bt_site[$yeshu]	.= "<td>{$value_site_arr['bt']['site_name']}</td>";
	//项目信息
	$xuhao	= $vid_num	= $tr_fenye	= 0;
	foreach ($vid_arr as $key_vid => $value) {
		$xuhao++;
		$vid_num++;
		//根据项目数量分页
		if($vid_num%$tr_num==0){
			$tr_fenye++;

		}
		if(empty($content[$yeshu][$tr_fenye][$key_vid])){
			$content[$yeshu][$tr_fenye][$key_vid]	.= "<td>$xuhao</td><td style='text-align:left;width:130px;'>{$xm_arr[$key_vid]}</td><td>{$unit_arr[$key_vid]}</td>";
		}
		if(empty($value_site_arr['vid'][$key_vid]['vd0'])){
			$value_site_arr['vid'][$key_vid]['vd0']	= '--';
		}
		$content[$yeshu][$tr_fenye][$key_vid]	.= "<td>{$value_site_arr['vid'][$key_vid]['vd0']}</td>";
	}
	/*foreach ($value_site_arr['vid'] as $key_vid => $value) {
		$xuhao++;
		if(empty($content[$yeshu][$key_vid])){
			$content[$yeshu][$key_vid]	.= "<td>$xuhao</td><td>{$xm_arr[$key_vid]}</td><td>{$unit_arr[$key_vid]}</td>";
		}
		$content[$yeshu][$key_vid]	.= "<td>{$value['vd0']}</td>";
	}*/
}
if($last_num>0){
	for($i=0;$i<$last_num;$i++){
		$bt_water_type[$yeshu]	.= "<td></td>";
		$bt_site[$yeshu]		.= "<td></td>";
		foreach ($content[$yeshu] as $key => $value) {
			$content[$yeshu][$key]	= explode("&&&",implode("<td></td>&&&",$value)."<td></td>");
		}
		//$content[$yeshu]	= explode("&&&",implode("<td></td>&&&",$content[$yeshu])."<td></td>");
	}
}


//表头单位信息
$bianhao_content	= '';
if(!empty($_POST['jl_bh'])){
	$bianhao_content	= "<table style=\"margin:0 auto;width:25cm;font-family:宋体;border:none;\"> 
<tr style=\"font-size:11pt;\"><td colspan=\"{$col_one}\" width=\"33%\" nowrap style='text-align:left;border:none;'>国家城市供水水质监测网.<span style=\"font-weight:bold;font-size:10pt\">青岛监测站</span></td><td align=\"right\" colspan=\"{$col_two}\" width=\"33%\" nowrap style='border:none;'>记录编号：{$_POST['jl_bh']}</td><td colspan=\"{$col_one}\" align=\"right\" width=\"33%\" nowrap style='text-align:right;border:none;'>版本号：AO</td></tr>
</table>";
}
//显示数据
$water_area_months	= '';
$table	= '';
foreach($bt_water_type as $key=>$value){
	foreach ($content[$key] as $key_tr_fenye => $value_vid) {
		$lines	= '';
		$lines	.= "<tr>".$value."</tr><tr>".$bt_site[$key]."</tr>";
		$lines	.= "<tr>".implode("</tr><tr>",$value_vid)."</tr>";
		//写在这里是为了 下载的时候正常（下载的时候只能有一个表头）
		$table	= "$bianhao_content
					<table style=\"margin:0 auto;width:25cm;border-collapse:collapse;font-size:10pt;font-family:宋体\" class=\"td_border\" cellspacing=\"0\" cellpadding=\"0\">
						<tr>
							<td colspan='{$max_colspan}' style=\"font-weight:bold;font-size:20pt;border:none;\">$title</td>
						</tr>
						<tr>
							<td colspan=\"{$max_colspan_ban}\" style=\"border:none;text-align:left;\">监测部门：{$_POST[jc_bm]}</td><td colspan=\"{$max_colspan_ban}\" style=\"border:none;text-align:right;\">填表日期：{$_POST[tb_date]}</td>
						</tr>
						$lines
						<tr><td colspan='3'>备注</td><td colspan=\"{$beizhu_num}\"></td></tr>
						<tr>
							<td colspan='{$last_one}' style='text-align:left;border:none;'>单位负责人:</td><td colspan=\"{$last_two}\" style=\"border:none;padding-right: 2cm;\">审核:</td><td colspan=\"{$last_three}\" style=\"border:none;text-align:right;padding-right:80px;\"> 制表：</td>
						</tr>
					</table>";
		$water_area_months	.= $table;
	}
	//temp("any_data/{$gx_set_json['result_mb_name']}");
}
$water_area_months	= temp("any_data/{$gx_set_json['result_mb_name']}");


if($_POST['sub']=='查看成果'){
	echo "
	<script src='$rooturl/js/jquery-2.1.0.js'></script>
	<script src='$rooturl/js/bootstrap.min.js'></script>
	<link href='$rooturl/css/bootstrap.min.css' rel='stylesheet' />
	<style>
		.tishi{display:inline-block;width:100%;height:100%;}
	</style>
	<script>
		$(function(){
			$(\".tishi[data-rel=popover]\").popover({html:true});
		})
	</script>";
	echo $water_area_months;
}
if($_POST['sub']=='下载成果'){
	header("Content-Type:   application/msexcel");        
    header("Content-Disposition:   attachment;   filename=".$file_name.".xls");        
    header("Pragma:   no-cache");        
    header("Expires:   0");        
	echo $water_area_months;
}
?>