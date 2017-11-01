<?php
/**
 * 功能：数据库表更新
 * 作者：Mr Zhou
 * 日期：2016-04-11
 * 描述：
 */
class Update_dbApp extends LIMS_Base {
	//再退回任务单时默认清空签字日期
	private  $clear_sign_date = true;
	/**
	 * 构造函数
	 */
	function __construct() {
		parent::__construct();
		$u = $this->_u;
		if( !$u['admin'] ){
			die("禁止访问！");
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-04-11
	 * 参数：
	 * 返回值：
	 * 功能描述：
	*/
	public function index(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		// error_reporting(E_ALL);
		// ini_set("display_errors", 1);
		echo '<style>green {color:#69aa46;}red {color:#dd5a43;}</style>';
		// $this->up_hub_info($DB);//分中心表
		// $this->up_cy($DB);//采样单记录
		// $this->up_py($DB);//称量配药记录
		// $this->up_bd($DB);//标准溶液标定记录
		$this->up_hyd($DB);//化验单原始记录
		// $this->up_qx($DB);//标准曲线原始记录
		// 检测项目配置初始化
		// $this->jcxm_dis_init();
		// $this->up_xmfa($DB);
		// $this->up_method($DB);
		// $this->up_report($DB);
	}
	private function error_msg($status,$error_msg,$msg_only_error=false,$die=false){
		if( !$msg_only_error && $status ){
			echo "<green>success</green>：{$error_msg}<br />";
		}else{
			echo "<red>error</red>：{$error_msg}<br />";
			if( $die ){
				die;
			}
		}
	}
	private function up_hub_info($DB){
		echo '<b>分中心hub_info表</b><br />';
		$columns = $this->get_columns('hub_info');
		if( !in_array('sort_name', $columns) ){
			$error_msg = '增加sort_name字段';
			$sql = "ALTER TABLE  `hub_info` ADD  `sort_name` VARCHAR( 50 ) NOT NULL COMMENT  '缩略名称' AFTER  `hub_name`";
			$this->error_msg($DB->query($sql),$error_msg);
			$DB->query("UPDATE `hub_info` SET `sort_name`=`hub_name` WHERE 1");
		}
	}
	private function up_hyd($DB){
		echo '<b>化验单assay_pay表</b><br />';
		$hyd_columns = $this->get_columns('assay_pay');
		if( !in_array('fp_id', $hyd_columns) ){
			$error_msg = '增加fp_id字段';
			$sql = "ALTER TABLE `assay_pay` ADD `fp_id` INT NOT NULL COMMENT '项目分包分配分中心ID' AFTER `fzx_id`";
			$query = $DB->query($sql);
			$this->error_msg($query,$error_msg);
			if( $query ){
				$error_msg = '更新fp_id内容';
				$sql = "UPDATE `assay_pay` SET `fp_id` = `fzx_id` WHERE `fp_id`='0'";
				$query = $DB->query($sql);
				$this->error_msg($query,$error_msg);
			}
		}
		if( !in_array('uid', $hyd_columns) || !in_array('uid2', $hyd_columns) ){
			if( !in_array('uid', $hyd_columns) ){
				$error_msg = '增加uid,uid2字段';
				$sql = "ALTER TABLE `assay_pay` 
				ADD `uid` INT NOT NULL COMMENT '第一化验员' AFTER `fp_id`,
				ADD `uid2` INT NOT NULL COMMENT '第二化验员' AFTER `uid`";
				$query = $DB->query($sql);
				$this->error_msg($query,$error_msg);
			}
			$user_query = $DB->query("SELECT * FROM `users` WHERE `group`!='0'");
			while($row = $DB->fetch_assoc($user_query)){
				$error_msg = "修改分中心（{$row['fzx_id']}）:{$row['userid']}的uid和uid2";
				$sql = "UPDATE `assay_pay` SET `uid` = '{$row['id']}' WHERE `uid`='0' AND `userid`='{$row['userid']}' AND `fzx_id`='{$row['fzx_id']}'";
				$query = $DB->query($sql);
				$sql = "UPDATE `assay_pay` SET `uid2` = '{$row['id']}' WHERE `uid2`='0' AND `userid2`='{$row['userid']}' AND `fzx_id`='{$row['fzx_id']}'";
				$query = $DB->query($sql);
				$this->error_msg($query,$error_msg);
			}
			$error_msg = '修改签字日期字段为datetime属性';
			$sql = "ALTER TABLE `assay_pay` 
				CHANGE `sign_date_01` `sign_date_01` DATETIME NULL COMMENT '第一化验员签字日期',
				CHANGE `sign_date_012` `sign_date_012` DATETIME NULL COMMENT '第二化验员签字日期',
				CHANGE `sign_date_02` `sign_date_02` DATETIME NULL COMMENT '校核日期',
				CHANGE `sign_date_03` `sign_date_03` DATETIME NULL COMMENT '复核日期',
				CHANGE `sign_date_04` `sign_date_04` DATETIME NULL COMMENT '审核日期'";
			$this->error_msg($DB->query($sql),$error_msg);
		}
		if( !in_array('btdata', $hyd_columns) ){
			$error_msg = '添加btdata字段参数';
			$sql = "ALTER TABLE `assay_pay` ADD `btdata` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '表头参数' AFTER `CB` ";
			$query = $DB->query($sql);
			$sql = "ALTER TABLE `bt` ADD `btdata` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '表头参数' AFTER `zongheng` ";
			$query = $DB->query($sql);
			$this->error_msg($query,$error_msg);
		}
	}
	private function up_qx($DB){
		echo '<b>曲线standard_curve表</b><br />';
		$qx_columns = $this->get_columns('standard_curve');
		if( !in_array('sign_012', $qx_columns) ){
			// 
			$error_msg = '增加第二化验员信息';
			$sql = "ALTER TABLE `standard_curve`
					ADD `sign_012` VARCHAR( 10 ) NOT NULL DEFAULT '' COMMENT '第二化验员' AFTER `sign_01`,
					ADD `sign_date_012` DATETIME NULL DEFAULT NULL COMMENT '第二化验员签字日期' AFTER `sign_date_01`";
			$this->error_msg($DB->query($sql),$error_msg);
			// 
			$error_msg = '修改签字日期字段为datetime属性';
			$sql = "ALTER TABLE `standard_curve`
				CHANGE `sign_date_01` `sign_date_01` DATETIME NULL DEFAULT NULL COMMENT '第一化验员签字日期',
				CHANGE `sign_date_012` `sign_date_012` DATETIME NULL DEFAULT NULL COMMENT '第二化验员签字日期',
				CHANGE `sign_date_02` `sign_date_02` DATETIME NULL DEFAULT NULL COMMENT '校核日期',
				CHANGE `sign_date_03` `sign_date_03` DATETIME NULL DEFAULT NULL COMMENT '复核日期',
				CHANGE `sign_date_04` `sign_date_04` DATETIME NULL DEFAULT NULL COMMENT '审核日期'";
			$this->error_msg($DB->query($sql),$error_msg);
			// 
			echo '修改曲线表状态，将未签字状态改为已开始状态，和化验单等表，状态统一，方便管理<br />';
			$error_msg = '先增加已开始状态';
			$sql = "ALTER TABLE `standard_curve` CHANGE `status` `status` ENUM( '未签字', '被退回', '已开始', '已完成', '已校核', '已复核', '已审核', '已停用' ) NOT NULL DEFAULT '已开始' COMMENT '曲线状态'";
			$this->error_msg($DB->query($sql),$error_msg);
			$error_msg = '取消被退回和未签字状态，更改为已开始';
			$sql = "UPDATE `standard_curve` SET `status` = '已开始' WHERE `status` = '被退回' OR `status` = '未签字'";
			$this->error_msg($DB->query($sql),$error_msg);
			$error_msg = '待未签字和被退回状态修改为已开始后，取消该状态';
			$sql = "ALTER TABLE `standard_curve` CHANGE `status` `status` ENUM( '已开始', '已完成', '已校核', '已复核', '已审核', '已停用' ) NOT NULL DEFAULT '已开始' COMMENT '曲线状态'";
			$this->error_msg($DB->query($sql),$error_msg);
		}
		if( !in_array('CC', $columns) ){
			$error_msg = '增加CC';
			$sql = "ALTER TABLE `standard_curve` ADD `CC` VARCHAR(50) NOT NULL AFTER `CB`";
			$this->error_msg($DB->query($sql),$error_msg);
		}
		if( !in_array('pid', $columns) ){
			$error_msg = '增加pid';
			$sql = "ALTER TABLE  `standard_curve` ADD  `pid` INT NOT NULL COMMENT  '父级ID' AFTER  `vid`";
			$this->error_msg($DB->query($sql),$error_msg);
		}
	}
	private function up_cy($DB){
		echo '<b>采样单cy表</b><br />';
		$columns = $this->get_columns('cy');
		if( !in_array('xmfb', $columns) ){
			$error_msg = '增加xmfb字段';
			$sql = "ALTER TABLE `cy` ADD `xmfb` text NOT NULL COMMENT '项目分包' AFTER `xc_exam_value`";
			$this->error_msg($DB->query($sql),$error_msg);
		}
	}
	private function up_py($DB){
		echo '<b>称量配药记录jzry表</b><br />';
	}
	private function up_bd($DB){
		echo '<b>标准溶液标定记录jzry_bd表</b><br />';
		$error_msg = '正在使用和正在标定状态修改为已开始';
		$bd_columns = $this->get_columns('jzry_bd');
		$sql = "UPDATE `jzry_bd` SET `status`='已开始' WHERE `status` IN ('正在使用','正在标定')";
		$this->error_msg($DB->query($sql),$error_msg);
		if(!in_array('uid',$bd_columns)){
			$error_msg = '增加uid和uid2字段';
			$sql = "ALTER TABLE `jzry_bd` ADD `uid` INT NOT NULL COMMENT '标定人员1' AFTER `vid` ,ADD `uid2` INT NOT NULL COMMENT '标定人员2' AFTER `uid`";
			$this->error_msg($DB->query($sql),$error_msg);
		}
		$error_msg = '修改标定表status字段属性';
		$sql = "ALTER TABLE `jzry_bd` CHANGE `status` `status` ENUM( '已开始', '已完成', '已校核', '已复核', '已审核', '已停用' ) NOT NULL DEFAULT '已开始' COMMENT '标定状态'";
		$this->error_msg($DB->query($sql),$error_msg);
		if(!in_array('bzry_nddw',$bd_columns)){
			$error_msg = '增加标准溶液浓度单位字段';
			$sql = "ALTER TABLE `jzry_bd` ADD `bzry_nddw` VARCHAR( 10 ) NOT NULL DEFAULT '' COMMENT '标准溶液浓度单位' AFTER `bzry_pznd`";
			$this->error_msg($DB->query($sql),$error_msg);
		}
		if(!in_array('assay_data',$bd_columns)){
			$error_msg = '增加化验数据assay_data字段';
			$sql = "ALTER TABLE `jzry_bd` ADD `assay_data` TEXT NOT NULL COMMENT '化验数据' AFTER `mol_m` ";
			$this->error_msg($DB->query($sql),$error_msg);
		}
		if(!in_array('json',$bd_columns)){
			$error_msg = '增加标准溶液浓度单位字段';
			$sql = "ALTER TABLE `jzry_bd` ADD `json` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
			$this->error_msg($DB->query($sql),$error_msg);
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-11
	 * 功能描述：根据历史数据初始化化验员所分配到的检测项目
	*/
	private function jcxm_dis_init(){
		echo '<b>根据历史数据初始化化验员所分配到的检测项目</b><br />';
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		if(!$u['admin']){
			die('非法操作！');
		}
		$users	= array();
		$query	= $DB->query("SELECT * FROM `xmfa` WHERE 1");
		while($row=$DB->fetch_assoc($query)){
			//主测
			if(intval($row['userid']) && !isset($users[$row['userid']])){
				$users[$row['userid']]=array();
			}
			if(intval($row['userid']) && !in_array($row['xmid'],$users[$row['userid']])){
				$users[$row['userid']][]=$row['xmid'];
			}
			//辅测
			if(intval($row['userid2']) && !isset($users[$row['userid2']])){
				$users[$row['userid2']]=array();
			}
			if(intval($row['userid2']) && !in_array($row['xmid'],$users[$row['userid2']])){
				$users[$row['userid2']][]=$row['xmid'];
			}
		}
		foreach($users AS $uid => $vidArr){
			$vidStr	= implode(',', $vidArr);
			$sql = "UPDATE `user_other` SET `v4`='{$vidStr}' WHERE `uid`='{$uid}' AND `v4`=''";
			$query = $DB->query($sql);
			$affected_rows = $DB->affected_rows();
			$error_msg = "修改（{$uid}）的数据，#影响了{$affected_rows}行";
			$this->error_msg($query,$error_msg);
		}
	}
	// 修改xmfa表
	private function up_xmfa($DB){
		echo '<b>修改xmfa表</b><br />';
		$xmfa_columns = $this->get_columns('xmfa');
		// 
		if( !in_array('blws', $xmfa_columns)){
			$sql = "ALTER TABLE `xmfa`
					ADD `blws` VARCHAR( 100 ) NOT NULL DEFAULT '' COMMENT '保留位数' AFTER `userid2` ,
					ADD `round_inits` CHAR( 1 ) NOT NULL DEFAULT '0' COMMENT '是否修约整数位' AFTER `blws` ";
			$error_msg = "新增blws和round_inits字段";
			$this->error_msg($DB->query($sql),$error_msg);
			// 
			$sql = "ALTER TABLE `xmfa` 
			CHANGE `act` `act` CHAR( 1 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1' COMMENT '是否启用' AFTER `fzx_id`,
			CHANGE `mr` `mr` CHAR( 1 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '是否设为默认' AFTER `act`,
			CHANGE `zzrz` `zzrz` CHAR( 1 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1' COMMENT '资质认证' AFTER `mr`,
			CHANGE `lxid` `lxid` INT NOT NULL DEFAULT '0' COMMENT '水样类型id（leixing）' AFTER `zzrz`,
			CHANGE `xmid` `xmid` INT NOT NULL DEFAULT '0' COMMENT '项目表ID（assay_value） AFTER `lxid`',
			CHANGE `fangfa` `fangfa` INT NOT NULL DEFAULT '0' COMMENT '检测方法ID' AFTER `xmid`,
			CHANGE `yiqi` `yiqi` INT NOT NULL DEFAULT '0' COMMENT '仪器ID' AFTER `fangfa`,
			CHANGE `jcx` `jcx` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '-' COMMENT '检出限' AFTER `yiqi`,
			CHANGE `unit` `unit` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '检测单位' AFTER `jcx`,
			CHANGE `hyd_bg_id` `hyd_bg_id` INT NOT NULL DEFAULT '0' COMMENT '表格模板ID' AFTER `unit`,
			CHANGE `userid` `userid` INT NOT NULL DEFAULT '0' COMMENT '主测ID' AFTER `hyd_bg_id`,
			CHANGE `userid2` `userid2` INT NOT NULL DEFAULT '0' COMMENT '辅测ID' AFTER `userid`,
			CHANGE `englishMark` `englishMark` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '项目的英文编码（英文标识）' AFTER `w5`,
			CHANGE `beizhu` `beizhu` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '备注' AFTER `englishMark`";
			$error_msg = "修改xmfa表所有字段属性，取消为空，为NULL的属性，都会设置一个默认值";
			$this->error_msg($DB->query($sql),$error_msg);
			$del_columns = array('hyd_bg_name', 'sgz_date', 'sgz_date2');
			foreach ($del_columns as $key => $value) {
				if( in_array($value, $xmfa_columns)){
					$query = $DB->query("ALTER TABLE `xmfa` DROP `{$value}`");
					$this->error_msg($query,"删除xmfa表字段：{$value}");
				}
			}
		}
	}
	// 修改method表
	private function up_method($DB){
		echo '<b>修改up_method表</b><br />';
		$method_columns = $this->get_columns('assay_method');
		$del_columns = array('w1', 'w2', 'w3', 'w4', 'w5', 'hyd_bg_name');
		foreach ($del_columns as $key => $value) {
			if( in_array($value, $method_columns)){
				$query = $DB->query("ALTER TABLE `assay_method` DROP `{$value}`");
				$this->error_msg($query,"删除assay_method表字段：{$value}");
			}
		}
		if( in_array('gl_xm', $method_columns) ){
			$error_msg = "assay_method表gl_xm字段更名为vid并且改为int类型";
			$sql = "ALTER TABLE `assay_method` CHANGE `gl_xm` `vid` INT( 11 ) NULL DEFAULT '0' COMMENT '关联项目'";
			$this->error_msg($DB->query($sql),$error_msg);
		}
		if( !in_array('value_C', $method_columns)){
			$error_msg = "method表增加value_C字段";
			$sql = "ALTER TABLE `assay_method` ADD `value_C` VARCHAR( 50 ) NOT NULL DEFAULT '' COMMENT '项目名称' AFTER `vid` ";
			$this->error_msg($DB->query($sql),$error_msg);
			// 
			$sql = "ALTER TABLE `assay_method`
				CHANGE `jcx` `jcx` VARCHAR( 8 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '默认检出限',
				CHANGE `unit` `unit` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '默认化学单位'";
			$error_msg = "修改assay_method表jcx，unit字段属性，取消为空，为NULL的属性";
			$this->error_msg($DB->query($sql),$error_msg);
			// 
			$DB->query("UPDATE `assay_value` SET `value_C`='高锰酸盐指数' WHERE  `id`='104'");
			$DB->query("UPDATE `assay_value` SET `value_C`='阴离子合成洗涤剂' WHERE  `id`='107'");
			$DB->query("UPDATE `assay_method` SET `vid`='104', `value_C`='耗氧量' WHERE `id` IN (291,295)");
			$DB->query("UPDATE `assay_method` SET `vid`='107', `value_C`='阴离子表面活性剂' WHERE `id` IN (64,233,498)");
		}
	}
	// 修改report表，增加print_status字段
	private function up_report($DB){
		echo '<b>修改up_report表</b><br />';
		$report_columns = $this->get_columns('report');
		if(!in_array('print_status', $report_columns)){
			$sql = "ALTER TABLE `report` ADD `print_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '打印状态（1打印，0未打印,-1代表化验单退回了）'";
			$error_msg = "修改report表，增加print_status字段";
			$this->error_msg($DB->query($sql),$error_msg);
		}
	}
}