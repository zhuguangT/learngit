<?php
/*
 *功能：多站点多项目趋势图(待整理)
 */
include_once '../temp/config.php';
if($u['userid']==''){
	nologin();
}
//根据站点、时间、项目获取检测数据,形成数组site_result([site_id][vid][cy_date]=>值)（注意平均值、小于检出限、原始值这些）
$result_arr	= $site_name_arr	= $site_date_arr	= array();
foreach($_POST['sites'] as $group_name_key=>$sites_arr){
	$site_arr	= array_unique(array_merge($site_arr,$sites_arr));
	$site_str	= implode(',',$sites_arr);
	$sql_result	= $DB->query("SELECT ao.sid,ao.site_name,ao.vid,c.cy_date,ao.vd0,ao._vd0,ao.ping_jun,ap.unit 
							FROM cy c LEFT JOIN cy_rec cr ON cr.cyd_id = c.id LEFT JOIN assay_order AS ao ON  ao.cid=cr.id LEFT JOIN assay_pay ap  ON ao.tid=ap.id 
							WHERE c.cy_date BETWEEN '".$_POST['begin_date']."' AND '".$_POST['end_date']."' AND ao.sid IN (".$site_str.") AND ao.vid IN (".$vid_str.") AND cr.zk_flag >= '0' AND ao.hy_flag>=0 AND c.group_name='".$group_name_key."' ORDER BY c.cy_date");
	while ($rs_result = $DB->fetch_assoc($sql_result)){
		if($rs_result['ping_jun']){
			$vd0	= "'".str_replace('<','',$rs_result['ping_jun'])."'";
		}else{
			$vd0	= "'".str_replace('<','',$rs_result['_vd0'])."'";
		}
		//同一站点的全部采样日期
		if(!in_array($rs_result['cy_date'], $site_date_arr[$rs_result['sid']])){
			$site_date_arr[$rs_result['sid']][]	= $rs_result['cy_date'];
		}
		//同一项目的全部采样日期
		if(!in_array($rs_result['cy_date'], $site_date_arr[$rs_result['vid']])){
			$site_date_arr[$rs_result['vid']][]	= $rs_result['cy_date'];
		}
		//站点名称
		$site_name_arr[$rs_result['sid']]	= $rs_result['site_name'];
		//结果值
		$result_site_arr[$rs_result['sid']][$rs_result['vid']][$rs_result['cy_date']]	= $vd0;
		$result_vid_arr[$rs_result['vid']][$rs_result['sid']][$rs_result['cy_date']]	= $vd0;
	}
}
//print_rr($result_arr);
$html_canvas	= '';
$html_script	= '';
$color_arr		= array('#0031EE','#AFD3F7','#E58167','#3FDD2E','#FB0F0F','#866667','#96A7B7','#C7839A','#DA6D0E','#D0C2A6','#B1E151','#29F755','#2985B3','#C6D6F0','#CAFFFF','#97BBCD');
//循环数组，根据不同情况。显示出多个或一个趋势图
if(count($result_site_arr)==1 || count($_POST['vid'])>1){//以站点分类// || count($_POST['vid'])>1
	if(count($result_site_arr)==1){
		$width_canvas	= '';
	}else{
		$width_canvas	= 'float:left;';
	}
	$site_i	= 0;
	foreach ($result_site_arr as $key_site_id => $value) {
		$data	= array();
		$datasets	= '';
		$canvar_name= $site_name_arr[$key_site_id];//站点名称
		$data['labels']	= $site_date_arr[$key_site_id];//x轴，采样时间
		$vid_i	= 0;
		foreach ($value as $key_vid => $value_vd0_arr) {
			//循环所有时间，没有时间的复制null
			$data['datasets'][$key_vid]['label']	= $_SESSION['assayvalueC'][$key_vid];
			foreach($data['labels'] as $value_date){
				if(isset($value_vd0_arr[$value_date])){
					$data['datasets'][$key_vid]['data'][]	= $value_vd0_arr[$value_date];
				}else{
					$data['datasets'][$key_vid]['data'][]	= 'null';
				}
			}
			$line_color	= 'strokeColor : "'.$color_arr[$vid_i].'",'//曲线的颜色
			.'pointColor : "'.$color_arr[$vid_i].'",'//数据点的颜色
			.'pointStrokeColor : "#fff",'//数据点轮廓的颜色
			.'pointHighlightFill : "#fff",'//鼠标移动到上面的时候，数据点的颜色
			.'pointHighlightStroke : "'.$color_arr[$vid_i].'",';//鼠标移动到上面的时候，数据点轮廓的颜色';
			$datasets	.= '{label : "'.$data['datasets'][$key_vid]['label'].'",'.$line_color.' data : ['.implode(',',$data['datasets'][$key_vid]['data']).']},';
			$vid_i++;
		}
		$datasets	= substr($datasets,0,-1);
		$data	= '{
			labels : ["'.implode('","',$data['labels']).'"],
			datasets : [
				'.$datasets.'
			]
		}';
		//把当前站点的趋势图代码记录下来
		$html_canvas	.= '<fieldset style="width:800px;padding:35px;margin:20px auto;border:2px solid #A8A8A8;'.$width_canvas.'"><!--<legend style="width:auto;margin:0 auto;">'.$canvar_name.'</legend>--><div style="width:800px;height:400px;"><canvas class="ceshi"></canvas><p style="text-align:center;font-weight:bold;font-size:16px;">'.$canvar_name.'</p></div></fieldset>';
		$html_script	.= 'var ctx'.$site_i.' = $(".ceshi").get('.$site_i.').getContext("2d");
							new Chart(ctx'.$site_i.').Line('.$data.',defaults );';
		$site_i++;
	}
}else{
	//以项目分类
	$vid_i	= 0;
	foreach ($result_vid_arr as $key_vid => $value) {
		$data	= array();
		$datasets	= '';
		$canvar_name= $_SESSION['assayvalueC'][$key_vid];//趋势图名称
		$data['labels']	= $site_date_arr[$key_vid];//x轴，采样时间
		$site_i	= 0;
		foreach ($value as $key_site_id => $value_vd0_arr) {
			//x轴，采样时间
			//$data['labels']	= array_keys($value_vd0_arr);
			//循环所有时间，没有时间的复制null
			$data['datasets'][$key_site_id]['label']	= $site_name_arr[$key_site_id];
			foreach($data['labels'] as $value_date){
				if(isset($value_vd0_arr[$value_date])){
					$data['datasets'][$key_site_id]['data'][]	= $value_vd0_arr[$value_date];
				}else{
					$data['datasets'][$key_site_id]['data'][]	= 'null';
				}
			}
			$line_color	= 'strokeColor : "'.$color_arr[$site_i].'",'//曲线的颜色
			.'pointColor : "'.$color_arr[$site_i].'",'//数据点的颜色
			.'pointStrokeColor : "#fff",'//数据点轮廓的颜色
			.'pointHighlightFill : "#fff",'//鼠标移动到上面的时候，数据点的颜色
			.'pointHighlightStroke : "'.$color_arr[$site_i].'",';//鼠标移动到上面的时候，数据点轮廓的颜色';
			$datasets	.= '{label : "'.$data['datasets'][$key_site_id]['label'].'",'.$line_color.' data : ['.implode(',',$data['datasets'][$key_site_id]['data']).']},';
			$site_i++;
		}
		$datasets	= substr($datasets,0,-1);
		$data	= '{
			labels : ["'.implode('","',$data['labels']).'"],
			datasets : [
				'.$datasets.'
			]
		}';
		//把当前站点的趋势图代码记录下来
		$html_canvas	.= '<fieldset style="width:800px;padding:35px;margin:20px auto;border:2px solid #A8A8A8;float:left;"><!--<legend style="width:auto;margin:0 auto;">'.$canvar_name.'</legend>--><div style="width:800px;height:400px;"><canvas class="ceshi"></canvas><p style="text-align:center;font-weight:bold;font-size:16px;">'.$canvar_name.'</p></div></fieldset>';
		$html_script	.= 'var ctx'.$vid_i.' = $(".ceshi").get('.$vid_i.').getContext("2d");
							new Chart(ctx'.$vid_i.').Line('.$data.',defaults );';
		$vid_i++;
	}
}
//disp("custom_chart.html");
disp("custom_chart2.html");
?>