<?php
/*
	功能：存放常规月报所需要的函数
	作者：高龙
	时间：2016/5/16
*/

//封装一个用于读取站点数据数组的函数
function dqsj($resultss,$unit_arr,$site_inf,$site_inf_arr,$site_sum){//start***
	foreach($resultss as $k=>$v){//**
		$vid_data_td=$site_jcjg_arr='';
		foreach($site_inf as $k2=>$v2){
			if($site_inf_arr[$k2][$k]){//判断站点是否检测该项目
				if(isset($site_inf_arr[$k2][$k]['vd0'])){  
					$vd0= $site_inf_arr[$k2][$k]['vd0'];
					$tid= $site_inf_arr[$k2][$k]['tid'];
				}else{
					$vd0='';
					$vd0_class	= $tid	= '';
				}
			}else{
				$vd0='/';
			}
			$site_jcjg_arr[] = $vd0;//取出所有站点某一个检测项目的值
			$vid_data_td.="<td class='{$vd0_class}' tid='{$tid}' style='vnd.ms-excel.numberformat:@'>".$vd0."</td>";
		}
		$xm_danwei='';//每次初始化防止项目单位出错
		if(isset($unit_arr[$k])){//获取项目单位
			$xm_danwei = $unit_arr[$k];
		}

		//调用函数求出最高、最低、平均值
		$zgzdpj = gdp($site_jcjg_arr,$site_sum);

		$week_bg_line="<tr align=\"center\"><td>".$v."</td><td>".$xm_danwei."</td>".$vid_data_td."<td>".$zgzdpj['zg']."</td><td>".$zgzdpj['zd']."</td><td>".$zgzdpj['pj']."</td></tr>";
	}//**
	
	return $week_bg_line;
}//end***


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
				$jcx = substr($vs,1);//求出这个项目的检出限值（传过来的一维数组是这个项目的所有检测值里面可能会有该项目的检测值）
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
