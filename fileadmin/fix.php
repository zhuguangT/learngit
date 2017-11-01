<?php
include '../temp/config.php';
$fzx_id=$_SESSION['u']['fzx_id'];
//include "config.inc.php";
//echo $title;
$id=$_GET[id];
$sel = "select * from filemanage where fzx_id='$fzx_id' and id = $id";
$rs = $DB->query($sel);
$res = $DB->fetch_assoc($rs);
disp('fileadmin/file_fix');
?>
