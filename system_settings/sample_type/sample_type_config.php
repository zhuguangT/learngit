<?php
/*
*功能：样品类型的小类增加功能
*参数：水样类型id
*作者：tielong
*时间：2014-03-21
*/
include '../../temp/config.php';
get_int($_GET[lxid]);
if($_GET[lxid]=="")
$_GET[lxid]=1;
$fzx_id=1;
if($_GET[value]==''){
	$_GET[value] ='mo';
}
$sql="select * from leixing  where parent_id=0 and act<>'0'";//执行标准
$lx=$DB->query($sql);
	while($r=$DB->fetch_assoc($lx))
	{
		//得到水样类型的下拉菜单 
		if($r[id]==$_GET[lxid]){
			$lxlist.="<option selected=\"selected\"  value=\"$r[id]\">$r[lname]</option>";
		}else
			$lxlist.="<option  value=\"$r[id]\">$r[lname]</option>";
	}
	if($_GET[lxid]!='')//查询该标准下的小类
	{
		$sql="select * from leixing where parent_id = $_GET[lxid] and act<>'0' ";
		$xiaolei=$DB->query($sql);
		$lxlist2=" <select name='uplname' onchange=\"location='./sample_type_config.php?lxid=$_GET[lxid]&type=uplname&value='+this.value;\">  ";
		 if(mysql_num_rows($xiaolei))//判断数据库返回值为空
		 {
			while($r=$DB->fetch_assoc($xiaolei))
			{
				$names[$r[id]]=$r[lname];
			}
			if($_GET[value]=='mo'){
				$k = 1;
				foreach($names as $key=>$value)
				{
					if($k==1){
						$lxlist2.="<option  selected=selected  value=\"$key\">$value</option>";
						$sample_type_name= $value;
						$sample_type_id= $key;
					}else{
						$lxlist2.="<option  value=\"$key\">$value</option>";
					}
					$k++;
				}
			}
			if($_GET[type]=='uplname')
			{ 
			foreach($names as  $key=>$value)
			{
  			    if($_GET[value]==$key)
				{
					$lxlist2.="<option  selected=selected  value=\"$key\">$value</option>";
					$sample_type_name= $value;
					$sample_type_id= $key;
				}
				else 
				{
					$lxlist2.="<option     value=\"$key\">$value</option>";
				}
			}
			}
		}
		else
		{
			    $lxlist2.="<option  >无</option>";
		}
		$lxlist2.="<select/>";
	}
	if($_GET[lxid]!=='' && $_GET[type_name]!='' && $_GET[type]='insert')//该标准下增加小的分类
	{
		$sql="INSERT INTO `leixing` (`id` ,`parent_id` ,`fzx_id` ,`lname` ,`jieshao` ,`act`)VALUES (NULL , $_GET[lxid], $fzx_id, '$_GET[type_name]',NULL, '1')";
		$DB->query($sql);
		gotourl("sample_type_config.php?lxid=$_GET[lxid]");
	}
	if($_GET[lxid]!='' && $_GET[sample_type_name]!='' && $_GET[type]='update' && $_GET[sample_type_id]!='')//该标准下增加小的分类
	{
		$sql="UPDATE `leixing` SET `lname` = '$_GET[sample_type_name]' WHERE `leixing`.`id` =$_GET[sample_type_id]  and parent_id=$_GET[lxid] ";
		$DB->query($sql);
		gotourl("sample_type_config.php?lxid=$_GET[lxid]");
	}
 
disp(sample_type_config);
?>