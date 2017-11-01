<?php
/*
*功能：统计报告查看页面（青岛个性化）
*作者：hanfeng
*时间：2015-05-11
*/
include '../temp/config.php';

if($u['userid'] == ''){
	nologin();
}
$fzx_id	= $u['fzx_id'];
$_POST	= array(
"site_type" => 1,
    "water_type" => 全部,
    "group_name" => Array
        (
            "0" => 月初大水（一）,
            "1" => 月初大水（二）
        ),

    "tjcs" => '',
    "sites" => Array
        (
            "月初大水（一）" => Array
                (
                    "0" => 107,
                    "1" => 105,
                    "2" => 104,
                    "3" => 108,
                    "4" => 109,
                    "5" => 106,
                    "6" => 103,
                    "7" => 102,
                    "8" => 110
                ),

            "月初大水（二）" => Array
                (
                    "0" => 117,
                    "1" => 157,
                    "2" => 115,
                    "3" => 113,
                    "4" => 111,
                    "5" => 118,
                    "6" => 116,
                    "7" => 112,
                    "8" => 114
                )

        ),

    "begin_date" => '2015-04-01',
    "end_date" => '2015-04-30',
    "xmmb" => '',
    "vid" => Array
        (
            "0" => 1,
            "1" => 2,
            "2" => 3,
            "3" => 6,
            "4" => 58,
            "5" => 69,
            "6" => 70,
            "7" => 86,
            "8" => 93,
            "9" => 94,
            "10" => 95,
            "11" => 96,
            "12" => 97,
            "13" => 99,
            "14" => 100,
            "15" => 103,
            "16" => 104,
            "17" => 105,
            "18" => 107,
            "19" => 108,
            "20" => 111,
            "21" => 114,
            "22" => 117,
            "23" => 118,
            "24" => 119,
            "25" => 120,
            "26" => 121,
            "27" => 122,
            "28" => 133,
            "29" => 135,
            "30" => 137,
            "31" => 138,
            "32" => 141,
            "33" => 152,
            "34" => 154,
            "35" => 157,
            "36" => 159,
            "37" => 161,
            "38" => 166,
            "39" => 170,
            "40" => 179,
            "41" => 181,
            "42" => 182,
            "43" => 185,
            "44" => 186,
            "45" => 190,
            "46" => 198,
            "47" => 205,
            "48" => 210,
            "49" => 212,
            "50" => 218,
            "51" => 220,
            "52" => 221,
            "53" => 223,
            "54" => 224,
            "55" => 247,
            "56" => 280,
            "57" => 292,
            "58" => 309,
            "59" => 315,
            "60" => 316,
            "61" => 317,
            "62" => 323,
            "63" => 376,
            "64" => 386,
            "65" => 392,
            "66" => 393,
            "67" => 394,
            "68" => 410,
            "69" => 484,
            "70" => 487,
            "71" => 494,
            "72" => 496,
            "73" => 497,
            "74" => 498,
            "75" => 499,
            "76" => 503,
            "77" => 510,
            "78" => 511,
            "79" => 523,
            "80" => 544,
            "81" => 545,
            "82" => 569,
            "83" => 586,
            "84" => 589,
            "85" => 592,
            "86" => 595,
            "87" => 598,
        ),
    "cgb_title" => 标题,
    "cgb_mb" => 2,
    "cgb_bt_cs" => Array
        (
            "0" => 站点名称,
            "1" => 采样日期
        ),

    "action" => view,
    "view" => 查看成果
);
//本批次、本水样类型、本月、项目ID
$group_name	= "'月初大水（一）','月初大水（二）'";
$site_ids	= implode(",",$_POST['sites']['月初大水（一）']).','.implode(",",$_POST['sites']['月初大水（二）']);
$water_type	= "5,55,88";
$date_bgin	= '2015-04-01';
$date_end	= '2015-04-30';
$date_group	= 'month';//日期按月分组
$vid	= implode(",",$_POST['vid']);
/*$sql	= $DB->query("SELECT rec.id FROM `cy` INNER JOIN `cy_rec` AS rec ON cy.id=rec.cyd_id WHERE cy.`group_name` in ({$group_name}) AND rec.`water_type` IN ({$water_type}) AND rec.`sid` in ({$site_ids}) AND cy.`cy_date` between '{$date_bgin}' AND '{$date_end}' ");
$cid	= '';
while($rs=$DB->fetch_assoc($sql)){
	$cid	.= $rs['id'].",";
}
if(!empty($cid)){
	$cid	= substr($cid,0,-1);
}
$sql_pay	= $DB->query("SELECT * FROM `assay_pay` WHERE cid in ($cid) AND vid in ($vid)");
$count_vid	= 0;
$vid_arr	= array();
while($rs_pay= $DB->fetch_assoc($sql_pay)){
	$count_vid++;
	//如果检测完成
	//if(){
		
	//}
}
*/
if($_GET['action']=='1'){
    $tjbg_list_lines	= '<tr align="center">
					    <td width="5%">1</td>
					    <td width="10%">5月</td>
					    <td width="13%">49/49</td>
					    <td width="20%">查看 下载</td>
					</tr>';
}else if($_GET['action']=='2'){
   echo $tjbg_list_lines    = '
            <tr align="center">
                        <td width="5%">序号</td>
                        <td width="10%">日期</td>
                        <td width="13%">进度</td>
                        <td width="20%">操作</td>
                    </tr>
            <tr align="center">
                        <td width="5%">1</td>
                        <td width="10%">5月</td>
                        <td width="13%">35/35</td>
                        <td width="20%">查看 下载</td>
                    </tr>';
                    exit;
}
disp("tjbg_list_qdyb.html");
?>