<?php
/**
  *功能：项目排序
  *作者：zhengsen
  *时间：2015-03-13
  *描述：n_set表存储模板排序
**/
include '../../temp/config.php';
//导航
$trade_global['daohang'] = array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'项目排序列表','href'=>'./system_settings/auto_set_value/set_value_list.php')
);
//将模板的排序更新到assay_value表中
if($_GET['action'] == 'yingyong'){
	if(!empty($_GET['mb_id'])){
		$mb_value=$DB->fetch_one_assoc("SELECT * FROM n_set WHERE `id`='{$_GET['mb_id']}'");
		$tmp_arr	= explode(",",$mb_value['module_value1']);
		$DB->query("UPDATE `assay_value` SET `seq`='999' WHERE 1");
		foreach($tmp_arr as $key=>$value){
			$DB->query("UPDATE `assay_value` SET `seq`='{$key}' WHERE `id`='{$value}'");
		}
		echo "yes";
	}else{
		echo "no";
	}
	exit;
}
//删除模板信息
if($_GET['del_id']){
	$del_query=$DB->query("DELETE FROM n_set WHERE id='".$_GET['del_id']."'");
	if(mysql_affected_rows()){
		echo "<script>alert('删除成功！');location.href='set_value_list.php';</script>";
	}else{
		echo "<script>alert('删除失败！请联系管理员！');location.href='set_value_list.php';</script>";
	}
}
//ajax添加模板名称
if($_GET['add_name']){
	$query_mb=$DB->query("SELECT * FROM n_set WHERE module_value2='".$_GET['add_name']."'");
	if(!mysql_num_rows($query_mb)){
		$DB->query("INSERT INTO n_set(module_name,module_value2)values('xm_px','".$_GET['add_name']."')");
		if(mysql_affected_rows()){
			echo mysql_insert_id();//插入成功
		}else{
			echo 0;//插入失败
		}
	}else{
		echo "存在";//已经存在
	}
	exit();
}
//修改模板的名称
if($_GET['update_id']&&$_GET['update_name']){
	$DB->query("UPDATE n_set SET module_value2='".$_GET['update_name']."' WHERE id='".$_GET['update_id']."'");
	if(mysql_affected_rows()){
		echo "<script>alert('修改成功！');location.href='set_value_list.php?xm_px_id=$_GET[update_id]';</script>";
	}else{
		echo "<script>alert('修改失败！请联系管理员！');location.href='set_value_list.php?xm_px_id=$_GET[update_id]';</script>";
	}
}
//查出所有项目信息
$sql	=	"SELECT `id`,`value_C` FROM `assay_value`  ORDER BY `id`";
$res	=	$DB->query( $sql );
while ( $row = $DB->fetch_assoc( $res ) ) {
	$av_id[]	=	$row['id'];
	$assay_values[$row['id']]=$row['value_C'];	
}
//查询出所有的模板
$option_px_mb='';
$n_set_sql="SELECT * FROM n_set WHERE module_name='xm_px'";
$n_set_query=$DB->query($n_set_sql);
$i=1;
while($n_set_rs=$DB->fetch_assoc($n_set_query)){
	if($i==1){
		$xm_px_id=$n_set_rs['id'];
	}
	if($_GET['xm_px_id']==$n_set_rs['id']){
		$option_px_mb.="<option value=".$n_set_rs['id']." selected=\"selected\">".$n_set_rs['module_value2']."</option>";
	}else{
		$option_px_mb.="<option value=".$n_set_rs['id'].">".$n_set_rs['module_value2']."</option>";
	}
	$i++;
}
if(empty($option_px_mb)){
	$option_px_mb="<option value=0>请先创建模板</option>";
}
if(empty($_GET['xm_px_id'])){
	$_GET['xm_px_id']=$xm_px_id;
}
$px_id_str='';
if(!empty($_GET['xm_px_id'])){
	$px_id_str=" AND id=".$_GET['xm_px_id'];
}
//查出模板信息
$n_set	=	"SELECT * FROM `n_set` WHERE module_name='xm_px' $px_id_str";

$n_row	=	$DB->fetch_one_assoc($n_set);
$xm_px			=	empty($n_row['module_value1']) ? array() : explode(',',$n_row['module_value1']);	//项目排列顺序
$yxz_count		=	count($xm_px);		//选择区项目个数
$av_count		=	count($assay_values);	//项目总个数
$wxz_count		=	$av_count-$yxz_count;	//未选择区项目个数
//找出在排序列表中没有的项目id补在最后
$xm_diff		=	array_diff($av_id,$xm_px);
//已经排序的项目
foreach($xm_px as $key=>$value){ 
		$select_value.='<div class="draggable" title='.$assay_values[$value].'>【】'.$assay_values[$value].'<input name="vid[]" value="'.$value.'" type="hidden"></div>';
}
//没有排序的项目
foreach($xm_diff as $key=>$value){
		$no_select_value.='<div class="draggable" title='.$assay_values[$value].'>【】'.$assay_values[$value].'<input name="vid[]" value="'.$value.'" type="hidden"></div>';           
}
unset($n_row);
disp("set_value_list.html");
?>
