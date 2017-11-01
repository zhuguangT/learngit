<?php
/*
	功能：查看和下载常规月报信息
	时间：2016/6/2
	作者：高龙
*/
	//截取月份
	$time_months = substr($time_start,0,7);

	//接受配置信息
	$xmcs = $mb_arr['xmcs'];
	
	//配置一个数组
	$h2l = array(
		'zg' => "<td>最高</td>",
		'zd' =>"<td>最低</td>",
		'pj' =>"<td>平均</td>",
		'jycs' =>"<td>检验次数</td>",
		'cbcs' =>"<td>超标次数</td>",
		'hgcs' =>"<td>合格次数</td>",
		'hgl' =>"<td>合格率(%)</td>",
	);

	//配置数据
	foreach($xmcs as $v){
		$h2.=$h2l[$v];
	}
	
	//站点合并的列数
	$hpls = count($xmcs);

	//获取站点名称
	$site_name_td=$site_lie_td='';
	foreach ($site_infor as $site_value) {
		$site_name_td.= "<td colspan=".$hpls.">".$site_value['site_name']."</td>";
		$site_lie_td.=$h2;
	}

	//标题需要合并的列
	$cols1=(count($site_inf)*$hpls);
	$z_cols=$mbjbls+$cols1;

	if($mb_arr['xzq_inf'] === 'xzq_inf'){//***
		//判断有没有分区数据
		if(empty($xzq_inf)){
			echo "<script>alert('您所选择的站点没有所属的行政区'); window.close();</script>";exit();
		}

		//获取分区名称
		$site_name_td=$site_lie_td='';
		foreach ($xzq_inf as $key=>$value) {
			$site_name_td.= "<td colspan=".$hpls.">".$key."</td>";
			$site_lie_td.=$h2;
		}

		//标题需要合并的列
		$cols1=(count($xzq_inf)*$hpls);
		$z_cols=$mbjbls+$cols1;

		//计算所有分区下的项目的最高、最低、平均
		foreach($xm_arr as $k1=>$v1){//**
			foreach($xzq_inf as $k2=>$v2){///**
				foreach($v2 as $k3=>$v3){
					if(!empty($return_max_min[$k3][$k1])){
						$zg[] = (string)$return_max_min[$k3][$k1][$time_months]['max'];
						$zd[] = (string)$return_max_min[$k3][$k1][$time_months]['min'];
						$pj[] = (string)$return_max_min[$k3][$k1][$time_months]['avg']['value'];
					}else{
						$zg[] = '/';
						$zd[] = '/';
						$pj[] = '/';
					}
				}
				$sznum = count($v2);
				$xzq_max_min[$k2][$k1][$time_months]['max'] = gdp($zg,$sznum)['zg'];
				$xzq_max_min[$k2][$k1][$time_months]['min'] = gdp($zd,$sznum)['zd'];
				$xzq_max_min[$k2][$k1][$time_months]['avg']['value'] = gdp($pj,$sznum)['pj'];
				$zg=$zd=$pj='';//将这些变量赋空避免影响下一次的循环
			}///**
		}//**

		//计算所有分区下的项目的检验次数、合格次数、合格率
		foreach($xm_arr as $k1=>$v1){//**
			foreach($xzq_inf as $k2=>$v2){///**
				foreach($v2 as $k3=>$v3){
					if(!empty($return_jc_cb_sum[$k3][$k1])){
						$jc[] = $return_jc_cb_sum[$k3][$k1][$time_months]['jc_sum'];
						$cb[] = $return_jc_cb_sum[$k3][$k1][$time_months]['cb_sum'];
					}else{
						$jc[] = '/';
						$cb[] = '/';
					}
				}
				if(array_sum($jc) == '0'){

				}else{
					$xzq_jc_cb_sum[$k2][$k1][$time_months]['jc_sum'] = array_sum($jc);
					$xzq_jc_cb_sum[$k2][$k1][$time_months]['cb_sum'] = array_sum($cb);
				}
				$jc=$cb='';//将这些变量赋空避免影响下一次的循环
			}///**
		}//**

		$site_infor=$return_max_min=$return_jc_cb_sum='';//赋空

		//替换值
		$site_infor=$xzq_inf;
		$return_max_min = $xzq_max_min;
		$return_jc_cb_sum = $xzq_jc_cb_sum;
	}//***

	//开始计算报表的模板
	$i = 0;
	foreach($xm_arr as $ks=>$vs){//**
		$i++;
		$resultss[$ks] = $vs;
		$week_bg_line.= dqsj($resultss,$unit_arr,$site_infor,$return_max_min,$return_jc_cb_sum,$time_months,$xmcs);//调用读取数组数据的函数
		if($i == count($xm_arr)){//计算该报告的模板
			$tjbg=temp("any_data/".$mbname);
		}
		$resultss = '';//赋空为了防止影响下一次循环
	}//**

