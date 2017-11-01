<?php
	/*
		功能：修改分厂数据
		时间：2016/8/31
		作者：高龙
	*/
include("../temp/config.php");//包含配置文件
if($u['userid']==''){//判定用户是否登陆
	nologin();
}

$begin_date = $_POST['begin_date'];//开始日期
$end_date   = $_POST['end_date'];//结束日期
$water_type = $_POST['water_type'];//水样类型

//修改分厂数据
foreach ($_POST['cb_id'] as $key => $value) {
	$vid_arr='';
	$cy_date = $_POST['cy_date'][$key];//采样日期
	$cy_time = $_POST['cy_time'][$key];//采样时间
	$site_name = $_POST['site_name'][$key];//站点名称
	$sid = $DB->fetch_one_assoc("select id from sites where site_name='{$site_name}'");//站点id
	//读取这个站点所有的项目
	foreach ($_POST['jieguo'] as $k => $v) {
		$vid_arr[$k] = $v[$key];
	}
	$json_data = json($vid_arr);//将数组转换成json格式
	if(!empty($sid)){
		$result = $DB->query("update changbu_data set cy_date='{$cy_date}',cy_time='{$cy_time}',site_name='{$site_name}',sid='{$sid}',json_data='{$json_data}' where id={$value}");
	}else{
		$result = $DB->query("update changbu_data set cy_date='{$cy_date}',cy_time='{$cy_time}',json_data='{$json_data}' where id={$value}");
	}
}

if($result){
	echo "<script>alert('保存成功！');location.href='cb_see.php?begin_date={$begin_date}&end_date={$end_date}&water_type={$water_type}'</script>";exit();
}else{
	echo "<script>alert('保存失败！ 请联系管理员！！');location.href='cb_see.php?begin_date={$begin_date}&end_date={$end_date}&water_type={$water_type}'</script>";exit();
}
?>