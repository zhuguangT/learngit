<?php
include "../temp/config.php";
$trade_global['daohang'][]  = array('icon'=>'','html'=>'地图查看','href'=>"$rooturl/admin/site_gmap2.php?yjd={$_GET['yjd']}&ywd={$_GET['ywd']}&jd={$_GET['jd']}&wd={$_GET['wd']}");
$_SESSION['daohang']['site_gmap2'] = $trade_global['daohang'];
echo "ff";
?>