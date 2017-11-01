<?php
/**
 * 修改模板信息功能（弹框显示页面）
 * 罗磊
 * 4-21
 * 处理来自模板列表请求，完成对指定模板信息修改
*/
include "../temp/config.php";
       $mbid = $_GET['id'];
       $sql="SELECT * FROM `report_template` where id ='$mbid'";
	   $rows = $DB->query($sql);
	   $row = $DB->fetch_assoc($rows);
       $mbname= $row['te_name'];
       $jiego = $row['jiego'];
	   if($jiego=='2'){
		$selected1='selected="selected"';
	   }else{
		$selected2='selected="selected"';
	   }
	   $state = $row['state'];
	   $hang1 = $row['hang1'];
	   $hang2 = $row['hang2'];
	  if($state == '1'){
	     $sta1 = "checked = 'checked' ";
	  }else if($state == '0'){
	     $sta  = "checked = 'checked' ";
	  }
	 
	   echo temp("bg/bg_mb_info");
?>