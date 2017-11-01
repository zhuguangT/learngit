<?php
/**
 * 功能：检测项目，方法配置
 * 作者：Mr Zhou
 * 日期：2015-12-17
 * 描述：
 */
class JcxmApp extends LIMS_Base {
	public	$file_path;
	public	$mr_leixing = 1;
	/**
	 * 构造函数
	 */
	function __construct() {
		global $global;
		if(isset($global['hyd']['jcxm_set_mr_lx'])){
			$this->mr_leixing = $global['hyd']['jcxm_set_mr_lx'];
		}
		parent::__construct();
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-12-17
	 * 功能描述：
	*/
	public function index(){
		$PublicApp = new PublicApp();
		$PublicApp->reto('正在跳转至检测方法配置页面','ahlims.php?app=jcxm&act=jcxm_set','info',1);
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-12-17
	 * 功能描述：检测项目配置
	*/
	public function jcxm_set(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$mr_leixing = $this->mr_leixing;
		global $global,$trade_global,$rooturl,$current_url;
		//导航
		$trade_global['daohang']= array(
			array('icon'=>'icon-home home-icon','html'=>'首页','href'=>$rooturl.'/main.php'),
			array('icon'=>'','html'=>'检测方法配置','href'=>$current_url)
		);
		//查询出所有的项目，提供项目名称搜索候选项
		$all_values = array();
		/*$sql = "SELECT * FROM `assay_value` WHERE 1 ORDER BY CONVERT(`value_C` USING gbk)";
		$query = $DB->query($sql);
		while ($row = $DB->fetch_assoc($query)) {
			$all_values[] = str_replace("'", '', $row['value_C']);
		}*/
		$all_values_data = json_encode($all_values);
		$this->disp('hyd/jcxm/jcxm_set',get_defined_vars());
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-03-03
	 * 功能描述：获取检测项目配置所有功能的提示信息
	 * 1、【检测方法配置】：信息配置不完整或者有问题的数据做出提示
	 * 2、【审核任务设置】：未分配化验员的检测项目做出提示
	 * 3、【审核任务设置】：已经将某项目分配给某化验员，但是并没有任何一个已配置的方法在化验员选择上（包括主测和辅测）都没有选择本化验员，需要做出提示
	 * 4、【审核任务设置】：根据实验室global配置里面的审核级数分别作出提示，比如哪几个项目没有配置校核人，哪几个项目没有配置复核人。哪一级哪几个项目没有配置一定要明确
	 * 5、【检测项目配置】
	*/
	public function get_jcxm_all_msg( $is_echo=true ){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		global $global;
		$all_msg = array();
		// 非管理员不接收通知
		if( !$u['system_admin'] && !$u['admin'] ){
			if( true != $is_echo ){
				return $all_msg;
			}else{
				die( !count($all_msg) ? '[]' : json_encode($all_msg) );
			}
		}
		// 配置有问题的检测方法
		$all_msg['xmfa_set'] = $this->get_error_data();
		// 获取所有已分配至化验员的检测项目
		$uids_has_seted = $this->get_uids_has_seted();
		// 获取本实验室已配置的检测项目
		$sys_has_seted = $this->get_sys_has_seted();
		// 得到实验室已配置的检测项目的数组字符串
		$sys_has_seted_vids = empty($sys_has_seted) ? '0' : implode(',', $sys_has_seted);
		// 查询出assay_value表所有检测项目进行判断处理
		$assay_value_query = $DB->query("SELECT `id`, `value_C` FROM `assay_value` WHERE `id` IN({$sys_has_seted_vids}) ORDER BY CONVERT(`value_C` USING gbk)");
		while ( $row = $DB->fetch_assoc($assay_value_query) ) {
			// 实验室已配置，但是并没有分配给任何一个化验员的检测项目
			if( !in_array($row['id'], $uids_has_seted['v4']) ){
				$all_msg['shhe_set']['jc'][] = $row;
			}
			// 校核，复核，审核，$global审核设置示例
			// $global['hyd']['sh_set'] = array( '02' => array('jh','校核','v1')[,array()...] )
			// $global['hyd']['sh_config'] = array( 'jh'=>array('v1','校核','已完成','02')[,array()...] )
			foreach ($global['hyd']['sh_config'] as $key => $value) {
				// 如果本实验室未配置本项审核级数则不进行统计
				if( !isset($global['hyd']['sh_set'][$value[3]]) ){
					$all_msg['shhe_set'][$key] = array();
				}else{
					// 必须是已分配至化验员的项目才检查是否配置校核，复核，审核
					if( in_array($row['id'], $uids_has_seted['v4']) && !in_array($row['id'], $uids_has_seted[$value[0]]) ){
						$all_msg['shhe_set'][$key][] = $row;
					}
				}
			}
			// 检测项目配置
			// $all_msg['jcxm_set'] = array();
			// 项目模板配置
			// $all_msg['xmmb_set'] = array();
		}
		if( true != $is_echo ){
			return $all_msg;
		}else{
			echo !count($all_msg) ? '[]' : json_encode($all_msg);
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-05-13
	 * 功能描述：信息配置不完整或者有问题的数据
	*/
	// 1、未设置检测方法，人员，检出限，表格，单位的
	// 2、未设置默认的
	// 3、存在多个默认方法的
	// 4、默认方法被停用，而非默认方法在启用状态的
	// 5、检测方法重复的
	// 6、两个化验员为同一个人的
	public function get_error_data(){
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$error_data = array();
		// 未设置检测方法，人员，表格，单位的，主辅测为同一个人的
		$sql_1 = "SELECT `id` FROM `xmfa` WHERE `fzx_id`='{$fzx_id}' AND ( `userid`=`userid2` OR `fangfa`='0' OR `hyd_bg_id`='0' OR `unit`='' )";
		$query_1 = $DB->query($sql_1);
		while ( $row = $DB->fetch_assoc($query_1) ) {
			$error_data[] = intval($row['id']);
		}
		// 未设置默认的,存在多个默认方法的
		$sql_2 = "SELECT `lxid`, `xmid` FROM `xmfa` WHERE `fzx_id`='{$fzx_id}' GROUP BY `lxid`, `xmid`";
		$query_2 = $DB->query($sql_2);
		while ( $row = $DB->fetch_assoc($query_2) ) {
			// 查找每一个水样类型下每一个项目的默认方法，默认方法只能有一个
			$query_3 = $DB->query("SELECT `id` FROM `xmfa` WHERE `fzx_id`='{$fzx_id}' AND `lxid`='{$row['lxid']}' AND `xmid` ='{$row['xmid']}' AND `mr`='1'");
			if( 1 != intval($DB->num_rows($query_3)) ){
				$error_data[] = $row['id'];
				while ( $row_3 = $DB->fetch_assoc($query_3)) {
					$error_data[] = intval($row_3['id']);
				}
			}
		}
		// 默认方法被停用，而非默认方法在启用状态的
		$sql_4 = "SELECT `id`, `lxid`, `xmid` FROM `xmfa` WHERE `fzx_id`='{$fzx_id}' AND `act`='0' AND `mr`='1' ";
		$query_4 = $DB->query($sql_4);
		while ( $row = $DB->fetch_assoc($query_4) ) {
			// 查找已启用的非默认方法
			$query_5 = $DB->query("SELECT `id` FROM `xmfa` WHERE `fzx_id`='{$fzx_id}' AND `lxid`='{$row['lxid']}' AND `xmid` ='{$row['xmid']}' AND `act`='1' AND `mr`='0' ");
			if( $DB->num_rows($query_5) > 0 ){
				$error_data[] = intval($row['id']);
				while ( $row_5 = $DB->fetch_assoc($query_3)) {
					$error_data[] = intval($row_5['id']);
				}
			}
		}
		// 数组去重并建立新的索引数组
		return array_values(array_filter(array_unique($error_data)));
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-12
	 * 功能描述：检测方法设置
	*/
	public function xmfa_set(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		global $rooturl,$global;
		//所有化验员
		$fx_users = $this->get_fx_users();
		echo eval($this->get_eval_code('hyd/jcxm/xmfa_set'));
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-13
	 * 功能描述：获取检测方法配置数据
	*/
	public function xmfa_list($fid=0){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		//定义筛选条件信息
		$sql_where_arr = array(" WHERE `x`.`fzx_id`='{$fzx_id}' ");
		//定义所有sql组合元素
		$sql_select = $sql_from = $sql_where = $sql_search = $sql_sort_order = $sql_limit = '';
		//SELECT查询的所有字段信息
		$sql_select	= "SELECT `x`.*, `table_cname`, `lx`.`lname`,
				IF( `am`.`value_C` IS NULL OR `am`.`value_C` = '',`av`.`value_C`,`am`.`value_C`) AS `value_C`,
				`yq`.`yq_mingcheng` AS `td4`, `yq`.`yq_xinghao` AS `td5`, `yq`.`yq_chucangbh` AS `yq_bh`,
				`am`.`method_number`, `am`.`method_name`, `ua`.`userid` AS `userida`, `ub`.`userid` AS `useridb` ";
		//FROM的表连接信息
		$sql_from	= "FROM `xmfa` AS `x`
				LEFT JOIN `yiqi` AS `yq` ON `x`.`yiqi`=`yq`.`id`
				LEFT JOIN `leixing` AS `lx` ON `x`.`lxid`=`lx`.`id`
				LEFT JOIN `users` AS `ua` ON `ua`.`id`=`x`.`userid`
				LEFT JOIN `users` AS `ub` ON `ub`.`id`=`x`.`userid2`
				LEFT JOIN `assay_value` AS `av` ON `x`.`xmid`=`av`.`id`
				LEFT JOIN `bt_muban` AS `bm` ON `x`.`hyd_bg_id`=`bm`.`id`
				LEFT JOIN `assay_method` AS `am` ON `x`.`fangfa`=`am`.`id`";
		//WHERE条件
		if( intval($fid) ){
			//指定fid的方法查询
			$sql_where = " WHERE `x`.`id`='".intval($fid)."' ";
		}else{
			// 获取配置不完整以及有问题的数据
			if( '4' == $_GET['show_type']){
				$error_data = $this->get_error_data();
				if( !empty($error_data) ){
					$sql_where_arr = array(" WHERE `x`.`id` IN(".implode(',', $error_data).") ");
				}
			}else{
				// 查看数据的类型
				if( '1' == $_GET['show_type']){
					$sql_where_arr[] = "AND `x`.`act`='1' ";// 已启用的方法
				}else if( '2' == $_GET['show_type']){
					$sql_where_arr[] = "AND `x`.`act`='0' ";// 已停用的方法
				}else if( '3' == $_GET['show_type']){
					// $sql_where_arr[] = "AND `x`.`act` IN('0','1')";// 所有检测方法
				}
				//定义筛选的化验员
				if( '全部' != $_GET['uid'] ){
					$uid = $_GET['uid'];
					//如果未指定用户则默认显示当前登录人的信息
					$user_jcxm = array();
					if( !$u['admin'] && !$u['system_admin'] ){
						$user_jcxm = $DB->fetch_one_assoc("SELECT `v4` FROM `user_other` WHERE `uid`='{$uid}'");
					}
					$user_jcxm = empty($user_jcxm['v4']) ? '0' : $user_jcxm['v4'];
					$sql_where_arr[] = "AND (x.`userid`='{$uid}' OR x.`userid2`='{$uid}' OR `xmid` IN({$user_jcxm})) ";

				}
				//水样类型
				if( '全部' != $_GET['leixing'] ){
					$sql_where_arr[] = "AND `x`.`lxid`='{$_GET['leixing']}' ";
				}
			}
			//组合WHERE条件
			$sql_where = implode('', $sql_where_arr);
			//bootstrapTable分页，排序，检索条件
			//ahlims.php?ajax=1&app=jcxm&act=xmfa_list&uid=607&leixing=5&search=苯&order=asc&limit=25&offset=50&_=1457157741309
			//定义搜索条件
			$search_str = trim($_GET['search']);
			if( !empty($search_str) ){
				//根据可能搜索的字段依次在项目名称，检测方法，检测依据，仪器名称，仪器型号，仪器编号，模板名称中进行检索
				$sql_search = "AND (  `av`.`value_C` LIKE '%{$search_str}%'
					OR `am`.`value_C` LIKE '%{$search_str}%'
					OR `method_name` LIKE '%{$search_str}%'
					OR `method_number` LIKE '%{$search_str}%'
					OR `yq`.`yq_mingcheng` LIKE '%{$search_str}%'
					OR `yq`.`yq_xinghao` LIKE '%{$search_str}%'
					OR `yq`.`yq_chucangbh` LIKE '%{$search_str}%'
					OR `table_cname` LIKE '%{$search_str}%' )";
			}
			//指定递增排序还是递减排序
			$order = !isset($_GET['sort']) ? 'ASC' : strtoupper(trim($_GET['order']));
			!in_array( $order, array('ASC', 'DESC') ) && ( $order = 'ASC' );
			//指定排序列
			$order_by_value_C = "CONVERT(`av`.`value_C` USING gbk) ASC, `lxid` ASC ,";
			$sort = (isset($_GET['sort']) && !empty($_GET['sort']) ) ? trim($_GET['sort']) : 'value_C';
			switch ($sort) {
				case 'userida':
					$sort = "`ua`.`userid` {$order}";
					break;
				case 'useridb':
					$sort = "`ub`.`userid` {$order}";
					break;
				case 'value_C':
					$order_by_value_C = '';
					$sort = "CONVERT(`av`.`value_C` USING gbk) {$order}, `lxid` ASC";
					break;
				case 'fangfa':
					$sort = "`am`.`method_number` {$order},`am`.`method_name` {$order}";
					break;
				case 'yiqi':
					$sort = "`yq`.`yq_mingcheng` {$order},`yq`.`yq_xinghao` {$order},`yq`.`yq_chucangbh` {$order}";
					break;
				default:
					$sort = "`{$sort}` {$order}";
					break;
			}
			// SQL排序字段及排序方式
			$sql_sort_order = "ORDER BY {$sort} , {$order_by_value_C} `x`.`id` ASC ";
			//定义分页信息，必须传递offset参数且limit数据大于0
			if( isset($_GET['offset']) && intval($_GET['limit']) ){
				$sql_limit = 'LIMIT '.intval($_GET['offset']).' , '.intval($_GET['limit']);
			}
		}
		$xmfa = array();
		$i = intval($_GET['offset']);
		//统计总行数
		$total = $DB->num_rows($DB->query("SELECT `x`.`id` {$sql_from} {$sql_where} {$sql_search}"));
		//查询详细信息
		$query = $DB->query( $sql_select . $sql_from . $sql_where . $sql_search . $sql_sort_order . $sql_limit );
		while ($row = $DB->fetch_assoc($query)) {
			$row['xuhao'] = ++$i;
			//在化验单中已使用的配置不允许删除
			$arow = $DB->fetch_one_assoc("SELECT `id` FROM `assay_pay` WHERE `fid`='{$row['id']}' LIMIT 1");
			$row['canDel'] = intval($arow['id']) ? false : true;
			// 赋值
			$xmfa[] = $row;
		}
		if(intval($fid)){
			return $xmfa[0];
		}else{
			echo json_encode(array('total'=>$total,'rows'=>$xmfa));
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-03-06
	 * 功能描述：检测xmfa表的修改，添加，删除权限并且返回当前fid的配置信息
	*/
	private function check_xmfa_power($fid){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$arow = $DB->fetch_one_assoc("SELECT * FROM `xmfa` WHERE `fzx_id`='{$fzx_id}' AND `id`='{$fid}'");
		//非系统管理员需要判断该方法是否允许进行操作
		if( !$u['system_admin'] && !$u['admin'] ){
			//查询当前用户已分配的检测项目
			$uid_other = $DB->fetch_one_assoc("SELECT `v4` FROM `user_other` WHERE `uid`='{$u['id']}'");
			$uid_dis_vid = empty($uid_other['v4']) ? array() : array_unique(explode(',', $uid_other['v4']));
			if( !$fid ){
				//如果未传递fid则默认为新增配置操作
				if( !in_array($_GET['xmid'], $uid_dis_vid) ){
					die(json_encode(array('error'=>'1','content'=>'对不起，你没有权限添加此配置信息！')));
				}
			}else{
				//检测是否有修改权限
				if( empty($arow['id']) ){
					die(json_encode(array('error'=>'1','content'=>'对不起，你要修改的数据不存在或已被删除！')));
				}else if( !in_array($arow['xmid'], $uid_dis_vid) ){
					die(json_encode(array('error'=>'1','content'=>'对不起，你没有权限修改此配置信息！')));
				}
			}
		}
		return $arow;//返回当前fid的配置信息
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-15
	 * 功能描述：删除无用检测方法配置信息
	*/
	public function xmfa_del(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$fid = intval($_GET['fid']);
		$this->check_xmfa_power($fid);//检查修改权限
		$arow = $DB->fetch_one_assoc("SELECT `id`, `userid`, `userid2` FROM `xmfa` WHERE `fzx_id`='{$fzx_id}' AND `id`='{$fid}' LIMIT 1");
		if( !intval($arow['id']) ){
			die(json_encode(array('error'=>'1','content'=>'此配置信息不存在，或已被删除！')));
		}else if( !in_array( $u['id'], array($arow['userid'], $arow['userid2']) ) && !$u['system_admin'] && !$u['admin'] ){
			die(json_encode(array('error'=>'1','content'=>'对不起，你没有权限删除此配置信息！')));
		}
		//在化验单中已使用的配置不允许删除
		$arow = $DB->fetch_one_assoc("SELECT `id` FROM `assay_pay` WHERE `fzx_id`='{$fzx_id}' AND `fid`='{$fid}' LIMIT 1");
		if(intval($arow['id'])){
			echo json_encode(array('error'=>'1','content'=>'在化验单中已使用的配置信息不允许删除，您可以选择停用此方法！'));
		}else{
			$query = $DB->query("DELETE FROM `xmfa` WHERE `fzx_id`='{$fzx_id}' AND `id`='{$fid}'");
			if($query){
				$DB->query("DELETE FROM `bt` WHERE `fid`='{$fid}'");
				echo json_encode(array('error'=>'0','content'=>'删除成功！'));
			}else{
				echo json_encode(array('error'=>'1','content'=>'配置信息删除失败，请刷新重试！'));
			}
		}

	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-03-08
	 * 功能描述：根据fid或者相应的水样类型
	*/
	public function get_leixing_by_xmfa(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$xmid = intval($_GET['xmid']);
		$fangfa = intval($_GET['fangfa']);
		$lxid_arr = array();
		$query = $DB->query("SELECT DISTINCT `lxid` FROM `xmfa` WHERE `fzx_id`='{$fzx_id}' AND `xmid`='{$xmid}'");
		while ($row = $DB->fetch_assoc($query)) {
			$lxid_arr[] = $row['lxid'];
		}
		echo json_encode($lxid_arr);
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-15
	 * 功能描述：检测方法设置保存
	*/
	public function xmfa_save(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$fid = intval($_GET['fid']);
		//检查修改权限,并返回当前的配置信息
		$xmfa_info = $this->check_xmfa_power($fid);
		//允许修改的字段
		$columns = array('act','fangfa','xmid','jcx','unit','yiqi','userid','userid2','w1', 'w2','w3','w4','w5','hyd_bg_id','zzrz','mr');
		//有控制信息的条件
		$notNullColumns = array('jcx'		=>'检出限',
								'xmid'		=>'检测项目',
								'unit'		=>'检测单位',
								'fangfa'	=>'检测方法',
								'hyd_bg_id'	=>'化验单表格');
		//条件判断
		//必须传递水样类型
		if(empty($_GET['lxid'])){
			die(json_encode(array('error'=>'1','content'=>'无法识别水样类型，请重试！','field'=>'lxid')));
		}
		//主测、辅测必须设置
		if( isset($_GET['userid']) && !intval($_GET['userid']) ){
			die(json_encode(array('error'=>'1','content'=>'未设置主测人员，请设置！')));
		}
		if( isset($_GET['userid2']) && !intval($_GET['userid2']) ){
			die(json_encode(array('error'=>'1','content'=>'未设置辅测人员，请设置！')));
		}
		//主测、辅测不能是同一个人
		if( isset($_GET['userid']) && isset($_GET['userid']) ){
			$check_uid1 = ( isset($_GET['userid']) ) ? $_GET['userid'] : $xmfa_info['userid'];
			$check_uid2 = ( isset($_GET['userid2']) ) ? $_GET['userid2'] : $xmfa_info['userid2'];
			if( $check_uid1 == $check_uid2 ){
				die(json_encode(array('error'=>'1','content'=>'主测、辅测不能是同一个人！')));
			}
		}
		//检出限，检测项目，水样类型，检测单位，检测方法，化验单表格必须配置
		foreach($notNullColumns AS $key => $value){
			if( isset($_GET[$key]) && empty($value) ){
				die(json_encode(array('error'=>'1','content'=>$notNullColumns[$key].'数据无效！','field'=>$key)));
			}
		}
		$sql_set = array();
		foreach ($_GET as $key => $value) {
			switch ($key) {
				case 'xmid':
				case 'fangfa':
				case 'hyd_bg_id':
					$_GET[$key] = intval($value);
					if(!$_GET[$key]){
						die(json_encode(array('error'=>'1','content'=>$notNullColumns[$key].'数据无效！','field'=>$key)));
					}
					break;
				case 'jcx':
					$_GET[$key] = trim($value);
					if('' ==$_GET[$key]){
						die(json_encode(array('error'=>'1','content'=>$notNullColumns[$key].'不能为空！','field'=>$key)));
					}
					break;
				case 'act':
					$key = 'act_action';
					//避免act事件带入sql语句中
					break;
				case 'act_status':
					$key = 'act';
					$_GET[$key] = intval($value);
					//将act_status参数更改为act字段
					break;
			}
			if(in_array($key,$columns)){
				$sql_set[$key] = "`{$key}`='{$value}'";
			}
		}
		if( isset($_GET['is_blws']) && intval($_GET['is_blws']) ){
			$blws_set = array();
			foreach ($_GET['blws'] as $key => $value) {
				$blws = intval($_GET['blws'][$key]);
				$yxsz = intval($_GET['yxsz'][$key]);
				$zdws = intval($_GET['zdws'][$key]);
				$blws_set[$blws] = array($blws, $yxsz, $zdws);
			}
			// 按照区间倒序排列
			krsort($blws_set);
			$sql_set['blws'] = "`blws`='".json_encode(array_values($blws_set))."'";
		}else{
			$sql_set['blws'] = "`blws`=''";
		}
		$success = false;
		$msg_content = '';
		$has_been_lxing = array();
		if(!empty($sql_set)){
			if( intval($fid) ){
				$action = '修改';
			}else{
				$action = '新增';
				$fid = 0;
				$sql_set['act'] = "`act`='1'";
				$sql_set['fzx_id'] = "`fzx_id`='{$fzx_id}'";
			}
			/*****************************/
			$all_leixing = $this->get_all_leixing();
			//以水样类型数组进行遍历操作
			if( !is_array($_GET['lxid']) ){
				$_GET['lxid'] = array($_GET['lxid']);
			}else{
				//数字去重
				$_GET['lxid'] = array_unique($_GET['lxid']);
			}
			foreach ($_GET['lxid'] as $key => $lxid) {
				$sql_set['lxid'] = "`lxid`='{$lxid}'";
				if( isset($_GET['mr']) ){
					$sql_set['mr'] = "`mr`='{$_GET['mr']}'";
				}
				if( '新增' == $action ){
					//查找在该水样类型和方法下是否设置了默认方法
					$mr_method_query = $DB->query("SELECT `id` FROM `xmfa` WHERE `fzx_id`='{$fzx_id}' AND `lxid`='{$lxid}' AND `xmid`='{$_GET['xmid']}' AND `mr`='1' LIMIT 1");
					//如果之前没有默认配置信息，就将新增的数据设置为默认，否则不默认
					$sql_set['mr'] = $DB->num_rows($mr_method_query) ? "`mr`='0'" : "`mr`='1'";
				}
				//组合SQL语句WHERE条件
				$sql_set_str = implode(',', $sql_set);
				//查询该水样类型下是否配置相同的方法（检测依据，项目，仪器）
				$check_fangfa_has_seted_sql = "SELECT `id` FROM `xmfa` WHERE `fzx_id`='{$fzx_id}' AND `lxid`='{$lxid}' AND `fangfa`='{$_GET['fangfa']}' AND `xmid`='{$_GET['xmid']}' AND `yiqi`='{$_GET['yiqi']}'";
				//如果是新增操作并且该方法已存在则continue
				if( '新增' == $action && $DB->num_rows($DB->query($check_fangfa_has_seted_sql)) ){
					$success = true;
					$has_been_lxing[] = $all_leixing[$lxid];
					continue;
				}else{
					if( '新增' == $action ){
						$success = $DB->query("INSERT INTO `xmfa` SET {$sql_set_str}");
						if( $success ){
							$fid = $DB->insert_id();
							$DB->query("INSERT INTO `bt` SET `fid`='{$fid}'");
						}
					}else{
						$success = $DB->query("UPDATE `xmfa` SET {$sql_set_str} WHERE `fzx_id`='{$fzx_id}' AND `id`='{$fid}' LIMIT 1");
					}
					if( isset($_GET['userid']) || isset($_GET['userid2']) ){
						//如果该化验员并未分配该项目，则默认分配上
						foreach (array('userid', 'userid2') as $uid_key => $uid_column) {
							if( isset($_GET[$uid_column]) && intval($_GET[$uid_column]) ){
								//查询出当前化验员已分配的项目
								$uid_dis_vid = $DB->fetch_one_assoc("SELECT `v4` FROM `user_other` WHERE `uid`='{$_GET[$uid_column]}'");
								$uid_dis_vid = empty($uid_dis_vid['v4']) ? array() : array_filter(explode(',', $uid_dis_vid['v4']));
								//如果未传递xmid参数则使用当前配置信息$xmfa_info里面的xmid
								$xmid = intval($_GET['xmid']) ? $_GET['xmid'] : $xmfa_info['xmid'];
								if( !in_array($xmid, $uid_dis_vid) ){
									$uid_dis_vid[] = $xmid;
									$uid_dis_vids_str = implode(',', $uid_dis_vid);
									$DB->query("UPDATE `user_other` SET `v4`='{$uid_dis_vids_str}' WHERE `uid`='{$_GET[$uid_column]}'");
								}
							}
						}
					}
				}
			}
			if( !empty($has_been_lxing) ){
				$msg_content = '同一水样类型下，同一项目、同一个分析仪器的方法只能配置一条记录<br /><strong class="red">【'.implode($has_been_lxing, '】</strong>,<strong class="red">【' ).'】</strong>已经配置过该方法，不再重复添加！';
			}
			if($success){
				$arow = intval($fid) ? $this->xmfa_list($fid) : '';
				echo json_encode(array('error'=>'0','content'=>$msg_content,'fid'=>$fid,'data'=>$arow));
			}else{
				echo json_encode(array('error'=>'1','content'=>$action.'失败！'));
			}
		}else{
			echo json_encode(array('error'=>'1','content'=>'请求数据错误，请刷新重试！'));
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-31
	 * 功能描述：设置默认方法
	*/
	public function xmfa_setMr(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$fid	= intval($_GET['id']);
		$xmid	= intval($_GET['xmid']);
		$lxid	= intval($_GET['lxid']);
		$this->check_xmfa_power($fid);//检查修改权限
		if( !$fid || !$lxid || !$xmid ){
			//配置方法id，水样类型和检测项目必须都有效
			die(json_encode(array('error'=>'1','content'=>'请求数据有误，请刷新重试！')));
		}
		$success = false;//先将操作状态设置为失败
		$sql = "UPDATE `xmfa` SET `mr`='1',`act`='1' WHERE `fzx_id`='{$fzx_id}' AND `id`='{$fid}'";
		if($DB->query($sql)){
			$sql = "UPDATE `xmfa` SET `mr`='0' WHERE `fzx_id`='{$fzx_id}' AND `xmid`='{$xmid}' AND `lxid`='{$lxid}' AND `id`!={$fid}";
			if($DB->query($sql)){
				$success = true;
			}
		}
		if($success){
			$data = array();//修改成功后将该水样类型下所有该项目的配置信息中的默认状态返回
			$query = $DB->query("SELECT `id`,`mr` FROM `xmfa` WHERE `fzx_id`='{$fzx_id}' AND `xmid`='{$xmid}' AND `lxid`='{$lxid}'");
			while ($row = $DB->fetch_assoc($query)) {
				$data[] = $row;
			}
			echo json_encode(array('error'=>'0','content'=>'','data'=>$data));
		}else{
			echo json_encode(array('error'=>'1','content'=>'设置默认方法失败，请刷新重试！'));
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-25
	 * 功能描述：检测方法详细信息配置弹出层
	*/
	public function xmfa_modal(){
		echo eval($this->get_eval_code('hyd/jcxm/xmfa_modal'));
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-02-26
	 * 功能描述：审核设置页面
	*/
	public function shhe_set(){
		global $global;
		$sh_list = array();
		//根据系统配置设置审核列表
		foreach ($global['hyd']['sh_set'] as $key => $value) {
			$sh_list[$value[0]] = $value[1];
		}
		echo eval($this->get_eval_code('hyd/jcxm/sh_set'));
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-02-28
	 * 功能描述：审核设置列表
	*/
	/*$global['hyd'] = array(
		'sh_set'=> array(
			'02'=>array('jh','校核','v1'),
			'03'=>array('fh','复核','v2')
		)//审核设置
		,'sh_config'=> array(
			'jh'=>array('v1','校核','已完成','02'),
			'fh'=>array('v2','复核','已校核','03'),
			'sh'=>array('v3','审核','已复核','04')
		)
	);*/
	public function shhe_set_list(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		global $global;
		//默认配置校核页面
		( '' == $_GET['shh_type'] ) && ( $_GET['shh_type'] = $global['hyd']['sh_set']['02'][0] );
		//配置当前审核级别在user_other表的存储字段
		$shh_type = $global['hyd']['sh_config'][$_GET['shh_type']][0];
		//默认显示当前用户的配置信息
		$user_id = intval($_GET['uid'])	? intval($_GET['uid']) : $u['id'];
		//必须传递合法用户信息
		if(!$user_id){
			die('用户信息传递错误！');
		}
		//查询得到指定用户已经配置的审核项目
		$arow = $DB->fetch_one_assoc("SELECT `uid`, `{$shh_type}` FROM `user_other` WHERE `uid`='{$user_id}' LIMIT 1");
		if( intval($arow['uid']) ){
			$has_shh_seted = empty($arow[$shh_type]) ? array() : explode("','",$arow[$shh_type]);
		}else{
			$has_shh_seted = array();
			$arow = array('uid'=>$user_id,$shh_type=>'');
			//检查数据库中是否有这个用户的配置记录，如果没有就新建一个
			$DB->query("INSERT INTO `user_other` SET `uid`='$user_id'");
		}
		//序号
		$xuhao = 1;
		//查询assay_value表所有的检测项目
		$assay_value = $this->get_assay_value();
		//在所有化验员已分配的检测项目中选中已配置给$user_id的审核项目
		$shhe_set_list = array();
		$sql = "SELECT `u`.`id` AS `uid`,`u`.`userid`,`o`.`id` AS `oid`,`o`.`v4` AS `vids`
				FROM `users` AS `u` LEFT JOIN `user_other` AS `o` ON `u`.`id`=`o`.`uid`
				WHERE `u`.`fzx_id`='{$fzx_id}' AND `u`.`group`!='0' AND `u`.`group`!='测试组' AND u.`hua_yan`='1' ORDER BY CONVERT(`u`.`userid` USING gbk)";
		$query = $DB->query( $sql );
		while( $row = $DB->fetch_assoc($query) ){
			$row['xuhao'] = $xuhao++;
			if(empty($row['oid'])){
				$row['vids'] = '';
				//如果user_other表没有当前化验员的配置信息则创建一个记录
				$DB->query("INSERT INTO `user_other` SET `uid`='{$row['uid']}'");
				$row['oid'] = $DB->insert_id();
			}
			//解析当前化验员已配置的检测项目
			$uid_jc_vids = empty($row['vids']) ? array() : explode(',', $row['vids']);
			//如果$user_id已配置的审核项目与当前化验员的检测项目有交集，则默认选中
			$checked = array_intersect($has_shh_seted, $uid_jc_vids) ? 'checked="checked"' : '';
			$row['fx_user'] = '<label class="btn btn-white"><input class="fx_user" name="fx_user[]" type="checkbox" value="'.$row['uid'].'" '.$checked.' />&nbsp;'.$row['userid'].'（<span class="jcxm_total_'.$row['uid'].'">'.count($uid_jc_vids).'</span>）</label>';
			foreach($uid_jc_vids as $key => $vid){
				$checked = in_array($vid,$has_shh_seted) ? 'checked="checked"' : '';
				$row['jcxm'] .= '<label class="btn btn-white"><input class="jcxm" name="jcxm[]" type="checkbox" value="'.$vid.'" '.$checked.' /><span class="value_C">&nbsp;'.$assay_value[$vid]['value_C'].'</span></label>';
			}
			$shhe_set_list[] = $row;
		}
		echo json_encode($shhe_set_list);
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-02-28
	 * 功能描述：审核设置保存
	*/
	public function shhe_set_save(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		global $global;
		if(empty($_POST['jcxm']) && empty($_POST['vid'])){
			die(json_encode(array('error'=>'1','content'=>'没有选择任何项目！')));
		}
		$uid	= $_POST['uid'];
		if( empty($uid) ){
			die(json_encode(array('error'=>'1','content'=>'用户ID数据错误，请刷新重试！')));
		}
		$shh_type = $global['hyd']['sh_config'][$_POST['shh_type']][0];
		if( isset($_POST['is_single']) ){
			// 单项目快速修改修改
			foreach ($uid as $key => $value) {
				$arow = $DB->fetch_one_assoc("SELECT `{$shh_type}` FROM `user_other` WHERE `uid` ='{$value}'");
				if( empty($arow[$shh_type]) ){
					$vids = $_POST['vid'];
				}else{
					$vids_arr = explode("','", $arow[$shh_type]);
					$vids_arr[] = $_POST['vid'];
					$vids = @implode("'',''",array_unique($vids_arr));
				}
				$sql="UPDATE `user_other` SET `{$shh_type}` = '{$vids}' WHERE `uid` ='{$value}'";
				if(!$DB->query($sql)){
					die(json_encode(array('error'=>'1','content'=>'修改失败！')));
				}
			}
		}else{
			$vids = empty($_POST['jcxm']) ? '' : @implode("'',''",array_unique($_POST['jcxm']));
			$sql="UPDATE `user_other` SET `{$shh_type}` = '{$vids}' WHERE `uid` ='{$uid}'";
			if(!$DB->query($sql)){
				die(json_encode(array('error'=>'1','content'=>'修改失败！')));
			}
		}
		echo json_encode(array('error'=>'0','content'=>''));
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-11
	 * 功能描述：检测项目分配时选择项目
	*/
	public function jcxm_dis_box(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		//查询出个人项目分配详细信息
		$arow = $DB->fetch_one_assoc("SELECT `id`, `uid`, `v4` AS `vids` FROM `user_other` WHERE `uid`='{$_GET['uid']}'");
		( '' == $arow['vids'] ) && ( $arow['vids'] = 0 );
		$arow['data'] = json_encode($arow);
		$uid_has_dised = array_unique(explode(',', $arow['vids']));
		//获取本实验室已配置的检测项目
		$sys_has_seted = $this->get_sys_has_seted();
		//获取所有已分配至化验员的检测项目
		$uids_has_seted = $this->get_uids_has_seted();
		//得到实验室已配置的检测项目以及已分配项目的合集
		$all_vids = implode(',', array_filter(array_unique(array_merge($sys_has_seted,$uid_has_dised))));
		//定义已分配的项目和未分配的项目以及全部项目
		$seted_values = $other_values = $all_values = array();
		$seted_values_sql = "SELECT `id`, `value_C` FROM `assay_value` WHERE `id` IN({$all_vids}) ORDER BY CONVERT(`value_C` USING gbk)";
		$seted_values_query = $DB->query($seted_values_sql);
		while ( $row = $DB->fetch_assoc($seted_values_query) ) {
			//记录所有实验室已配置的项目为搜索提供候选项目
			// $all_values[] = str_replace("'", '', $row['value_C']);
			if(in_array($row['id'],$uid_has_dised)){
				if(!in_array($row['id'],$sys_has_seted)){
					//xmfa表配置的项目不允许取消
					$seted_values['hasTY'][$row['id']] = $row['value_C'];
				}else{
					//未在xmfa表配置的项目允许取消
					$seted_values['hasQY'][$row['id']] = $row['value_C'];
				}
			}else if( !in_array($row['id'], $uids_has_seted['v4']) ){
				//实验室已配置，但是并没有分配给任何一个化验员的检测项目
				$other_values['notDis'][$row['id']] = $row['value_C'];
			}else{
				//未分配至当前化验员但已分配给其他化验员的项目
				$other_values['hasDis'][$row['id']] = $row['value_C'];
			}
		}
		//项目排序
		$seted_values['hasQY'] = $this->sort_values($seted_values['hasQY'],$water_type);
		$seted_values['hasTY'] = $this->sort_values($seted_values['hasTY'],$water_type);
		$other_values['notDis'] = $this->sort_values($other_values['notDis'],$water_type);
		$other_values['hasDis'] = $this->sort_values($other_values['hasDis'],$water_type);
		//所有可选择的项目转换为json数据
		$all_values_data = json_encode($all_values);
		echo eval($this->get_eval_code('hyd/jcxm/jcxm_dis_box'));
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-11
	 * 功能描述：检测项目分配保存
	*/
	public function jcxm_dis_save(){
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$uid	= $_POST['uid'];
		if( empty($uid) ){
			die(json_encode(array('error'=>'1','content'=>'用户ID数据错误，请刷新重试！')));
		}
		if( isset($_POST['is_single']) ){
			// 单项目快速修改修改
			foreach ($uid as $key => $value) {
				$arow = $DB->fetch_one_assoc("SELECT `v4` FROM `user_other` WHERE `uid` ='{$value}'");
				if( empty($arow['v4']) ){
					$vids = $_POST['vid'];
				}else{
					$vids = @implode(',',array_unique(explode(',', $arow['v4'].",".$_POST['vid'])));
				}
				$sql = "UPDATE `user_other` SET `v4`='{$vids}' WHERE `uid`='{$value}'";
				if(!$DB->query($sql)){
					echo json_encode(array('error'=>'1','content'=>'修改失败！'));
				}
			}
		}else{
			$vids = empty($_POST['vid']) ? '' : @implode(',',array_unique($_POST['vid']));
			$sql = "UPDATE `user_other` SET `v4`='{$vids}' WHERE `uid`='{$uid}'";
			if(!$DB->query($sql)){
				echo json_encode(array('error'=>'1','content'=>'修改失败！'));
			}
		}
		echo json_encode(array('error'=>'0','content'=>''));
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-12-21
	 * 功能描述：检测项目配置
	*/
	public function jcxm_set_jcxm_set(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		global $global,$trade_global,$rooturl,$current_url;
		//水样类型
		$water_type = empty($_GET['leixing']) ? $this->mr_leixing : $_GET['leixing'];
		//获取本实验室已启用的检测项目
		$sys_has_seted = $this->get_sys_has_seted($water_type,1);
		//查询出所有的项目
		$seted_values = $other_values = array();
		$other_values['未启用的项目'] = array();
		$query = $DB->query("SELECT `id`, `value_C` FROM `assay_value` WHERE 1 ORDER BY CONVERT(`value_C` USING gbk)");
		while ($row = $DB->fetch_assoc($query)) {
			if(in_array($row['id'],$sys_has_seted)){
				// 系统已启用的检测项目
				$seted_values[$row['id']] = $row['value_C'];
			}else{
				//未启用的项目
				$other_values['未启用的项目'][$row['id']] = $row['value_C'];
			}
		}
		//排序
		$seted_values = $this->sort_values($seted_values,$water_type);
		//项目模板
		$xmmb = $this->get_xmmb();
		echo eval($this->get_eval_code('hyd/jcxm/jcxm_set_jcxm_set'));
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-12-21
	 * 功能描述：检测项目配置保存
	*/
	public function jcxm_set_save(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		// 接收水样类型
		if( isset($_POST['leixing']) && intval($_POST['leixing']) ){
			$water_type = intval($_POST['leixing']);
		}else{
			die(json_encode(array('error'=>'1','content'=>'水样类型错误！')));
		}
		// 没有接收到配置项目时默认一个0，这样SQL在使用IN查询时不会出错
		if(!isset($_POST['vid']) || !is_array($_POST['vid']) || empty($_POST['vid'])){
			$_POST['vid'] = array(0);
		}
		$post_vid = implode(',', $_POST['vid']);
		// 已停用的检测项目（默认方法都被停用的项目）
		$yi_tingYong = $this->get_sys_has_seted($water_type,'0',array("`mr`='1'"));
		// 依然启用的检测项目
		$yiRan_qiYong = $this->get_sys_has_seted($water_type,'1',array("`xmid` IN({$post_vid})"));
		// 将要停用的检测项目
		$jiangY_tingY = $this->get_sys_has_seted($water_type,'1',array("`xmid` NOT IN({$post_vid})"));
		// 只处理新启用的项目
		foreach (array_diff($_POST['vid'], $yiRan_qiYong) as $key => $vid) {
			if( in_array($vid, $yi_tingYong) ){
				// 已停用的检测项目只启用默认方法
				$query = $DB->query("UPDATE `xmfa` SET `act`='1' WHERE `fzx_id`='{$fzx_id}' AND `lxid`='{$water_type}' AND `xmid`='{$vid}' AND `mr`='1'");
			}else{
				// 从未配置过的检测项目新增一条配置记录
				$query = $DB->query("INSERT INTO `xmfa` SET `fzx_id`='{$fzx_id}', `act`='1', `mr`='1', `xmid`='{$vid}', `lxid`='{$water_type}', `userid`='{$u['id']}'");
			}
			if( !$query ){
				die(json_encode(array('error'=>'1','content'=>'修改失败，请重试！')));
			}
		}
		// 停用项目
		foreach ($jiangY_tingY as $key => $vid) {
			// 之前启用的项目，现在停用
			$query = $DB->query("UPDATE `xmfa` SET `act`='0' WHERE `fzx_id`='{$fzx_id}' AND `lxid`='{$water_type}' AND `xmid`='{$vid}'");
			if( !$query ){
				die(json_encode(array('error'=>'1','content'=>'修改失败，请重试！')));
			}
		}
		echo json_encode(array('error'=>'0','content'=>''));
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-31
	 * 功能描述：获取项目模板
	*/
	private function get_xmmb(){
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$xmmb	= array();
		$sql	= $DB->query("SELECT * FROM `n_set` WHERE `fzx_id` IN ('0','{$fzx_id}') AND `module_name`='xmmb' ORDER BY `fzx_id`,`module_value4`,CONVERT(`module_value2` USING gbk)");
		while($row = $DB->fetch_assoc($sql)){
			$row['count'] = count(array_unique(explode(',', $row['module_value1'])));
			$xmmb[] = $row;
		}
		return $xmmb;
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-31
	 * 功能描述：获取水样类型（只获取大水样类型）
	*/
	private function get_leixing(){
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$leixing	= array();
		$sql	= $DB->query("SELECT * FROM `leixing` WHERE `parent_id`='0' AND `act`='1' ORDER BY `id` ASC");
		while($row = $DB->fetch_assoc($sql)){
			$leixing[$row['id']] = $row['lname'];
		}
		return $leixing;
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-31
	 * 功能描述：获取所有水样类型
	*/
	private function get_all_leixing(){
		$DB		= $this->_db;
		$leixing= array();
		$query = $DB->query("SELECT `id`,`lname` FROM `leixing` WHERE `parent_id`='0' AND `act`='1'");
		while($lx = $DB->fetch_assoc($query)){
			$leixing[$lx['id']] = $lx['lname'];
			$sql_xleixing = $DB->query("SELECT `id`,`lname` FROM `leixing` WHERE `parent_id`='{$lx['id']}' AND `act`='1'");
			while($xlx = $DB->fetch_assoc($sql_xleixing)){
				$leixing[$xlx['id']] = '----'.$xlx['lname'];
			}
		}
		return $leixing;
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-31
	 * 功能描述：获取项目默认排序
	*/
	private function get_default_value_order($water_type){
		// $DB		= $this->_db;
		// $fzx_id	= $this->fzx_id;
		// $water_type_arr = array(1=>'地表水',3=>'地下水',5=>'饮用水');
		// if(!array_key_exists($water_type, $water_type_arr)){
		// 	return array();
		// }
		// $sql = "SELECT `module_value1` AS `order` FROM `n_set` WHERE `fzx_id`='0' AND `module_namee`='xm_px' AND `module_value4`='{$water_type_arr[$water_type]}'";
		// $value_order = $DB->fetch_one_assoc($sql);
		$default_order = array(
			//地表水环境质量标准109项
			'1' => array(97,99,114,104,118,119,198,120,121,159,161,181,141,166,138,133,135,137,179,105,108,107,185,2,190,182,186,154,157,496,280,225,394,58,86,69,70,193,142,143,145,195,146,148,151,169,167,497,307,308,283,495,503,523,203,205,206,208,209,211,212,219,222,223,224,323,317,303,304,336,337,339,301,386,316,376,292,315,309,302,335,410,103,592,595,598,300,324,342,226,227,228,392,411,504,380,177,217,199,216,168,131,385,348,364,349,359,361,353,358,559,374,408,229)
		);
		if( $water_type == '1' ){
			return $default_order[1];
		}
		// if( empty($value_order) && empty($default_order[$water_type]) ){
		// 	return array();
		// }else{
		// 	return empty($value_order) ? $default_order[$water_type] : explode(',',$value_order['order']);
		// }
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-31
	 * 参数：$array 数组，键值必须是vid，$water_type 水样类型
	 * 功能描述：将提供了vid顺序的项目进行排序，无法排序的vid 放在最后面
	*/
	private function sort_values($array,$water_type='1'){
		//获取默认排序
		$xm_px = $this->get_default_value_order($water_type);
		//如果没有可提供的顺序则直接返回原数组
		if( empty($xm_px)  ){
			return $array;
		}
		$vids1 = $vids2 = array();//初始化排序后的新数组
		$vids2 = array_keys($array);//针对键值，即vid进行排序
		//排序，将可以排序的项目单独存储，并在原数组中剔除
		//将得到一个排完顺序的新数组vids1，和只剩下无法排序的项目数组vids2
		foreach ($xm_px as $vid) {
			if(in_array($vid, $vids2)){
				$vids1[] = $vid;
				$current_key = array_keys($vids2,$vid);
				unset($vids2[$current_key[0]]);
			}
		}
		$new_array = array();
		//将已经排完顺序的vids1以及无法排序的vids数组合并
		$vids2 = array_merge($vids1, $vids2);
		foreach ($vids2 as $vid) {
			$new_array[$vid] = $array[$vid];
		}
		return $new_array;
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-31
	 * 功能描述：获取所有检测依据
	*/
	public function get_all_method(){
		$DB		= $this->_db;
		$sql = "SELECT `id`,`method_number`,`method_name` FROM `assay_method` WHERE 1 ORDER BY `method_number`";
		$methods = array();
		$query = $DB->query($sql);
		while ($row=$DB->fetch_assoc($query)) {
			$methods[$row['id']] = $row['method_number'].'['.$row['method_name'].']';
		}
		return $methods;
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-31
	 * 功能描述：获取所有使用仪器
	*/
	public function get_all_yiqi(){
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$sql = "SELECT `id`,`yq_mingcheng`,`yq_xinghao`,`yq_chucangbh` FROM `yiqi` WHERE `fzx_id`='$fzx_id' ORDER BY CONVERT(`yq_mingcheng` USING gbk)";
		$yiqi = array( 0 => '--请选择仪器--' );
		$query = $DB->query($sql);
		while ($row=$DB->fetch_assoc($query)) {
			$yiqi[$row['id']] = $row['yq_mingcheng'].$row['yq_xinghao'].'['.$row['yq_chucangbh'].']';
		}
		return $yiqi;
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-31
	 * 功能描述：获取化验单模板
	*/
	public function get_all_muban(){
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$sql = "SELECT * FROM `bt_muban` WHERE 1 AND `act`='1' ORDER BY CONVERT(`table_cname` USING gbk)";
		$muban = array();
		$query = $DB->query($sql);
		while ($row=$DB->fetch_assoc($query)) {
			$muban[$row['id']] = $row['table_cname'];
		}
		return $muban;
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-31
	 * 功能描述：根据水样类型获取实验室已配置的检测项目
	*/
	public function get_seted_jcxm($water_type=0){
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$water_type = intval($water_type);
		// if( empty($water_type) ){
		// 	return array();
		// }
		$jcxm_set = implode(',', $this->get_sys_has_seted($water_type,'all'));
		$sql = "SELECT `id`,`value_C` FROM `assay_value` WHERE 1 AND `id` IN({$jcxm_set}) ORDER BY CONVERT(`value_C` USING gbk)";
		$query = $DB->query($sql);
		while ($row = $DB->fetch_assoc($query)) {
			$jcxm[$row['id']] = $row['value_C'];
		}
		return $jcxm;
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-31
	 * 功能描述：根据uid获取检测项目列表
	*/
	public function get_all_vids(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		if( $u['admin'] || $u['system_admin'] ){
			$jcxm = $this->get_seted_jcxm(intval($_GET['leixing']));
		}else{
			$jcxm = $this->get_jcxmByUid();
		}
		echo json_encode(array('error'=>'0','jcxm'=>$jcxm));
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-31
	 * 功能描述：根据uid获取检测项目列表
	*/
	public function get_jcxmByUid(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		/*$water_type = intval($_GET['leixing']);
		if(!$water_type){
			die(json_encode(array('error'=>'1','content'=>'未传递水样类型参数')));
		}*/
		$sql_where = '';
		if( intval($_GET['uid']) || (!$u['admin'] && !$u['system_admin']) ){
			$_GET['uid'] = intval($_GET['uid']) ? intval($_GET['uid']) : $u['id'];
			$vidStr = $DB->fetch_one_assoc("SELECT `v4` FROM `user_other` WHERE 1 AND `uid`='{$_GET['uid']}'");
			if( empty($vidStr['v4']) ){
				$jcxm = array();
			}else{
				// 过滤空值
				$vidStr['v4'] = implode(',', array_filter(explode(',', $vidStr['v4'])));
				$sql = "SELECT `id`,`value_C` FROM `assay_value` WHERE 1 AND `id` IN({$vidStr['v4']}) ORDER BY CONVERT(`value_C` USING gbk)";
				$query = $DB->query($sql);
				while ($row = $DB->fetch_assoc($query)) {
					$jcxm[$row['id']] = $row['value_C'];
				}
			}
		}else{
			$jcxm = $this->get_seted_jcxm($_GET['leixing']);
		}
		return $jcxm;
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-31
	 * 功能描述：获取本实验室的化验员
	*/
	public function get_fx_users(){
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$fx_users = array();
		$sql = "SELECT `id`,`userid` FROM `users` WHERE `fzx_id`='$fzx_id' AND `group`!='0' AND `group`!='测试组' AND `hua_yan`='1' ORDER BY CONVERT(`userid` USING gbk)";
		$query = $DB->query( $sql );
		while( $row = $DB->fetch_assoc($query) ){
			$fx_users[$row['id']] = $row['userid'];
		}
		return $fx_users;
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-31
	 * 功能描述：获取每个化验员已分配到的检测项目
	*/
	public function get_user_jcxm(){
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		//所有化验员
		$fx_users = $this->get_fx_users();
		$userids_str = implode(',',array_keys($fx_users));
		if(''==$userids_str){
			return array();
		}
		$vids_uids = array();
		$sql = "SELECT * FROM `user_other` WHERE `uid` IN({$userids_str})";
		$query = $DB->query( $sql );
		while ($row=$DB->fetch_assoc($query)) {
			$vids = explode(',', $row['v4']);
			foreach ($vids as $key => $vid) {
				$vids_uids[$vid][$row['uid']] = $fx_users[$row['uid']];
			}
		}
		return $vids_uids;
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-31
	 * 功能描述：获取assay_value表的所有的检测项目
	*/
	private function get_assay_value(){
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$assay_value = array();
		$query = $DB->query("SELECT `id`, `value_C` FROM `assay_value` WHERE 1 ORDER BY CONVERT(`value_C` USING gbk)");
		while ( $row = $DB->fetch_assoc($query) ) {
			$assay_value[$row['id']] = $row;
		}
		return $assay_value;
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-03-03
	 * 返回：array
	 * 功能描述：获取本实验室已配置的检测项目
	*/
	public function get_sys_has_seted($water_type='',$act='1',$other_find=''){
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$sql_where = '';
		// 水样类型
		$sql_where .= empty($water_type) ? '' : " AND `lxid`='{$water_type}'";
		// 启用状态
		$sql_where .= ( 'all' == trim($act) ) ? '' : " AND `act`='{$act}'";
		// 其他查询条件
		( is_array($other_find) && !empty($other_find) ) && ( $sql_where .= ' AND ' . implode(' AND ', $other_find) );
		$sys_has_seted = array();
		$sys_has_seted_sql = "SELECT DISTINCT `xmid` FROM `xmfa` WHERE `fzx_id`='{$fzx_id}' {$sql_where}";
		$sys_has_seted_query = $DB->query($sys_has_seted_sql);
		while ( $row = $DB->fetch_assoc($sys_has_seted_query) ) {
			$sys_has_seted[] = intval($row['xmid']);
		}
		return $sys_has_seted;
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-03-03
	 * 功能描述：获取所有已分配至化验员的检测项目
	*/
	private function get_uids_has_seted(){
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		//查询出所有已分配至化验员的检测项目
		$uids_has_seted_vids = $uids_has_seted = array();
		$uids_has_seted_sql = "SELECT `u`.`id` AS `uid`,`u`.`userid`,`o`.`v1`,`o`.`v2`,`o`.`v3`,`o`.`v4`
				FROM `users` AS `u` LEFT JOIN `user_other` AS `o` ON `u`.`id`=`o`.`uid` 
				WHERE `u`.`fzx_id`='{$fzx_id}' AND `u`.`group`!='0' AND `u`.`group`!='测试组' AND u.`hua_yan`='1' ORDER BY CONVERT(`u`.`userid` USING gbk)";
		$uids_has_seted_query = $DB->query($uids_has_seted_sql);
		while ( $row = $DB->fetch_assoc($uids_has_seted_query) ) {
			$uids_has_seted_vids['v1'][] = str_replace("'", '', $row['v1']);	//校核
			$uids_has_seted_vids['v2'][] = str_replace("'", '', $row['v2']);	//复核
			$uids_has_seted_vids['v3'][] = str_replace("'", '', $row['v3']);	//审核
			$uids_has_seted_vids['v4'][] = str_replace("'", '', $row['v4']);	//检测
		}
		$uids_has_seted['v1'] = array_unique(explode(',', implode(',', $uids_has_seted_vids['v1']) ) );
		$uids_has_seted['v2'] = array_unique(explode(',', implode(',', $uids_has_seted_vids['v2']) ) );
		$uids_has_seted['v3'] = array_unique(explode(',', implode(',', $uids_has_seted_vids['v3']) ) );
		$uids_has_seted['v4'] = array_unique(explode(',', implode(',', $uids_has_seted_vids['v4']) ) );
		return $uids_has_seted;
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-06-07
	 * 功能描述：获取select2数据
	*/
	public function get_select2_data(){
		$u		= $this->_u;
		$vid = intval($_GET['vid']);
		$field = trim($_GET['field']);
		$search = trim($_GET['search']);
		if( $u['system_admin'] || $u['admin'] ){
			$user = $this->get_fx_users();
		}else{
			$user_jcxm = $this->get_user_jcxm();
			$user = $user_jcxm[$vid];
		}
		$return_data['results'] = array();
		if( is_array($user) && !empty($user) ){
			foreach ($user as $key => $value) {
				$return_data['results'][] = array('id' => $key, 'text' => $value);
			}
		}
		echo json_encode($return_data);
	}
}