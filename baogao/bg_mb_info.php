<?php
/**
 * �޸�ģ����Ϣ���ܣ�������ʾҳ�棩
 * ����
 * 4-21
 * ��������ģ���б�������ɶ�ָ��ģ����Ϣ�޸�
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