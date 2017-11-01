<?php
/**
 * 功能：获取质控范围及判断质控是否合格
 * 参数：int $vid 项目id
 * 参数：typeid水样类型id
 * 参数：nd浓度
 * 参数：jieguo为需要判断是否在质控范围的数值。
 * 参数：leixing为jieguo数值的数据类型，如：加标回收率（j），室内精密度(snjmd)，室间精密度（sjjmd），室内相对误差（snxdwc）,室间相对误差（sjxdwc）。
 * 返回值：传入项目id和水体类型未必传参数，如果传入浓度返回质控范围数组，传入jieguo和leixing，返回是否合格信息
 * 功能描述：jieguo参数和leixing参数必须同时存在。传入项目id和水体类型未必传参数，如果传入浓度返回质控范围数组，传入jieguo和leixing，返回是否合格信息
*/

function get_zkfw($fzx_id,$vid,$typeid,$nd,$jieguo='',$leixing='')
{
	if($nd == '')
	{
		return '';
	}

	$sql = "SELECT * FROM `zk_set` WHERE `vid` = '$vid' AND `water_type` = '$typeid'"; 
	$result = mysql_query($sql);
	$j = mysql_num_rows($result);
	if($j == 0)
	{
	   return '质控范围没有设置!';
	}
	$row = array();
	for($i=0;$i<$j;$i++)
	{
	   $row[] = mysql_fetch_assoc($result);
	}

	$fanwei = array();//范围数组
	$res = '';//结果判断
	
	//根据浓度判断适用哪个质控范围
    foreach($row as $k)
	{
	    $f = $p = 0;
	    //取出浓度范围
		if(substr($k['nd'],0,1)=='>')
	    {
		    $a = substr($k['nd'],1);
		   
	    	if($nd > $a)
			{
				$f = 1;
			}
			
	    }else if(substr($k['nd'],0,1)=='<'){
			$a = substr($k['nd'],1);
	    	if($nd < $a&&$nd >= 0)
			{
				$f = 1;
			}
		}else{
			$arr = array();
			$arr = preg_split('/\D+/',$k['nd']);
			if($nd >= $arr['0']&&$nd <= $arr['1'])
			{
				$f = 1;
			}
		}
		if($f == 1)
		{
			if($jieguo == ''&&$leixing=='')//如果传入的参数没有jieguo和leixing，输出该浓度下的所有质控范围。		
			{
				$fanwei['nd'] = $k['nd'];
				$fanwei['sn_jmd'] = $k['sn_jmd'];
				$fanwei['sj_jmd'] = $k['sj_jmd'];
				$fanwei['jbhs'] = $k['jbhs'];
				$fanwei['sn_xdwc'] = $k['sn_xdwc'];
				$fanwei['sj_xdwc'] = $k['sj_xdwc'];
				if($typeid = 0)
				{
					return '50';
				}
				return $fanwei;
			}else{
				$lei = array('j','snjmd','sjjmd','sjxdwc','snxdwc');//判断是否数据类型是否匹配?
				if(!in_array($leixing,$lei))
				{
					return '您输入的数据类型有误';
				}
				switch($leixing)
				{
					case 'j':
						 if($k['jbhs'] != '')
				         {
					        if(substr($k['jbhs'],0,2) == '≤')
							{
								$a = preg_match('/\d+/',$k['jbhs'],$a);
								if($jieguo <= $a[0])
								{
									$p = 1;
								}
							}else{
								$arr = array();
								$arr = preg_split('/\D+/',$k['nd']);
								if($jieguo <= $arr[1]&&$jieguo >= $arr[0])
								{
									$p = 1;
								}
							}
				         }else{
				         	return '质控范围没有设置';
				         }
				         break;

					case 'snjmd':
						$a = substr($k['sn_jmd'],2);
						if($jieguo <= $a&&$jieguo >= 0)
						{
							$p = 1;
						}
						break;
					case 'sjjmd':
						$a = substr($k['sj_jmd'],2);
						if($jieguo <= $a&&$jieguo >= 0)
						{
							$p = 1;
						}
						break;
					case 'snxdwc':
						if($k['sn_xdwc'] != '')
				         {
							preg_match('/\d+/',$k['sn_xdwc'],$a);
							if(abs($jieguo) <= abs($a[0]))
							{
								$p = 1;
							}
				         }else{
				         	return '质控范围没有设置';
				         }
				         break;
					case 'sjxdwc':
						if($k['sj_xdwc'] != '')
				         {
							preg_match('/\d+/',$k['sj_xdwc'],$a);
							if(abs($jieguo) <= abs($a[0]))
							{
								$p = 1;
							}
				         }else{
				         	return '质控范围没有设置';
				         }
				         break;
				}
				if($p == 1)
				{
					return '合格';
				}else{
					return '不合格';
				}	
			}
		}
	}

}
