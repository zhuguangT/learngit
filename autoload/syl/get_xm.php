<?
/*
*功能：导入项目的标示
*作者：zhengsen
*/
include("../../temp/config.php");
$arr     = array();
$lujing="../files/xm_bh.pdf";
$arr=pdftoarr($lujing);
//print_rr($arr);exit();
//取出数组键数
for($i=0;$i<count($arr);$i++){
        //循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
       $line  =$arr[$i];
	   if($line=="Administration Division Name"){
			$get_xm="start";
	   }
	   if($line=="Fecal Streptococcus"){
			$get_xm="";
	   }
	   if($get_xm=="start"){
			if(preg_match("/^[\x{4e00}-\x{9fa5}]{1,4}/u",$line)){
				$zhi[$line]=$arr[$i+2];
			}
	   }
}
//echo count($zhi);
//print_rr($zhi);exit();
$sql="select * from assay_value where 1";
$query=$DB->query($sql);
while($rs=$DB->fetch_assoc($query)){
	if($zhi[$rs['value_C']]){
		$DB->query("update assay_value set englishMark='".$zhi[$rs['value_C']]."' where id='".$rs['id']."'");
	}
}
?>