//封装一个用于求最高、最低和平均值的函数
function gdp($ccsjg,$sznum){//**
	foreach($ccsjg as $v){//判断数组中是否有"/"
		if($v !== '/'){
			$xiegang = true;
		}
	}
	if($xiegang){
		foreach($ccsjg as $key=>$vs){//删除数组中的'/' 和 ''特殊的字符串
			if($vs === '' || $vs ==='/'){
				unset($ccsjg[$key]);
			}
			$result = stripos($vs,'<');
			if( $result !== FALSE){
				$jcx = substr($vs,1);
			}
		}
	}else{
		$jgz_arr['zg'] = '/';
		$jgz_arr['zd'] = '/';
		$jgz_arr['pj'] = '/';
		return $jgz_arr;
	}


	//对所得结果进行判定
	if(!count($ccsjg)){
		$zg = '';
		$zd = '';
		$pj = '';
	}else{ 
		foreach($ccsjg as $va){
			//用来判断变量是否是汉字
			if(preg_match("/[\x7f-\xff]/",$va)){
				$han[] = $va;
			}

			//用来判断字符串中是否含有'<'
			if(strstr($va,'<')){
				$bans[] = $va;
				preg_match('/\d+/',$va,$sz);
				$ban[] = $sz[0]/2;
			}

			//用来判断是否是数字
			if(is_string($va) && $va != '' && !preg_match("/[\x7f-\xff]/",$va) && !strstr($va,'<')){
				$numbers[] = $va;
			}
		}//foreach结束符

		//再对结果进行判定
		if((count($han)) && (!count($ban) && !count($numbers))){
			$zg = $han[0];
			$zd = $han[0];
			$pj = $han[0];
		}else{
			//将$ban数组里的数值加起来
			if(count($ban)){
				$banall = array_sum($ban);
			}
			
			//将$numbers数组里的值加起来
			if(count($numbers)){
				$numbersall = array_sum($numbers);
			}

		    //最后求平均值
			$allnum = (($banall+$numbersall)/$sznum);
			$pj = round($allnum,2);

			//判断检测结果是否全为<校准线的情况
			if(count($bans) && !count($numbers)){
				$zg = $bans[0];
				$zd = $bans[0];
			}else{
				//判断$bans数组是否为真
				if(count($bans)){
					$zd = $bans[0];

					//判断$numbers数组元个数并将最大值赋值
					if(count($numbers) == 1){
						$zg = $numbers[0];
					}else{
						//对$numbers数组进行从小到大排序
						natsort($numbers);

						//将数组$numbers合成字符串
						$numberszf = implode(',',$numbers);

						$zg = substr($numberszf,(strrpos($numberszf,',')+1));
					}	
				}else{
					//判断$numbers数组元素个数并将最大值和最小值分别赋值
					if(count($numbers) == 1){
						$zg = $numbers[0];
						$zd = $numbers[0];
					}else{
						//对$numbers数组进行从小到大排序
						natsort($numbers);

						//将数组$numbers合成字符串
						$numberszf = implode(',',$numbers);

						$zd = substr($numberszf,0,strpos($numberszf,','));
						$zg = substr($numberszf,(strrpos($numberszf,',')+1));
					}	
				}
			}
		
		}
	}

	//调用函数对平均值进行修约
	$pj = _round($pj,3);

	//用检出限和最高、最低、平均值进行比较
	if(isset($jcx)){
		if($pj < $jcx){
			$pj = '<'.$jcx;
		}
	}
	//将所得的最高、最低和平均值返回
	$jgz_arr['zg'] = $zg;
	$jgz_arr['zd'] = $zd;
	$jgz_arr['pj'] = $pj;
	return $jgz_arr;
	
}//**

?>