<?php
/**
 * 功能：LIMS基础类
 * 作者：Mr Zhou
 * 日期：2015-10-03
 * 描述：
 * */
abstract class LIMS_Base{
	protected	$_u		= null;
	protected	$_db	= null;
	protected	$fzx_id	= FZX_ID;
	protected	$_rooturl	= '';
	protected	$ajax_action = false;
	protected	$hyd_config = null;
	// 构造函数
	public function __construct(){
		global $DB,$u,$rooturl,$global;
		$this->_u	= $u;
		$this->_db	= $DB;
		$this->_global	= $global;
		$this->_rooturl	= $rooturl;
		$this->fzx_id	= FZX_ID;
		/************************/
		if($u['is_zz'] && intval($_GET['fzx_id'])){
			$this->fzx_id = intval($_GET['fzx_id']);
		}
		//是否为ajax请求
		$this->ajax_action = intval($_REQUEST['ajax']) ? true : false;
		// hyd_config
		$this->hyd_config = include AH_PATH.'lib/hyd.inc.php';
	}
	/**
	 * 指定默认显示内容,继承此类的类必须声明此方法
	 */
	abstract function index();
	/**
	 * 
	 * 功能：执行指定方法
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 参数: $action string 方法名称
	 */
	public function do_action($action){
		//判断该类中是否定义了请求的方法
		if ($action && method_exists($this, $action)){
			$this->$action();	//调用方法
		}else{
			exit('操作失误！');
		}
	}
	/**
	 * 
	 * 功能：系统提示
	 * 作者：Mr Zhou
	 * 日期：
	 * 参数：
	 */
	public function sys_msg($content){
		$msg_title = $content;
		$msg_content = $content;
		//$msg_title = $msg_content = '您访问的页面出错了！';
		if( true != $this->is_ajax ){
			die( $this->temp('error_page',get_defined_vars()) );
		}else{
			die( json_encode(array('error'=>'1','content'=>$msg_content,'data'=>array())) );
		}
	}
	/**
	 * 更新模板缓存
	 *
	 * @param $tplfile	模板原文件路径
	 * @param $compiledtplfile	编译完成后，写入文件名
	 * @return $strlen 长度
	 */
	public function template_refresh($tplfile, $compiledtplfile) {
		$str = @file_get_contents ($tplfile);
		$str = $this->template_parse ($str);
		$strlen = file_put_contents ($compiledtplfile, $str );
		chmod ($compiledtplfile, 0777);
		return $strlen;
	}
	/**
	 * 功能：读取模板
	 * 作者：Mr Zhou
	 * 日期：
	 * 参数： $file_path string 模板文件名
	 * 返回值 unknown
	 */
	final function template_compile($file_path) {
		//给模板文件增加html后缀
		substr($file_path, -5) == '.html' || $file_path .='.html';
		//模板文件绝对路径
		$_tpl = SITE_ROOT.'/template/'.$file_path;
		//缓存文件路径
		$_cache_tpl = SITE_ROOT.'/template/ahlims_cache/'.$file_path.'.tpl';
		if( file_exists($_cache_tpl)){
			//模板文件最后被修改时间
			$_tpl_update_time = intval(filemtime($_tpl));
			//缓存文件生成时间
			$_cache_tpl_create_time = intval(filectime($_cache_tpl));
			//当模板文件的的最后编辑时间在缓存文件生成之前，直接调用缓存文件
			if( $_tpl_update_time < $_cache_tpl_create_time ){
				return @file_get_contents ( $_cache_tpl );
			}else{
				//如果模板文件被编辑过，则删除重新生成
				@unlink($_cache_tpl);
			}
		}else{
			$dir_arr  = explode('/', $file_path);
			array_pop($dir_arr );
			$directory = SITE_ROOT.'/template/';
			array_unshift($dir_arr, 'ahlims_cache');//向数组插入元素
			foreach ($dir_arr as $key => $dir) {
				$directory = str_replace('//', '/', $directory.= '/'.$dir);
				if( !is_dir($directory) ) {
					@mkdir($directory, 0777, true);
				}
			}
		}
		if (!file_exists($_tpl)) {
			die('template/'.$file_path.' 不存在！') ;
		}
		$str = @file_get_contents ( $_tpl );
		$str = $this->template_parse ($str);
		$strlen = file_put_contents ($_cache_tpl, $str );
		@chmod ($_tpl, 0777);
		return $str;
	}
	/**
	 * 功能：编译模板
	 * 作者：Mr Zhou
	 * 日期：
	 * 参数： 模板文件字符串
	 * 返回值 unknown
	 */
	public function template_parse($str) {
		//解析模板
		//PHP代码
		$str = preg_replace ( "/\{php\s+(.+)\}/", "<?php \\1?>", $str );
		$str = preg_replace ( "/\{echo\s+(.+)\}/", "<?php echo \\1?>", $str );
		//if elseif else
		$str = preg_replace ( "/\{if\s+(.+?)\}/", "<?php if(\\1) { ?>", $str );
		$str = preg_replace ( "/\{else\}/", "<?php } else { ?>", $str );
		$str = preg_replace ( "/\{elseif\s+(.+?)\}/", "<?php } elseif (\\1) { ?>", $str );
		$str = preg_replace ( "/\{\/if\}/", "<?php } ?>", $str );
		//for 循环
		$str = preg_replace("/\{for\s+(.+?)\}/","<?php for(\\1) { ?>",$str);
		$str = preg_replace("/\{\/for\}/","<?php } ?>",$str);
		//++ --
		$str = preg_replace("/\{\+\+(.+?)\}/","<?php ++\\1; ?>",$str);
		$str = preg_replace("/\{\-\-(.+?)\}/","<?php ++\\1; ?>",$str);
		$str = preg_replace("/\{(.+?)\+\+\}/","<?php \\1++; ?>",$str);
		$str = preg_replace("/\{(.+?)\-\-\}/","<?php \\1--; ?>",$str);
		//foreach
		$str = preg_replace ( "/\{loop\s+(\S+)\s+(\S+)\}/", "<?php if(is_array(\\1)) foreach(\\1 AS \\2) { ?>", $str );
		$str = preg_replace ( "/\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}/", "<?php \$n=1; if(is_array(\\1)) foreach(\\1 AS \\2 => \\3) { ?>", $str );
		$str = preg_replace ( "/\{\/loop\}/", "<?php \$n++;} ?>", $str );
		//输出变量
		$str = preg_replace ( "/\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*\(([^{}]*)\))\}/", "<?php echo \\1;?>", $str );
		$str = preg_replace ( "/\{\\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*\(([^{}]*)\))\}/", "<?php echo \\1;?>", $str );
		$str = preg_replace ( "/\{(\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/", "<?php echo \\1;?>", $str );
		$str = preg_replace("/\{(\\$[a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]+)\}/es", "\$this->temp_addquote('<?php echo \\1;?>')",$str);
		$str = preg_replace ( "/\{([A-Z_\x7f-\xff][A-Z0-9_\x7f-\xff]*)\}/s", "<?php echo \\1;?>", $str );
		return $str;
	}
	/**
	 * 功能：转义 // 为 /
	 * 作者：Mr Zhou
	 * 日期：2015-10-04
	 * 参数 $var	转义的字符
	 * 返回值 转义后的字符
	 */
	final function temp_addquote($var) {
		return str_replace ( "\\\"", "\"", preg_replace ( "/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var ) );
	}
	/**
	 * 功能：获取解析后的html
	 * 作者：Mr Zhou
	 * 日期：
	 * 参数： $file_path	[String|Array] 模板路径
	 * 参数： $defined_vars	需要映射的变量
	 *		不在函数里面使用的时候
	 *		1、可以不传递此参数，默认解析$GLOBALS里面的变量
	 *		2、自定义数组变量传递，数组键值必须是变量名
	 *		在函数里面调用的时候
	 *		1、通过函数get_defined_vars()传递当前函数里面全部可用变量自定义数组变量传递
	 *			$content = $this->temp('hyd/test',get_defined_vars());
	 *		2、自定义数组变量传递，数组键值必须是变量名
	 * 返回值 解析后的html
	 */
	final function temp($file_path,$defined_vars=null){
		global $global,$trade_global,$headtitle,$mainname,$mainversion,$u,$now,$dwname;
		if(is_array($defined_vars)){
			extract($defined_vars,EXTR_SKIP);	//如果传递自定义映射数组则进行解析
		}else{
			extract($GLOBALS,EXTR_SKIP);		//如果未传递自定义映射变量则解析$GLOBALS里面的
		}
		if(is_array($trade_global)){
			$trade_global = json_encode($trade_global);
		}
		$content = '';	//生命解析后数据存储变量
		//允许同时传递多个模板文件，所以以数组遍历的形式进行解析模板
		$templates = is_array($file_path) ? $file_path : array($file_path);
		foreach ($templates as $template) {
			$content .= eval($this->get_eval_code($template));
		}
		return $content;
	}
	/**
	 * 功能：根据模板获取可执行的php代码
	 * 作者：Mr Zhou
	 * 日期：2015-10-04
	 * 参数 $file_path	[String] 模板路径
	 *		$content = eval($this->get_eval_code('hyd/test'));
	 * 返回值 可执行的php代码
	 * $this->disp('hyd/test',get_defined_vars());
	 */
	final function get_eval_code($template){
		return 'ob_start();?>'.$this->template_compile($template).'<?php $eval_code = ob_get_contents();ob_end_clean();return $eval_code;';
	}
	/**
	 * 功能：输出页面
	 * 作者：Mr Zhou
	 * 日期：2015-10-05
	 * 参数 $file_path	[String|Array] 模板路径
	 * 参数 $defined_vars	需要映射的变量
	 *		这两个参数都是为temp方法提供的
	 *		$this->disp('hyd/test',get_defined_vars());
	 * 返回值 可执行的php代码
	 * $this->disp('hyd/test',get_defined_vars());
	 */
	final function disp($file_path,$defined_vars=null){
		if(is_array($file_path)){
			$file_path[] = 'bottom';
		}else{
			$file_path = array('head',$file_path,'bottom');
		}
		die($this->temp($file_path,$defined_vars));
	}
	/**
	 * 功能：数据溯源
	 * 作者：
	 * 日期：2015-10-29
	 * 参数1：[int] [id] [数据表id]
	 * 参数2：[varchar] [userid] [签字人]
	 * 参数3：[varchar] [html] [需要存储的html记录代码]
	 * 参数4：[varchar] [liyou] [修改理由]
	 * 参数5：[varchar] [syColumn] [数据表字段名]
	 * 返回值：
	 * 描述：
	*/
	protected function dataSuYuan($id,$userid,$html,$liyou,$syColumn) {
		global $DB,$pdf_files;
		if(empty($pdf_files)){
			$pdf_files	  = '/home/files/';
		}
		$md5	= md5($html);
		$cs		= $DB->fetch_one_assoc("SELECT COUNT( `id` ) as ci FROM `hy_shuyuan` WHERE `{$syColumn}`='{$id}'");
		$row	= $DB->fetch_one_assoc("SELECT `id`,`md5`,`liyou` FROM `hy_shuyuan` WHERE `{$syColumn}`='{$id}' ORDER BY `id` DESC LIMIT 1");
		$md51	= substr($md5,0,2);//获取到前两位作为文件夹名称
		$md52	= substr($md5,2,2);//获取到2到4位作为二级文件夹名称
		if($row['md5']!=$md5 && (empty($liyou) || $row['liyou']!=$liyou)){//以前插入过数据
			$DB->query("INSERT INTO `hy_shuyuan` set `{$syColumn}`='{$id}',`userid`='{$userid}',`cishu`='{$cs['ci']}',`rdate`= now(),`liyou`='{$liyou}',`md5`='{$md5}',`html`='1'");
			//创建一级目录
			$path	= $pdf_files.'shuyuan/'.$md51;
			if(!file_exists($path)){
				mkdir($path,0777);
			}
			//创建二级目录
			$path	= $path.'/'.$md52;
			if(!file_exists($path)){
				mkdir($path,0777);
			}
			//创建文件并写入内容
			$path	= $path.'/'.$md5;
			file_put_contents($path,$html);
			//压缩文件
			$command3	= "gzip ".$pdf_files."shuyuan/".$md51."/".$md52."/".$md5;	//添加压缩文件  不保留原文件
			exec($command3,$out3,$status3);
		}
	}
	/**
	 * 功能：查询出数据表所有的字段
	 * 作者：
	 * 日期：2015-10-31
	 * 返回值：
	 * 描述：
	*/
	protected function get_columns($table_name){
		global $DB;
		if(empty($table_name)){
			return array();
		}
		$sql = "SELECT `COLUMN_NAME` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA`='{$DB->dbname}' AND `TABLE_NAME`='{$table_name}'";
		$query = $DB->query($sql);
		$columns = array();
		while ($row=$DB->fetch_assoc($query)) {
			$columns[] = $row['COLUMN_NAME'];
		}
		return $columns;
	}
	/**
	 * 功能：ajax请求数据方法
	 * 作者：
	 * 日期：2015-10-31
	 * 返回值：
	 * 描述：
	*/
	public function ajaxRequest(){
		$method = isset($_GET['method']) && !empty($_GET['method']) ? $_GET['method'] : (isset($_POST['method']) && !empty($_POST['method']) ? $_POST['method'] : '');
		if(empty($method)){
			die(json_encode(array('error'=>'1','content'=>'请求的方法不能为空！')));
		}
		die(json_encode(array('error'=>'0','content'=>$this->$method())));
	}
	// 析构函数
	function __destruct(){}
}