<?php
	/*
		功能:返回统计报告中所需要的配置信息
		作者:高龙
		时间:2016/4/29
		描述:将获取统计报告配置信息的一段代码封装起来减少代码冗余
	*/
//根据set_id来查询该报告所需要的配置信息
if($_GET['set_id']){
	$cg_rs=$DB->fetch_one_assoc("SELECT * FROM `baogao_list` WHERE id='".$_GET['set_id']."'");
	if(!empty($cg_rs['result_set'])){
		$_POST=json_decode($cg_rs['result_set'],true);
	}
	if(!empty($cg_rs['gx_set'])){
		$mb_arr=json_decode($cg_rs['gx_set'],true);
	}
}else if(!empty($_POST)){
	if(isset($jzjz)){
		//$xm_arrreny	= '{"show_note":"yes","result_mb_name":"sgcbg","result_xmb_name":"tjbg_month_ryxbg","mbjbls":"4","xmbjbls":"4","canshu":{"jichu":["title","bumen","tb_date"],"canshu":["tr_td_num"]}}';
		$xm_arrreny	= '{"show_note":"yes","result_mb_name":"sgcbg","result_php_name":"tjbg_cgmonth_sgcbg.php","mbjbls":"2","monthbgfunc":"tjbg_cgmonth_sgcbg_func.php","canshu":{"jichu":["title","bumen","tb_date"]},"mbhtd":["ypbh"],"mbhtd2":["sybh"]}';
	}else{
		//$xm_arrreny	= '{"show_note":"yes","result_mb_name":"sgcbg","result_xmb_name":"tjbg_month_xbg","mbjbls":"10","xmbjbls":"1","mbtd":["ccbh","dmmc","bh","y","r","s","f","sw","ll","qw"],"xmbtd":["ccbh"],"canshu":{"jichu":["title","bumen","tb_date"],"canshu":["tr_td_num"]}}';
		$xm_arrreny	= '{"show_note":"yes","result_mb_name":"sgcbg","result_php_name":"tjbg_cgmonth_sgcbg.php","mbjbls":"2","monthbgfunc":"tjbg_cgmonth_sgcbg_func.php","canshu":{"jichu":["title","bumen","tb_date"]},"mbhtd":["ypbh"],"mbhtd2":["sybh"]}';
	}
	$mb_arr=json_decode($xm_arrreny,true);
}
if(empty($mb_arr)){
	echo "<script>alert('警告：您在数据库里没有进行该表的个性设置，请联系管理员！！'); window.close();</script>";
	exit();
}

if(empty($_POST)){
	echo "<script>alert('警告：您没有在“设置”里进行报表内容设置，请设置后再点击查看！！。'); window.close();</script>";
	exit();
}
$monthbgfunc	= "tjbg_cgmonth_bg_func.php";
if(!empty($mb_arr['monthbgfunc'])){//判断包含不同的报告函数
	$monthbgfunc = $mb_arr['monthbgfunc'];
}

$time_duan = $_POST['choose_date'];//获取具体的时间段
$mbname = $mb_arr['result_mb_name'];//获取该报告的模板名称
$xmbname = $mb_arr['result_xmb_name'];//获取该报告续模板名称
$mbjbls  = $mb_arr['mbjbls'];//模板基本列数（除项目以外的列数）
$xmbjbls = $mb_arr['xmbjbls'];//续模板基本列数（除项目以外的列数）
$mbtd = $mb_arr['mbtd'];//获取模板的<td></td>列（除项目列以外的剩余的列）
$xmbtd = $mb_arr['xmbtd'];//获取续模板的<td></td>列（除项目列以外的剩余的列）
if(!empty($mb_arr['mb2td'])){//获取水质类别 超标项目等<td></td>列
	$mb2td = $mb_arr['mb2td'];
}
$col_max = $_POST['col_max'];//获取每页的站点数
$row_max = $_POST['row_max'];//获取每页的项目数

//获取批次名称及批次名称下的站点id并将数组付给$_POST['sites']
if(!empty($_POST['alone_sites'])){
	$sid = implode(',', $_POST['alone_sites']);
	$result = $DB->query("SELECT site_id,group_name FROM site_group WHERE id in({$sid})");
	while ($pmzid = $DB->fetch_assoc($result)) {
		$pmzid_arr[$pmzid['group_name']][] = $pmzid['site_id']; 
	}
	$_POST['sites'] = $pmzid_arr;
}
?>