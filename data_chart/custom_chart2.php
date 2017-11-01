<?php
/*
 *功能：多站点多项目趋势图(待整理)
 */
include_once '../temp/config.php';
if($u['userid']==''){
	nologin();
}
if(!empty($_POST)){
	if(!empty($_POST['title'])){
		$title	= $_POST['title'];
	}else{
		$title	= '任意站点、任意项目趋势图查看';
	}
	//echo $json_zhi	= JSON($_POST);
	//$DB->query("insert into `n_set` set `module_name`='chart',`module_value1`='自定义趋势图查看',`module_value2`='{$json_zhi}'");
	//根据站点、时间、项目获取检测数据,形成数组site_result([site_id][vid][cy_date]=>值)（注意平均值、小于检出限、原始值这些）
	$result_site_arr= $result_vid_arr	= $site_name_arr	= $site_date_arr	= array();
	$html_site	= $html_vid	= '';
	$site_arr	= array();
	//由于代码改动这里重新赋值变量
	if(!empty($_POST['time_start'])){
		$begin_date		= $_POST['time_start'];
	}else if(!empty($_POST['begin_date'])){
		$begin_date		= $_POST['begin_date'];
	}
	if(!empty($_POST['time_end'])){
		$end_date		= $_POST['time_end'];
	}else if(!empty($_POST['end_date'])){
		$end_date		= $_POST['end_date'];
	}
	if(!empty($_POST['alone_vid'])){
		$vids		= $_POST['alone_vid'];
	}else if(!empty($_POST['vid'])){
		$vids		= $_POST['vid'];
	}
	foreach($_POST['sites'] as $group_name_key=>$sites_arr){
		$site_arr	= array_unique(array_merge($site_arr,$sites_arr));
		$site_str	= implode(',',$sites_arr);
		$sql_result	= $DB->query("SELECT ao.sid,ao.site_name,ao.vid,cr.cy_date,ao.vd0,ao._vd0,ao.ping_jun,ap.unit 
								FROM cy_rec cr LEFT JOIN assay_order AS ao ON  ao.cid=cr.id LEFT JOIN assay_pay ap  ON ao.tid=ap.id 
								WHERE cr.cy_date BETWEEN '".$begin_date."' AND '".$end_date."' AND ao.sid IN (".$site_str.") AND ao.vid IN (".implode(',',$vids).") AND cr.zk_flag >= '0' AND ao.hy_flag>=0 ORDER BY cr.cy_date");
		while ($rs_result = $DB->fetch_assoc($sql_result)){
			if($rs_result['ping_jun']){
				if(stristr($rs_result['ping_jun'],'<')){
					$vd0	= (str_replace('<','',$rs_result['ping_jun']))/2;
				}else{
					$vd0	= $rs_result['ping_jun'];//"'".str_replace('<','',$rs_result['ping_jun'])."'";
				}
			}else{
				if(stristr($rs_result['_vd0'],'<')){
					$vd0	= (str_replace('<','',$rs_result['_vd0']))/2;
				}else{
					$vd0	= $rs_result['_vd0'];//"'".str_replace('<','',$rs_result['_vd0'])."'";
				}
			}
			$vd0	= "'".$vd0."'";
			//同一站点的全部采样日期
			if(@!in_array($rs_result['cy_date'], $site_date_arr[$rs_result['sid']])){
				$site_date_arr[$rs_result['sid']][]	= $rs_result['cy_date'];
			}
			//同一项目的全部采样日期
			if(@!in_array($rs_result['cy_date'], $site_date_arr[$rs_result['vid']])){
				$site_date_arr[$rs_result['vid']][]	= $rs_result['cy_date'];
			}
			//post存储 site
			if(@!array_key_exists($rs_result['sid'],$result_site_arr)){
				$html_site	.= $rs_result['site_name']."<input type='hidden' name='sites[{$rs_result['group_name']}][]' value='{$rs_result['sid']}' />、";
			}
			//post存储 vid
			if(@!array_key_exists($rs_result['vid'],$result_vid_arr)){
				$html_vid	.= $_SESSION['assayvalueC'][$rs_result['vid']]."<input type='hidden' name='vid[]' value='{$rs_result['vid']}' />、";
			}
			//站点名称
			$site_name_arr[$rs_result['sid']]	= $rs_result['site_name'];
			//结果值
			$result_site_arr[$rs_result['sid']][$rs_result['vid']][$rs_result['cy_date']]	= $vd0;
			$result_vid_arr[$rs_result['vid']][$rs_result['sid']][$rs_result['cy_date']]	= $vd0;
		}
	}
	$html_canvas	= '';
	$html_script	= '';
	//循环数组，根据不同情况。显示出多个或一个趋势图
	if(empty($_POST['how_show']) && (count($result_site_arr)==1 || count($vids)>1)){
		$_POST['how_show']	= "按照站点分类查看";
	}
	if($_POST['how_show'] == '按照站点分类查看'){//以站点分类// || count($_POST['vid'])>1
		if(count($result_site_arr)==1){
			$width_canvas	= 'width:100%;';
		}else{
			$width_canvas	= 'width:49%;float:left;';
		}
		$site_i	= 0;
		foreach ($result_site_arr as $key_site_id => $value) {
			$data	= array();
			$series	= '';
			$canvas_name= $site_name_arr[$key_site_id];//站点名称
			$data['labels']	= $site_date_arr[$key_site_id];//x轴，采样时间
			$vid_i	= 0;
			$lengend	= '';
			foreach ($value as $key_vid => $value_vd0_arr) {
				if($key_vid == '104'){
					$_SESSION['assayvalueC'][$key_vid]	= '耗氧量（CODм𝔫法，以O₂计）';
				}
				$data['series'][$key_vid]['label']	= $_SESSION['assayvalueC'][$key_vid]."";
				foreach($data['labels'] as $value_date){
					if(isset($value_vd0_arr[$value_date])){
						$data['series'][$key_vid]['data'][]	= $value_vd0_arr[$value_date];
					}else{
						$data['series'][$key_vid]['data'][]	= '"-"';
					}
				}
				$lengend.= '"'.$data['series'][$key_vid]['label'].'",';
				$series	.= '{name : "'.$data['series'][$key_vid]['label'].'","type":"line", data : ['.implode(',',$data['series'][$key_vid]['data']).']},';
				$vid_i++;
				//echo "<br><br>";
			}
			$lengend= substr($lengend, 0,-1);
			$series	= substr($series,0,-1);
			$data	= '{
				tooltip: {
	                show: true,
	                trigger: "axis",
	                enterable : false,
	            },
	            dataZoom: {
			        show: true,
			        start : 0,
			        end : 100,
			    },
	            yAxis : [{name : "指标含量",min : 0}],
				legend: {
	                data:['.$lengend.']
	            },
	            xAxis : [
	                {
	                	name : "采样时间",
	                    data : ["'.implode('","',$data['labels']).'"],
	                }
	            ],
				series : [
					'.$series.'
				],
				toolbox: {
	        		show : true,
					feature : {
						magicType: {show: true, type: ["line", "bar"]},
						saveAsImage : {
			                show : true,
			                title : "保存为图片",
			                type : "jpeg",
			                lang : ["点击本地保存"] 
		            	}
		        	}
		        }
			}';
			//把当前站点的趋势图代码记录下来
			$html_canvas	.= '<fieldset style="min-width:630px;padding:10px;margin:20px auto;border:2px solid #A8A8A8;'.$width_canvas.'"><!--<legend style="width:auto;margin:0 auto;">'.$canvas_name.'</legend>--><div style="width:100%;height:300px;" class="ceshi"></div><p style="text-align:center;font-weight:bold;font-size:16px;">'.$canvas_name.'</p></fieldset>';
			$html_script	.= 'var ctx'.$site_i.' = echarts.init($("div.ceshi").get('.$site_i.'));
								ctx'.$site_i.'.setOption('.$data.');';
			$site_i++;
		}
	}else{
		//以项目分类
		$vid_i	= 0;
		foreach ($result_vid_arr as $key_vid => $value) {
			//查询出项目的标准值
			//$jcxz	= str_replace(array('＜','＞','<','≤','>','≥'),'', $jcxz);
			if(count($result_vid_arr)==1){
				$width_canvas	= 'width:100%;';
			}else{
				$width_canvas	= 'width:49%;float:left;';
			}
			$data	= array();
			$series	= '';
			$canvas_name	= $_SESSION['assayvalueC'][$key_vid];//趋势图名称
			$data['labels']	= $site_date_arr[$key_vid];//x轴，采样时间
			$site_i	= 0;
			$lengend= '';
			foreach ($value as $key_site_id => $value_vd0_arr) {
				//循环所有时间，没有时间的复制null
				$data['series'][$key_site_id]['label']	= $site_name_arr[$key_site_id];
				foreach($data['labels'] as $value_date){
					if(isset($value_vd0_arr[$value_date])){
						$data['series'][$key_site_id]['data'][]	= $value_vd0_arr[$value_date];
					}else{
						$data['series'][$key_site_id]['data'][]	= '"-"';
					}
				}
				$lengend.= '"'.$data['series'][$key_site_id]['label'].'",';
				$series	.= '{name : "'.$data['series'][$key_site_id]['label'].'","type":"line", data : ['.implode(',',$data['series'][$key_site_id]['data']).']},';
				$site_i++;
			}
			$lengend= substr($lengend, 0,-1);
			$series	= substr($series,0,-1);
			$data	= '{
				tooltip: {
	                show: true,
	                trigger: "axis",
	                enterable : false
	            },
	            dataZoom: {
			        show: true,
			        start : 0,
			        end : 100,
			    },
	            yAxis : [{name : "指标含量",min : 0}],
				legend: {
	                data:['.$lengend.']
	            },
	            xAxis : [
	                {
	                	name : "采样时间",
	                    data : ["'.implode('","',$data['labels']).'"],
	                }
	            ],
				series : [
					'.$series.'
				],
				toolbox: {
	        		show : true,
					feature : {
						magicType: {show: true, type: ["line", "bar"]},
						saveAsImage : {
			                show : true,
			                title : "保存为图片",
			                type : "jpeg",
			                lang : ["点击本地保存"] 
		            	}
		        	}
		        }
			}';
			//把当前站点的趋势图代码记录下来
			$html_canvas	.= '<fieldset style="min-width:630px;padding:10px;margin:20px auto;border:2px solid #A8A8A8;'.$width_canvas.'"><!--<legend style="width:auto;margin:0 auto;">'.$canvas_name.'</legend>--><div style="width:100%;height:300px;" class="ceshi"></div><p style="text-align:center;font-weight:bold;font-size:16px;">'.$canvas_name.'</p></fieldset>';
			$html_script	.= 'var ctx'.$vid_i.' = echarts.init($("div.ceshi").get('.$vid_i.'));
								ctx'.$vid_i.'.setOption('.$data.');';
			$vid_i++;
		}
	}
}
if(!empty($html_site)){
	$html_site	= substr($html_site,0,-3);
}
if(!empty($html_vid)){
	$html_vid	= substr($html_vid,0,-3);
}
disp("custom_chart2.html");
?>
