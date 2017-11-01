<?php
/*
*功能：查看和下载水功能区月报告信息
*作者：zhengsen
*时间：2014-10-23
 */
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
$jcbz_sql="SELECT aj.vid,n.module_value2 as water_type,aj.value_C,aj.xz,aj.panduanyiju FROM n_set n JOIN assay_jcbz aj ON n.id=aj.jcbz_bh_id WHERE module_name='jcbz_bh' AND module_value3='1' ";
$jcbz_query=$DB->query($jcbz_sql);
while($jcbz_rs=$DB->fetch_assoc($jcbz_query)){
	$jcbz[$jcbz_rs['water_type']][$jcbz_rs['vid']]=$jcbz_rs['value_C'];
	if(!empty($jcbz_rs['panduanyiju'])){
		$pdyj_arr=json_decode($jcbz_rs['panduanyiju'],true);
		if(!empty($pdyj_arr)){
			$pd_jcxz_arr[$jcbz_rs['water_type']][$jcbz_rs['vid']]	= $pdyj_arr;
		}else{
			$pd_jcxz_arr[$jcbz_rs['water_type']][$jcbz_rs['vid']]=$jcbz_rs['panduanyiju'];
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
foreach($_POST['sites'] as $group_name_key=>$sites_arr){
	$site_arr=array_unique(array_merge($site_arr,$sites_arr));
	$site_str=implode(',',$sites_arr);
	$sql="SELECT cr.*,ao.cid,ao.id as aid,ao.vd0,ao.ping_jun,ao.vid,ao.hy_flag,c.cy_date as c_date,s.syxz,s.st_type,s.site_name,ap.over,ap.unit,ap.jc_xz,s.sgnq_code,s.sgnq,s.xz_area,cr.water_height,cr.liu_l,
	cr.qi_wen FROM cy c LEFT JOIN cy_rec cr ON cr.cyd_id = c.id LEFT JOIN assay_order AS ao ON  ao.cid=cr.id LEFT JOIN sites AS s ON s.id = ao.sid  LEFT JOIN assay_pay ap  ON ao.tid=ap.id WHERE c.cy_date BETWEEN '".$_POST['begin_date']."' AND '".$_POST['end_date']."' AND
    c.site_type = '".$_POST['site_type']."' AND s.id IN (".$site_str.") AND ao.vid IN (".$vid_str.") AND cr.zk_flag >= '0'	 AND ao.hy_flag>=0 AND c.group_name='".$group_name_key."' group by aid,cr.water_type  ORDER BY cr.water_type,c.cy_date, cr.bar_code";
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
		if(empty($unit_arr[$rs['vid']])){
			$unit_arr[$rs['vid']]=$rs['unit'];//项目单位
		}
		//采样时间
		if(!empty($rs['c_date'])&&$rs['c_date']!='0000-00-00'&&!in_array($rs['c_date'],$cy_date_arr)){
			$cy_date_arr[] = $rs['c_date'];//结束日期
		}
		//按照这个排序和分类
		if($gx_set_json['px']=='cy_date'){
			$first_key	= $rs['c_date'];
		}else{
			$first_key	= $rs['water_type'];
		}
		//水样类型
		$result[$first_key][$rs['id']]['water_type']=$rs['water_type'];
		//表头数据
		$result[$first_key][$rs['id']][bt]['sgnq_code']=$rs['sgnq_code'];//水功能区序号
		$result[$first_key][$rs['id']][bt]['sgnq']=$rs['sgnq'];//水功能区名称
		$result[$first_key][$rs['id']][bt]['site_name']=$rs['site_name'];//控制断面
		$result[$first_key][$rs['id']][bt]['xz_area']=$rs['xz_area'];//行政区
		$result[$first_key][$rs['id']][bt]['cy_date']=$rs['c_date'];//采样日期
		$result[$first_key][$rs['id']][bt]['cy_time']=substr($rs['cy_time'],0,5);//采样时间
		$result[$first_key][$rs['id']][bt]['water_height']=$rs['water_height'];//水位
		$result[$first_key][$rs['id']][bt]['liu_l']=$rs['liu_l'];//流量
		$result[$first_key][$rs['id']][bt]['qi_wen']=$rs['qi_wen'];//流量/蓄水量
		//化验项目数据
		if(!empty($show_shuju_arr) && !in_array($rs['over'],$show_shuju_arr)){
			$vd0	= '';
		}else{
			if(!empty($rs['ping_jun'])&&$global['bg_pingjun']) {
				$vd0 = $rs['ping_jun'];
			}else{
				$vd0 =$rs['vd0'];
			}
			if(in_array($rs['vid'],$global['modi_data_vids'])&&$vd0<='0'&&$vd0!=''){
				$vd0='未检出';
			}
			$vd0 = str_replace(" ","",$vd0);
		}
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
		//print_rr($rs);
		//print_rr($return_data);
		$vd0=str_replace(array("<",">"),array("&lt;","&gt;"),$vd0);
		if($return_data['status']){
			$vd0='<span style="color:red">'.$vd0.'*</span>';
		}

		$result[$first_key][$rs['id']]['vid'][$rs['vid']]['vd0']=$vd0;

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
//print_rr($result);
//exit();
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
	define("COL_MAX",6);
}else{
	define("COL_MAX",$_POST['col_max']);
}
$col_max=COL_MAX;
$xu_width=40;
$vid_width=130;
$unit_width=70;
$bz_width=160;
$site_width=(1000-$xu_width-$vid_width-$unit_width-$bz_width)/COL_MAX;
$z_col_max=$col_max+4;
$col_one=intval($z_col_max/3);
$col_two=$z_col_max-(2*$col_one);
//定义每页项目行数
if(empty($_POST['row_max'])){
	define("ROW_MAX",31);
}else{
	define("ROW_MAX",$_POST['row_max']);
}
$site_nums=count($result);
$bz_colspan=COL_MAX+1;
//显示水功能去的信息
$col_nums=0;
$row_nums=0;
$i=0;
if(empty($result)){
	echo "<script>alert('没有查询任何数据'); window.close();</script>";exit();
}
//标题的显示
//$title = $_POST['begin_date'] . "至" . $_POST["end_date"]. get_site_list($site_arr,3) . "等站点水质监测成果表";
$date_str	= '';
if($_GET['set_id'] && $_GET['year']&&$_GET['month']){
	$year_month=date('Y年n月',strtotime($_GET['year'].'-'.$_GET['month']));
	$date_str='('.$year_month.')';
}
$title =$_POST['cgb_title'].$date_str;
//根据水样类型升序排列
@ksort($resutl);

$result_temp=array();
//重新构建结果数组
foreach($result as $key=>$value){
	$result_temp+=$value;
	
}
$result=$result_temp;
foreach($result as $k =>$v){
	$i++;
	$col_nums++;
	$cid_arr[]=$k;
	if($col_nums%COL_MAX=='0'|| $col_nums==count($result)){

		$add_tds=COL_MAX-count($cid_arr);
		if(!empty($add_tds)){
			$add_td_lines='';
			for($a=1;$a<=$add_tds;$a++){
				$add_td_lines.="<td width=\"90px\"></td>";
			}
		}
		$btcs_lines='';
		$vid_lines='';
		$row_nums=0;
		$z=1;
		$rows=count($_POST['cgb_bt_cs']);
		//循环获取表头参数
		foreach($_POST['cgb_bt_cs'] as $k1=>$v1){
			if($z==1){
				$btcs_lines.="<tr><td rowspan=".$rows." align=\"center\" >序号</td>";
			}else{
				$btcs_lines.="<tr>";
			}
			if(is_array($btcs_arr[$global['cgb_bt_cs'][$v1]])){
				$btcs_lines.="<td >{$btcs_arr[$global['cgb_bt_cs'][$v1]][0]}</td><td >{$btcs_arr[$global['cgb_bt_cs'][$v1]][1]}</td><td></td>";
			}else{
				if($v1=='站点名称'){
					$btcs_lines.="<td>检验项目</td><td >单位</td><td >国家标准</td>";
				}else{
					$btcs_lines.="<td colspan=\"3\">{$v1}</td>";
				}
			}
			foreach($cid_arr as $k2=>$v2){
				$btcs_lines.="<td >{$result[$v2][bt][$global['cgb_bt_cs'][$v1]]}</td>";
			}
			$btcs_lines.="{$add_td_lines}</tr>";
			$z++;
		}
		//循环获取项目数据
		foreach($xm_arr as $k3=>$v3){
			$row_nums++;
			$vid_lines.="<tr><td align=\"center\">".$row_nums."</td><td>{$v3}</td>";
			$j=0;
			foreach($cid_arr as $k4=>$v4){
				$j++;
				if(!isset($result[$v4]['vid'][$k3]['vd0'])){
					$result[$v4]['vid'][$k3]['vd0']='--';
				}
				$gjbz=$jcxz_arr[$max_water_type][$k3];
				//----提示相关项目的结果值---开始
				$tishi	= $tishi_attr	= '';
				$chongfu_arr= array();
				//传入vid返回 相关项目的数组（function.php）
				$is_cunzai	= in_str($k3,$global['related_value']);
				if($is_cunzai){
					$tishi	.= "<table><tr><td style='font-weight:bold;'>项目名称</td><td style='font-weight:bold;'>结果值</td></tr>";
					foreach($is_cunzai  as $value){
						if($result[$v4]['vid'][$value]['vd0']){
							//这里一定要将双引号 改为 单引号，不然提示框将由属性值变成正常标签值。会导致页面严重变形
							$result_str	= str_replace('"',"'", $result[$v4]['vid'][$value]['vd0']);
							$tishi	.= "<tr><td style='width:50%;' nowrap>".$_SESSION['assayvalueC'][$value]."</td><td style='width:50%;' nowrap>".$result_str."</td></tr>";
						}else{
							$tishi	.= "<tr><td style='width:50%;' nowrap>".$_SESSION['assayvalueC'][$value]."</td><td style='width:50%;' nowrap>无值</td></tr>";
						}
					}
					$tishi	.= "</table>";
					$tishi_attr	= " data-trigger='hover focus' data-placement='top' data-rel='popover' data-animation='true' data-original-title='相关项目值' data-content=\"{$tishi}\" ";
				}
				//----提示相关项目结果值---结束
				/*
				if(stristr($global['related_value'],"&{$k3}&")){
					$tmp_related_value_arr	= explode('|',trim($global['related_value'],'|'));
					$tishi	.= "<table>";
					$chongfu_arr	= array();
					foreach ($tmp_related_value_arr as $key => $value) {
						if(stristr($value,"&{$k3}&")){
							$tmp_related_value	= explode('&', trim($value,'&'));
							foreach ($tmp_related_value as $key => $value) {
								if(!in_array($value,$chongfu_arr)){
									$chongfu_arr[]	= $value;
									if($result[$v4]['vid'][$value]['vd0']){
										//这里一定要将双引号 改为 单引号，不然提示框将由属性值变成正常标签值。会导致页面严重变形
										$result_str	= str_replace('"',"'", $result[$v4]['vid'][$k3]['vd0']);
										$tishi	.= "<tr><td style='width:50%;' nowrap>".$_SESSION['assayvalueC'][$value]."</td><td style='width:50%;' nowrap>".$result_str."</td></tr>";
									}else{
										$tishi	.= "<tr><td style='width:50%;' nowrap>".$_SESSION['assayvalueC'][$value]."</td><td style='width:50%;' nowrap>无值</td></tr>";
									}
								}
							}
						}
					}
					$tishi	.= "</table>";
					$tishi_attr	= " data-trigger='hover focus' data-placement='top' data-rel='popover' data-animation='true' data-original-title='相关项目值' data-content=\"{$tishi}\" ";
				}*/
				if($j==1){
					if($gjbz==''){
						$gjbz='无';
					}
					if(strlen($gjbz)>=54){
						$gjbz="<span style=\"font-size:9pt\">".$gjbz."</span>";
					}
					$vid_lines.="<td align=\"left\">{$unit_arr[$k3]}</td><td  align=\"left\" style=\"mso-number-format:'\@'\">{$gjbz}</td><td  align=\"left\" style=\"mso-number-format:'\@'\" ><span class=\"tishi\" {$tishi_attr}>{$result[$v4][vid][$k3][vd0]}</span></td>";
				}else{
					$vid_lines.="<td  align=\"left\" style=\"mso-number-format:'\@'\" ><span class=\"tishi\" {$tishi_attr}>{$result[$v4][vid][$k3][vd0]}</span></td>";
				}
			}
			$vid_lines.="{$add_td_lines}</tr>";
			if($row_nums%ROW_MAX=='0' || $row_nums==count($xm_arr)){
				$water_area_month_lines=$btcs_lines.$vid_lines;
				if($col_nums==count($result)&&$row_nums==count($xm_arr) && $gx_set_json['show_note'] == 'yes'){
					$water_area_month_lines.=
					"<tr height=\"40px\">
						<td  align=\"center\" colspan=\"3\">水质评价</td>
						<td colspan=".$bz_colspan." >{$_POST['sz_pj']}</td>
					</tr>
					<tr height=\"40px\">
						<td  align=\"center\" colspan=\"3\">备注</td>
						<td colspan=".$bz_colspan." >{$_POST['bz']}</td>
					</tr>";
				}
				$bianhao_content	= '';
				if(!empty($_POST['jl_bh'])){
					$bianhao_content	= "<table style=\"margin:0 auto;width:$bg_width;font-family:宋体\"> 
		<tr style=\"font-size:11pt\"><td colspan=\"{$col_one}\" width=\"33%\" nowrap>国家城市供水水质监测网.<span style=\"font-weight:bold;font-size:10pt\">青岛监测站</span></td><td align=\"right\" colspan=\"{$col_two}\" width=\"33%\" nowrap>记录编号：{$_POST['jl_bh']}</td><td colspan=\"{$col_one}\" align=\"right\" width=\"33%\" nowrap>版本号：AO</td></tr>
	</table>";
				}
				$water_area_months.= temp("any_data/any_sites_result_export2");
				if($col_nums==count($result)&&$row_nums==count($xm_arr)){
					$water_area_months.=temp('any_data/any_sites_export_qz.html');
				}
				$water_area_months.='<div style="PAGE-BREAK-AFTER: always"></div>';
				$vid_lines="";
			}
		}
		$cid_arr=array();
	}
}
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
	if($gx_set_json['show_chart'] == 'yes'){
		$no_show	= 'yes';
		echo "<script src='$rooturl/js/lims/echarts-all.js'></script>";
		include("../data_chart/custom_chart2.php");
		//$html_canvas	= '<fieldset style="min-width:630px;padding:10px;margin:20px auto;border:2px solid #A8A8A8;width:1000px;"><!--<legend style="width:auto;margin:0 auto;">'.$canvas_name.'</legend>--><div style="width:100%;height:300px;" class="ceshi"></div><p style="text-align:center;font-weight:bold;font-size:16px;">'.$canvas_name.'</p></fieldset>';
		echo "$html_canvas
				<script type=\"text/javascript\">
					$html_script
				</script>";
	}
	echo $water_area_months;
}
if($_POST['sub']=='下载成果'){
	header("Content-Type:   application/msexcel");        
    header("Content-Disposition:   attachment;   filename=".$file_name.".xls");        
    header("Pragma:   no-cache");        
    header("Expires:   0");        
	echo $water_area_months;
}
