<?php
include "../temp/config.php";
$id=$_GET['id'];
$pingjia_id=$_GET['pingjia_id'];
$sql="delete from `ghs_pingjia` where `id`=$pingjia_id";
$info=$DB->query($sql);
gotourl("$rooturl/gys/ghs_pingjia.php?id=$id");
?>