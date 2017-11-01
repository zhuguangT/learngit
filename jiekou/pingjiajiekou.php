<?php
/**
 * 功能：往对接表里插入数据（duijie表）
 * 作者: Mr Zhou
 * 日期: 2014-03
 * 描述
*/
include("../temp/config.php");
//评价系统表格式与我们项目数据的对应
//$arrValue = array("47"=>"COND","5"=>"ARS","6"=>"HG","14"=>"CD","20"=>"CR6","15"=>"PB","7"=>"SE","22"=>"CN","46"=>"PH","25"=>"DOX","32"=>"CODMN","26"=>"BOD5","21"=>"NH3N","58"=>"FCG","44"=>"TP","1"=>"F","42"=>"VLPH","45"=>"OIL","3"=>"SO4","2"=>"CL","24"=>"NO3","10"=>"FE","11"=>"MN","8"=>"CU","9"=>"ZN","23"=>"LAS","30"=>"S2","41"=>"TN","43"=>"TN","16"=>"CODCR","40"=>"CHLA","75"=>"CLARITY");
$zt     = '';
$cydId  = $_REQUEST['cyd_id'];
$sites	= $_REQUEST['sites'];
//如果后期加入批量上传功能，这里可以兼容
if(is_array($cydId)){
	$cydId	= implode(",",$cydId);
}
//如果后期加入每批次选站点上传功能，这里可以兼容
if(is_array($sites)){
	$sites	= implode(",",$sites);
}
$rsCy   = $DB->fetch_one_assoc("select * from `cy` where id='".$cydId."'");
	$insert		= "insert into `duijie` (STCD,STNM,PRPNM,LYNM,SPT,cyd_id,vid,englishMark,result) values";
	$where_sites	= '';
	//如果没有传递站点，就默认上传该批次全部的站点
	if(!empty($sites)){
		$where_sites	.= " r.sid in($sites) ";
	}
	$no_mark	= $no_mark_vid	= array();
	$sqlShuJu	= "select s.site_name,s.site_code,s.site_line,s.site_vertical,r.* from `sites` as s right join `cy_rec` as r on s.id=r.sid where r.cyd_id in({$cydId}) AND r.zk_flag>=0 AND r.zk_flag!=1 and r.status!='-1' $where_sites";
	$queryShuJu	= $DB->query($sqlShuJu);
	while($rsShuJu=$DB->fetch_assoc($queryShuJu)){
		//$json	= json_decode($rsShuJu['sjson'],true);
		//if($json['STCD']){//测站编号不为空
			$cyTime = '';
			$STCD	= $rsShuJu['site_code'];//测站编码
			$PRPNM	= $rsShuJu['site_line'];//垂线编号
			$LYNM	= $rsShuJu['site_vertical'];//层面编号
			//采样时间不同项目兼容获取(有采样时间的和没有采样时间的)
			if($rsShuJu['cy_time']!=''){
				if($rsShuJu['cy_date']=='0000-00-00 00:00:00' || $rsShuJu['cy_date']==''){
					$cyTime = $rsCy['cy_date']." ".$rsShuJu['cy_time'];
				}else{
					$cyTime	= $rsShuJu['cy_date']." ".$rsShuJu['cy_time'];
				}
			}
			else{
				$cyTime = $rsShuJu['cy_date'];
			}
			$guding	 = "'".$STCD."','".$rsShuJu['site_name']."','".$PRPNM."','".$LYNM."','".$cyTime."','".$cydId."'";
			$queryOrder = $DB->query("select o.site_name,o.vid,o.vd0,v.value_C,v.englishMark from `assay_order` as o inner join `assay_value` as v on o.vid=v.id where o.cid='".$rsShuJu['id']."' and o.hy_flag>=0 AND o.sid>0");
			//一些存储在cy_rec表的数据
			if(!empty($rsCy['qi_wen'])){
				$insert .= "(".$guding.",'','AIRT','".$rsCy['qi_wen']."'),";
			}
			while($rsOrder=$DB->fetch_assoc($queryOrder)){
				if($rsOrder['englishMark']==''){
					if(!in_array($rsOrder['vid'],$no_mark_vid)){
						$no_mark_vid[]	= $rsOrder['vid'];
						$no_mark[]	= $rsOrder['value_C']."(".$rsOrder['vid'].")";
					}
				}
				//改变科学计数法的存储方式
				if(stristr($rsOrder['vd0'],'×10<sup>')){
					$rsOrder['vd0'] = str_replace('×10<sup>','E',str_replace('</sup>','',$rsOrder['vd0']));
				}
				$insert .= "(".$guding.",'{$rsOrder['vid']}','".$rsOrder['englishMark']."','".$rsOrder['vd0']."'),";
				$zt	 = "yes";
				//}
			}
		//}
	}
$insert = substr($insert,0,-1);
//echo $insert."<br>";exit;
if($zt=='yes'){
	if(!empty($no_mark)){
		$no_mark	= implode(",",$no_mark);
		
	}else{
		$no_mark	= '';
	}
	$DB->query($insert);
	if((int)$DB->insert_id()>0){
		if($no_mark!=''){
			echo "<script>alert(\"                 上传成功 \\n 以下项目没有设置英文标识，请联系管理员：\\n $no_mark\");</script>";
		}else{
			prompt('上传成功');
		}
	}else{
		prompt('上传失败：请联系管理员');
	}
}else{
	prompt('上传失败:未找到有数据的化验单');
}
//print_rr($url);//url_stack
if(stristr($url[1],'bg_liebiao.php')){
	gotourl($url[$_u_][1]);
}else{
        foreach($url as $usrVal){
                if(stristr($usrVal,'bg_liebiao.php')){
                        gotourl($usrVal);
                        break;
                }
        }
}
//单位的判断，保留位数，平行加标的值，是用平均值还是原始值
/*foreach($shujuArr as $recVal){
	$json = json_decode($recVal['sjson'],true);
	$lie  = $zhi = "";
	foreach($recVal as $key=>$orderVal){
		if(array_key_exists($key,$arrValue)){
			$lie .= ",`".$arrValue[$key]."`";
			$zhi .= ",'".$orderVal."'";
		}
	}
	$insert = "insert into `duijie` (`STCD`,`STNM`,`PRPNM`,`LYNM`,`SPT`,`AIRT`,`WT`".$lie.") values('".$json['STCD']."','".$recVal['site_name']."','".$json['PRPNM']."','".$json['LYNM']."','".$recVal['cyTime']."','".$recVal['qi_wen']."','".$recVal['water_temperature']."'".$zhi.")";
	$sqlArr[] = $insert;
}
print_rr($sqlArr);
foreach($sqlArr as $key=>$val){
	//$DB->query($val);
}*/
?>
