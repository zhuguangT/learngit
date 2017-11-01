<?php
/*
	功能：计算不同报告所需要的数据
	时间：2016-7-21
	作者：高龙
*/
	if($mbrows['chuli_mark'] == 'wsaq'){//计算和评价饮用水化学处理剂卫生安全性评价检测报告中的数据*
		$pj=$result='';//赋空防止影响循环
		if($jc_xz == '--'){
			$rx_xz = '在规定的投加量使用时，处理后水的一般感官应符合评价标准的要求';
			$pj    = '合格';
		}else{///*
			if(strstr($jc_xz,'~') || strstr($jc_xz,'-') ){
				if(strstr($jc_xz,'~')){
					$jcxarr = explode('~',$jc_xz);
				}
				if(strstr($jc_xz,'-')){
					$jcxarr = explode('-',$jc_xz);
				}
				$rx_xz = ($jcxarr[0]*0.1).'~'.($jcxarr[1]*0.1);
				$result = is_chaobiao($xmid,$water_type,$rx_xz,$yysz_pjhl);//用于判断$yysz_pjhl是否合格。
				$result = $result['status'];// 此值为 1 时不合格 为 0 时合格
				if($result == 0){
					$pj = '合格';
				}
				if($result == 1){
					$pj = '不合格';
				}
				if($result != 0 && $result != 1){
					$pj = $result;
				}
			}else{////*
				if(strstr($jc_xz,'>') || strstr($jc_xz,'<')){
					$fh = substr($jc_xz,0,1);
					$zhi = substr($jc_xz,1);
					$rx_xz = $fh.($zhi*0.1);
				}
				if(preg_match("/[\x7f-\xff]/",substr($jc_xz,3)) || substr($jc_xz,3) == ''){
					$rx_xz = '在规定的投加量使用时，处理后水的一般感官应符合评价标准的要求';
					$pj    = '合格';
				}else{
					$fh = substr($jc_xz,0,3);
					$zhi = substr($jc_xz,3);
					$rx_xz = $fh.($zhi*0.1);
				}
				if($pj !== '合格'){/////**
					$result = is_chaobiao($xmid,$water_type,$rx_xz,$yysz_pjhl);//用于判断$yysz_pjhl是否合格。
					$result = $result['status'];// 此值为 1 时不合格 为 0 时合格
					if($result == 0){
					$pj = '合格';
					}
					if($result == 1){
						$pj = '不合格';
					}
					if($result != 0 && $result != 1){
						$pj = $result;
					}
				}//////**
				
			}////*
		}///*
	}//*
	if($mbrows['chuli_mark'] == 'sps' || $mbrows['chuli_mark'] == 'fhcl'){//计算生活饮用水输配水设备安全性评价中所需要的数据
		$pj='';//把值赋空防止影响下一次循环
		$zj_arr = array(94=>'≤0.5',682=>'≤10',683=>'≤2');  //存放含有增加量的项目
		$xm_kb = $xm_kbarr[$xmid];//取出项目空白值
		if($xm_kb != ''){//***
			if($xm_kb=='未检出'&&$is_eglish){
				$xm_kb='Not detected';
			}
			if($xm_kb=='无'&&$is_eglish){
				$xm_kb='No';
			}	
		}//***
		if($zj_arr[$xmid]){
			$jiekb = array($jie,$xm_kb);
			switch ($jiekb) {//******
				case strstr($jiekb[0],'<') && strstr($jiekb[1],'<'):
					$pj = '合格';
					break;
				case preg_match("/[\x7f-\xff]/",$jiekb[0]) || preg_match("/[\x7f-\xff]/",$jiekb[1]):
					$pj = '合格';
					break;
				case strstr($jiekb[0],'<') || strstr($jiekb[1],'<'):
					strstr($jiekb[0],'<')?$jz = (substr($jiekb[0],1) - $jiekb[1]):$jz = (substr($jiekb[1],1) - $jiekb[0]);
					$result = is_chaobiao($xmid,$water_type,$zj_arr[$xmid],$jz);//用于判断$yysz_pjhl是否合格。
					$result = $result['status'];// 此值为 1 时不合格 为 0 时合格
					if($result == 0){
					$pj = '合格';
					}
					if($result == 1){
						$pj = '不合格';
					}
					if($result != 0 && $result != 1){
						$pj = $result;
					}
					break;
				default:
					$jz = $jiekb[0] - $jiekb[1];
					$result = is_chaobiao($xmid,$water_type,$zj_arr[$xmid],$jz);//用于判断$yysz_pjhl是否合格。
					$result = $result['status'];// 此值为 1 时不合格 为 0 时合格
					if($result == 0){
					$pj = '合格';
					}
					if($result == 1){
						$pj = '不合格';
					}
					if($result != 0 && $result != 1){
						$pj = $result;
					}
					break;
			}//******
			$jc_xz = $zj_arr[$xmid];
		}else{
			$result = is_chaobiao($xmid,$water_type,$jc_xz,$jie);//用于判断$yysz_pjhl是否合格。
				$result = $result['status'];// 此值为 1 时不合格 为 0 时合格
				if($result == 0){
					$pj = '合格';
				}
				if($result == 1){
					$pj = '不合格';
				}
				if($result != 0 && $result != 1){
					$pj = $result;
				}
		}
	}

?>