<?php

 
 include ("../temp/config.php");

 $data=array();
 $vid=$_GET['vid'];
 $R=$DB->query("SELECT ao.*,s.water_type as slx  FROM `assay_order` as ao Left JOIN `sites` as s  ON ao.sid=s.id WHERE ao.tid = '$_GET[tid]' ORDER BY ao.`cid`  ASC ,ao.`hy_flag` DESC  ");//根据化验单号查询结果 和化验单上面的水样类型
 while($row = $DB->fetch_assoc($R)){
      
	 if($row['slx']=='管网水' || $row['slx']=='出厂水'){//执行生活饮用水卫生标准
	   $bid=26;	  
	   $xzhi=$DB->fetch_one_assoc("SELECT xz FROM `assay_jcbz`  where bid='$bid' and vid='$vid' ");
	   if($xzhi['xz']!=''){
	   if(floatval($row['_vd0'])>floatval($xzhi['xz']))//判断该项目的生活饮用水的标准限值和实际值的大小
	   $orid=$row[id];
	   if($orid)
	   $data[]=$orid;
       }
	   
    }
	
	 elseif($row['slx']=='水源水' || $row['slx']=='地表水') {//执行地表水环境质量标准
		 $bid=25;
		 $xzhi=$DB->fetch_one_assoc("SELECT xz FROM `assay_jcbz`  where bid='$bid' and vid='$vid' ");
		 if($xzhi['xz']!=''){
	     if(floatval($row['_vd0'])>floatval($xzhi['xz']))//判断该项目的生活饮用水的标准限值和实际值的大小
	     $orid=$row[id];
	     if($orid)
	     $data[]=$orid;
	    }
	 }   
}


 echo  json_encode($data);//返回json数据
 
 	


?>
