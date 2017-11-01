<?php
/**
 * 功能：公共类
 * 作者：Mr Zhou
 * 日期：2015-10-15
 * 描述：
 * */
class PublicApp extends LIMS_Base {
	/**
	 * 构造函数
	 */
	function __construct() {
		parent::__construct();
	}
	public function index(){
		#code
	}
	/** 
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-15
	 * 参数：$name
	 * 参数：$data_list
	 * 参数：$use_key
	 * 参数：$has_all
	 * 功能描述：
	*/
	static function get_select($name,$data_list=null,$use_key=false,$has_all=true){
		$suffix = '';//后缀
		$data = isset($_GET[$name]) && !empty($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) && !empty($_POST[$name]) ? $_POST[$name] : '');
		if(!is_array($data_list)){
			$data_list = array();
			switch ($name) {
				case 'year'://年份
					global $begin_year;
					$suffix = '年';
					$has_all = false;//年份不显示全部选项
					$last_year  = intval(date('Y')) + 1;
					('' === $data) && $data = date('Y');
					intval($begin_year) < 2014 && $begin_year = 2014;
					for($year = $last_year; $year >= $begin_year; $year--){
						$data_list[] = $year;
					}
					break;
				case 'month'://月份
					$suffix = '月';
					('' === $data) && $data = date('m');
					for($month = 1; $month <= 12; $month++){
						$data_list[] = ($month < 10) ? '0'.$month : $month;
					}
					break;
				
				default:
					# code...
					break;
			}
		}
		if(empty($data_list)){
			return '';
		}
		$value_key = ($use_key===false) ? 'value' : 'key';
		$select_html = '<select name="'.$name.'" class="auto_select">';
		(true === $has_all) && $select_html .= '<option value="全部">全部</option>';
		foreach($data_list as $key => $value){
			$selected = ($$value_key==$data) ? 'selected="selected"' : '';
			$select_html .= '<option '.$selected.' value="'.$$value_key.'">'.$value.$suffix.'</option>';
		}
		return $select_html . '</select>';
	}
	/** 
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-15
	 * 参数：$name
	 * 参数：$data_list
	 * 参数：$use_key
	 * 参数：$has_all
	 * 功能描述：
	*/
	static function get_enum_list($table_name,$culumn_name){
		global $DB;
		$culumn = $DB->fetch_one_assoc("SHOW COLUMNS FROM `{$table_name}` WHERE `field`='{$culumn_name}'");
		eval('$enum_list = '.str_replace('enum(','array(',$culumn['Type']).';');
		return $enum_list;
	}
	/** 
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-04-23
	 * 参数：$fzx_id
	 * 功能描述：
	*/
	static function get_fx_users($fzx_id=0,$get_fz=false){
		global $DB;
		intval($fzx_id) || $fzx_id = FZX_ID;
		$fx_users = array();
		if( true == $get_fz ){
			$sql = "SELECT `module_value1` AS `name`, `module_value2` AS `uids` FROM `n_set` WHERE `module_name`='hyy_group' AND `fzx_id`='{$fzx_id}'";
			$query = $DB->query($sql);
			while ($row = $DB->fetch_assoc($query)) {
				$fx_users[$row['name']] = '【分组】'.$row['name'];
			}
		}
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
	 * 日期：2016-04-23
	 * 参数：$fzx_id
	 * 功能描述：
	*/
	static function get_fx_items($fzx_id=0){
		global $DB;
		intval($fzx_id) || $fzx_id = FZX_ID;
		return $_SESSION['assayvalueC'];
	}
	/**
	 * 功能：自动跳转函数
	 * 作者: Mr Zhou
	 * 日期: 2015-09-16
	 * 参数1：[string] $content 页面提示内容
	 * 参数2：[string] url 跳转链接，默认会跳至之前页面
	 * 参数3：[string] $alert_style 显示内容的样式，可选info绿色（用于执行成功时） danger红色（一般用于错误，失败，警示时）
	 * 参数4：[string] $sec 页面等待几秒钟以后跳转
	 * 描述: 自动跳转 信息提示 页面
	*/
	final function reto($content='',$url='',$class='info',$sec=1){
		global $rooturl,$last_url;
		$seconds = empty($_GET['sec']) ? $sec : $_GET['sec'];
		$alert_style = empty($_GET['class']) ? $class : $_GET['class'];	//info  danger
		$auto_url = empty($url) ? (empty($_GET['autourl']) ? $last_url : $rooturl.urldecode($_GET['autourl'])) : $url;
		$reto_content = empty($content) ? (empty($_GET['content']) ? '页面跳转中' : $_GET['content']) : $content;
		die($this->temp('reto',get_defined_vars()));
	}
	// 获取项目名称
	static function get_value_C($vid){
		global $DB;
		$value = $DB->fetch_one_assoc("SELECT `value_C` FROM `assay_value` WHERE `id`='{$vid}'");
		return $value['value_C'];
	}
}