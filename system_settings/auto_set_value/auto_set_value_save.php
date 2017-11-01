<?php
/**
  *功能：项目排序
  *作者：zhengsen
  *时间：2015-03-13
  *描述：n_set表存储模板排序
**/
include '../../temp/config.php';
if(empty($_POST['vid'])){
	$_POST['vid']=array();
}
	//此段代码用于模板项目排序
	$px_str		=	implode(',',$_POST['vid']);
	//更新项目
	$xm_px=$DB->fetch_one_assoc("select * from n_set where module_name='xm_px' AND id='".$_POST['xm_px_id']."'");
	if($xm_px['module_value1']==$px_str){
		echo '<div style="margin:0 auto;margin-top:300px;text-align:center;border:1px solid;width:240px;height:50px;line-height:40px">无修改操作,1秒后返回……</div>',"<script>setTimeout('location.href=\'set_value_list.php?xm_px_id=$_POST[xm_px_id]\'',1000);</script>";
		exit();
	}
	$sql		=	"UPDATE `n_set` SET module_value1='".$px_str."' WHERE module_name='xm_px' AND id='".$_POST['xm_px_id']."'";
	$DB->query($sql);
	if($DB->affected_rows() == 1)
		echo '<div style="margin:0 auto;margin-top:300px;text-align:center;border:1px solid;width:240px;height:50px;line-height:40px">修改成功,1秒后返回</div>',"<script>setTimeout('location.href=\'set_value_list.php?xm_px_id=$_POST[xm_px_id]\'',1000);</script>";
	else
		echo '<div style="margin:0 auto;margin-top:300px;text-align:center;border:1px solid;width:240px;height:50px;line-height:40px">修改失败,1秒后返回</div>',"<script>setTimeout('location.href=\'set_value_list.php?xm_px_id=$_POST[xm_px_id]\'',1000);</script>";