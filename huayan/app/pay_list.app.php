<?php
/**
 * 功能：化验任务列表
 * 作者：Mr Zhou
 * 日期：2016-04-18
 * 描述：
 */
class Pay_listApp extends LIMS_Base {
	/**
	 * 构造函数
	 */
	function __construct() {
		parent::__construct();
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-04-18
	 * 功能描述：
	*/
	public function index(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$mr_leixing = $this->mr_leixing;
		// print_rr($_COOKIE);
		global $global,$trade_global,$rooturl,$current_url;
		// 导航（前面部分在config.php中赋值）
		$trade_global['daohang'][]	= array('icon'=>'','html'=>'化验任务列表'.$_GET['tid'],'href'=>$current_url);
		// 记录下本页面的导航到 session中
		$_SESSION['daohang']['ahlims']	= $trade_global['daohang'];
		// 总中心查看分中心数据
		if( $u['is_zz'] && isset($_GET['fzx']) && intval($_GET['fzx']) ){
			if( $u['jindu_manage'] || $u['baogao'] ){
				$fzx_id	= intval($_GET['fzx']);
			}else{
				if( $fzx_id != $_GET['fzx'] ){
					goback('你没有查看分中心数据的权限。');
				}
			}
		}
		// 确定分析人员
		$users_list = PublicApp::get_fx_users($fzx_id,true);
		if( isset($_GET['uid']) ){
			$_GET['uid'] = intval($_GET['uid']);
		}else if( isset($_COOKIE['pay_list_bs_table_uid']) ){
			$_GET['uid'] = $_COOKIE['pay_list_bs_table_uid'];
		}else if( in_array($u['id'], array_keys($users_list)) ){
			$_GET['uid'] = intval($u['id']);
		}else{
			$_GET['uid'] = '全部';
		}
		// 化验单状态，增加未完成状态
		$hyd_status = PublicApp::get_enum_list('assay_pay', 'over');
		array_unshift($hyd_status, '未完成');
		$this->disp('hyd/pay_list',get_defined_vars());
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-04-18
	 * 功能描述：获取化验单列表数据
	*/
	public function pay_list($fid=0){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		// 总中心查看分中心数据
		if( $u['is_zz'] && isset($_GET['fzx']) && intval($_GET['fzx']) ){
			if( $u['jindu_manage'] || $u['baogao'] ){
				$fzx_id	= intval($_GET['fzx']);
			}
		}
		//定义筛选条件信息
		$sql_where_arr = array();
		//定义所有sql组合元素
		$sql_select = $sql_from = $sql_where = $sql_search = $sql_sort = $sql_order = $sql_limit = '';
		//SELECT查询的所有字段信息
		$sql_select	= "SELECT `ap`.*, `cy`.`cy_date`, `cy`.`jcwc_date`, `ua`.`userid` AS `userid`, `ub`.`userid` AS `userid2` ";
		//FROM的表连接信息
		$sql_from	= "FROM `assay_pay` AS `ap`
			LEFT JOIN `cy` ON `ap`.`cyd_id` = `cy`.`id`
			LEFT JOIN `users` AS `ua` ON `ua`.`id`=`ap`.`uid`
			LEFT JOIN `users` AS `ub` ON `ub`.`id`=`ap`.`uid2`
			LEFT JOIN `assay_value` AS `av` ON `av`.`id` = `ap`.`vid` ";
		$sql_where_arr[] = "WHERE 1";
		$sql_where_arr[] = "`ap`.`is_xcjc`='0'";
		// WHERE条件
		// 考虑项目分包的问题
		if( !isset($_GET['is_xmfb']) || empty($_GET['is_xmfb']) ){
			// 只查看本中心的任务
			$sql_where_arr[] = "`ap`.`fzx_id` = '{$fzx_id}'";
		}else{
			// 只查看被分包的任务
			$sql_where_arr[] = "`ap`.`fp_id` = '{$fzx_id}' AND ap.`fzx_id` != '{$fzx_id}'";
		}
		// 任务性质
		if( isset($_GET['site_type']) && intval($_GET['site_type']) ){
			$sql_where_arr[] = "`cy`.`site_type` = '{$_GET['site_type']}'";
		}
		// 采样批次
		if( isset($_GET['cyd_id']) && intval($_GET['cyd_id']) ){
			$sql_where_arr[] = "`ap`.`cyd_id` = '{$_GET['cyd_id']}'";
		}
		// 采样日期
		if( !isset($_GET['year']) || !intval($_GET['year']) ){
			$year = date('Y');
			$sql_where_arr[] = "YEAR(cy.`cy_date`) = '{$year}'";
		}else{
			$year = intval($_GET['year']);
			$sql_where_arr[] = "YEAR(cy.`cy_date`) = '{$year}'";
		}
		if( isset($_GET['year']) && intval($_GET['month']) ){
			$sql_where_arr[] = "MONTH(cy.`cy_date`) = '{$_GET['month']}'";
		}
		// 分析人员
		$get_uid = !isset($_GET['uid']) ? 0 : trim($_GET['uid']);
		if( is_numeric($get_uid) ){
			$uid = intval($_GET['uid']) ? intval($_GET['uid']) : $u['id'];
			$sql_where_arr[] = "(`ap`.`uid`='{$uid}' OR `ap`.`uid2`='{$uid}')";
		}else if( '全部' != $get_uid && !empty($get_uid) ){
			// 化验员分组信息
			$hyy_group = $DB->fetch_one_assoc("SELECT `module_value2` AS `uids` FROM `n_set` WHERE `module_name`='hyy_group' AND `fzx_id`='{$fzx_id}' AND `module_value1`='{$get_uid}'");
			if( !empty($hyy_group) ){
				$sql_where_arr[] = "( `ap`.`uid` IN ({$hyy_group['uids']}) OR `ap`.`uid2` IN ({$hyy_group['uids']}) )";
			}
		}
		// 化验项目
		if( isset($_GET['vid']) && intval($_GET['vid']) ){
			$sql_where_arr[] = "`ap`.`vid` = '{$_GET['vid']}'";
		}
		// 任务状态
		if( isset($_GET['status']) && '全部' != $_GET['status'] ){
			if( '未完成' == $_GET['status']){
				$_GET['status'] = "未开始','已开始";
			}
			$sql_where_arr[] = "`ap`.`over` IN('{$_GET['status']}')";
		}
		//定义搜索条件
		$search_str = trim($_GET['search']);
		if( !empty($search_str) ){
			//根据可能搜索的字段依次在项目名称，检测方法，检测依据，仪器名称，仪器型号，仪器编号，模板名称中进行检索
			$pay_by_id = $sql_like = array();
			if( intval($search_str) ){
				$pay_by_id = $DB->fetch_one_assoc("SELECT `id` FROM `assay_pay` WHERE `id`='{$search_str}' AND `fp_id`='{$fzx_id}'");
				if( !empty($pay_by_id) ){
					$sql_where_arr = array(" WHERE `ap`.`id` = '{$search_str}' ");
				}
			}
			// 根据化验单id或者样品编号搜索时不再考虑其他字段信息
			if( empty($pay_by_id) ){
				if( preg_match("/^[a-zA-Z]{2,}/",$search_str) || preg_match("/\d{4,}/",$search_str) ){
					// 组合搜索条件
					$sql_where = implode(' AND ', $sql_where_arr);
					// 清空sql_where条件，避免在实际查询时重复搜索条件
					$sql_where_arr = array(' WHERE 1 ');
					$search_tid_by_code = "SELECT `ap`.`id` 
												FROM `assay_pay` AS `ap` 
												LEFT JOIN `assay_order` AS `ao` ON `ao`.`tid`=`ap`.`id` 
											{$sql_where} AND `ao`.`bar_code` LIKE '%{$search_str}%'";
					$sql_like[] = "`ap`.`id` IN( {$search_tid_by_code} )";
				}else{
					$sql_like[] = "`assay_element` LIKE '%{$search_str}%'";
					$sql_like[] = "`ua`.`userid` LIKE '%{$search_str}%'";
					$sql_like[] = "`ub`.`userid` LIKE '%{$search_str}%'";
					if( preg_match("/\d{4}-\d{2}-\d{2}/",$search_str) ){
						$sql_like[] = "`cy`.`cy_date` = '{$search_str}'";
						$sql_like[] = "`cy`.`jcwc_date` = '{$search_str}'";
					}
				}
				$sql_search = ' AND (' . implode(' OR ', $sql_like) . ')';
			}
		}
		//组合WHERE条件
		$sql_where = implode(' AND ', $sql_where_arr);
		//指定递增排序还是递减排序
		$order = !isset($_GET['sort']) ? 'ASC' : strtoupper(trim($_GET['order']));
		!in_array( $order, array('ASC', 'DESC') ) && ( $order = 'ASC' );
		//指定排序列
		$sort = (isset($_GET['sort']) && !empty($_GET['sort']) ) ? trim($_GET['sort']) : 'assay_element';
		switch ($sort) {
			case 'userid':
				$sort = "CONVERT( `ua`.`userid` USING gbk ) {$order}";
				break;
			case 'userid2':
				$sort = "CONVERT( `ub`.`userid` USING gbk ) {$order}";
				break;
			case 'assay_element':
				$sort = "CONVERT( `assay_element` USING gbk ) {$order}";
				break;
			default:
				$sort = "`{$sort}` {$order}";
				break;
		}
		$sql_sort_order = "ORDER BY {$sort} , `ap`.`id` ASC ";
		//定义分页信息，必须同时传递offset和limit参数并且limit数据必须大于0
		if( isset($_GET['offset']) && intval($_GET['limit']) ){
			$sql_limit = 'LIMIT '.intval($_GET['offset']).' , '.intval($_GET['limit']);
		}
		$i = intval($_GET['offset']);
		$pay_list = 
		$pay_list_z = //主测
		$pay_list_f = //辅测
		$hub_info = array();
		//统计总行数
		$total = $DB->num_rows($DB->query("SELECT `ap`.`id` {$sql_from} {$sql_where} {$sql_search}"));
		//查询详细信息
		$query = $DB->query( $sql_select . $sql_from . $sql_where . $sql_search . $sql_sort_order . $sql_limit );
		while ($row = $DB->fetch_assoc($query)) {
			$row['xuhao'] = ++$i;
			$row['row_data'] = $this->get_row_data_by_tid($row['id']);
			// 是否已打印
			$row['printed'] = intval($row['printed']) ? true : false;
			// 是否允许删除
			$row['canDel'] = ( ( empty($row['sign_01']) && $row['fp_id'] == $fzx_id && ($u['xd_csrw'] || $u['system_admin']) ) ) ? true : false;
			// 是否允许查看
			$row['canView'] = ( '未开始' != $row['sign_01'] || $u['id'] == $row['uid'] || $u['id'] == $row['uid2'] || $u['admin'] ) ? true : false;
			// 判断是否是分包的任务
			if( $row['fzx_id'] == $row['fp_id']){
				$row['xmfb_msg'] = '';
			}else{
				if( empty($hub_info) ){
					// 查询出所有分中心站点信息
					$hub_info_query = $DB->query("SELECT `id`,`sort_name` FROM `hub_info` WHERE 1");
					while ($hub_info_row = $DB->fetch_assoc($hub_info_query)) {
						$hub_info[$hub_info_row['id']] = $hub_info_row['sort_name'];
					}
				}
				if( $fzx_id == $row['fzx_id'] ){
					$row['xmfb_msg'] = '<span class="red">已分包给<strong>'.$hub_info[$row['fp_id']].'</strong></span>';
				}else if( $fzx_id == $row['fp_id'] ){
					$row['xmfb_msg'] = '<span class="red"><strong>'.$hub_info[$row['fzx_id']].'</strong>分包任务</span>';
				}else{
					$row['xmfb_msg'] = '<span class="red"><strong>'.$hub_info[$row['fzx_id']].'</strong>分包给<strong>'.$hub_info[$row['fp_id']].'</strong></span>';
				}
			}
			if( $row['uid'] == $get_uid ){
				$pay_list_z[] = $row;
			}else{
				$pay_list_f[] = $row;
			}
		}
		$pay_list = $pay_list_z + $pay_list_f;
		if(intval($fid)){
			return $pay_list[0];
		}else{
			echo json_encode(array('total'=>$total,'rows'=>$pay_list));
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-05-10
	 * 功能描述：获取采样单编号
	*/
	public function get_cyd_bh_list(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		// 总中心查看分中心数据
		if( $u['is_zz'] && isset($_GET['fzx']) && intval($_GET['fzx']) ){
			if( $u['jindu_manage'] || $u['baogao'] ){
				$fzx_id	= intval($_GET['fzx']);
			}
		}
		// 年月
		$cy_month = '';
		$cy_year = (isset($_GET['year']) && intval($_GET['year'])) ? intval($_GET['year']) : date("Y");
		if( isset($_GET['month']) || '全部' != trim($_GET['month']) ){
			$cy_month = (isset($_GET['month']) && intval($_GET['month'])) ? intval($_GET['month']) : date("m");
			$cy_month = "AND MONTH(`cy`.`cy_date`) = '{$cy_month}'";
		}
		// 所有采样单编号
		$cyd_bh_list = array();
		$sql = "SELECT `id`,`cyd_bh`,`group_name`,`site_type`,`cy_date` FROM `cy` WHERE `status` >= '5' AND YEAR(`cy`.`cy_date`) = '{$cy_year}' {$cy_month} AND `fzx_id` = {$fzx_id} ORDER BY `cyd_bh` DESC";
		$query = $DB->query( $sql );
		while( $row = $DB->fetch_assoc( $query ) ){
			 //委托任务不显示批名
			if( '3' == $row['site_type'] ){
				$row['group_name'] = '委托任务'.$row['cy_date'];
			}
			$cyd_bh_list[$row['id']] = $row['cyd_bh'].'【'.$row['group_name'].'】';
		}
		if( !empty($cyd_bh_list) ){
			$sel_list = PublicApp::get_select('cyd_id',$cyd_bh_list,true,true);
		}else{
			$sel_list = '<select class="auto_select"><option>全部</option></select>';
		}
		if( $this->ajax_action ){
			echo $sel_list;
		}else{
			return $sel_list;
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-04-18
	 * 功能描述：通过化验单号查询化验单
	*/
	private function get_row_data_by_tid($tid){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		global $global;
		include_once INC_DIR.'/cy_func.php';
		$bar_code = array();
		$zk_info = array(
			'xcpx' => array(),
			'qckb' => array(),
			'snpx' => array(),
			'jbhs' => array(),
			'pxjb' => array(),
			'other' => array()
		);
		$total = $already = 0;
		if('bjyth'==$global['hyd']['danwei']){
			$order_by = 'RIGHT(LEFT(`bar_code`,11),4)';
		}else{
			$order_by = 'RIGHT(LEFT(`bar_code`,13),4)';
		}
		$sql = "SELECT `hy_flag`, `bar_code`, `vd0` FROM `assay_order` WHERE `tid`='{$tid}' ORDER BY {$order_by} ";
		$query = $DB->query($sql);
		while ($row = $DB->fetch_assoc($query)) {
			$total++;
			if( '' != $row['vd0'] ){
				$already++;
			}
			$hy_flag = intval($row['hy_flag']);
			if( $hy_flag >= 0 || -6 == $hy_flag ){
				$bar_code[] = $row['bar_code'];
				if( $u['xd_cy_rw'] || $u['xd_csrw'] ){
					if( '-6' == $hy_flag ){
						$zk_info['xcpx'][] = $row['bar_code'].'（现场平行）<br />';
					}else if( in_array($hy_flag, $global['qckb_flag']) ){
						$zk_info['qckb'][] = $row['bar_code'].'（全程序空白）<br />';
					}
				}
			}else if( in_array($hy_flag, array(-20, -26)) ){
				$zk_info['snpx'][] = $row['bar_code'].'（室内平行）<br />';
			}else if( in_array($hy_flag, array(-40, -46)) ){
				$zk_info['jbhs'][] = $row['bar_code'].'（加标回收）<br />';
			}else if( -66 == $hy_flag ){
				$zk_info['pxjb'][] = $row['bar_code'].'（平行加标）<br />';
			}else{
				$zk_info['other'][] = $row['bar_code'].'&nbsp;&nbsp;';
			}
		}
		$zk_bar_code = '';
		foreach ($zk_info as $key => $value) {
			$zk_bar_code .= implode('', $value);
		}
		if( empty($zk_bar_code) ){
			$short_zk_info = '';
		}else{
			$short_zk_info = '<br /><b>质控编号：</b><br /><font color="green"><b>'.$zk_bar_code.'</b></font>';
		}
		$short_barcode = '<b>样品编号：</b><br />'.str_replace('、', '<br />', get_short_barcode($bar_code));
		$short_barcode = str_replace('～', ' <span class=\'red\' style=\'font-weight:900;font-size:2rem;\'>～</span> ', $short_barcode);
		return array(
			'total' => $total,
			'already' => $already,
			'bar_code' => str_replace('"', "'", $short_barcode.$short_zk_info)
		);
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-04-25
	 * 功能描述：修改化验员
	*/
	public function modi_pay_user(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$uid = ( isset($_POST['uid']) && intval($_POST['uid']) ) ? intval($_POST['uid']) : 0;
		$uid2 = ( isset($_POST['uid2']) && intval($_POST['uid2']) ) ? intval($_POST['uid2']) : 0;
		if( !(($u['xd_csrw'] || $u['system_admin'])) && !$u['admin'] ){
			die(json_encode(array('error'=>'1','content'=>'你没有权限修改化验员！')));
		}else if( !$uid && !$uid2 ){
			die(json_encode(array('error'=>'1','content'=>'化验员数据请求错误！')));
		}else if( empty($_POST['tid']) ){
			die(json_encode(array('error'=>'1','content'=>'化验单ID请求错误！')));
		}else{
			!$uid && $uid = $uid2;
			$modi_uid = $uid2 ? 'uid2' : 'uid';
			$modi_uname = $uid2 ? 'userid2' : 'userid';
			$fx_user = $DB->fetch_one_assoc("SELECT `id`,`userid` FROM `users` WHERE `id`='{$uid}'");
			if( empty($fx_user) ){
				die(json_encode(array('error'=>'1','content'=>'待修改化验员不存在！')));
			}
			$query = false;
			$affected_rows = 0;
			$error_msg = '修改失败！';
			$hyd_id_str = implode(',', $_POST['tid']);
			$check_status = $this->check_status($hyd_id_str);
			if( !empty($check_status['canNotModi']) ){
				$error_msg = implode(',', $check_status['canNotModi']).'化验单已经签字不能再修改！';
			}else if( !empty($check_status['canModi']) ){
				$hyd_id_str = implode(',', $check_status['canModi']);
				$query = $DB->query( "UPDATE `assay_pay` SET `{$modi_uid}`='{$fx_user['id']}', `{$modi_uname}`= '{$fx_user['userid']}' WHERE `id` IN ({$hyd_id_str}) " );
				if( $query ){
					$affected_rows = $DB->affected_rows();
				}
			}
			if( $query ){
				echo json_encode(array('error'=>'0','content'=>$affected_rows));
			}else{
				echo json_encode(array('error'=>'1','content'=>$error_msg));
			}
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-04-24
	 * 功能描述：删除化验员
	*/
	public function del_pay_by_id(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		if( !$u['system_admin'] || !$u['admin'] ){
			die(json_encode(array('error'=>'1','content'=>'你没有权限删除化验单！')));
		}else if( empty($_POST['tid']) ){
			die(json_encode(array('error'=>'1','content'=>'化验单ID请求错误！')));
		}else{
			$query = false;
			$affected_rows = 0;
			$error_msg = '删除失败！';
			$hyd_id_str = implode(',', $_POST['tid']);
			$check_status = $this->check_status($hyd_id_str);
			if( !empty($check_status['canNotModi']) ){
				$error_msg = implode(',', $check_status['canNotModi']).'化验单已经签字不能再删除！';
			}else if( !empty($check_status['canModi']) ){
				foreach($check_status['canModi'] as $key =>$tid){
					$arow = $DB->fetch_one_assoc("SELECT `cyd_id` FROM `assay_pay` WHERE `id`='{$tid}' AND `fzx_id`='{$fzx_id}'");
					$DB->query("UPDATE `cy` set `hyd_count`=`hyd_count`-1 WHERE `id`='{$arow['cyd_id']}' AND `fzx_id`='{$fzx_id}'");
					$query = $DB->query("SELECT `vid`,`cid` FROM `assay_order` WHERE `tid`='$_GET[hyd_id]' ");
					while($row = $DB->fetch_assoc($query)){
						$a = $DB->fetch_one_assoc("SELECT `assay_values` FROM `cy_rec` WHERE `id`='{$row['cid']}'");
						$a = elementsToArray($a['assay_values']);
						$a = implode(',',array_diff($a,array($r['vid'])));
						$DB->query("UPDATE `cy_rec` SET `assay_values`='{$a}' WHERE `id`='{$row['cid']}'");
					}
					$DB->query("DELETE FROM `assay_order` WHERE `tid`='{$tid}'");
					$DB->query("DELETE FROM `assay_pay` WHERE `id`='{$tid}' AND `fzx_id`='{$fzx_id}'");
				}
				$query = true;
			}
			if( $query ){
				echo json_encode(array('error'=>'0','content'=>$affected_rows));
			}else{
				echo json_encode(array('error'=>'1','content'=>$error_msg));
			}
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-04-24
	 * 功能描述：检查化验单是否具体删除修改权限，已经签字的化验单不允许删除和修改
	*/
	private function check_status($hyd_id_str){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$check_status = array();
		$sql = "SELECT `id`,`sign_01`,`over` FROM `assay_pay` WHERE `id` IN ({$hyd_id_str}) AND `fzx_id`='{$fzx_id}'";
		$query = $DB->query($sql);
		while ( $row = $DB->fetch_assoc($query) ) {
			if( empty($row['sign_01']) ){
				$check_status['canModi'][] = $row['id'];
			}else{
				$check_status['canNotModi'][] = $row['id'];
			}
		}
		return $check_status;
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-04-25
	 * 功能描述：化验单列表个性化配置功能
	*/
	public function pay_list_setting(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$hyy_group = array();
		$sql = "SELECT `id`, `module_value1` AS `name`, `module_value2` AS `uids` FROM `n_set` WHERE `module_name`='hyy_group' AND `fzx_id`='{$fzx_id}'";
		$query = $DB->query($sql);
		while ($row = $DB->fetch_assoc($query)) {
			$hyy_group[] = $row;
		}
		echo json_encode(array('error'=>'0','content'=>$hyy_group));
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-04-26
	 * 功能描述：化验单列表个性化配置功能保存
	*/
	public function setting_save(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$name = trim($_POST['name']);
		$value = trim($_POST['value']);
		$set_id = trim($_POST['set_id']);
		$set_type = trim($_POST['set_type']);
		if( !$u['admin'] && !$u['system_admin'] ){
			die(json_encode(array('error'=>'2','content'=>"对不起，你没有编辑权限！")));
		}
		// 判断请求修改字段是否正确
		$name = trim($_POST['name']);
		$uids = is_array($_POST['uids']) ? implode(',', $_POST['uids']) : trim($_POST['uids']);
		if( empty($name) ){
			die(json_encode(array('error'=>'2','content'=>"分组名称不能为空！")));
		}
		// 判断请求设置类型是否正确
		if( !in_array($set_type,array('hyy_group'))){
			die(json_encode(array('error'=>'2','content'=>"参数【{$set_type}】不正确！")));
		}
		if( 'add' == $set_id ){
			$sql = "INSERT INTO `n_set` SET `fzx_id`='{$fzx_id}', `module_name`='{$set_type}', `module_value1`='{$name}', `module_value2`='{$uids}'";
		}else if( intval($set_id) ){
			$arow = $DB->fetch_one_assoc("SELECT `id` FROM `n_set` WHERE `id`='{$set_id}' AND `module_name`='{$set_type}' AND `fzx_id`='{$fzx_id}'");
			if( empty($arow) ){
				die(json_encode(array('error'=>'1','content'=>'请求修改的数据不存在，或已被删除！')));
			}else{
				$sql = "UPDATE `n_set` SET `module_value1`='{$name}', `module_value2`='{$uids}' WHERE `id`='{$arow['id']}'";
			}
		}else{
			die(json_encode(array('error'=>'1','content'=>'请求操作的数据ID不正确！')));
		}
		$search_sql = "SELECT `id` FROM `n_set` WHERE `fzx_id`='{$fzx_id}' AND `module_name`='{$set_type}' AND `module_value1`='{$name}'";
		$old_arow = $DB->fetch_one_assoc($search_sql);
		if( !empty($old_arow['id']) && $set_id != $old_arow['id'] ){
			die(json_encode(array('error'=>'1','content'=>"分组【{$name}】已存在！")));
		}
		if( !$DB->query($sql) ){
			echo json_encode(array('error'=>'1','content'=>'操作失败！'));
		}else{
			'add' == $set_id && $set_id = $DB->insert_id();
			echo json_encode(array('error'=>'0','content'=>'','set_id'=>$set_id));
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-04-26
	 * 功能描述：化验单列表个性化配置功能删除
	*/
	public function setting_del(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$set_id = trim($_POST['set_id']);
		if( !$u['admin'] && !$u['system_admin'] ){
			die(json_encode(array('error'=>'2','content'=>"对不起，你没有编辑权限！")));
		}
		$arow = $DB->fetch_one_assoc("SELECT `id` FROM `n_set` WHERE `id`='{$set_id}' AND `module_name`='hyy_group' AND `fzx_id`='{$fzx_id}'");
		if( empty($arow) ){
			die(json_encode(array('error'=>'1','content'=>'请求删除的数据不存在，或已被删除！')));
		}else{
			$sql = "DELETE FROM `n_set` WHERE `id`='{$set_id}' AND `module_name`='hyy_group' AND `fzx_id`='{$fzx_id}'";
		}
		if( !$DB->query($sql) ){
			echo json_encode(array('error'=>'1','content'=>'删除失败！'));
		}else{
			echo json_encode(array('error'=>'0','content'=>'','set_id'=>$set_id));
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-06-03
	 * 功能描述：批量合并化验单
	*/
	public function piliang_hb(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		global $global;
		$hb_data = array(
			'count' => 0,
			'data' => array(),
			'tids' => array()
		);
		$assay_pays = $assay_tids = array();
		if(!empty($_POST['ids'])){
			$ids = implode("','", $_POST['ids']);
			$check_users = "AND (`uid`='{$u['id']}' OR `uid2`='{$u['id']}')";
			if( $u['admin'] || $u['system_admin']){
				$check_users = '';
			}
			$sql = "SELECT `id`,`cyd_id`,`vid`,`fid`,`assay_element` FROM `assay_pay` WHERE `id` IN('{$ids}') AND (`sign_01`='' OR `sign_01` IS NULL ) {$check_users} AND `fp_id`='{$fzx_id}' ORDER BY `fid` ASC,`id` ASC";
			$query = $DB->query($sql);
			while ($row = $DB->fetch_assoc($query)) {
				$assay_pays[$row['id']] = $row;
				$assay_tids[$row['fid']][] = $row['id'];
			}
			foreach ($assay_tids as $fid => $row) {
				if( count($row) < 2 ){
					continue;
				}
				$tids = implode("','", $row);
				$sql_order = "UPDATE `assay_order` SET `tid`='{$row[0]}' WHERE `tid` IN('{$tids}') AND `tid` != '{$row[0]}'";
				$sql_pay = "DELETE FROM `assay_pay` WHERE `id` IN('{$tids}') AND `id` != '{$row[0]}' AND `fp_id`='{$fzx_id}'";
				$DB->query($sql_order);
				$DB->query($sql_pay);
				$hb_data['count']++;
				$hb_data['tids'] = array_merge($hb_data['tids'],$row);
				$hb_data['data'][] = "<span style='color:#000;padding-left:20px;'>[{$row[0]}]&nbsp;{$assay_pays[$row[0]]['assay_element']}<span>";
			}
			$msg = (intval($hb_data['count'])>0) ? "合并化验单{$hb_data['count']}张!" : '没有符合合并条件的化验单。';
			echo json_encode(array('error'=>'0','content'=>$msg,'data'=>$hb_data));
		}else{
			die("没有传递正确的化验单ID参数，合并失败！");
			echo json_encode(array('error'=>'1','content'=>'没有传递正确的化验单ID参数，合并失败！'));
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-06-03
	 * 功能描述：批量拆分化验单
	*/
	public function piliang_cf(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		global $global;
		$cf_data = array(
			'count' => 0,
			'data' => array(),
			'tids' => array()
		);
		$assay_pays = array();
		if(!empty($_POST['ids'])){
			$ids = implode("','", $_POST['ids']);
			$check_users = "AND (`uid`='{$u['id']}' OR `uid2`='{$u['id']}')";
			if( $u['admin'] || $u['system_admin']){
				$check_users = '';
			}
			$sql = "SELECT * FROM `assay_pay` WHERE `id` IN('{$ids}') AND (`sign_01`='' OR `sign_01` IS NULL ) {$check_users} AND `fp_id`='{$fzx_id}' ORDER BY `id` ASC";
			$query = $DB->query($sql);
			while ($row = $DB->fetch_assoc($query)) {
				$assay_pays[] = $row;
			}
			foreach ($assay_pays as $key => $row) {
				$sql = "SELECT DISTINCT `cyd_id` FROM `assay_order` WHERE `tid`='{$row['id']}' AND `cyd_id`!='{$row['cyd_id']}'";
				$query = $DB->query($sql);
				$num_rows = $DB->num_rows($query);
				if( !intval($num_rows) ){
					continue;
				}
				$cf_data['count']++;
				$cf_data['tids'][] = $row['id'];
				$cf_data['data'][] = "<span style='color:#000;padding-left:20px;'>[{$row['id']}]&nbsp;{$row['assay_element']}<span>";
				$pay_columns = $this->get_columns('assay_pay');
				unset($pay_columns[array_search('id', $pay_columns)]);
				$pay_columns = implode('`,`', $pay_columns);
				while ($cyd_row = $DB->fetch_assoc($query)) {
					$sel_old_columns = str_replace('`cyd_id`', "'{$cyd_row['cyd_id']}'", $pay_columns);
					$sql_copy_pay = "INSERT INTO `assay_pay` (`{$pay_columns}`) SELECT `{$sel_old_columns}` FROM `assay_pay` WHERE `id`='{$row['id']}' AND `fp_id`='{$fzx_id}'";
					$DB->query($sql_copy_pay);
					$new_tid = $DB->insert_id();
					$sql_up_order = "UPDATE `assay_order` SET `tid`='{$new_tid}' WHERE `tid`='{$row['id']}' AND `cyd_id`='{$cyd_row['cyd_id']}'";
					$DB->query($sql_up_order);
					$cf_data['tids'][] = $new_tid;
				}
			}
			$msg = (intval($cf_data['count'])>0) ? "拆分化验单{$cf_data['count']}张!" : '没有符合拆分条件的化验单。';
			echo json_encode(array('error'=>'0','content'=>$msg,'data'=>$cf_data));
		}else{
			die("没有传递正确的化验单ID参数，拆分失败！");
			echo json_encode(array('error'=>'1','content'=>'没有传递正确的化验单ID参数，拆分失败！'));
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-06-03
	 * 功能描述：批量载入化验单
	*/
	public function piliang_zr(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		global $global;
		$zr_data = array(
			'count' => 0,
			'data' => array(),
			'tids' => array()
		);
		if(!empty($_POST['s'])){
			$counts = 0;
			//仪器载入配置存在fid关联配置和仪器id关联配置两种，需要根据数据库字段来具体区分
			$column_name = $DB->fetch_one_assoc("SELECT `COLUMN_NAME` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA`='{$DB->dbname}' AND `TABLE_NAME`='yq_autoload_set' AND `COLUMN_NAME` IN ('fid','yq_id')");
			foreach($_POST['s'] as $tid){
				$sql_where = " AND `sign_01`='' AND `over` IN('未开始','已开始')";
				if( !$u['admin'] ){
					$sql_where .= " AND (`uid`='{$u['id']}' OR `uid2`='{$u['id']}')";
				}
				$rs = $DB->fetch_one_assoc("SELECT `ap`.`id`, `ap`.`vid`, `ap`.`fid`, `x`.`fangfa` FROM `assay_pay` `ap` LEFT JOIN `xmfa` `x` ON `ap`.`fid`=`x`.`id`  WHERE `ap`.`id`='{$tid}' {$sql_where}");
				if( empty($rs) ){
					continue;
				}
				if('fid'==$column_name['COLUMN_NAME']){
					$load_rs = $DB->fetch_one_assoc("SELECT `s`.`fid` FROM `yq_autoload_set` `s` LEFT JOIN `xmfa` `x` ON `s`.`fid`=`x`.`fangfa` AND `s`.`fzx_id`=`x`.`fzx_id` WHERE `x`.`fzx_id`='{$fzx_id}' AND `x`.`id`='{$rs['fid']}'");
				}else if('yq_id'==$column_name['COLUMN_NAME']){
					 $load_rs = $DB->fetch_one_assoc("SELECT `s`.`yq_id` FROM `yq_autoload_set` `s` LEFT JOIN `xmfa` `x` ON `s`.`yq_id`=`x`.`yiqi` AND `s`.`fzx_id`=`x`.`fzx_id` WHERE `x`.`fzx_id`='{$fzx_id}' AND `x`.`id`='{$rs['fid']}'");
				}else{
					$load_rs = array();
				}
				if(!empty($load_rs[$column_name['COLUMN_NAME']])){
					$_GET['tid'] = $rs['id'];
					$_GET['vid'] = $rs['vid'];
					$_GET['fid'] = $rs['fangfa'];
					include(SITE_ROOT."/autoload/loadtable.php");
					if( $count > 0 ){
						$zr_data['count']  += $count;
						$zr_data['tids'][] = $tid;
						$zr_data['data'][] = "<span style='color:#000;padding-left:20px;'>[{$tid}]&nbsp;{$_SESSION['assayvalueC'][$rs['vid']]}<span>";
					}
				}
			}
			$msg = (intval($zr_data['count'])>0) ? "自动载入数据{$zr_data['count']}个!" : '没有数据载入。';
			echo json_encode(array('error'=>'0','content'=>$msg,'data'=>$zr_data));
		}else{
			die("没有传递正确的化验单ID参数，载入失败！");
			echo json_encode(array('error'=>'1','content'=>'没有传递正确的化验单ID参数，载入失败！'));
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-31
	 * 功能描述：根据uid获取检测项目列表
	*/
	public function get_jcxmByUid($uid=0){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		!empty($uid) && ( $_GET['uid'] = $uid );
		if( intval($_GET['uid']) ){
			$_GET['uid'] = intval($_GET['uid']) ? intval($_GET['uid']) : $u['id'];
			$vidStr = $DB->fetch_one_assoc("SELECT `v4` FROM `user_other` WHERE 1 AND `uid`='{$_GET['uid']}'");
			if( empty($vidStr['v4']) ){
				$jcxm = array();
			}else{
				// 过滤空值
				$jcxm = explode(',', $vidStr['v4']);
			}
		}else{
			$jcxm_set = new JcxmApp();
			$jcxm_set->fzx_id = $fzx_id;
			$jcxm = $jcxm_set->get_sys_has_seted();
		}
		// 去重，过滤空值
		$vidStr = implode(',', array_filter(array_unique($jcxm)));
		empty( $vidStr ) && ( $vidStr = '0' );
		$sql = "SELECT `id`,`value_C` FROM `assay_value` WHERE 1 AND `id` IN({$vidStr}) ORDER BY CONVERT(`value_C` USING gbk) ASC";
		$query = $DB->query($sql);
		$jcxm = array();
		while ($row = $DB->fetch_assoc($query)) {
			if( $this->ajax_action ){
				$jcxm[] = array(
							'id' => $row['id'],
							'text' => $row['value_C']
						);
			}else{
				$jcxm[$row['id']] = $row['value_C'];
			}
		}
		if( $this->ajax_action ){
			echo json_encode(array('error'=>'0','data'=>$jcxm));
		}else{
			return $jcxm;
		}
	}
}