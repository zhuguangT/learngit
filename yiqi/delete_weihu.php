<?php

     //仪器维护的删除

     include "../temp/config.php";
     $wid=$_GET['wid'];
     $id=$_GET['yid'];//仪器id
     
      $R=$DB->query("delete from yiqi_weihu where id=$wid  ");
      
       gotourl("$rooturl/yiqi/yq_weihujilu.php?id=$id");
     
     ?>
