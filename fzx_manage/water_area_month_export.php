<?php
/*
*功能：查看和下载水功能区月报告信息
*作者：zhengsen
*时间：2014-10-23
 */
include '../temp/config.php';
include INC_DIR . "cy_func.php";
if($u['userid']==''){
	nologin();
}
$fzx_id=$u['fzx_id'];
$tjcs_rs=$DB->fetch_one_assoc("SELECT module_value1 FROM n_set WHERE id='".$_GET['tjcs']."'");
$tjcs=$tjcs_rs['module_value1'];
$hub_rs=$DB->fetch_one_assoc("SELECT * FROM hub_info WHERE id='".$_GET['fzx_id']."'");
$btcs_arr=array('sgnq_code'=>'水功能区序号','sgnq'=>'水功能区名称','site_name'=>'控制断面','xz_area'=>'行政区','cy_date'=>array('采样日期','年月日'),'cy_time'=>array('采样时间','时分'),'water_height'=>array('水位','m'),'liu_l'=>array('流量/蓄水量','m³/s/亿m³'),'qi_wen'=>array('气温','℃'));
//print_rr($btcs_arr);
//查询下化验单数据在什么状态下能显示到报告上
$show_shuju_arr	= array();
$show_shuju_old	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='show_shuju' ORDER BY id DESC LIMIT 1");
if(!empty($show_shuju_old['module_value1'])){
	$show_shuju_arr	= explode(",",$show_shuju_old['module_value1']);
}
//查询出自定义的项目排序
$xm_px_arr=array();
$xm_px_rs=$DB->fetch_one_assoc("SELECT * FROM n_set WHERE module_name='xm_px'");
if(!empty($xm_px_rs['module_value1'])){
	$xm_px_arr=explode(',',$xm_px_rs['module_value1']);
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
$fzx_str	= '';
if(!empty($_GET['fzx_id'])&&$_GET['fzx_id']!="全部"){
	$fzx_str="AND c.fzx_id='".$_GET['fzx_id']."'";
}
//只获取分中心上报的数据
if($_GET['fzx_id']!=$fzx_id || $_GET['fzx_id']=="全部"){
	//由于报告获取站点时有点问题，临时关闭上报数据的功能.如需开启直接取消注释即可
	//$fzx_str	.= " AND ao.`assay_over`='over' ";
}
$sql="SELECT cr.*,ao.vid,ao.vd0,ao.ping_jun,cr.site_name,s.sgnq,s.sgnq_code,s.xz_area,s.area,ap.over,ap.unit,c.fzx_id FROM cy c JOIN cy_rec cr ON c.id=cr.cyd_id JOIN assay_order ao ON  cr.id=ao.cid AND cr.sid=ao.sid LEFT JOIN assay_pay ap ON ao.tid=ap.id LEFT JOIN  sites s ON ao.sid=s.id WHERE cr.zk_flag>=0 AND cr.sid>0 AND ao.hy_flag>=0 ".$fzx_str." AND (s.tjcs LIKE '%,".$_GET['tjcs']."%' or  s.tjcs LIKE '%".$_GET['tjcs'].",%') AND cr.cy_date LIKE '".$_GET['year']."-".$_GET['month']."%' group by s.area,s.water_system,s.xz_area,ao.id";

$query=$DB->query($sql);
$vid_arr=array();
while($rs=$DB->fetch_assoc($query)){
	//项目数组
	$max_water_type=get_water_type_max($rs['water_type'],$cr['fzx_id']);
	if(empty($jcbz[$max_water_type][$rs['vid']])){
		$vid_arr[$rs['vid']]=$xm[$rs['vid']];//防止在assay_jcbz表里没有初始化某项目导致项目为空（初始化完成后应该去掉）
	}else{
		$vid_arr[$rs['vid']]=$jcbz[$max_water_type][$rs['vid']];
	}
	//水样类型
	$result[$rs['id']]['water_type']=$rs['water_type'];
	//采样表的备注信息
	$result[$rs['id']]['cy_note']=$rs['cy_note'];
	//表头数据
	$result[$rs['id']]['bt']['sgnq_code']=$rs['sgnq_code'];//水功能区序号
	$result[$rs['id']]['bt']['sgnq']=$rs['sgnq'];//水功能区名称
	$result[$rs['id']]['bt']['site_name']=$rs['site_name'];//控制断面
	$result[$rs['id']]['bt']['xz_area']=$rs['xz_area'];//行政区
	$result[$rs['id']]['bt']['cy_date']=$rs['cy_date'];//采样日期
	$result[$rs['id']]['bt']['cy_time']=substr($rs['cy_time'],0,5);//采样时间
	$result[$rs['id']]['bt']['water_height']=$rs['water_height'];//水位
	$result[$rs['id']]['bt']['liu_l']=$rs['liu_l'];//流量
	$result[$rs['id']]['bt']['qi_wen']=$rs['qi_wen'];//流量/蓄水量
	//化验项目数据
	if(!empty($show_shuju_arr) && !in_array($rs['over'],$show_shuju_arr)){
		$vd0	= '';
	}else{
		if(!empty($rs['ping_jun'])&&$global['bg_pingjun']) {
			$vd0 = $rs['ping_jun'];
		}else{
			$vd0 =$rs['vd0'];
		}
		$vd0 = str_replace(' ', '', $vd0);
	}
	$result[$rs['id']]['vid'][$rs['vid']]['vd0']=$vd0;
	if(!empty($rs['unit'])){
		$unit_arr[$rs['vid']]=$rs['unit'];
	}

}
//print_rr($result);
ksort($vid_arr);
//根据项目排序显示项目
if(!empty($xm_px_arr)){
	foreach($xm_px_arr as $key=>$value){
		if(array_key_exists($value,$vid_arr)){
			$new_vid_arr[$value]=$vid_arr[$value];
			unset($vid_arr[$value]);
		}
	}
}
if(!empty($vid_arr)&&!empty($new_vid_arr)){
	foreach($vid_arr as $key=>$value){
		$new_vid_arr[$key]=$value;
	}
}
if(!empty($new_vid_arr)){
	$vid_arr=$new_vid_arr;
}
//print_rr($vid_arr);
//定义每页站点列数
define("COL_MAX",6);
//定义每页项目行数
define("ROW_MAX",38);
$site_nums=count($result);

//显示水功能去的信息
$col_nums=0;
$row_nums=0;
$i=0;
if(empty($result)){
	echo "<script>alert('没有查询任何数据'); window.close();</script>";
	exit();
}
$notes='';
foreach($result as $k =>$v){
	$i++;
	$notes.="<td>".$v['cy_note']."</td>";
	$col_nums++;
	$cid_arr[]=$k;
	if($col_nums%COL_MAX=='0'|| $col_nums==count($result)){
		$add_tds=COL_MAX-count($cid_arr);
		if(!empty($add_tds)){
			$add_td_lines='';
			for($a=1;$a<=$add_tds;$a++){
				$add_td_lines.="<td></td>";
			}
		}
		$btcs_lines='';
		$vid_lines='';
		$row_nums=0;
		//循环获取表头参数
		foreach($btcs_arr as $k1=>$v1){
			if(is_array($v1)){
				$btcs_lines.="<tr><td width=\"130px\">{$v1[0]}</td><td width=\"60px\">{$v1[1]}</td>";
			}else{
				$btcs_lines.="<tr><td colspan='2'>{$v1}</td>";
			}
			foreach($cid_arr as $k2=>$v2){
				if($k1=="cy_date"){
					$btcs_lines.="<td style=\"mso-number-format:'yyyy\\-mm\\-dd';\"  align=\"left\">{$result[$v2][bt][$k1]}</td>";
				}elseif($k1=='qi_wen'){
					$btcs_lines.="<td style=\"mso-number-format:'\@'\"  align=\"left\">{$result[$v2][bt][$k1]}</td>";
				}else{
					$btcs_lines.="<td  align=\"left\" style=\"mso-number-format:'\@'\">{$result[$v2][bt][$k1]}</td>";
				}
			}
			$btcs_lines.="{$add_td_lines}</tr>";

		}
		//补全备注的td
		$add_note_tds=COL_MAX-count($cid_arr);
		if($add_note_tds>0){
			for($n=1;$n<=$add_note_tds;$n++){
				$notes.="<td>&nbsp;</td>";
			}
		}
		//循环获取项目数据
		foreach($vid_arr as $k3=>$v3){
			$row_nums++;
			$vid_lines.="<tr><td>{$v3}</td>";
			$j=0;
			foreach($cid_arr as $k4=>$v4){
				$j++;
				if($j==1){
					if($v3=="水温"){
						$vid_lines.="<td>{$unit_arr[$k3]}</td><td width=\"90px\" style=\"mso-number-format:'\@'\"  align=\"left\">{$result[$v4][vid][$k3][vd0]}</td>";
					}else{
						$vid_lines.="<td>{$unit_arr[$k3]}</td><td width='90px'  style=\"mso-number-format:'\@'\" align=\"left\">{$result[$v4][vid][$k3][vd0]}</td>";
					}
				}else{
					if($v3=="水温"){
						$vid_lines.="<td width=\"90px\" style=\"mso-number-format:'\@'\"  align=\"left\">{$result[$v4][vid][$k3][vd0]}</td>";
					}else{
						$vid_lines.="<td width=\"90px\" style=\"mso-number-format:'\@'\"  align=\"left\">{$result[$v4][vid][$k3][vd0]}</td>";
					}
				}
			}

			$vid_lines.="{$add_td_lines}</tr>";
			if($row_nums%ROW_MAX=='0' || $row_nums==count($vid_arr)){
				$vid_lines.="</tr>";
				$water_area_month_lines=$btcs_lines.$vid_lines;
				$water_area_months.= temp("water_area_month_export");
				$water_area_months.='<div style="PAGE-BREAK-AFTER: always"></div>';
				$vid_lines=$notes="";
			}
		}
		$cid_arr=array();
	}
}
$shangbao	= '';
if($_GET['action']=='view'){
	//判断是不是分中心，如果是分中心就显示“上报到总中心”的按钮
	if($_GET['fzx_id']!='全部'){
		echo '<script language="javascript" src="'.$rooturl.'/js/boxy.js"></script>
		<link rel="stylesheet" href="'.$rooturl.'/css/lims/buttons.css" />
		<link rel="stylesheet" href="'.$rooturl.'/css/boxy.css" />';
		$is_shangbao	= $DB->fetch_one_assoc("SELECT module_value1 FROM `n_set` WHERE fzx_id='{$_GET['fzx_id']}' AND module_name='month_export_shangbao' AND module_value2='".$_GET['year']."-".$_GET['month']."' AND module_value3='{$_GET['tjcs']}'");
		if($is_shangbao['module_value1']=='finish'){
			if($is_fzx['is_zz']=='0'){
				echo " <a href=\"#\" title='本报表的数据已经上报到总中心' onclick=\"alert('本报表的数据已经上报到总中心');\" style=\"position:fixed;right:80px;bottom:15px;border: 1px solid #84ACC3 !important;
background-color: #FFB752;\" class=\"button\"> 数据已上报  </a>";
			}else{
				echo " <a href=\"#\" title='本报表的数据已经被报表人员审核' onclick=\"alert('本报表的数据已经被报告人员审核确认，不能再修改。');\" style=\"position:fixed;right:80px;bottom:15px;border: 1px solid #84ACC3 !important;
background-color: #FFB752;\" class=\"button\"> 数据已审核  </a>";
			}
		}else if($fzx_id==$_GET['fzx_id']){//这个判断防止总中心可以看到分中心的“上报至总中心”按钮
			if($u['jcbg_sh'] || $u['jcbg_sh']){
				$is_fzx	= $DB->fetch_one_assoc("SELECT `is_zz` FROM `hub_info` WHERE id='{$_GET['fzx_id']}'");
				if($is_fzx['is_zz']=='0'){
					//echo $shangbao	= " <a href=\"#\" title='将本报表的数据上报到总中心' onclick=\"if(confirm('确认上报数据吗？数据上报后将不能再修改。'))location.href='shangbao.php?action=month_export&year={$_GET['year']}&month={$_GET['month']}&tjcs={$_GET['tjcs']}'\" style=\"position:fixed;right:80px;bottom:15px;\" class=\"button blue\"> 上报至总中心  </a>";
				}else{
					//echo $shangbao	= " <a href=\"#\" title='数据审核确认' onclick=\"if(confirm('确认审核通过吗？确认后数据将不能再修改。'))location.href='shangbao.php?action=month_export&year={$_GET['year']}&month={$_GET['month']}&tjcs={$_GET['tjcs']}'\" style=\"position:fixed;right:80px;bottom:15px;\" class=\"button blue\"> 数据审核通过  </a>";
				}
				echo "<a href='#' onclick=\"alert('数据上报功能临时关闭，重新启用时会在qq群通知');\"  style=\"position:fixed;right:80px;bottom:15px;\" class=\"button blue\">上报功能临时关闭</a>";
			}
		}
	}
	echo $water_area_months;
}
if($_GET['action']=='load'){
	header("Content-Type:   application/msexcel");        
    header("Content-Disposition:   attachment;   filename=监测成果表.xls");        
    header("Pragma:   no-cache");        
    header("Expires:   0");        
	echo $water_area_months;
}
