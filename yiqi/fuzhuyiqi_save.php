<?php


     //辅助仪器的增加页面

     include "../temp/config.php";
     
     
     $yq_fenlei=$_POST['yq_fenlei'];
     $yq_shebeimingcheng=$_POST['yq_shebeimingcheng'];
     $yq_anzhuangdidian=$_POST['yq_anzhuangdidian'];
     $yq_qijubianhao=$_POST['yq_qijubianhao'];
     $yq_guanliren=$_POST['yq_guanliren'];
     $yq_state=$_POST['yq_state'];
    
    
    $action=$_GET['action'];
     $id=$_GET['id'];
     //echo $yq_state;
    //1 合格  2准用  3停用;
      $nid=$_POST['id'];
      $a=$_POST['action'];
      //echo $nid;
    if($yq_fenlei!='' && $nid==''){
		$head='增加辅助仪器';
      $R=$DB->query("insert into n_set set name='fuzhuyiqi',str1='$yq_fenlei',
      str2='$yq_shebeimingcheng',str3='$yq_anzhuangdidian',str4='$yq_qijubianhao',
      str5='$yq_guanliren',`int1`=$yq_state ");
      
      gotourl("$rooturl/yiqi/hn_fuzhuyiqimanager.php");
    }
    if($action=='删除'){
		$R=$DB->query("delete from n_set  where name='fuzhuyiqi' and id=$id ");
	}
	if($action=='修改' || $a=='修改'){
		$head='修改辅助仪器';

		  
		  if($nid!=''){
			  
			  	if($yq_state=='合格'){$yq_state=1;  } 
		  elseif($yq_state=='准用'){$yq_state=2; }
		  else {$yq_state=3; }
		  $R=$DB->query("update  n_set set name='fuzhuyiqi',str1='$yq_fenlei',
		  str2='$yq_shebeimingcheng',str3='$yq_anzhuangdidian',str4='$yq_qijubianhao',
            str5='$yq_guanliren',`int1`=$yq_state where id=$nid");
            gotourl("$rooturl/yiqi/hn_fuzhuyiqimanager.php");
		  }
		  else{
			  
		$R=$DB->query("select * from n_set  where name='fuzhuyiqi' and id=$id ");
		$r=$DB->fetch_assoc($R);
     $yq_fenlei=$r['str1'];
     $yq_shebeimingcheng=$r['str2'];
     $yq_anzhuangdidian=$r['str3'];
     $yq_qijubianhao=$r['str4'];
     $yq_guanliren=$r['str5'];
     $yq_state=$r['int1'];
     	if($r[int1]==1){$yq_state='合格';  } 
		  elseif($r[int1]==2){$yq_state='准用'; }
		  else {$yq_state='停用'; }
		  
		  //gotourl("$rooturl/yiqi/hn_fuzhuyiqimanager.php");
			  }
	
	}
    
    
    
    disp('fuzhuyiqi_save.html');
     
     
     
     ?>
