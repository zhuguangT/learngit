<?php
/**
 * 功能：标准溶液，标准样品 打印台账
 * 作者: Mr Zhou
 * 日期: 2014-10-21
 * 描述: 
*/
include ('../../temp/config.php');
$fzx_id = FZX_ID;
//导航
$trade_global['daohang'] = array(array('icon'=>'icon-home home-icon','html'=>'首页','href'=>$rooturl.'/main.php'),array('icon'=>'','html'=>'打印台账','href'=>$current_url));
$trade_global['css'] = array('lims/main.css');

if(isset($_GET['wz_type']) && in_array($wz_type, array('bzry', 'bzyp'))) {
    $wz_type = $_GET['wz_type'];
} else { 
    $wz_type = 'bzry';
}

if(!empty($_GET['wz_name'])&& $_GET['wz_name']!='全部'){
    $wname="   AND  bz.`wz_name`='{$_GET['wz_name']}'";
}
$file_code_ary['bzry'] = '';
$file_code_ary['bzyp'] = '';
$file_code = $file_code_ary[$wz_type];

$wz_type_ary = array('bzry'=>'标准溶液', 'bzyp'=>'标准样品');
$wz_type = $wz_type_ary[$wz_type];


$year = intval($_GET['year']) ? intval($_GET['year']) : date('Y');

$sql = "SELECT bz.*, bl.`op_type`, bl.`amount`, bl.`jie_cun`, bl.`op_date`,bl.`op_man`
		FROM `bzwz_ls` bl LEFT JOIN `bzwz` bz ON bz.`id` = bl.`wz_id`
		WHERE bz.`fzx_id`='$fzx_id' AND bz.`wz_type` = '{$wz_type}' AND `op_date` LIKE '$year%' $wname
		ORDER BY `create_date` ,`wz_name`, `wz_bh`, bl.`id`";
$res = $DB->query($sql);
$result = array();
while($row = $DB->fetch_assoc($res)) {
    $timeline		= strtotime($row['op_date']);
    $row['date']	= date('d', $timeline);
    $row['year']	= date('Y', $timeline);
    $row['month']	= date('m', $timeline);
    if($row['op_type'] == '入库') {
        $row['in_amount'] = $row['amount'];
    } else {
        $row['out_amount'] = $row['amount'];
    }
    $result[$row['wz_name']][$row['wz_bh']][] = $row;
}
if($_GET['lx']!='2') //1代表 是 老格式 打印 2 代表是 汇总 打印
foreach($result as $wz_name => $wz) {
    foreach($wz as $wz_bh => $rows) {
        include 'bzwz_taizhang_disp_header.php';
        foreach($rows as $line) {
            include 'bzwz_taizhang_disp_body.php';
        }
        echo '</table><div > <input type="button" onclick="fenye(this)" value="另起一页" /> </div>';
    }
}
else{
	foreach($result as $wz_name => $wz) {
	        include 'bzwz_taizhang_disp_header.php';
	    foreach($wz as $wz_bh => $rows) {
	        foreach($rows as $line) {
	            include 'bzwz_taizhang_disp_body.php';
	        }
	    }// echo '</table><p class="pf" />';
	    echo '</table><div ><input type="button" onclick="fenye(this); " value="另起一页" /> </div>';
	}
}
echo '</body></html>';
