<?php
/*
检查曲线关联函数是否已修改
当曲线退回修改后和化验单存的截距斜率不一致时作出提示
*/
include './temp/config.php';
//查询出曲线表所有的字段
function get_column($table_name){
	global $DB;
	$sql = "SELECT `COLUMN_NAME` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA`='{$DB->dbname}' AND `TABLE_NAME`='{$table_name}'";
	$query = $DB->query($sql);
	$columns = array();
	while ($row=$DB->fetch_assoc($query)) {
		$columns[] = $row['COLUMN_NAME'];
	}
	return $columns;
}
$sc_col = get_column('standard_curve');
//print_rr($sc_col);
$sc_table_col = array(
	'id' => "`id` int(1) NOT NULL AUTO_INCREMENT",
	'fzx_id' => "`fzx_id` int(11) NOT NULL COMMENT '分中心id' AFTER `id`",
	'type' => "`type` ENUM( '1', '2' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1' COMMENT '1:手工2:仪器' AFTER `fzx_id`",
	'vid' => "`vid` int(11) NOT NULL COMMENT '项目id'  AFTER `type`",
	'assay_element' => "`assay_element` varchar(100) NOT NULL COMMENT '项目名称' AFTER `vid`",
	'uid' => "`uid`  int(11) NOT NULL COMMENT '创建人id' AFTER `assay_element`",
	'userid' => "`userid` varchar(10) NOT NULL COMMENT '创建人姓名' AFTER `uid`",
	'bdid' => "`bdid` int(11) DEFAULT 0 COMMENT '标准液(bzwz_detail)id' AFTER `userid`",
	'jz_id' => "`jz_id` int(11) DEFAULT 0 COMMENT '自配液(jzry)id' AFTER `bdid`",
	'jzbd_id' => "`jzbd_id` int(11)  DEFAULT 0 COMMENT '标定液(jzry_bd)id' AFTER `jz_id`",
	'CA' => "`CA` varchar(10) DEFAULT '' COMMENT '截距(a)' AFTER `jzbd_id`",
	'CB' => "`CB` varchar(10) DEFAULT '' COMMENT '斜率(b)' AFTER `CA`",
	'CR' => "`CR` varchar(10) DEFAULT '' COMMENT '相关系数r' AFTER `CB`",
	'CT' => "`CT` varchar(10) DEFAULT '' COMMENT 'T值' AFTER `CR`",
	'equation' => "`equation` VARCHAR(100) DEFAULT '' COMMENT '曲线方程式' AFTER `CT`",
	'unit' => "`unit` varchar(10) DEFAULT '' COMMENT '曲线单位' AFTER `CT`",
	'create_date' => "`create_date` varchar(10) DEFAULT '' COMMENT '创建日期' AFTER `unit`",
	'td0' => "`td0` varchar(100) DEFAULT '' COMMENT '仪器名称及型号'",
	'td1' => "`td1` varchar(100) DEFAULT '' COMMENT '比色皿规格'",
	'td2' => "`td2` varchar(100) DEFAULT '' COMMENT '波长'",
	'td3' => "`td3` varchar(100) DEFAULT '' COMMENT '定容体积'",
	'td4' => "`td4` varchar(100) DEFAULT '' COMMENT '调零方法'",
	'td5' => "`td5` varchar(100) DEFAULT '' COMMENT '曲线编号'",
	'yq_bh'=>"`yq_bh` VARCHAR( 100 ) DEFAULT '' COMMENT '仪器编号' AFTER `td5`",
	'td6' => "`td6` varchar(100) DEFAULT '' COMMENT '率定日期'",
	'td7' => "`td7` varchar(100) DEFAULT '' COMMENT '标液名称'",
	'td8' => "`td8` varchar(100) DEFAULT '' COMMENT '标液浓度'",
	'td9' => "`td9` varchar(100) DEFAULT '' COMMENT '配制日期'",
	'td10' => "`td10` varchar(100) DEFAULT ''",
	'td11' => "`td11` varchar(100) DEFAULT ''",
	'td12' => "`td12` varchar(100) DEFAULT ''",
	'td13' => "`td13` varchar(100) DEFAULT ''",
	'td14' => "`td14` varchar(100) DEFAULT ''",
	'td15' => "`td15` varchar(100) DEFAULT ''",
	'td16' => "`td16` varchar(100) DEFAULT ''",
	'td17' => "`td17` varchar(100) DEFAULT ''",
	'td18' => "`td18` varchar(100) DEFAULT ''",
	'td19' => "`td19` varchar(100) DEFAULT ''",
	'td20' => "`td20` varchar(100) DEFAULT ''",
	'td21' => "`td21` varchar(100) DEFAULT ''",
	'td22' => "`td22` varchar(100) DEFAULT ''",
	'td23' => "`td23` varchar(100) DEFAULT ''",
	'td24' => "`td24` varchar(100) DEFAULT ''",
	'td25' => "`td25` varchar(100) DEFAULT ''",
	'td26' => "`td26` varchar(100) DEFAULT ''",
	'td27' => "`td27` varchar(100) DEFAULT ''",
	'td28' => "`td28` varchar(100) DEFAULT ''",
	'td29' => "`td29` varchar(100) DEFAULT ''",
	'td30' => "`td30` varchar(100) DEFAULT '' COMMENT '备注'",
	'td31' => "`td31` varchar(100) DEFAULT '' COMMENT '率定日期'",
	'td32' => "`td32` varchar(100) DEFAULT '' COMMENT '温度'",
	'td33' => "`td33` varchar(100) DEFAULT '' COMMENT '湿度'",
	'sign_01' => "`sign_01` varchar(10) DEFAULT ''",
	'sign_02' => "`sign_02` varchar(10) DEFAULT ''",
	'sign_03' => "`sign_03` varchar(10) DEFAULT ''",
	'sign_04' => "`sign_04` varchar(10) DEFAULT ''",
	'status' => " `status` ENUM( '未签字', '被退回', '已完成', '已校核', '已复核', '已审核', '已停用' ,'正在率定','正在使用','曲线被退回') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '未签字' COMMENT '状态' AFTER `sign_date_04`",
	'table_name' => " `table_name` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'sc_001' COMMENT '表格名称'",
	'json' => "`json` text DEFAULT '' COMMENT 'json目前存放（曲线回退的一些信息）'");

