<?php
/**
  * 功能：采样通知单签字、采样接受人签字时保存表头容器信息 
  * 作者：zhengsen
  * 时间：2014-4-15
**/

include "../temp/config.php";
include "../inc/cy_func.php";
$fzx_id=$u['fzx_id'];
if($u['userid'] == '') nologin();
$cyd_id = $_GET['cyd_id'];
if(!empty($_GET['d']['cy_rwxd_user'])){
	$_GET['d']['status']=1;
}
$rs_cy = $DB->fetch_one_assoc("SELECT * FROM cy WHERE id ='".$_GET['cyd_id']."'");
if($rs_cy['status']<'2'){
	$status='2';
}else{
	$status=$rs_cy['status'];
}
if(!empty($_GET['d']['cy_rwjs_qz_date'])){
	$_GET['d']['status']=$status;
}
update_rec('cy',$_GET['d'], $cyd_id);//更新cy表里数据
//如果是采样接收人签字
if(isset($_GET['d']['cy_rwjs_qz_date'])){
	$cy_rq_info=$DB->fetch_one_assoc("select rq_info from `cy` where id='".$cyd_id."'");
	if(empty($cy_rq_info['rq_info'])){
		//查询批次下的所有项目
		$value_arr=array();
		$sql="select * from `cy_rec` where `cyd_id` ='".$cyd_id."' order by id";
		$res=$DB->query($sql);
		while($rs=$DB->fetch_assoc($res))
		{
			$assay_values=explode(',',$rs['assay_values']);
			$value_arr=array_merge($assay_values,$value_arr);
		}
		//查询出容器关联的项目、保存剂、规格,以json形式储存在cy表里的rq_info
		$rq_sql="select * from `rq_value` where vid!='' AND fzx_id='".$fzx_id."'  order by id";
		$rq_query=$DB->query($rq_sql);
		$rq_data=array();
		while($rq_rs=$DB->fetch_assoc($rq_query))
		{
			$rq_value=explode(',',$rq_rs['vid']);
			if(array_intersect($value_arr,$rq_value))
			{
				$rq_data[$rq_rs['id']]['rq_name']=$rq_rs['rq_name'];
				$rq_data[$rq_rs['id']]['bcj']=$rq_rs['bcj'];
				$rq_data[$rq_rs['id']]['rq_size']=$rq_rs['rq_size'];
				$rq_data[$rq_rs['id']]['vid']=$rq_value;
			}
		}
		if(!empty($rq_data))
		{
			$rq_data_json=JSON($rq_data);
			$up_cy=$DB->query("update `cy` set `rq_info`='".$rq_data_json."' where id='".$_GET['cyd_id']."'");
		}
	}
	//采样接受人签字时更新cy的采样接收人和接受时间
	if($u['userid']==$rs_cy['cy_user']&!empty($rs_cy['cy_user'])||$u['userid']=='admin'){
		$cy_json_arr= json_decode($rs_cy['json'],true);
		$cy_json_arr['userid_img']['cy_rwjs_user']	= $u['userid_img'];//电子签名信息
		$cy_json	= JSON($cy_json_arr);
		$DB->query("update `cy` set `cy_rwjs_user`='".$u['userid']."',`status`='".$status."',`json`='{$cy_json}' where `id`='".$cyd_id."' ");
	}
	if($u['userid']==$rs_cy['cy_user2']&!empty($rs_cy['cy_user2'])){
		$cy_json_arr= json_decode($rs_cy['json'],true);
		$cy_json_arr['userid_img']['cy_rwjs_user2']	= $u['userid_img'];//电子签名信息
		$cy_json	= JSON($cy_json_arr);
		if($rs_cy['cy_rwjs_user']){
			$DB->query("update `cy` set `cy_rwjs_user2`='".$u['userid']."', `status`='".$status."',`json`='{$cy_json}' where `id`='".$cyd_id."'");
		}else{
			$DB->query("update `cy` set `cy_rwjs_user2`='".$u['userid']."', `status`='".$status."',`json`='{$cy_json}' where `id`='".$cyd_id."'");
		}
		
	}

}

gotourl("cy_tzd.php?cyd_id={$cyd_id}&action=cy");
?>
