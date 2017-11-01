<?php
/**
  * 功能：显示、修改、删除容器列表内容，
  * 作者：zhengsen
  * 时间：2014-4-10
**/
include '../../temp/config.php';
//查询容器的信息
//导航
$trade_global['daohang'] = array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'采样容器列表','href'=>'./system_settings/cy_rq_manage/rq_list.php')
);
if(empty($u['userid'])){
	nologin();
}
$fzx_id=$u['fzx_id'];
//ajax删除容器设置
if($_GET['action']=='del')
{
	if($_GET['id'])
	{
		$del_sql="DELETE FROM `rq_value` WHERE id='".$_GET['id']."'";
		$del_query=$DB->query($del_sql);
		if($del_query)
			echo '1';
		else
			echo '0';
	}
	exit();
}
//查询出所有的项目
$sql_all_value="SELECT av.* FROM assay_value av JOIN xmfa x ON av.id=x.xmid  AND x.act='1' AND x.fzx_id='".$fzx_id."' ORDER BY av.id";
$query_all_value=$DB->query($sql_all_value);
while($rs_all_value=$DB->fetch_assoc($query_all_value))
{
	$all_value[$rs_all_value['value_C']]=$rs_all_value['id'];
}
$sql_rq="SELECT * FROM `rq_value` WHERE fzx_id='".$fzx_id."'";
$query_rq=$DB->query($sql_rq);
$i=0;
$rq_line="";
$rq_name_arr=array();
while($rs_rq=$DB->fetch_assoc($query_rq))
{
	$i++;
	$vid_arr=explode(',',$rs_rq['vid']);
	$value_C=array_intersect($all_value,$vid_arr);
	$vname_str='';
	$vname_arr=array();
	foreach($value_C as $k=>$v)
	{
		$vname_arr[]=$k;
	}
	if(!in_array($rs_rq['rq_name'],$rq_name_arr)){
		$rq_name_arr[$i]=$rs_rq['rq_name'];
		$class=$i;
	}else{
		$calss=array_search($rs_rq['rq_name'],$rq_name_arr);
	}
	$vname_str=implode(',',$vname_arr);
	//style='white-space: nowrap;text-overflow: ellipsis;overflow: hidden;'(用来隐藏超出的部分)
	$rq_line.="<tr class='{$class}'><td>{$i}</td><td>{$rs_rq['rq_name']}</td><td>".$rs_rq['rq_size']."</td><td>".$rs_rq['mr_shu']."</td><td>".$rs_rq['bcj']."</td><td >".$vname_str."</td><td class='action-buttons'><a class='green icon-edit bigger-130' href='rq_modi.php?id=".$rs_rq['id']."' title='修改'></a>&nbsp;|&nbsp;<a class='red icon-remove bigger-140' href='#' onclick='del_rq(this,".$rs_rq['id'].")' title='删除'></a></td></tr>";
}
//获得容器名称的下拉菜单
$rq_name_option="";
foreach($rq_name_arr as $key=>$value)
{
	$values=htmlspecialchars($value);
	$rq_name_option.="<option value='{$key}'>{$values}</option>";
}
disp("rq_list.html");
?>