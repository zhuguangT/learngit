<?php
/**
 * 功能：显示某种样品类型下的每个项目的默认检验方法
 * 作者：tielong zhangdengsheng
 * 日期：2014-03-18
 * 描述：对其样品类型下进行项目方法添加
*/
include '../../temp/config.php';
$fzx_id	= FZX_ID;//中心
$arr	= $_POST[vid];
$sl=$_POST[syxm];
//echo $data	= implode(',',$arr);
/*$sname = $DB->fetch_one_assoc("SELECT * FROM `leixing` WHERE id=$sl");
if($sname[parent_id]!='0'){

}*/
##################已选项目处理
foreach ($arr as $key=>$value)
{
////////////////////////////////////////////////
	$sq=$DB->query("SELECT id FROM `xmfa` WHERE lxid= '$sl' AND mr='1' AND xmid='$value' AND  fzx_id='".$fzx_id."'" );//xmfa是否有该项目的默认数据
	$num=mysql_num_rows($sq);
	if($num == '0'){//xmfa没有该项目的默认数据
	
		$sqll=$DB->fetch_one_assoc("SELECT * FROM `xmfa` WHERE lxid= '$sl' AND xmid='$value' AND  fzx_id='".$fzx_id."'" );
		if(isset($sqll[id])){ ///////	xmfa存在该项目的数据
			$DB->query("UPDATE xmfa set mr='1',act='0' WHERE  id= '$sqll[id]'");
		}else{
			$sqq=$DB->fetch_one_assoc("SELECT * FROM assay_value AS av WHERE av.id= '$value'" );//获得默认方法
			$sqsl=$DB->query("SELECT * FROM assay_value AS av WHERE av.id= '$value'" );
			$numm=mysql_num_rows($sqsl);  //获取数
			if($numm != '0'){//默认项目信息assay_value表有该项目
				if($sqq[moren_method]==''){//该项目默认的方法为空
					$DB->query("INSERT INTO xmfa SET fzx_id=$fzx_id,lxid='$sl',xmid='$value',fangfa='0',mr='1',act='0',englishMark='$sqq[englishMark]'");
				}else{//该项目默认的方法不为空
					$fangf=explode('|',$sqq['moren_method']);
					foreach($fangf as $key=>$value2){
						$sylx=$sl.':';
						if(strpos($value2,$sylx) !== false){//该水样类型下的默认方法
							$fangf2=explode(':',$value2);
							$sqqq=$DB->fetch_one_assoc("SELECT * FROM assay_method AS am WHERE am.id= '$fangf2[1]'" );//查询默认的信息
							$DB->query("INSERT INTO xmfa SET fzx_id=$fzx_id,lxid='$sl',xmid='$value',hyd_bg_id='$sqqq[hyd_bg_id]',fangfa='$fangf2[1]',mr='1',act='0',jcx='$sqqq[jcx]',englishMark='$sqq[englishMark]',w1='$sqqq[w1]',w2='$sqqq[w2]',w3='$sqqq[w3]',w4='$sqqq[w4]',w5='$sqqq[w5]',unit='$sqqq[unit]'");
						}else if(strpos($value2,':') == false){//该项目不同水样类型下都用的默认方法
							$sqqq=$DB->fetch_one_assoc("SELECT * FROM assay_method AS am WHERE am.id= '$value2'" );//查询默认的信息
							$DB->query("INSERT INTO xmfa SET fzx_id=$fzx_id,lxid='$sl',xmid='$value',hyd_bg_id='$sqqq[hyd_bg_id]',fangfa='$value2',mr='1',act='0',jcx='$sqqq[jcx]',englishMark='$sqq[englishMark]',w1='$sqqq[w1]',w2='$sqqq[w2]',w3='$sqqq[w3]',w4='$sqqq[w4]',w5='$sqqq[w5]',unit='$sqqq[unit]'");
						}else{//该项目没有默认方法
							$DB->query("INSERT INTO xmfa SET fzx_id=$fzx_id,lxid='$sl',xmid='$value',fangfa='0',mr='1',act='0',englishMark='$sqq[englishMark]'");
						}
					}
				}
			}else{//默认项目信息assay_value表没有该项目
				$DB->query("INSERT INTO xmfa SET fzx_id=$fzx_id,lxid='$sl',xmid='$value',fangfa='0',mr='1',act='0',englishMark='$sqq[englishMark]'");
			}
		
		}
	//print_rr($arr);die;
		
	}
}
##################获取全部项目
$sql = $DB->query("SELECT id as vid,value_C FROM `assay_value` where 1" );
$t=array();
while($r = $DB->fetch_assoc($sql))
{
$t[]=$r['vid'];
}
##################未选项目处理
if($arr==''){
	$DB->query("UPDATE xmfa set mr='0',act='0' WHERE  lxid= '$sl' AND fzx_id='".$fzx_id."'");
}else{
	$w = array_diff( $t, $arr );
	foreach ($w as $key=>$value)
	{   	  	  //
		$DB->query(" UPDATE xmfa set mr='0',act='0'  WHERE xmid='$value' AND lxid= '$sl' AND fzx_id='".$fzx_id."'");
	}
}
gotourl("$rooturl/system_settings/assay_method/assay_method_list.php?lxid=$sl");
?>
