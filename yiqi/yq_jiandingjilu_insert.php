<?php
     //仪器检定记录的的增加页面
      include "../temp/config.php";
       $id=$_GET['id'];
      if($id!='')$head='新增仪器检定记录';

      $jiandingren=$_POST['jiandingren'];
      $action=$_GET['action'];
      if($jiandingren!='' && $action=='新增'){
		  $id=$_POST['id'];
		  $shangcijianding=$_POST['shangcijianding'];
		  $cicijianding=$_POST['cicijianding'];
		  $beizhu=$_POST['beizhu'];
		  $R=$DB->query("insert into n_set set name='yiqijiandingjilu', str1='$jiandingren',
		  str2='$shangcijianding',str3='$cicijianding',str4='$beizhu',str5='$id' ");
		  gotourl("$rooturl/yiqi/yq_jiandingjilu.php?id=$id");
		  }
		  
	if($action=='修改' ){
		  $head='修改仪器检定记录';
		  $jid=$_GET['id'];//n_set的id
		  $id=$_GET['yid'];//仪器的id
		  $r=$DB->fetch_one_assoc("select * from n_set where id=$jid ");
		      $jiandingren=$_POST['jiandingren'];
			  $shangcijianding=$_POST['shangcijianding'];
			  $cicijianding=$_POST['cicijianding'];
			  $beizhu=$_POST['beizhu'];
			  $R=$DB->query("update n_set set str1='$jiandingren',str2='$shangcijianding', str3='$cicijianding',str4='$beizhu'  where id=$jid "); 
			 if(!empty($_POST[ssve]))
			  gotourl("$rooturl/yiqi/yq_jiandingjilu.php?id=$id"); 
	  }
	  if($action=='删除'){
		  $jid=$_GET['id'];
		  $id=$_GET['yid'];
		  $R=$DB->query("delete  from n_set   where id=$jid "); 
		  gotourl("$rooturl/yiqi/yq_jiandingjilu.php?id=$id"); 
		  }
      disp('yq_jiandingjilu_insert.html');
     ?>