if(in_array('fx_qz_date',$sc_col)){
	$sql = "ALTER TABLE `standard_curve` CHANGE `fx_qz_date` `sign_date_01` varchar(10) DEFAULT ''";
	$DB->query($sql);
	echo $sql,'<br />';
}
if(in_array('jh_qz_date',$sc_col)){
	$sql = "ALTER TABLE `standard_curve` CHANGE `jh_qz_date` `sign_date_02` varchar(10) DEFAULT ''";
	$DB->query($sql);
	echo $sql,'<br />';
}
if(in_array('fh_qz_date',$sc_col)){
	$sql = "ALTER TABLE `standard_curve` CHANGE `fh_qz_date` `sign_date_03` varchar(10) DEFAULT ''";
	$DB->query($sql);
	echo $sql,'<br />';
}
if(in_array('sh_qz_date',$sc_col)){
	$sql = "ALTER TABLE `standard_curve` CHANGE `sh_qz_date` `sign_date_04` varchar(10) DEFAULT ''";
	$DB->query($sql);
	echo $sql,'<br />';
}
if(in_array('table_type',$sc_col)){
	$sql = "ALTER TABLE `standard_curve` CHANGE `table_type` `table_name` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '表格名称'";
	$DB->query($sql);
	echo $sql,'<br />';
}

foreach ($sc_table_col as $key => $value) {
	if(in_array($key,$sc_col)){
		$sql = "ALTER TABLE `standard_curve` CHANGE `$key` {$value}";
	}else{
		$sql = "ALTER TABLE `standard_curve` ADD {$value}";
	}
	$DB->query($sql);
	echo $sql,'<br />';
}
$sql = "UPDATE `standard_curve` SET `td23`=`td24`,`td24`=`td25`,`td25`=`td28`,`td28`=`td29`,`td29`=`td30` WHERE `vid`=121 AND `CA`='' AND `CB`='' AND `CR`=''";
$DB->query($sql);
echo $sql,'<br />';
$sql = "UPDATE `standard_curve` SET `CA`=`td19`,`CB`=`td20`,`CR`=`td18`,`assay_element`=`td22`,`td18`=`td23`,`td30`=`td21`,`td31`=`td6` WHERE `CA`='' AND `CB`='' AND `CR`=''";
$DB->query($sql);
echo $sql,'<br />';
$sql = "UPDATE `standard_curve` SET `table_name`='sc_003' WHERE `table_name` = '1'";
$DB->query($sql);
echo $sql,'<br />';
$sql = "UPDATE `standard_curve` SET `table_name`='sc_004' WHERE `table_name` = '2'";
$DB->query($sql);
echo $sql,'<br />';
$query = $DB->query("UPDATE  `standard_curve` SET `status` = '未签字' WHERE `status` = '正在率定'");
$query = $DB->query("UPDATE  `standard_curve` SET `status` = '已完成' WHERE `status` = '正在使用'");
$query = $DB->query("UPDATE  `standard_curve` SET `status` = '被退回' WHERE `status` = '曲线被退回'");
$query = $DB->query("ALTER TABLE `standard_curve` CHANGE `status` `status` ENUM( '未签字', '被退回', '已完成', '已校核', '已复核', '已审核', '已停用' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '未签字' COMMENT '状态'");

$sql = "SELECT id,userid FROM `users` WHERE 1";
$query = $DB->query($sql);
while ($row = $DB->fetch_assoc($query)) {
	$DB->query("UPDATE `standard_curve` SET `uid`='{$row['id']}' WHERE userid = '{$row['userid']}'");
}