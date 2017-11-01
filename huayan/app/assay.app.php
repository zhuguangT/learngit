<?php
/**
 * 功能：原始记录表
 * 作者：Mr Zhou
 * 日期：2015-10-29
 * 描述：
 */
class AssayApp extends LIMS_Base {
	public	$tid;
	public	$file_path;

	/**
	 * 构造函数
	 */
	function __construct() {
		parent::__construct();
		$global	= $this->_global;
		//模板文件路径
		$this->file_path = $global['hyd']['plan_file_path'];
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-29
	 * 功能描述：
	*/
	public function index(){}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-29
	 * 功能描述：进行修改记录的存储
	*/
	public function dataSuYuan($a='',$b='',$c='',$d='',$e=''){
		$u		= $this->_u;
		$tid	= intval($_POST['id']);
		$arow	= $this->get_sc_data($tid,'json');
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
		parent::dataSuYuan($arow['id'],$u['userid'],$html,$xiuGaiLiYou,'tid');
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-29
	 * 功能描述：获取化运动表头信息，assay_pay表数据
	*/
	protected function get_sc_data($tid,$column='*',$can_null=false){
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		('*'!=$column) && $column = '`id`,`'.strtr($column,array('`'=>'',' '=>'',','=>'`,`')).'`';
		$arow = $DB->fetch_one_assoc("SELECT {$column} FROM `assay_pay` WHERE `id`='{$tid}' AND `fzx_id`='{$fzx_id}'");
		if(empty($arow['id']) && false===$can_null){
			$error_msg = '该原始记录表信息不存在或已被删除！';
			if(!$this->ajax_action){
				Reto($error_msg,$last_url,'danger');
			}else{
				die(json_encode(array('error'=>'1','html'=>'','content'=>$error_msg)));
			}
		}
		return $arow;
	}
}