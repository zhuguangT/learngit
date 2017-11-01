<?php
include "../temp/config.php";
/*$optionBh= '请选择采样单号：<select name="cydh" id="cydh">';
$year    = date('Y');
$bhQuery = $DB->query("select DISTINCT cyd_bh from `cy` where year(cy_date)>=".$year." order by id desc");
while($rs= $DB->fetch_assoc($bhQuery)){
	$optionBh .= "<option value=\"".$rs['cyd_bh']."\">".$rs['cyd_bh']."</option>";
}
$optionBh.='</select>';
*/
$trade_global['daohang'][]	= array('icon'=>'','html'=>'样品扫描','href'=>"$rooturl/cy/barstatus.php");
$_SESSION['daohang']['barstatus']	= $trade_global['daohang'];
disp('saomiao');




?>
