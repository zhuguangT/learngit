<?php
/**
  * 功能：显示添加或者修改容器列表内容，
  * 作者：zhengsen
  * 时间：2014-4-11
**/
include '../../temp/config.php';
if(empty($u['userid'])){
	nologin();
}
$action="添加";
if(!empty($_GET['id'])){
	$rq_id=$_GET['id'];
	$action="修改";
}else{
	$rq_id='';
}
//导航
$trade_global['daohang'] = array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'采样容器列表','href'=>'./system_settings/cy_rq_manage/rq_list.php'),
	array('icon'=>'','html'=>$action.'修改容器信息','href'=>'./system_settings/cy_rq_manage/rq_modi.php?id='.$rq_id)
);
$fzx_id=$u['fzx_id'];
$sql_all_value="SELECT av.* FROM assay_value av JOIN xmfa x ON x.xmid=av.id WHERE x.fzx_id='".$fzx_id."' AND x.act='1' ORDER BY av.id";
$query_all_value=$DB->query($sql_all_value);
while($rs_all_value=$DB->fetch_assoc($query_all_value))
{
	$all_value[$rs_all_value['value_C']]=$rs_all_value['id'];
}
//点击添加按钮时判断一个实验室是否有启用的检测项目
if($_GET['action']=='click_add'){
	if(!empty($all_value)){
		echo '1';
	}else{
		echo '0';
	}
	exit();
}
if($_GET['action']=='check'){
	if(!empty($_GET['rq_id'])){
		$rq_id_str="AND id!='".$_GET['rq_id']."'";
	}else{
		$rq_id_str='';
	}
	$rq_rs=$DB->fetch_one_assoc("SELECT * FROM `rq_value` WHERE fzx_id='$fzx_id' AND rq_name='".$_GET['rq_name']."' AND rq_size='".$_GET['rq_size']."' ".$rq_id_str." ");
	if(!empty($rq_rs)){
		echo '1';
	}else{
		echo '0';
	}
	exit();
}
$rq_value=array();

if($_GET['id'])
{
	$id=$_GET['id'];
	$rs_rq=$DB->fetch_one_assoc("SELECT * FROM `rq_value` WHERE id='".$_GET['id']."' AND fzx_id='".$fzx_id."'");
	$rq_name=htmlspecialchars($rs_rq['rq_name']);
	$bcj=$rs_rq['bcj'];
	$rq_size=$rs_rq['rq_size'];
	$rq_value=explode(',',$rs_rq['vid']);
	$current_value=array_intersect($all_value,$rq_value);
	
}
$current_nums=0;
if(!empty($current_value))
{
	
	$current_nums=count($current_value);
	$col=6-($current_nums%6);
	$i=0;
	$current_line='';
	foreach($current_value as $k =>$v)
	{
		$i++;
		if($i%6==1)
		{
			$current_line.="<tr>";
		}	
		if($current_nums==$i&&$col!=6)
		$current_line.="<td width='17%'><label><input type='checkbox' name='vid[]' value=".$v." checked='checked'>".$k."</label></td><td colspan=".$col."></td>";
		else
		$current_line.="<td width='17%'><label><input type='checkbox' name='vid[]' value=".$v." checked='checked'>".$k."</label></td>";
		if($i%6==0||($current_nums==$i))
		{
			$current_line.="</tr>";
		}
	}
}
$no_select_line='';
$i=0;
$no_select_arr=array_diff($all_value,$rq_value);
$no_select_nums=count($no_select_arr);
$col=6-($no_select_nums%6);
foreach($no_select_arr as $k2=>$v2)
{
	$i++;
	if($i%6==1)
	{
		$no_select_line.="<tr>";
	}
	if($no_select_nums==$i&&$col!=6)
	{
		$no_select_line.="<td width='17%'><input type='checkbox' name='vid[]' value=".$v2.">".$k2."</td><td colspan=".$col.">&nbsp;</td>";
	}
	else
	{
		$no_select_line.="<td width='17%'><label><input type='checkbox' name='vid[]' value=".$v2.">".$k2."</label></td>";
	}
	if($i%6==0||($all_nums==$i))
	{
		$no_select_line.="</tr>";
	}
}
//显示容器规格
$rq_size_option='';
if(!empty($global['rq_size']))
{
	foreach($global['rq_size'] as $k=>$v)
	{
		if($v==$rq_size)
		{
			$rq_size_option.="<option value=".$v." selected='selected'>".$v."</option>";
		}
		else
		{
			$rq_size_option.="<option value=".$v.">".$v."</option>";
		}
	}
}
//显示分类
$rq_fenlei = "";
$sqlping = $DB->query("select * from n_set where module_name='pingzifenlei'");
while($pingzi = $DB->fetch_assoc($sqlping)){
		if($rs_rq['fenlei']==$pingzi['module_value1']){
			$rq_fenlei .= "<option value={$pingzi['module_value1']} selected='selected'>{$pingzi['module_value1']}";
		}else{
			$rq_fenlei .= "<option value={$pingzi['module_value1']}>{$pingzi['module_value1']}";
		}
}

disp("rq_modi.html");
?>
