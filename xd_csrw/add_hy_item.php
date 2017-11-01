<?php
include("../temp/config.php");
include INC_DIR.'cy_func.php';
if(!$u['userid']){
	nologin();
}
if(isset($_GET['cyd_id']) && ($cyd_id = (int)$_GET['cyd_id'])) {
    $cyd = get_cyd($cyd_id);
} else {
    exit('bad request');
}
$fzx_id=$u['fzx_id'];
//查询出不同的水样类型下项目的名称
$aj_value_sql="SELECT lx.id,aj.vid,aj.value_C FROM	`leixing` lx JOIN `n_set` n ON lx.id=n.module_value2 JOIN `assay_jcbz` aj ON n.id=aj.jcbz_bh_id JOIN `xmfa` x ON aj.vid=x.xmid WHERE lx.parent_id='0'  AND x.fzx_id='".$fzx_id."' AND x.act='1' AND n.module_value3='1'";
$aj_value_query=$DB->query($aj_value_sql);
while($aj_value_rs=$DB->fetch_assoc($aj_value_query)){
	$aj_value_arr[$aj_value_rs['id']][$aj_value_rs['vid']]=$aj_value_rs['value_C'];
}
//查询出所有的项目
$xm_sql="SELECT * FROM assay_value WHERE 1";
$xm_query=$DB->query($xm_sql);
while($xm_rs=$DB->fetch_assoc($xm_query)){
	$all_xm_arr[$xm_rs[id]]=$xm_rs['value_C'];
}
//查询出cy_rec表的数据
$rec_sql = "SELECT * FROM cy_rec WHERE cyd_id = '".$_GET['cyd_id']."' AND status = '1' AND sid > -1  order by sid desc,id ASC";
//echo $rec_sql;
$rec_query = $DB->query($rec_sql);
$data = array();
while($row = $DB->fetch_assoc($rec_query)) {
	$vid_str='';
	$water_type_max='';
	$vid_options='';
	$vid_name_arrs=array();
	$vid_arrs=array();
	if($row['zk_flag']=='-6'){
		$row['site_name']=$row['site_name'].'<br/>(平行)';
	}
	if(empty($row['water_type'])){
		$water_type_bh=substr($row['bar_code'],1,1);
		$wtbh = get_all_wtbh();
		$water_type=array_search($water_type_bh,$wtbh);
		$sql1="SELECT * FROM cy WHERE id = '".$_GET['cyd_id']."'";
		$slx=$DB->fetch_one_assoc($sql1);
		$lx=explode(',',$slx['water_type']);
		if (is_array($lx)) {
			foreach ($lx as $value) {
				$water_type_maxs[]=get_water_type_max($value,$fzx_id);
			}
			$water_type_maxs=implode(',',$water_type_maxs);
			$water_type_str='AND x.lxid in ('.$water_type_maxs.')';
		}
		else
		{
			$water_type_str='AND x.lxid ='.$water_type_max;
		}
	}else{
		$water_type_max=get_water_type_max($row['water_type'],$fzx_id);
		if($row['water_type']==$water_type_max){
			$water_type_str='AND x.lxid= '.$water_type_max;
		}else{
			$water_type_str='AND x.lxid in ('.$water_type_max.','.$row['water_type'].')';
		}
	}
	$vid_arrs=explode(',',$row['assay_values']);//站点项目
	//查询某水样类型下有方法的项目
	$xmfa_value_sql="SELECT *,av.value_C FROM `xmfa` x JOIN	`assay_value` av ON x.xmid=av.id WHERE x.fzx_id='".$fzx_id."' AND x.mr='1' ".$water_type_str." GROUP BY x.xmid ORDER BY x.xmid";
	$xmfa_value_query=$DB->query($xmfa_value_sql);
	while($xmfa_value_rs=$DB->fetch_assoc($xmfa_value_query)){
			$vid_options.='<option value='.$xmfa_value_rs['xmid'].'>'.$xmfa_value_rs['value_C'].'</option>';
	}
	foreach($vid_arrs as $key=>$value){
		if(!empty($aj_value_arr[$water_type_max][$value])){
			$vid_name_arrs[$value]=$aj_value_arr[$water_type_max][$value];
		}else{
			$vid_name_arrs[$value]=$all_xm_arr[$value];
		}
	}
	$vid_str=implode(',',$vid_name_arrs);
	$add_hy_item_lines.="<tr style='height:40px'>
						<td >{$row['site_name']}</td>
						<td >{$row['bar_code']}</td>
						<td align=\"left\" style=\"font-size:12px\">{$vid_str}</td>
						<td><select name='vid[{$row['id']}][]' class='chosen-select' multiple='' data-placeholder='请选择要添加或删除的项目...' style='display: none;width:400px;'>".$vid_options."</select></td>
						</tr>";
}

if(!empty($all_assay_value)){
	$all_nums=count($all_assay_value);
	$add_tds=7-($all_nums%7);
	$i=0;
	$assay_value_line='';
	foreach($all_assay_value as $k =>$v)
	{
		$i++;
		if($i%7==1)
		{
			$assay_value_line.="<tr>";
		}	
		if($all_nums==$i&&$add_tds!=7){
			$assay_value_line.="<td ><span>".$v."</span></td>";
			for($j=0;$j<$add_tds;$j++){
				$assay_value_line.="<td>&nbsp;</td>";
			}
		}
		else{
			$assay_value_line.="<td><span>".$v."</span></td>";
		}
		if($i%7==0||($all_nums==$i))
		{
			$assay_value_line.="</tr>";
		}
	}
}
//print_rr($all_assay_value);

echo temp('add_hy_item');
