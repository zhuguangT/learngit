<?php
/**
 * 功能：标准曲线
 * 作者：Mr Zhou
 * 日期：2015-10-03
 * 描述：
 */
class QuxianApp extends LIMS_Base {
	public	$sc_id;
	public	$hyd_id;
	public	$sc_type;
	public	$file_path;
	/**
	 * 构造函数
	 */
	function __construct() {
		parent::__construct();
		$global	= $this->_global;
		//最大行数
		define('_MAXline_', 12);
		//模板文件路径
		$this->file_path = $global['hyd']['plan_file_path'];
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 功能描述：查看曲线表单
	*/
	public function index($sc_form=''){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$sc_id	= intval($_GET['id']);
		$arow	= $this->get_sc_data($sc_id,'type',true);
		global $global,$trade_global,$rooturl,$current_url;
		//导航
		$trade_global['js']		= array('date-time/bootstrap-datepicker.min.js');
		$trade_global['css']	= array('lims/main.css','datepicker.css');
		$trade_global['daohang']= array(
				array('icon'=>'icon-home home-icon','html'=>'首页','href'=>$rooturl.'/main.php'),
				array('icon'=>'','html'=>'曲线列表','href'=>$rooturl.'/huayan/ahlims.php?app=quxian&act=sc_list&sc_type='.$arow['type']),
				array('icon'=>'','html'=>'标准曲线','href'=>$current_url)
			);
		$sc_form = !empty($sc_form) ? $sc_form : $this->view_sc();
		if('1'==$_GET['print']){
			$trade_global = json_encode($trade_global);
			$print_html = preg_replace('/<script.*>(.*)<\/script>/isU','',$sc_form);
			echo eval($this->get_eval_code('hyd/print_hyd'));
		}else{
			$this->disp('hyd/bzqx/standard_curve',get_defined_vars());
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 功能描述：曲线列表
	*/
	public function sc_list(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		global $global,$trade_global,$rooturl,$current_url;
		/*******************************************************/
		/*if($u['is_zz']){
			$sql = "SELECT * FROM `hub_info`";
			$query = $DB->query($sql);
			$hub_info_select = '实验室名称：<select name="fzx_id">';
			while ($row=$DB->fetch_assoc($query)) {
				$select = ($row['id']==intval($_GET['fzx_id']))? 'selected' : '';
				$row['hub_name'] = str_replace('辽宁省水环境监测中心', '', $row['hub_name']);
				empty($row['hub_name']) && $row['hub_name']='辽宁省水环境监测中心';
				$hub_info_select .= '<option '.$select.' value="'.$row['id'].'">'.$row['hub_name'].'</option>';
			}
			$hub_info_select .= '</select>';
		}else*/{
			$hub_info_select = '';
		}
		/*******************************************************/
		//曲线类别，1手工法曲线2仪器法曲线
		$sc_type = (intval($_GET['sc_type'])) ? intval($_GET['sc_type']) : '1';
		$sc_list_header = ($sc_type !=1 ) ? '仪器曲线列表' : '标准曲线列表';
		//导航
		$trade_global['daohang']	= array(
				array('icon'=>'icon-home home-icon','html'=>'首页','href'=>$rooturl.'/main.php'),
				array('icon'=>'','html'=>'基础实验','href'=>$current_url),
				array('icon'=>'','html'=>$sc_list_header,'href'=>$current_url)
			);
		//率定日期已超过90天的曲线会自动变为已停用状态
		//$DB->query("UPDATE `standard_curve` SET `status`='已停用' WHERE `fzx_id`='$fzx_id' AND `status`!='已停用' AND `td31`<'".date('Y-m-d',strtotime('-90 days'))."'");
		$this->disp('hyd/bzqx/quxian_list',get_defined_vars());
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 功能描述：曲线列表JSON数据
	*/
	public function sc_json_list(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		//曲线类别，1手工法曲线2仪器法曲线
		$sc_type = (intval($_GET['sc_type'])) ? intval($_GET['sc_type']) : '1';
		//设置默认查询条件
		$_GET['vid']	= intval($_GET['vid'])		? intval($_GET['vid']) : '全部';
		$_GET['year']	= empty($_GET['year'])		? date('Y')	: trim($_GET['year']);
		$_GET['month']	= empty($_GET['month'])		? date('m')	: trim($_GET['month']);
		$_GET['status']	= empty($_GET['status'])	? '全部'	: trim($_GET['status']);
		//曲线列表数据
		$data		= array();
		//SQL条件
		$sc_sql		= array();
		$sc_sql[0]	= "`fzx_id`='$fzx_id'";
		$sc_sql[1]	= "`type`='{$sc_type}'";
		$sc_sql[2]	= "YEAR(`td31`)='{$_GET['year']}'";
		$sc_sql[3]	= ($_GET['vid']		== '全部') ? '1' : "`vid`='{$_GET['vid']}'";
		$sc_sql[4]	= ($_GET['status']	== '全部') ? '1' : "`status`='{$_GET['status']}'";
		$sc_sql[5]	= ($_GET['month']	== '全部') ? '1' : "MONTH(`td31`)='{$_GET['month']}'";
		//获取SQL查询的条件
		$sql = "SELECT sc.*,IF(`bdid`>0,CONCAT('（标准液）',`td7`),IF(`jz_id`>0,CONCAT('（自配液）',`td7`),IF(`jzbd_id`>0,CONCAT('（标定液）',`td7`),'信息错误'))) by_info,CONCAT(`td4`,'（编号：',`yq_bh`,'）') yq_info FROM `standard_curve` AS sc WHERE ".implode(' AND ',$sc_sql)." ORDER BY `td31` DESC,`id` DESC";
		$query	= $DB->query($sql);
		$xuhao	= 1;
		while($row = $DB->fetch_assoc($query)){
			$row['xuhao'] = $xuhao++;
			$row['canModi'] = $this->canModi($row);
			$data[] = $row;
		}
		echo json_encode($data);
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-15
	 * 功能描述：获取曲线关联的项目，增加$hyd_vid参数，在新建曲线的时需要包含当前化验单检测的vid
	*/
	protected function get_xm_list($sc_type,$vid=0){
		$DB = $this->_db;
		$fzx_id	= $this->fzx_id;
		$xm_list = $xm_arr = array();
		if('1'==$sc_type){
			//查询出与标准溶液关联或者与jzry表（自配溶液）关联的化验项目供新建曲线时选择
			$query_bd = $DB->query("SELECT DISTINCT `vid` FROM `bzwz_detail` AS bd LEFT JOIN `bzwz` ON `bzwz`.id=bd.`wz_id` WHERE `bzwz`.`wz_type`='标准溶液' AND `bzwz`.fzx_id='$fzx_id' AND `bzwz`.`time_limit` > curdate()");
			while ($row=$DB->fetch_assoc($query_bd)) {
				$xm_arr[] = $row['vid'];
			}
			$xm_str = empty($xm_arr) ? 0 : implode(',', $xm_arr);
			$sql_jz = "SELECT DISTINCT `vid` FROM `jzry` WHERE `fzx_id`='$fzx_id' AND `sj_yxrq` > curdate() AND `vid` NOT IN ($xm_str)";
			$query_jz = $DB->query($sql_jz);
			while ($row=$DB->fetch_assoc($query_jz)) {
				$xm_arr[] = $row['vid'];
			}
		}else if('2'==$sc_type){
			$sql = "SELECT DISTINCT `vid` FROM `standard_curve` WHERE `fzx_id`='{$fzx_id}' AND `type`='{$sc_type}'";
			$query = $DB->query($sql);
			while ($row=$DB->fetch_assoc($query)) {
				$xm_arr[] = $row['vid'];
			}
		}else{
			return array();
		}
		if(intval($vid) && !in_array($vid, $xm_arr)){
			$xm_arr[] = $vid;
		}
		foreach ($_SESSION['assayvalueC'] as $vid => $value_C) {
			if(in_array($vid, $xm_arr)){
				$xm_list[$vid] = $value_C;
			}
		}
		return $xm_list;
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 功能描述：查看曲线表单信息，在新建曲线的时候需要单独组织表单，所以直接传递$arow
	*/
	public function view_sc($arow=null){
		$u = $this->_u;
		if(!is_array($arow)){
			$DB		= $this->_db;
			$sc_id	= intval($_GET['id']);
			//获取曲线数据
			$arow = $this->get_sc_data($sc_id);
			$arow['print']		= trim($_GET['print']);
			$arow['canModi']	= $this->canModi($arow);
			$arow['canTuihui']	= $this->canTuihui($arow);
		}
		//加密令牌
		$_SESSION['token_key']['quxian'][$arow['id']] = md5(uniqid(rand()));
		$viewScFunction = 'viewScType'.$arow['type'];
		$content = $this->$viewScFunction($arow);
		if($this->ajax_action && !intval($_GET['print'])){
			echo json_encode(array('error'=>'0','html'=>$content));
		}else{
			return $content;
		}
	}
	/**
	 * 功能：查看手工曲线
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 功能描述：查看手工曲线
	*/
	protected function viewScType1($arow){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$global	= $this->_global;
		$rooturl= $this->_rooturl;
		//获取曲线单位
		$arow['opp'] = $this->get_sc_unit($arow);
		//获取曲线化验数据
		$arow['sc_line'] = $this->get_sc_line($arow['id']);
		//获取曲线方程式
		$arow['quxian'] = $this->get_sc_gongshi($arow);
		$plan = eval($this->get_eval_code('hyd/bzqx/plan_'.$arow['table_name']));
		return eval($this->get_eval_code('hyd/bzqx/standard_form'));
	}
	/**
	 * 功能：查看仪器曲线
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 功能描述：查看仪器曲线
	*/
	protected function viewScType2($arow){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$global	= $this->_global;
		$rooturl= $this->_rooturl;
		$arow['quxian'] = $this->get_sc_gongshi($arow);
		$arow['opp'] = $this->get_sc_unit($arow);
		if(!empty($arow['vid'])){
			$sql = "SELECT `id`,`pid` FROM `assay_value` WHERE `pid`='{$arow['vid']}' LIMIT 1";
			$value_C = $DB->fetch_one_assoc($sql);
		};
		// error_reporting(E_ALL);
		// ini_set("display_errors",1);
		if(!empty($value_C)){
			// 查询关联的分量曲线
			$fenLiang = array();
			$sql1 = "SELECT `v`.`id` AS `vid`, `v`.`value_C` AS `assay_element`,
			 `sc`.`id`, `sc`.`fzx_id`, `sc`.`type`, `sc`.`pid`, `sc`.`CA`, `sc`.`CB`, `sc`.`CC`, `sc`.`CR` ,`sc`.`unit` 
			 FROM `assay_value` AS `v` LEFT JOIN `standard_curve` AS `sc` ON `v`.`id`=`sc`.`vid` AND `sc`.`pid` = '{$arow['id']}'
			 WHERE `v`.`pid`='{$arow['vid']}' ORDER BY `v`.`id`";
			$sql2 = "SELECT `id` AS `vid`, `value_C` AS `assay_element` FROM `assay_value` WHERE `pid`='{$arow['vid']}' ORDER BY `id`";
			$sql = empty($arow['id']) ? $sql2 : $sql1;
			$query = $DB->query($sql);
			while($row=$DB->fetch_assoc($query)){
				$row['guanLian'] = '<a class="guanlian_quxian" data-vid="'.$row['vid'].'" data-valuec="'.$row['assay_element'].'" href="#">'.(!empty($row['pid'])?$row['id']:'关联').'</a>';
				$fenLiang[$row['vid']] = $row;
			}
			//总量曲线表格
			$plan = eval($this->get_eval_code('/hyd/bzqx/plan_sc_zl.html'));
			//清除化验单模板里面的js代码并且加上处理曲线的js代码
			// $plan = preg_replace ( '/<script.*>(.*)<\/script>/isU','', $plan );
			return $yq_js_code.eval($this->get_eval_code('hyd/bzqx/standard_form'));
		}
		//仪器曲线表格
		$plan = eval($this->get_eval_code('/hyd/bzqx/plan_sc_yq.html'));
		//清除化验单模板里面的js代码并且加上处理曲线的js代码
		$plan = preg_replace ( '/<script.*>(.*)<\/script>/isU','', $plan );
		//$plan = str_replace ( '$aline','', $plan );
		return $yq_js_code.eval($this->get_eval_code('hyd/bzqx/standard_form'));
	}
	/**
	 * 功能：曲线选择列表
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 功能描述：化验单曲线切换时选择列表
	*/
	public function sel_sc(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$rooturl= $this->_rooturl;
		$vid	= intval($_GET['vid']);		//项目id
		$sc_id	= intval($_GET['id']);		//曲线id
		$hyd_id	= intval($_GET['hyd_id']);	//化验单id
		$hyd_vid= intval($_GET['hyd_vid']);	//化验单vid
		//曲线类型1：手工法标准曲线2：仪器标准曲线，默认为手工法标准曲线
		$sc_type= (intval($_GET['sc_type'])) ? intval($_GET['sc_type']) : '1';
		//因为存在关联的曲线和化验单的化验项目不一致的情况，所以需要查询出曲线关联的化验项目
		if(!$sc_id){
			$arow = array();
		}else{
			$arow = $DB->fetch_one_assoc("SELECT `vid` FROM `standard_curve` WHERE `id`='{$sc_id}' AND `fzx_id`='{$fzx_id}'");
		}
		if(!$vid){
			//没有关联曲线或者曲线被删除的时候使用化验单的化验项目
			$vid = $_GET['vid'] = (!intval($arow['vid'])) ? $hyd_vid : $arow['vid'];
		}
		//获取化验项目，提供项目选择列表
		$xm_list = $this->get_xm_list($sc_type,$hyd_vid);

		$sc_lines = '';
		$date = date('Y-m-01', strtotime('-6 month'));
		$query = $DB->query("SELECT `id`,`CA`,`CB`,`CR`,`CC`,`uid`,`userid` AS `create_man`,`td31` AS `create_date`,`status`,`sign_01` FROM `standard_curve` WHERE `fzx_id`='{$fzx_id}' AND `vid`='{$vid}' AND  `td31` >= '$date' AND `type`='{$sc_type}' ORDER BY `td31` DESC,`id` DESC");
		while($row=$DB->fetch_assoc($query)){
			//是否具有修改权限
			$row['canModi'] = $this->canModi($row);
			//操作按钮，查看修改，复制，删除
			$sc_control = array('view_sc'=>'','dele_sc'=>'');
			if(!$row['canModi']){
				$view_title	= '查看曲线';		//提示title
				$view_icon	= 'icon-zoom-in';	//按钮图标
			}else{
				$view_icon	= 'icon-edit';		//按钮图标
				$view_title	= '查看或修改曲线';		//提示title
				//删除曲线
				$sc_control['dele_sc'] = ' | <a class="red icon-remove bigger-130" href="javascript:void(0);" data-app="quxian" data-act="delete_sc" data-id="'.$row['id'].'" title="删除曲线"></a>';
			}
			//查看（修改）曲线
			$sc_control['view_sc'] = '<a class="blue '.$view_icon.' bigger-130" href="javascript:void(0);" data-app="quxian" data-act="view_sc" data-id="'.$row['id'].'" title="'.$view_title.'"></a>';
			//获取曲线方程式index
			$row['quxian'] = $this->get_sc_gongshi($row);
			empty($row['CA']) && ($row['CA']='/');
			empty($row['CB']) && ($row['CB']='/');
			empty($row['CR']) && ($row['CR']='/');
			empty($row['quxian']) && ($row['quxian']='/');
			$checked = ($sc_id==$row['id']) ? 'checked="checked"':'';
			$sc_lines .= '<tr>
				<td><label style="width:100%;cursor:pointer;">'.$row['id'].'
					<input style="cursor:pointer;" type="radio" name="sc_bd" '.$checked.' value="'.$row['id'].'|'.$row['CA'].'|'.$row['CB'].'|'.$row['CR'].'"  />
				</label></td>
				<td>'.$row['quxian'].'</td>
				<td>'.$row['CA'].'</td>
				<td>'.$row['CB'].'</td>
				<td>'.$row['CR'].'</td>
				<td>'.$row['create_man'].'</td>
				<td>'.$row['create_date'].'</td>
				<td>'.$row['status'].'</td>
				<td class="visible-md visible-lg hidden-sm hidden-xs action-buttons">
					'.$sc_control['view_sc'].'<!-- 查看曲线 -->
					'.$sc_control['dele_sc'].'<!-- 删除曲线 -->
				</td></tr>';
		}
		if(''==$sc_lines){
			$sc_lines = '<tr><td colspan="9" class="alert alert-danger center"><strong>没有查到数据！</strong></td></tr>';
		}
		$warning = '';
		if( $sc_id && empty($arow['vid']) ){
			$warning = '<div class="alert alert-danger">注意：本化验单关联的第【<strong>'.$sc_id.'</strong>】号曲线已被删除，请关联其他曲线！</div>';
		}
		if($vid != $hyd_vid){
			$valueC = $_SESSION['assayvalueC'];
			$warning .= '<div class="alert alert-danger">注意：您选择的化验项目【<strong>'.$valueC[$vid].'</strong>】与化验单的【<strong>'.$valueC[$hyd_vid].'</strong>】不一致！</div>';
		}
		echo '<!-- 选择曲线 -->
		<div style="width:800px;margin:0 auto;overflow:auto;">
		  <form action="#" method="get" name="form_select" class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>
					选择 <span style="font-size:16px">'.PublicApp::get_select('vid',$xm_list,true,false).'</span> 公式参数
					<button type="button" class="btn btn-primary btn-sm create_sc" data-app="quxian" data-act="create_sc">新建曲线</button>
				</h3>
			</div>
			<div class="modal-body">
			  '.$warning.'
			  <table  class="table table-bordered center" style="width:100%" align=center>
				<tr><td>记录号</td><td>回归方程</td><td>截距</td><td>斜率</td><td>r值</td><td>创建人</td><td>创建日期</td><td>状态</td><td>操作</td></tr>
				'.$sc_lines.'
			  </table>
			</div>
			<div class="modal-footer">
				<input type="hidden" name="hyd_id" value="'.$hyd_id.'" />
				<input type="hidden" name="app" value="'.$_GET['app'].'" />
				<input type="hidden" name="sc_type" value="'.$sc_type.'" />
				<a href="javascript:void(0);" class="btn btn-primary btn-sm" id="sel_qx_ok">确定</a>
				<a href="javascript:void(0);" class="btn btn-sm" data-dismiss="modal">取消</a>
			</div>
		  </form>
		</div><!-- 选择曲线 end -->';
	}
	/**
	 * 功能：分量曲线选择列表
	 * 作者：Mr Zhou
	 * 日期：2017-04-26
	 * 功能描述：总量曲线关联相关分量时选择列表
	*/
	public function  sel_flsc(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$rooturl= $this->_rooturl;
		$vid	= intval($_GET['fl_vid']);		//项目id
		$pid	= intval($_GET['pid']);		//曲线id
		$valueC	= trim($_GET['valueC']);	//项目名称
		$sc_type= '2';
		$sc_lines = '';
		$date = date('Y-m-01', strtotime('-6 month'));
		$query = $DB->query("SELECT `id`,`pid`,`CA`,`CB`,`CR`,`CC`,`uid`,`userid` AS `create_man`,`td31` AS `create_date`,`status`,`sign_01` FROM `standard_curve` WHERE `fzx_id`='{$fzx_id}' AND `vid`='{$vid}' AND  `td31` >= '$date' AND `type`='{$sc_type}' ORDER BY `td31` DESC,`id` DESC");
		while($row=$DB->fetch_assoc($query)){
			//是否具有修改权限
			$row['canModi'] = $this->canModi($row);
			//操作按钮，查看修改，复制，删除
			$sc_control = array('view_sc'=>'','dele_sc'=>'');
			if(!$row['canModi']){
				$view_title	= '查看曲线';		//提示title
				$view_icon	= 'icon-zoom-in';	//按钮图标
			}else{
				$view_icon	= 'icon-edit';		//按钮图标
				$view_title	= '查看或修改曲线';		//提示title
				//删除曲线
				$sc_control['dele_sc'] = ' | <a class="red icon-remove bigger-130" href="javascript:void(0);" data-app="quxian" data-act="delete_sc" data-id="'.$row['id'].'" title="删除曲线"></a>';
			}
			//查看（修改）曲线
			$sc_control['view_sc'] = '<a class="blue '.$view_icon.' bigger-130" href="javascript:void(0);" data-app="quxian" data-act="view_sc" data-id="'.$row['id'].'" title="'.$view_title.'"></a>';
			//获取曲线方程式index
			$row['quxian'] = $this->get_sc_gongshi($row);
			$checked = ($pid==$row['pid']) ? 'checked="checked"':'';
			$sc_lines .= '<tr>
				<td><label style="width:100%;cursor:pointer;">'.$row['id'].'
					<input style="cursor:pointer;" type="radio" name="sc_bd" '.$checked.' value="'.$row['id'].'"  />
				</label></td>
				<td>'.$row['quxian'].'</td>
				<td>'.$row['CA'].'</td>
				<td>'.$row['CB'].'</td>
				<td>'.$row['CR'].'</td>
				<td>'.$row['create_man'].'</td>
				<td>'.$row['create_date'].'</td>
				<td>'.$row['status'].'</td>
				<td class="visible-md visible-lg hidden-sm hidden-xs action-buttons">
					'.$sc_control['view_sc'].'<!-- 查看曲线 -->
					'.$sc_control['dele_sc'].'<!-- 删除曲线 -->
				</td></tr>';
		}
		if(''==$sc_lines){
			$sc_lines = '<tr><td colspan="9" class="alert alert-danger center"><strong>没有查到数据！</strong></td></tr>';
		}
		$warning = '';
		echo '<!-- 选择曲线 -->
		<div style="width:800px;margin:0 auto;overflow:auto;">
		  <form action="#" method="get" name="form_select" class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>
					'.$valueC.'
					<button type="button" class="btn btn-primary btn-sm create_sc" data-app="quxian" data-act="create_sc">新建曲线</button>
				</h3>
			</div>
			<div class="modal-body">
			  '.$warning.'
			  <table  class="table table-bordered center" style="width:100%" align=center>
				<tr><td>记录号</td><td>回归方程</td><td>截距</td><td>斜率</td><td>r值</td><td>创建人</td><td>创建日期</td><td>状态</td><td>操作</td></tr>
				'.$sc_lines.'
			  </table>
			</div>
			<div class="modal-footer">
				<input type="hidden" name="pid" value="'.$pid.'" />
				<input type="hidden" name="vid" value="'.$vid.'" />
				<input type="hidden" name="valueC" value="'.$valueC.'" />
				<input type="hidden" name="sc_type" value="2" />
				<input type="hidden" name="app" value="quxian" />
				<input type="hidden" name="act" value="related_to_psc" />
				<a href="javascript:void(0);" class="btn btn-primary btn-sm" id="sel_qx_ok">确定</a>
				<a href="javascript:void(0);" class="btn btn-sm" data-dismiss="modal">取消</a>
			</div>
		  </form>
		</div><!-- 选择曲线 end -->';
	}
	/**
	 * 功能：将曲线关联到化验单
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 功能描述：将曲线关联到化验单
	*/
	public function related_to_hyd(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$rooturl= $this->_rooturl;
		$hyd_id	= intval($_GET['hyd_id']);
		$sc_bd = explode('|', $_GET['sc_bd']);
		if(intval($sc_bd)){
			$query = $DB->query("UPDATE `assay_pay` SET `scid`='{$sc_bd[0]}',`bdid`=0,`CA`='{$sc_bd[1]}',`CB`='{$sc_bd[2]}' WHERE `id`='{$hyd_id}' AND `fp_id`='{$fzx_id}'");
		}
		if($query){
			die(json_encode(array('error'=>'0','content'=>'')));
		}else{
			die(json_encode(array('error'=>'1','content'=>'关联失败！')));
		}
	}
	/**
	 * 功能：将曲线关联到父级曲线
	 * 作者：Mr Zhou
	 * 日期：2017-04-26
	 * 功能描述：将曲线关联到父级曲线
	*/
	public function related_to_psc(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$rooturl= $this->_rooturl;
		$vid	= intval($_GET['vid']);
		$pid	= intval($_GET['pid']);
		$sc_bd = intval($_GET['sc_bd']);
		if(intval($sc_bd)){
			$query = $DB->query("UPDATE `standard_curve` SET `pid`='0' WHERE `pid`='{$pid}' AND `vid`='{$vid}' AND `fzx_id`='{$fzx_id}'");
			$query = $DB->query("UPDATE `standard_curve` SET `pid`='{$pid}' WHERE `id`='{$sc_bd}' AND `fzx_id`='{$fzx_id}'");
		}
		if($query){
			die(json_encode(array('error'=>'0','content'=>'')));
		}else{
			die(json_encode(array('error'=>'1','content'=>'关联失败！')));
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 功能描述：获取曲线表头信息，standard_curve表数据
	*/
	protected function get_sc_data($sc_id,$column='*',$can_null=false){
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		('*'!=$column) && $column = '`id`,`'.strtr($column,array('`'=>'',' '=>'',','=>'`,`')).'`';
		//  AND `fzx_id`='{$fzx_id}'
		$arow = $DB->fetch_one_assoc("SELECT {$column} FROM `standard_curve` WHERE `id`='{$sc_id}'");
		if(empty($arow['id']) && false===$can_null){
			if(!$this->ajax_action){
				Reto('该曲线信息不存在或已被删除！',$last_url,'danger');
			}else{
				die(json_encode(array('error'=>'1','html'=>'','content'=>'该曲线信息不存在或已被删除！')));
			}
		}
		return $arow;
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 功能描述：获取曲线数据点信息，standard_curve_record表数据
	*/
	protected function get_sc_line($sc_id,$column='*'){
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$sc_line = array();
		('*'!=$column) && $column = '`id`,`'.strtr($column,array('`'=>'',' '=>'',','=>'`,`')).'`';
		$sql = "SELECT {$column} FROM `standard_curve_record` WHERE `sc_id`='{$sc_id}' ORDER BY `vd0`+0";
		$query=$DB->query($sql);
		for($i=0;$i<_MAXline_;$i++){
			$sc_line[$i] = $DB->fetch_assoc($query);
		}
		return $sc_line;
	}
	/**
	 * 功能：获取曲线方程式
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 功能描述：根据曲线的配置获取相应的方程式
	*/
	protected function get_sc_gongshi($sc){
		global $global;
		$quxian = '';
		$sc_config = $global['hyd']['qx'];
		//曲线设置 1（默认）：y=bx+a，2：x=by+a
		if('2'==$sc_config['type']){
			$x='y';$y='x';
		}else{
			$x='x';$y='y';
		}
		if( '' !== $sc['CA'] && '' !== $sc['CB'] ){
			$cx = empty($sc['CC']) ? '' : $sc['CC'].$x.'²+';
			$quxian = $y.'='.$cx.$sc['CB'].$x.(($sc['CA']>=0)?'+'.$sc['CA']:$sc['CA']);
		}
		return $quxian;
	}
	/**
	 * 功能：获取曲线单位列表
	 * 作者：Mr Zhou
	 * 日期：2015-09-23
	 * 参数1：[int] [sc_unit]
	 * 返回值：
	 * 功能描述：
	*/
	protected function get_sc_unit($arow){
		$sc_unit = $arow['unit'];
		if(!$this->canModi($arow)){
			return $sc_unit;
		}
		//如果有编辑权限，返回曲线选择单位列表
		$sc_unit_arr = array('µg','µg/L','mg/L','mg','µg/mL','度','NTU');
		if(!empty($sc_unit) && !in_array($sc_unit, $sc_unit_arr)){
			$sc_unit_arr[] = $sc_unit;
		}
		$unit_select = '<select name="unit">';
		foreach ($sc_unit_arr as $key => $unit) {
			$selected = ($unit == $sc_unit) ? 'selected' : '';
			$unit_select .= '<option '.$selected.' value="'.$unit.'">'.$unit.'</option>';
		}
		return $unit_select.'</select>';
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 功能描述：判断当前用户是否具有修改权限
	*/
	public function canModi($arow){
		$u = $this->_u;
		// if( isset($_GET['print']) ){
		// 	return false;
		// }else{
			$a = $b = false;
			(isset($_GET['act']) && 'create_sc' == $_GET['act']) && $a = true;
			($u['id'] == $arow['uid'] && isset($arow['sign_01']) && empty($arow['sign_01'])) && $b=true;
			return ( $a || $b || $u['admin'] ) ? true : false;
		// }
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 功能描述：判断当前用户是否具有退回权限
	*/
	public function canTuihui($arow){
		$u = $this->_u;
		$fzx_id	= $this->fzx_id;
		$a = $b = $c = false;
		//校核人、复核人、审核人 在化验员 签字后 可以看到"回退按钮"
		if($fzx_id==$arow['fzx_id']&&!empty($arow['sign_01'])){
			$jh_arr = explode("','",$u['user_other']['v1']);
			$fh_arr = explode("','",$u['user_other']['v2']);
			$sh_arr = explode("','",$u['user_other']['v3']);
			//未复核并且具有校核该项目的权限
			(empty($arow['sign_03'])&&in_array($arow['vid'],$jh_arr))&&$b=true;
			//未审核并且具有复核该项目的权限
			(empty($arow['sign_04'])&&in_array($arow['vid'],$fh_arr))&&$b=true;
			//未**核并且具有审核该项目的权限
			(empty($arow['sign_05'])&&in_array($arow['vid'],$sh_arr))&&$c=true;
			return ( $a || $b || $c || $u['admin'] ) ? true : false;
		}else{
			return false;
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 功能描述：判断有没有修改记录
	*/
	public function has_th_record($sc_id){
		$shuyuan_rows = 0;
		//判断有没有修改记录
		if(intval($sc_id)){
			$hy_shuyuan		= $DB ->query("SELECT `id` FROM `hy_shuyuan` WHERE `bzqx_id` = '{$sc_id}'");
			$shuyuan_rows   = $DB->num_rows($hy_shuyuan);
		}
		return ($shuyuan_rows>0) ? true : false;
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-29
	 * 功能描述：进行修改记录的存储
	*/
	public function dataSuYuan($a='',$b='',$c='',$d='',$e=''){
		$u		= $this->_u;
		$sc_id	= intval($_POST['id']);
		$arow	= $this->get_sc_data($sc_id,'json');
		$content	= $_POST['content'];
		$bzqx_Json	= json_decode($arow['json'],true);
		if(empty($bzqx_Json['退回'])){
			$xiuGaiLiYou = '';
		}else{
			$huiTuiLiYou = end($bzqx_Json['退回']);
			$xiuGaiLiYou = $huiTuiLiYou['xiuGaiLiYou'];
		}
		//在溯源文件里面删除掉js代码
		$html = preg_replace('/<script.*>(.*)<\/script>/isU','',$content);
		$html = str_replace('\\"', '"', $html);
		parent::dataSuYuan($arow['id'],$u['userid'],$html,$xiuGaiLiYou,'bzqx_id');
	}
	/**
	 * 功能：获取标准溶液列表
	 * 作者：Mr Zhou
	 * 日期：2015-10-07
	 * 功能描述：用于创建曲线，切换标液时
	*/
	public function getBzryBox(){
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$rooturl= $this->_rooturl;
		//曲线关联的项目
		$xm_list = $this->get_xm_list(1);
		$sc_plan = array('sc_005','sc_006','sc_yq');
		$content = eval($this->get_eval_code('hyd/bzqx/get_bzry'));
		echo $content;
	}
	/**
	 * 功能：获取标准溶液列表
	 * 作者：Mr Zhou
	 * 日期：2015-10-29
	 * 功能描述：用于创建曲线，切换标液时
	*/
	public function getBzryList(){
		$vid	 = intval($_GET['vid']);
		$wz_type = intval($_GET['wz_type']);
		$wz_type = ($wz_type)? $wz_type:1;
		$getBzry = 'getBzry'.$wz_type;
		echo $this->$getBzry($vid);
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-29
	 * 功能描述：获取标准溶液（资源管理）列表
	*/
	private function getBzry1($vid){
		$DB = $this->_db;
		$fzx_id = $this->fzx_id;
		//标准溶液
		$sql = "SELECT `bzwz`.`wz_bh`,`bzwz`.`wz_name`,`bzwz`.`amount`,`bzwz`.`amount`,`bzwz`.`unit`,`bzwz`.`time_limit`,bd.`consistence`,bd.`id` FROM `bzwz_detail` AS bd LEFT JOIN `bzwz` ON `bzwz`.`id`=bd.`wz_id` WHERE `bzwz`.fzx_id='$fzx_id' AND bd.`vid`='$vid' AND `bzwz`.`wz_type`='标准溶液' AND `bzwz`.`time_limit` > curdate() ORDER BY `bzwz`.`wz_bh`, `bzwz`.`time_limit`";
		$query = $DB->query($sql);
		$bzry_lines = '';
		while($row=$DB->fetch_assoc($query)){
			$disabled = ($row['amount']>0) ? '':'disabled=""';
			$bzry_vid_checkbox	= '<label style="width:100%;cursor:pointer;"><input style="cursor:pointer;" class="ace" type="radio" name="bzry_id" class="bzry_radio" '.$disabled.' value="'.$row['id'].'|'.$row['wz_name'].'|'.$row['consistence'].'" />&nbsp;<span class="lbl">'.$row['wz_name'].'</span></label>';
			$bzry_lines	.= '<tr><td>'.$row['wz_bh'].'</td><td align="left">'.$bzry_vid_checkbox.'</td><td>'.$row['consistence'].'</td><td>'.$row['time_limit'].'</td><td>'.$row['amount'].'('.$row['unit'].')</td></tr>';
		}
		return '<tr align="center"><td width="20%">标准溶液编号</td><td width="30%">标准溶液名称</td><td width="15%">标液浓度</td><td width="15%">有效期</td><td width="20%">余量(单位)</td></tr>'.$bzry_lines;
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-29
	 * 功能描述：获取自配溶液（基础实验）列表
	*/
	private function getBzry2($vid){
		$DB = $this->_db;
		$fzx_id = $this->fzx_id;
		//自配溶液
			$sql = "SELECT `id`,`sjmc`,`sj_nd`,`pz_user`,`start_date`,`sj_yxrq`  FROM `jzry` WHERE `fzx_id`='$fzx_id' AND `vid`='$vid' AND `sj_yxrq` > curdate()";
			$query = $DB->query($sql);
			$bzry_lines = '';
			while($row=$DB->fetch_assoc($query)){
				$bzry_vid_checkbox	= '<label style="width:100%;cursor:pointer;"><input style="cursor:pointer;" class="ace" type="radio" name="bzry_id" class="bzry_radio" '.$disabled.' value="'.$row['id'].'|'.$row['sjmc'].'|'.$row['sj_nd'].'" />&nbsp;<span class="lbl">'.$row['sjmc'].'</span></label>';
				$bzry_lines	.= '<tr><td align="left">'.$bzry_vid_checkbox.'</td><td>'.$row['start_date'].'</td><td>'.$row['pz_user'].'</td><td>'.$row['sj_nd'].'</td><td>'.$row['sj_yxrq'].'</td></tr>';
			}
			return '<tr><td width="20%">自配溶液名称</td><td width="30%">配制日期</td><td width="15%">配制人</td><td width="15%">自配液浓度</td><td width="20%">有效日期</td></tr>'.$bzry_lines;
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-29
	 * 功能描述：获取标定标液（基础实验）列表
	*/
	private function getBzry3($vid){
		$DB = $this->_db;
		$fzx_id = $this->fzx_id;
		//标定标液
			$sql ="SELECT `id`,`bzry_name`,`bzry_bdrq`,`bzry_pznd`,`bzry_nongdu`,`fx_user` FROM  `jzry_bd` WHERE `fzx_id`='$fzx_id' AND `vid`='$vid' AND YEAR(`bzry_bdrq`)=YEAR(CURRENT_DATE()) AND (MONTH(CURRENT_DATE())-MONTH(`bzry_bdrq`)<=2) ORDER BY `id` DESC";
			$query = $DB->query($sql);
			$bzry_lines = '';
			while($row=$DB->fetch_assoc($query)){
				$bzry_vid_checkbox	= '<label style="width:100%;cursor:pointer;"><input style="cursor:pointer;" class="ace" type="radio" name="bzry_id" class="bzry_radio" '.$disabled.' value="'.$row['id'].'|'.$row['bzry_name'].'|'.$row['bzry_nongdu'].'" />&nbsp;<span class="lbl">'.$row['bzry_name'].'</span></label>';
				$bzry_lines	.= '<tr>
					<td align="left">'.$bzry_vid_checkbox.'</td><td>'.$row['bzry_bdrq'].'</td><td>'.$row['fx_user'].'</td><td>'.$row['bzry_pznd'].'</td><td><a href="'.$rooturl.'/jcsy/bybd/bzry_bd.php?bd_id='.$row['id'].'" target="_blank">'.$row['bzry_nongdu'].'</a></td></tr>';
			}
			return '<tr><td width="20%">标定溶液名称</td><td width="30%">标定日期</td><td width="15%">标定人</td><td width="15%">近似浓度</td><td width="20%">标定浓度</td></tr>'.$bzry_lines;
	}
	/**
	 * 功能：新建曲线
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 功能描述：
	*/
	public function create_sc(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$arow	= array();
		$fzx_id	= $this->fzx_id;
		$global	= $this->_global;
		$rooturl= $this->_rooturl;
		$sc_id	= intval($_GET['id']);//新建时需要接收id，！必须！
		//曲线类型1：手工法标准曲线2：仪器标准曲线，默认为手工法标准曲线
		$sc_type = (intval($_GET['sc_type'])) ? intval($_GET['sc_type']) : '1';
		if($sc_type == '1'){
			//新建手工法曲线时，关联显示该项目上次所做曲线的基本信息
			$arow = $DB->fetch_one_assoc("SELECT `id`,`fzx_id`,`type`,`vid`,`assay_element`,`bdid`,`jz_id`,`jzbd_id`,`unit`,`td0`,`td1`,`td2`,`td3`,`td4`,`td8`,`qx_sx`,`qx_xx` FROM `standard_curve` WHERE `fzx_id`='{$fzx_id}' AND `vid`='{$_GET['vid']}' AND `type`='{$sc_type}' ORDER BY `id` DESC LIMIT 1");
			//获取上次该项目曲线每个数据点的取样体积
			$arow['sc_line'] = $this->get_sc_line($arow['id'],'vd0');
		}else{
			//新建仪器曲线时，直接关联化验单表头数据
			$arow = array();
			$arow['type'] = 2;
		}
		//标液信息
		$arow = $this->get_bzry_info($arow);
		//曲线单位
		$arow['opp']	= $this->get_sc_unit($arow);
		//曲线id一般为0，复制化验单表头时特殊
		$arow['id'] = $sc_id; //赋值曲线新建id
		$arow['canModi'] = true; //创建曲线时必须允许修改
		$arow['td31'] = date('Y-m-d'); //默认当天为率定日期
		$arow['vid'] = intval($_GET['vid']); //关联化验项目
		$arow['assay_element']  = $_SESSION['assayvalueC'][$arow['vid']];
		empty($arow['assay_element']) && ($arow['assay_element'] = PublicApp::get_value_C($arow['vid']));
		//新建曲线必须存在模板
		if(empty($_GET['table_name'])){
			if(!$this->ajax_action){
				PublicApp:reto('请选择正确的曲线模板！',$last_url,'danger',10);
			}else{
				die(json_encode(array('error'=>'1','html'=>'','content'=>'请选择正确的曲线模板！')));
			}
		}else{
			$arow['table_name'] = trim($_GET['table_name']);
		}
		if(2==$arow['type']){
			$this->view_sc($arow);
		}else{
			$plan = eval($this->get_eval_code('hyd/bzqx/plan_'.$arow['table_name']));
			$html = eval($this->get_eval_code('hyd/bzqx/standard_form'));
			if(!$this->ajax_action){
				$this->index($html);
			}else{
				echo json_encode(array('error'=>'0','html'=>$html,'content'=>''));
			}
		}
	}
	/**
	 * 功能：赋值标液信息
	 * 作者：Mr Zhou
	 * 日期：2015-10-07
	 * 功能描述：加载传递过来的标准溶液信息
	*/
	public function get_bzry_info($arow=array()){
		$bzry_info	= explode('|',$_GET['bzry_id']);	//分割bzry_id参数
		$bzry_name	= trim($bzry_info[1]);				//标准溶液名称
		$bzry_nong	= floatval($bzry_info[2]);			//标准溶液浓度
		//bzwz_detail表id,jzry表id,jzry_bd表id
		$arow['bdid']=$arow['jz_id']=$arow['jzbd_id']='';
		$bzry_type	= array('bdid','bdid','jz_id','jzbd_id');
		$arow[$bzry_type[intval($_GET['wz_type'])]] = intval($bzry_info[0]);
		//剔除浓度，小数点和0之后就是数据单位了
		$by_unit	= trim(str_replace(array($bzry_nong,'.','0'),'',$bzry_info[2]));//标液单位
		//将单位剔除掉才是真正的带有小数精确位数的浓度
		$bzry_nong	= trim(str_replace($by_unit,'',$bzry_info[2]));//标准溶液浓度
		//表格默认的单位是µg/mL，当标液单位与之不一致时需要转换单位
		//将标液单位全部转换为小写并且将中文全角字符转换为英文半角字符，方便对比
		$by_unit	= strtolower(str_replace(array('μ'), array('µ'), $by_unit));
		switch ($by_unit) {
			case 'mg/l':	//mg/L等价于µg/mL
			case 'ug/ml':	//误将µ书写成u
			case 'µg/ml':	//µg/ml
							$bzry_nong; break;
			case 'µg/l' :	//
			case 'µg/l' :	//
							$bzry_nong *= 1000; break;
			case 'mg/ml':	//
							$bzry_nong /= 1000; break;
			case '度':
			case 'ntu':		//浑浊度单位
							$bzry_nong /= 1000; break;
			default:	$bzry_nong = ''; break;
		}
		$by_data = $this->get_sc_data($arow['id'],'id,td10,td11,td12,td13,td14,td15,td16,td17,td18',true);
		//如果浓度值和上次选择一样，则默认上次的稀释过程
		if(!empty($by_data) && $bzry_nong==$arow['td8']){
			foreach ($by_data as $key => $value) {
				$arow[$key] = $by_data[$key];
			}
		}
		$arow['td7'] = $bzry_name;	//标准溶液名称
		$arow['td8'] = $bzry_nong;	//标准溶液浓度
		return $arow;
	}
	/**
	 * 功能：删除曲线
	 * 作者：Mr Zhou
	 * 日期：2015-10-07
	 * 功能描述：
	*/
	public function delete_sc(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$sc_id	= intval($_GET['id']);
		$arow	= $this->get_sc_data($sc_id);
		//删除该标准曲线
		if( $u['admin'] || (empty($arow['sign_02']) && $arow['uid'] == $u['id'])){
			//删除数据
			$DB->query("DELETE FROM `standard_curve` WHERE `id`='$sc_id'");
			$DB->query("DELETE FROM `standard_curve_record` WHERE `sc_id`='$sc_id'");
			//Reto('删除成功！',$last_url);
			die(json_encode(array('sc_id'=>$sc_id,'error'=>'0','content'=>'')));
		}else{
			if(empty($arow['sign_02'])){
				$content = '只有该曲线的创建者【'.$arow['userid'].'】可以删除这条曲线！';
			}else{
				$content = '该曲线已校核，不再允许删除，请退回后再进行删除！';
			}
			//Reto($content,$last_url,'danger');
			die(json_encode(array('sc_id'=>$sc_id,'error'=>'1','content'=>$content)));
		}
	}
	/**
	 * 功能：曲线修改
	 * 作者：Mr Zhou
	 * 日期：2015-10-05
	 * 功能描述：
	*/
	public function modi_sc(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$global	= $this->_global;
		$rooturl= $this->_rooturl;
		$sc_id	= intval($_POST['id']);
		if($sc_id){
			$arow = $this->get_sc_data($sc_id,'uid');
			if($u['id']!=$arow['uid'] && !$u['admin']){
				die(json_encode(array('error'=>'1','content'=>'你不是该曲线的创建人,不能修改本曲线！')));
			}
		}
		//曲线类别，1手工法曲线2仪器法曲线
		$sc_type = (!intval($_POST['sc_type'])) ? 1 : intval($_POST['sc_type']);
		//保存td0-td33的原始数据
		empty($_POST['td31']) && ($_POST['td31']=date('Y-m-d'));
		$key_val_arr = array();
		for($i=0;$i<=33;$i++){
			//只修改传递过来的参数
			if(isset($_POST['td'.$i])){
				$key_val_arr[$i] = "`td$i` = '".trim($_POST['td'.$i])."'";
			}
		}
		$key_val_arr[]	= "`type`='{$sc_type}'";
		$key_val_arr[]	= "`vid`='{$_POST['vid']}'";
		$key_val_arr[]	= "`unit`='{$_POST['unit']}'";
		$key_val_arr[]	= "`bdid`='{$_POST['bdid']}'";
		$key_val_arr[]	= "`jz_id`='{$_POST['jz_id']}'";
		$key_val_arr[]	= "`yq_bh`='{$_POST['yq_bh']}'";
		$key_val_arr[]	= "`jzbd_id`='{$_POST['jzbd_id']}'";
		$key_val_arr[]	= "`table_name`='{$_POST['table_name']}'";
		$valueC=mysql_real_escape_string($_SESSION['assayvalueC'][$_POST['vid']]);
		$key_val_arr[]	= "`assay_element`='{$valueC}'";
		$key_val_arr[]	= "`qx_xx`='{$_POST['qx_xx']}'";
		$key_val_arr[]	= "`qx_sx`='{$_POST['qx_sx']}'";

		//这是保存并签字
		if('save_sign'==$_POST['submit_flag']){
			// $key_val_arr[]	= "`sign_01`='{$u['userid']}',`sign_date_01`=CURDATE(),`status`='已完成'";
			$key_val_arr[]	= "`sign_01`='{$u['userid']}',`sign_date_01` = if(`sign_date_01` != '' AND `sign_date_01` IS NOT NULL,`sign_date_01`,CURDATE()),`status`='已完成'";
		}
		$key_val_str = implode(',',$key_val_arr);
		if( 0 === $sc_id ){
			$query = $DB->query("INSERT INTO `standard_curve` SET $key_val_str, `fzx_id`='$fzx_id',`create_date`=curdate(),`uid`='{$u['id']}',`userid`='{$u['userid']}'");
			$sc_id = $DB->insert_id();
		}else{
			$query = $DB->query("UPDATE `standard_curve` SET $key_val_str WHERE `id`='$sc_id' AND `fzx_id`='$fzx_id'");
		}
		//保存曲线明细表
		$x = $y =array();//vd1(含量)$x与vd6(吸光度-空白)$y
		!is_array($_POST['vd0']) && $_POST['vd0']=array();
		foreach ($_POST['vd0'] as $key => $vd0) {
			//曲线明细记录id
			$sc_d_id = intval($_POST['sc_d_id'][$key]);
			//如果取样体积为空则该浓度点无效
			if('' === $vd0){
				if(!$sc_d_id){
					continue;
				}else{
					$DB->query("DELETE FROM `standard_curve_record` WHERE `id`='$sc_d_id'");
				}
			}
			//保存vd0-vd9的原始数据
			$key_val_arr = array();
			$x[]=$_POST['vd1'][$key];
			$y[]=$_POST['vd6'][$key];
			for ($i=0; $i <= 9; $i++) {
				$key_val_arr[] = "`vd$i` = '".trim($_POST['vd'.$i][$key])."'";
			}
			$key_val_str = implode(',', $key_val_arr);
			if(!intval($_POST['sc_d_id'][$key]) || 0 === intval($_POST['id']) ){
				$DB->query("INSERT INTO `standard_curve_record` SET `sc_id`='$sc_id',$key_val_str ");
			}else{
				$DB->query("UPDATE `standard_curve_record` SET $key_val_str WHERE `id`='$sc_d_id' AND `sc_id`='$sc_id'");
			}
		}
		//曲线计算
		$key_val_arr = array();
		$CA = $CB = $CC = $CR = $CT = '';
		$sc_config = $global['hyd']['qx'];	//曲线配置
		if($sc_type==2){
			//仪器曲线直接录入截矩和斜率
			$CA	= trim($_POST['CA']);
			$CB	= trim($_POST['CB']);
			$CC	= trim($_POST['CC']);
			$CR	= trim($_POST['CR']);
			$CT = trim($_POST['CT']);
		}else{
			//曲线数据计算
			if('2' == $sc_config['type']){
				$data = $this->standard_curve($y,$x);
			}else{
				$data = $this->standard_curve($x,$y);
			}
			if(is_array($data)){
				$jg['T'] = _round($data['T'],3);
				$round_R = _round($data['R'],4);
				$jg['R'] = ( floatval($round_R) >= 1 ) ? '0.9999' : $round_R;
				if('2' == $sc_config['type']){
					$jg['A'] = _round($data['A'],4);
					$jg['B'] = round_yxws($data['B'],4,6);
					$remarks = $_POST['td30'];
				}else{
					$jg['A'] = _round($data['A'],4);
					$jg['B'] = round_yxws($data['B'],4);
					$jg['t'] = 't='.$jg['T'].';t(0.05)='.$data['t'].';'.$data['d'];
					//将之前的t值检验结果从备注里清空并重新赋值,t值检验的完整格式是：t=-3.490;t(0.05)=2.015;t>=t(0.05) 不合格
					//$remarks = $jg['t'].trim(preg_replace("/(t=.+;t\(0.05\)=.+[合格]{1})/",'',$_POST['td30']));
				}
				$CA	= trim($jg['A']);
				$CB	= trim($jg['B']);
				$CC	= trim($jg['C']);
				$CR	= trim($jg['R']);
				$CT = trim($jg['T']);
				$key_val_arr['td30'] = "`td30`='{$remarks}'";
			}
		}
		$key_val_arr['CA']	= "`CA`='{$CA}'";
		$key_val_arr['CB']	= "`CB`='{$CB}'";
		$key_val_arr['CC']	= "`CC`='{$CC}'";
		$key_val_arr['CR']	= "`CR`='{$CR}'";
		$key_val_arr['CT']	= "`CT`='{$CT}'";
		$equation = $this->get_sc_gongshi(array( 'CA'=>$CA,'CB'=>$CB,'CC'=>$CC ));
		$key_val_arr['equation'] = "`equation`='{$equation}'";
		if(count($key_val_arr)){
			$key_val_str = implode(',',$key_val_arr);
			$query = $DB->query("UPDATE `standard_curve` SET $key_val_str WHERE `id`='$sc_id' AND `fzx_id`='$fzx_id'");
		}
		$content = ( 0 === intval($_POST['id']) ) ? '曲线添加成功！' : '';
		echo json_encode(array('sc_id'=>$sc_id,'error'=>'0','content'=>$content));
	}
	/**
	 * 功能：曲线计算
	 * 作者：Mr Zhou
	 * 日期：2014-10-22
	 * 描述：
	*/
	private function standard_curve($x,$y){
		$count_x = count($x);
		$count_y = count($x);
		if($count_x == 0 || $count_x!=$count_y){
			return '';
		}
		$avg_x = array_sum($x)/$count_x;
		$avg_y = array_sum($y)/$count_y;
		$sum1=$sum2=$sum3=$sum4=0;
		foreach($x as $key => $value){
			$sum1 += ($x[$key]-$avg_x)*($y[$key]-$avg_y);
			$sum2 += ($x[$key]-$avg_x)*($x[$key]-$avg_x);
			$sum3 += ($y[$key]-$avg_y)*($y[$key]-$avg_y);
			$sum4 += $x[$key]*$x[$key];
		}
		//求平方根
		$cr = sqrt($sum2 * $sum3);
		//中间变量2
		$So = @(sqrt(($sum3-($sum1*$sum1)/$sum2)/($count_x-2)));
		$Sa = @($So*sqrt($sum4/($count_x*$sum2)));
		//计算求得截距斜率等值
		$b = (floatval($sum2) > 0) ? ($sum1/$sum2) : '';
		$a = (floatval($b) > 0) ? ($avg_y - $b*$avg_x) : '';
		$A = (floatval($b) > 0) ? (-$a/$b) : '';
		$B = (floatval($b) > 0) ? (1/$b) : '';
		$T = (floatval($Sa) > 0) ? ($a/$Sa) : '';
		$R = (floatval($cr) > 0) ? ($sum1/$cr) : '';

		//T值数据对照表
		$_data=array(0,6.314,2.920,2.353,2.132,2.015,1.943,1.895,1.860,1.833,1.812,1.796,1.782,1.771,1.761);
		$t = $_data[$count_x-2];
		$p = ($T<$t) ? '合格' : '不合格';
		$d = ($T<$t) ? ' t&lt;t(0.05) 合格\n' : 't&gt;=t(0.05) 不合格\n';
		return array('a'=>$a,'b'=>$b,'A'=>$A,'B'=>$B,'R'=>$R,'T'=>$T,'t'=>$t,'p'=>$p,'d'=>$d);
	}
	/**
	 * 功能：R值修约
	 * 作者：Mr Zhou
	 * 日期：2014-10-22
	 * 描述：小数部分修到第一个不是9的数值为止，最多不超过$R的小数位数
	*/
	private function round_sc_r($r){
		$max_r = 0.9999;	//
		$max_ws = 4;		//保留位数设置
		$is_floor = true;	//修约时 true舍位修约，false允许进位修约
		$r = ($is_floor) ? floor($r*pow(10,$max_ws))/pow(10,$max_ws) : _round($r,$max_ws);
		return (floatval($r) >= 1) ? $max_r : $r;
	}
	/**
	 * 功能：曲线数据溯源
	 * 作者：Mr Zhou
	 * 日期：2015-10-28
	 * 功能描述：查看曲线修改记录
	*/
	public function scSuYuan(){
		$DB		= $this->_db;
		global $pdf_files;
		$sc_id	= intval($_GET['id']);
		//个性config里面配置的路径
		$pdf_files = (!isset($pdf_files) || empty($pdf_files)) ? '/home/files/' : $pdf_files;
		//获取到MD5的值作为文件夹和文件的名称
		$hymd	= $DB ->query("SELECT * FROM `hy_shuyuan` WHERE `bzqx_id` = '{$sc_id}' ORDER BY `cishu` ASC");
		$syHtml	= '';
		while($hymds=$DB->fetch_assoc($hymd)){
			$hydev		= substr($hymds['md5'],0,2);//获取到前两位作为文件夹名称
			$hyfile		= substr($hymds['md5'],2,2);//获取到2到4位作为二级文件夹名称
			$filename1	= $pdf_files.'shuyuan/'.$hydev.'/'.$hyfile.'/'.$hymds['md5'].'.gz';
			$filename	= $pdf_files.'shuyuan/'.$hydev.'/'.$hyfile.'/'.$hymds['md5'];
			if(!file_exists($filename)){
				$command2 = "gunzip -c {$filename1} > {$filename}";   //解压缩
				exec($command2,$out2,$status2);
			}
			if( !file_exists($filename)){
				continue;
			}
			$str = @file_get_contents($filename);
			if(!empty($str)){
				if($hymds['cishu']<1){
					$syHtml	.= '<fieldset style="width:27cm;padding:20px;margin:20px auto;border:2px solid #A8A8A8;"><legend><blank> '.$hymds['userid'].' 于 '.$hymds['rdate'].' 确认签字，曲线记录表如下:</blank> </legend>';
				}else{
					$syHtml	.= '<fieldset style="width:27cm;padding:20px;margin:20px auto;border:2px solid #A8A8A8;"><legend>'.$hymds['userid'].' 于 '.$hymds['rdate'].' 修改了曲线信息，修改后曲线记录表如下:</legend>';
				}
				$syHtml		.= '<div style="margin:0 auto;padding:0;width:27cm;" class="center">'.$str.'</div></fieldset><br />';
				$str	= '';
			}
		}
		$header = '标准曲线修改记录';
		$syHtml = preg_replace('/<script.*>(.*)<\/script>/isU','',$syHtml);
		echo eval($this->get_eval_code('hyd/dataSuYuan'));
	}
	// 远程计算曲线，返回曲线计算信息
	public function get_abr(){
		$x = $_POST['x'];
		$y = $_POST['y'];
		if( empty($x) || empty($y) || count($x)	!= count($y) ){
			die(json_encode(array('error'=>'1','content'=>'XY参数不合法！')));
		}
		$abr = $this->standard_curve($x,$y);
		echo json_encode(array('error'=>'0','content'=>$abr));
	}
	public function get_abrs(){
		if(!is_array($_POST['xy'])){
			die(json_encode(array('error'=>'1','content'=>'XY参数不合法！')));
		}
		$abrs = array();
		foreach ($_POST['xy'] as $key => $value) {
			$x = $value['x'];
			$y = $value['y'];
			if( empty($x) || empty($y) || count($x)	!= count($y) ){
				$abrs[$key] = array();
				//die(json_encode(array('error'=>'1','content'=>'XY参数不合法！')));
			}else{
				$abrs[$key] = $this->standard_curve($x,$y);
			}
		}
		echo json_encode(array('error'=>'0','content'=>$abrs));
	}
}
?>