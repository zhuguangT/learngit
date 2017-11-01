<?php
/*
*功能：查看和下载水质日报信息
*作者：zhengsen
*时间：2015-05-28
 */
include '../temp/config.php';
include INC_DIR . "cy_func.php";
include '../baogao/bg_func.php';
if($u['userid']==''){
	nologin();
}
$fzx_id=$u['fzx_id'];//获得分中心id
if($_GET['set_id']){
	$cg_rs=$DB->fetch_one_assoc("SELECT * FROM `baogao_list` WHERE id='".$_GET['set_id']."'");
	if(!empty($cg_rs['result_set'])){
		$_POST=json_decode($cg_rs['result_set'],true);
		if(empty($_POST['tb_date'][$_GET['date']])){
			$_POST['tb_date']	= $_GET['date'];
		}else{
			$_POST['tb_date']	= $_POST['tb_date'][$_GET['date']];
		}
	}
}
//print_rr($_GET);
//print_rr($_POST);
if(!empty($_POST['vid'])){
	$vid_arr=$_POST['vid'];
	$vid_str=implode(',',$vid_arr);
}else{
	echo "<script>alert('请先选择化验项目'); window.close();</script>";
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
//查询出所有的水样类型
$sql_lx="SELECT * FROM leixing WHERE (fzx_id=1 or fzx_id=0)";
$query_lx=$DB->query($sql_lx);
while($rs_lx=$DB->fetch_assoc($query_lx)){
	$lx_name_arr[$rs_lx['id']]=$rs_lx['lname'];
}
$site_arr=$cy_date_arr=array();
//查询中心的官网数据
foreach($_POST['sites'] as $group_name_key=>$sites_arr){
	$site_arr=array_unique(array_merge($site_arr,$sites_arr));
	$site_str=implode(',',$sites_arr);
	$sql="SELECT cr.*,ao.cid,ao.id as aid,ao.vd0,ao.ping_jun,ao.vid,ao.hy_flag,c.cy_date as c_date,s.st_type,cr.site_name,ap.unit,ap.jc_xz,s.syxz,s.sgnq_code,s.sgnq,s.xz_area,cr.water_height,cr.liu_l,
	cr.qi_wen FROM cy c LEFT JOIN cy_rec cr ON cr.cyd_id = c.id LEFT JOIN assay_order AS ao ON  ao.cid=cr.id LEFT JOIN sites AS s ON s.id = ao.sid  LEFT JOIN assay_pay ap  ON ao.tid=ap.id WHERE c.cy_date ='".$_GET['date']."' AND
    c.site_type = '".$_POST['site_type']."' AND s.id IN (".$site_str.") AND ao.vid IN (".$vid_str.") AND cr.zk_flag >= '0'	 AND ao.hy_flag>=0 AND c.group_name='".$group_name_key."' group by aid,s.xz_area,cr.water_type  ORDER BY cr.water_type,c.cy_date, cr.bar_code";
	$query=$DB->query($sql);
	while($rs=$DB->fetch_assoc($query)){
		//项目数组
		$max_water_type=get_water_type_max($rs['water_type'],$u['fzx_id']);
		if(empty($unit_arr[$rs['vid']])){
			$unit_arr[$rs['vid']]=$rs['unit'];//项目单位
		}
		//采样时间
		if(!empty($rs['c_date'])&&$rs['c_date']!='0000-00-00'&&!in_array($rs['c_date'],$cy_date_arr)){
			$cy_date_arr[] = $rs['c_date'];//结束日期
		}
		if(empty($rs['xz_area'])){
			$rs['xz_area']='无分区';
		}
		//水样类型
		//$result[$rs['xz_area']][$rs['id']]['water_type']=$rs['water_type'];
		//站点名称
		$result[$rs['water_type']][$rs['xz_area']][$rs['id']]['site_name']=$rs['site_name'];
		//化验项目数据
		if(!empty($rs['ping_jun'])&&$global['bg_pingjun']) {
			$vd0 = $rs['ping_jun'];
		}else{
			$vd0 =$rs['vd0'];
		}
		if(in_array($rs['vid'],$global['modi_data_vids'])&&$vd0<='0'&&$vd0!=''){
			$vd0='未检出';
		}
		$vd0 = str_replace(" ","",$vd0);
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
		if($return_data['status']){
			$vd0='<span style="color:red">'.$vd0.'*</span>';
		}
		$result[$rs['water_type']][$rs['xz_area']][$rs['id']]['vid'][$rs['vid']]=$vd0;

		//$result[$rs['id']]['vid'][$rs['vid']]['unit']=$rs['unit'];

	}
}


//查询分厂出厂水的数据
$sql_cb="SELECT * FROM changbu_data  WHERE cy_date='".$_GET['date']."' ";
$query_cb=$DB->query($sql_cb);
while($rs_cb=$DB->fetch_assoc($query_cb)){
	$cb_data=array();
	$cb_data['site_name']=$rs_cb['site_name'];
	$cb_data['vid']=json_decode($rs_cb['json_data'],true);
	$result[$rs_cb['water_type']][$rs_cb['xz_area']][]=$cb_data;
}

//获取采样时间的范围
if(!empty($cy_date_arr)){
	if(min($cy_date_arr)==max($cy_date_arr)){
		$cy_date_fw=$cy_date_arr[0];
	}else{
		$cy_date_fw = min($cy_date_arr)."～".max($cy_date_arr);
	}
}
//给传过来的项目赋值项目名称
foreach($vid_arr as $key=>$value){
	if(empty($jcbz[$max_water_type][$value])){
		$xm_arr[$value]=$xm[$value];//防止在assay_jcbz表里没有初始化某项目导致项目为空（初始化完成后应该去掉）
	}else{
		$xm_arr[$value]=$jcbz[$max_water_type][$value];
	}
}
//print_rr($result);exit();
//获取项目排序的设置
if($_POST['xm_px_id']){
	$xm_px_rs=$DB->fetch_one_assoc("SELECT * FROM n_set WHERE id='".$_POST['xm_px_id']."'");
	$xm_px_arr=explode(",",$xm_px_rs['module_value1']);
}
//根据设置进行项目排序
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
//根据定义的水样类型进行排序
$wt_px_arr=array(array_search('出厂水',$lx_name_arr),array_search('管网水',$lx_name_arr));
if(!empty($wt_px_arr)){
	foreach($wt_px_arr as $key=>$value){
		if(!empty($result[$value])){
			$result_temp[$value]=$result[$value];
			unset($result[$value]);
		}
	}
	$result=$result_temp+$result;
}


//获取项目名称和国家标准的td
$xm_name_td=$xz_td='';
foreach($xm_arr as $key=>$value){
	$xm_name_td.="<th>".$value."</th>";
	$xz_td.="<th>".$jcxz_arr[$max_water_type][$key]."</th>";
}

//print_rr($result);
//print_rr($xm_arr);exit();
//print_rr($vid_arr);
//标题需要合并的列
$cols1=count($xm_arr);
$z_cols=2+$cols1;
$cols2=2+$cols1-5;

$i=0;
if(empty($result)){
	echo "<script>alert('没有查询任何数据'); window.close();</script>";exit();
}
//标题的显示
$title =$_POST['cgb_title'];
//报告的显示
$day_bg_line='';
$xu_width=40;
$area_width=0;//40;
$site_width=120;
$vid_width=((1000-$xu_width-$area_width-$site_width)/$cols1)-4;
$row_count	= 6;//下载是前面需要留出一列用来截图方便，这个变量是第一行要合并多少列(标题+表尾行数)
//print_rr($result);exit();
foreach($result as $k =>$v){
	$row_count++;
	$day_bg_line.="<tr><th colspan=\"2\" style=\"border-right:0px;font-weight:bold\">".$lx_name_arr[$k]."</th><th colspan=".$cols1." style=\"border-left:0px\"></th></tr>";
	foreach($v as $k1=>$v1){
		$row_nums=count($v1);
		$row=1;
		foreach($v1 as $k2=>$v2){
			$i++;
			$vid_data_td='';
			foreach($xm_arr as $k3=>$v3){
				if(isset($v2['vid'][$k3])){
					$vd0=$v2['vid'][$k3];
				}else{
					$vd0='--';
				}
				$vid_data_td.="<td style='text-align:center;vnd.ms-excel.numberformat:@'>".$vd0."</td>";
			}
			if($row==1){
				$day_bg_line.="<tr><td align=center>".$i."</td><td align=center>".$v2['site_name']."</td>".$vid_data_td."</tr>";//<td rowspan=".$row_nums.">".$k1."</td>
			}else{
				$day_bg_line.="<tr><td align=center>".$i."</td><td align=center>".$v2['site_name']."</td>".$vid_data_td."</tr>";
			}
			$row_count++;
			$row++;
		}
	}
}
if(!empty($_POST['tb_date'])){
	$file_name=$_POST['tb_date'].'水质日报';
}else{
	$file_name=$_GET['date'].'水质日报';
}
if($_GET['action']=='view'){
	echo temp("any_data/tjbg_day_bg");
}
if($_GET['action']=='load'){
	header("Content-Type:   application/msexcel");        
    header("Content-Disposition:   attachment;   filename=".$file_name.".xls");        
    header("Pragma:   no-cache");        
    header("Expires:   0");        
	$left_row	= '<tr><td rowspan="'.$row_count.'" style="border:none;"></td></tr>';
	echo temp("any_data/tjbg_day_bg");//<col style="width:{$area_width}px" />
}
?>
