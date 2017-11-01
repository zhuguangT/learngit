<?php

      //删除仪器的代码并删除该仪器的图片

   include "../temp/config.php";
   $yid=$_GET['yid'];
   $sql = "SELECT * FROM `yiqi` WHERE `id` = $yid";
   $data = $DB->fetch_one_assoc($sql);
   $file = $data['yq_image'];
   if(empty($file)){
   		$DB->query("delete from  `yiqi` where id=$yid");
	   echo "<script>history.go(-1);</script>";
   }else{
   		if(@unlink($file)){
		   $DB->query("delete from  `yiqi` where id=$yid");
		   echo "<script>history.go(-1);</script>";
		   // gotourl("$rooturl/yiqi/hn_yiqimanager.php");
	   }else{
	   		echo "<script>alert('文件删除失败！请联系管理员!');</script>";
	   }
   }
  

  
   
   ?>
   
   
   
