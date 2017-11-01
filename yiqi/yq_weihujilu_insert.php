<?php

     //仪器维护的增加页面

     include "../temp/config.php";
   
   
   
     $id=$_GET['id'];
     
     $yid=$_POST['id'];//点保存时候接受仪器的id
     $no=$_POST['no']; //作为记录表的唯一编号	
     $zhi=$_GET['action'];
     
     $aa=$_POST['action'];
     $s=$_POST['wid'];
     $f=$_POST['yid'];
      $biaotou='新增仪器设备维护记录';
     
       $kaijishijian=$_POST['kaijishijian'];// 开机时间	
	   $neirong=$_POST['neirong'];// 工作内容	
	   $wendu=$_POST['wendu'];// 温度	
	   $shidu =$_POST['shidu'];//湿度	
	   $kaijiqingkuang=$_POST['kaijiqingkuang'];// 开机情况	
	   $guanjiqingkuang =$_POST['guanjiqingkuang'];//关机情况	
	   $shiyongshijian =$_POST['shiyongshijian'];//使用时间	
	   $shiyongren =$_POST['shiyongren'];//使用人	
	   $weihuriqi =$_POST['weihuriqi'];//维护日期	
	   $weihuneirong =$_POST['weihuneirong'];//维护内容 
	   $weihujielun =$_POST['weihujielun'];//维护结论	
	   $weihuren =$_POST['weihuren'];//维护人	
	   $beizhu =$_POST['beizhu'];//备注
	    
             if($id!=''){
            $g=$DB->query("select * from yiqi_weihu where yid=$id and no!='' ");  
            while($d=$DB->fetch_assoc($g))
            $noo=$d['no']; 
            if($noo!=''){
            $wu='none';}
            }

      if($aa=='修改'){
		    $R=$DB->query("update   yiqi_weihu set 
				   kaijishijian='$kaijishijian',
				   neirong='$neirong',
				   wendu='$wendu',
				   shidu ='$shidu',
				   kaijiqingkuang='$kaijiqingkuang',
				   guanjiqingkuang ='$guanjiqingkuang',
				   shiyongshijian ='$shiyongshijian',
				   shiyongren ='$shiyongren',
				   weihuriqi ='$weihuriqi',
				   weihuneirong ='$weihuneirong',
				   weihujielun ='$weihujielun',
				   weihuren ='$weihuren',
				   beizhu ='$beizhu'
				   where id=$s
		    ");
		    
		   $R=$DB->query("update   yiqi_weihu set 
				   no='$no'
				   where yid=$f and no!=''
		    ");
		    
	
		    
		  //  echo $aa;   echo $s,$f;exit;
		     gotourl("$rooturl/yiqi/yq_weihujilu.php?id=$f");
	  }
         
     //echo $yid;
     if($yid!='' ){

	    if($noo!=''){
	    $R=$DB->query("insert into yiqi_weihu set 
             	   yid='$yid',
				   kaijishijian='$kaijishijian',
				   neirong='$neirong',
				   wendu='$wendu',
				   shidu ='$shidu',
				   kaijiqingkuang='$kaijiqingkuang',
				   guanjiqingkuang ='$guanjiqingkuang',
				   shiyongshijian ='$shiyongshijian',
				   shiyongren ='$shiyongren',
				   weihuriqi ='$weihuriqi',
				   weihuneirong ='$weihuneirong',
				   weihujielun ='$weihujielun',
				   weihuren ='$weihuren',
				   beizhu ='$beizhu'
	          "); 
	           }
	       else{
			 	  $R=$DB->query("insert into yiqi_weihu set 
             	   yid='$yid',
				   no='$no',
				   kaijishijian='$kaijishijian',
				   neirong='$neirong',
				   wendu='$wendu',
				   shidu ='$shidu',
				   kaijiqingkuang='$kaijiqingkuang',
				   guanjiqingkuang ='$guanjiqingkuang',
				   shiyongshijian ='$shiyongshijian',
				   shiyongren ='$shiyongren',
				   weihuriqi ='$weihuriqi',
				   weihuneirong ='$weihuneirong',
				   weihujielun ='$weihujielun',
				   weihuren ='$weihuren',
				   beizhu ='$beizhu' ");
			   }
			   
			    gotourl("$rooturl/yiqi/yq_weihujilu.php?id=$yid");
		   }
	    
	    
     
     
     
     
          disp('yq_weihujilu_insert.html');











     ?>
