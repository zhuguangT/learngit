<?php
/**
  * 功能：保存容器信息
  * 作者：zhengsen
  * 时间：2014-4-11
**/
include '../../temp/config.php';
if(empty($u['userid'])){
	nologin();
}

$fzx_id=$u['fzx_id'];
//如果获得id就执行更新操作
if($_POST['id'])
{
	if(!empty($_POST['vid']))
	{
		$vid=implode(",",$_POST['vid']);
	}
	else
	{
		$vid='';
	}
	$sql="UPDATE `rq_value` SET `rq_name`='".$_POST['rq_name']."',`bcj`='".$_POST['bcj']."',`rq_size`='".$_POST['rq_size']."',`vid`='".$vid."',`mr_shu`='".$_POST['mr_shu']."',`fenlei`='".$_POST['fenlei']."' WHERE id='".$_POST['id']."'" ;
	$query=$DB->query($sql);
}
//如果没有获得id就执行插入操作
else
{
	if(!empty($_POST['vid']))
	{
		$vid=implode(",",$_POST['vid']);
	}
	else
	{
		$vid='';
	}
	$sql="insert into `rq_value` (`fzx_id`,`rq_name`,`bcj`,`rq_size`,`vid`,`mr_shu`,`fenlei`) values('".$fzx_id."','".$_POST['rq_name']."','".$_POST['bcj']."','".$_POST['rq_size']."','".$vid."','".$_POST['mr_shu']."','".$_POST['fenlei']."')";
	$query=$DB->query($sql);
}
echo "<script>location.href='rq_list.php'</script>";
?>
