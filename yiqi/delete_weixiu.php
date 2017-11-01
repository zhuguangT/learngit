<?php

     //仪器维修的删除

      include "../temp/config.php";
      $wid=$_GET['wid'];
      $id=$_GET['yid'];//仪器id
     
      $R=$DB->query("delete from yiqi_weixiu where id=$wid  ");
      
       gotourl("$rooturl/yiqi/yq_weixiujilu.php?id=$id");
     
     ?>
