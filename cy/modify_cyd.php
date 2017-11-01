<?php
/**
* 修改采样单
*/

include "../temp/config.php";

if($u[userid] == '') nologin();
switch($_GET[action]){
    case '修改采样日期':
        $DB->query("update `cy` set `cy_date`='".$_GET['cy_date']."' where `id`='".$_GET['cyd_id']."'");
        break;
    case '修改采样人':
        $DB->query("update `cy` set `cy_user`='".$_GET['cy_user']."' where `id`='".$_GET['cyd_id']."'");
        break;
    case '删除':
        $DB->query("delete from `cy` where id='".$_GET['cyd_id']."'");
        $DB->query("delete from `cy_rec` where cyd_id='".$_GET['cyd_id']."'");
        $DB->query("delete from `assay_order` where cyd_id='".$_GET['cyd_id']."'");
        $DB->query("delete from `assay_pay` where cyd_id='".$_GET['cyd_id']."'");
	$DB->query("delete from `report` where cyd_id='".$_GET['cyd_id']."'");
        break;
    case '修改采样单编号':
        $DB->query("update `cy` set `cyd_bh`='".$_GET['cyd_bh']."' where `id`='".$_GET['cyd_id']."'");
        break;
}
gotourl($_SESSION['url']);

?>
