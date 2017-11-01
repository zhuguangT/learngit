<?php
include("../temp/config.php");
if($u['userid']==''){
	nologin();
}
$fzx_id=$u['fzx_id'];
$hub_rs=$DB->fetch_one_assoc("SELECT * FROM hub_info WHERE id='".$fzx_id."'");
$xz_area=$hub_rs['xz_area'];//所属区域
$water_type=$_POST['water_type'];//水样类型

//查询每个站点的id放入$site_id数组中
foreach($_POST['site_name'] as $key=>$value){
	if(!empty($value)){
		$siteid = $DB->fetch_one_assoc("select `id` from `sites` where `site_name`='{$value}'");
		$site_id[$key] = $siteid['id'];
	}else{
		$site_id[$key] = '';
	}
}
$vid_data_old	= array();
if(!empty($_POST['site_name'])){
	foreach($_POST['site_name'] as $key=>$value){
		if(!empty($value)){
			$year=$month= $day='';
			$cy_date	= $_POST['cy_date'][$key];//采样日期
			$cy_time    = $_POST['cy_time'][$key];//采样时间
			$sid        = $site_id[$key];//站点id
			$cy_date_arr= explode('-',$cy_date);
			$year		= $cy_date_arr[0];//年
			$month		= $cy_date_arr[1];//月
			$day		= $cy_date_arr[2];//日
			$site_name	= $value;//站点名称

			foreach($_POST['vid'] as $key2=>$value2){
				if($value2[$key]==''){
					$value2[$key]='--';
				}
				$value2[$key]	= str_replace('＜','<',$value2[$key]);
				//保存原始结果
				if(empty($_POST['vid_old'][$key2][$key]) || _round($_POST['vid_old'][$key2][$key],'2')!=$value2[$key]){
					$vid_data_old[$key2]= $value2[$key];//保存最新原始结果
				}else{
					$vid_data_old[$key2]= $_POST['vid_old'][$key2][$key];//不更改原始结果
				}
				$vid_data[$key2]	= $value2[$key];
			}
			$json_data		= JSON($vid_data);
			$json_data_old	= JSON($vid_data_old);
			$cb_rs			= array();

			//查询该站点是否已经存在
			$cb_rs	= $DB->fetch_one_assoc("SELECT * FROM `changbu_data` WHERE cy_date='".$cy_date."' AND site_name='".$site_name."' AND fzx_id='".$fzx_id."' AND `cy_time`='".$cy_time."' AND water_type='".$water_type."'");
			//返回记录就更新否则就插入新数据
			if(!empty($cb_rs)){
				$status=$DB->query("UPDATE `changbu_data` SET `cy_date`='".$cy_date."',`site_name`='".$site_name."',`json_data`='".$json_data."',`json_data_old`='".$json_data_old."' WHERE id='".$cb_rs['id']."'");
				if(!$status){
					echo "<script>alert('保存失败,请联系管理员');location.href='cb_input.php'</script>";exit();
				}
			}else{
				$DB->query("INSERT INTO changbu_data(year,month,day,fzx_id,site_name,sid,cy_time,xz_area,water_type,cy_date,json_data,json_data_old)values('".$year."','".$month."','".$day."','".$fzx_id."','".$site_name."','".$sid."','".$cy_time."','".$xz_area."','".$water_type."','".$cy_date."','".$json_data."','".$json_data_old."')");
				if(mysql_affected_rows()){
					$status=1;
				}else{
					$status=0;
				}
				if(!$status){
					echo "<script>alert('保存失败,请联系管理员！');location.href='cb_input.php'</script>";exit();
				}
			}
		}
	}
}
if($status){
	echo "<script>alert('保存成功！');location.href='cb_input.php?water_type={$water_type}'</script>";exit();
}
?>