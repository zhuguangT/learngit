<?php
/**
 * 功能：
 * 作者：Mr Zhou
 * 日期：2016-05-03
 * 描述：化验单
 */
class Hyd_setApp extends LIMS_Base {
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
		global $global,$trade_global,$rooturl,$current_url;
		$hyd_config = $this->hyd_config;
		$this->disp('hyd/hyd_set',get_defined_vars());
	}
}