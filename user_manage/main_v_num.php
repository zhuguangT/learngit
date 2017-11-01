<?php
include "../temp/config.php";
$sql="SELECT * FROM sjqm WHERE id=$_POST[id]";
$res=$DB->query($sql);
$data=$DB->fetch_assoc($res);
echo $data['kucun'];
die;