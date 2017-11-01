<?php
//辽阳样品编码恢复
include("temp/config.php");
$right_arr	= array("葠窝水库坝前(左上)"=>"CB201412-0016",
"高家堡子"=>"CB201412-0017",
"水泉"=>"CB201412-0018",
"梨庇峪"=>"CB201412-0019",
"九口峪"=>"CB201412-0020",
"河沿村"=>"CB201412-0021",	
"二道河水文站"=>"CB201412-0024",
"二道河水文站(实验室平行)"=>"CB201412-0024P",
"东支入库口"=>"CB201412-0026",
"郝家店水文站"=>"CB201412-0027",
"西支入库口"=>"CB201412-0028",
"上麻屯"=>"CB201412-0029",
"八会镇"=>"CB201412-0030",
"小西沟村"=>"CB201412-0031",
"马蹄岭子"=>"CB201412-0032",
"全程序空白"=>"CB201412-0033",
"辽阳"=>"CB201412-0034",
"南沙坨子"=>"CB201412-0035",
"管桥"=>"CB201412-0036",
"汤河水库坝前(左上)"=>"CB201412-0037",
"汤河水库坝前(左上)(平行)"=>"CB201412-0038",
"入太子河河口"=>"CB201412-0039",
"鸡冠山村"=>"CB201412-0040"
);
print_rr($right_arr);
$sql	= $DB->query("select * from `cy` where `fzx_id`='10' and `cy_date`='2014-12-04'");
$aaa= '';
while($rs= $DB->fetch_assoc($sql)){
	echo "###################批次开始";
	print_rr($rs);
	$sql_rec	= $DB->query("select * from `cy_rec` where cyd_id='{$rs['id']}'");
	while($rs_rec= $DB->fetch_assoc($sql_rec)){
		$bar_code	= '';
		$hy_flag_where = '';
		echo "=============>rec站点开始";
		print_rr($rs_rec);
		$cid= $rs_rec['id'];
		if($rs_rec['zk_flag']=='-6'){
			$rs_rec['site_name'] .= "(平行)";
		}
		$bar_code = $right_arr[$rs_rec['site_name']];
		if($bar_code!=''){
			$DB->query("update `cy_rec` set bar_code='{$bar_code}' where id='{$cid}'");
			if($rs_rec['zk_flag']>=0){
				$DB->query("update `assay_order` set bar_code='{$bar_code}P' where cyd_id='{$rs['id']}' and cid='{$cid}' and sid>=0 and hy_flag='-20'");
				$DB->query("update `assay_order` set bar_code='{$bar_code}J' where cyd_id='{$rs['id']}' and cid='{$cid}' and sid>=0 and hy_flag='-40'");
				$DB->query("update `assay_order` set bar_code='{$bar_code}' where cyd_id='{$rs['id']}' and cid='{$cid}' and sid>=0 and hy_flag>=0");
			}else if($rs_rec['zk_flag']=='-6'){
				$DB->query("update `assay_order` set bar_code='{$bar_code}' where cyd_id='{$rs['id']}' and cid='{$cid}' and sid>=0");
			}
		}else{
			$aaa .= $rs_rec['site_name'];
		}
		//and hy_flag='{$hy_flag}'
		echo "=============>rec站点结束<br>";
	}
	echo "##################批次结束<br>";
	echo $aaa;
}

?>