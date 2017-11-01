<?php

     //仪器维护的修改
     
     
     include "../temp/config.php";
     $wid=$_GET['wid'];
     $id=$_GET['yid'];//仪器id
     $action=$_GET['action'];
     if($action=='详细'){
		 $biaotou='仪器设备维护详细记录';
		 $w='none';
		 $wu='none';
		 $sub='none';
		 //$but='返回';
	$g=$DB->query("select * from yiqi_weihu where yid=$id and no!='' ");  
             $d=$DB->fetch_assoc($g);
            $noo=$d['no']; 
		  $R=$DB->query("select * from yiqi_weihu where id=$wid "); 
		  $r=$DB->fetch_assoc($R);
	   //$noo=$r['no'];
	   $aa=$r['kaijishijian'];// 开机时间	
	   $b=$r['neirong'];// 工作内容	
	   $c=$r['wendu'];// 温度	
	   $dd =$r['shidu'];//湿度	
	   $e=$r['kaijiqingkuang'];// 开机情况	
	   $f =$r['guanjiqingkuang'];//关机情况	
	   $gg =$r['shiyongshijian'];//使用时间	
	   $h =$r['shiyongren'];//使用人	
	   $i =$r['weihuriqi'];//维护日期	
	   $j =$r['weihuneirong'];//维护内容 
	   $k =$r['weihujielun'];//维护结论	
	   $l =$r['weihuren'];//维护人	
	   $m =$r['beizhu'];//备注 
	 }
       if($action=='修改'){
		   $biaotou='修改仪器设备维护记录';
          $g=$DB->query("select * from yiqi_weihu where yid=$id and no!='' ");  
             $d=$DB->fetch_assoc($g);
            $no=$d['no']; 
       $R=$DB->query("select * from yiqi_weihu where id=$wid "); 
       $r=$DB->fetch_assoc($R);
       $kaijishijian=$r['kaijishijian'];// 开机时间	
	   $neirong=$r['neirong'];// 工作内容	
	   $wendu=$r['wendu'];// 温度	
	   $shidu =$r['shidu'];//湿度	
	   $kaijiqingkuang=$r['kaijiqingkuang'];// 开机情况	
	   $guanjiqingkuang =$r['guanjiqingkuang'];//关机情况	
	   $shiyongshijian =$r['shiyongshijian'];//使用时间	
	   $shiyongren =$r['shiyongren'];//使用人	
	   $weihuriqi =$r['weihuriqi'];//维护日期	
	   $weihuneirong =$r['weihuneirong'];//维护内容 
	   $weihujielun =$r['weihujielun'];//维护结论	
	   $weihuren =$r['weihuren'];//维护人	
	   $beizhu =$r['beizhu'];//备注
	   //$no=$r['no']; //作为记录表的唯一编号	
     }
      disp('yq_weihujilu_insert.html');  
     ?>
     
     
     
     
     
     
     
     
     
     
     
