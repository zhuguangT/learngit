<?php
include "temp/config.php";
if($_GET[fx_user]=='' || $_GET[jh_user]=='' || $_GET[fh_user]=='' || $_GET[sh_user]=='') backa(5,"未通过审核的标准溶液不允许启用!");
else{
	$DB->query("update `bzry` set `qiyong`='已启用' where `id`=$_GET[bzry_id]");
	gotourl("bzry_list.php");
}
?>


