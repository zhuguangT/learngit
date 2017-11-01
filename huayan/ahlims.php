<?php
/**
 * 功能：安恒LIMS
 * 作者: Mr Zhou
 * 日期: 2015-09-29
 * 描述: 安恒LIMS化验单程序入口文件
*/
//规范编码为utf-8格式
header("Content-type:text/html;charset=utf-8");
// 入口声明
define('IN_AHLIMS', true);
//系统开始时间
define('SYS_TIME', time());
define('SYS_START_TIME', microtime());
//包含config文件
include_once '../temp/config.php';
//定义网站根目录
define('SITE_ROOT',$rootdir);
// 定义网站根路径
define('SITE_URL',$rooturl);
//AH_LIMS框架路径
define('AH_PATH', format_dir(dirname(__FILE__).'/'));
// 模块路径
!defined('APP_PATH') && define('APP_PATH', AH_PATH.'/app/');
//启动程序
AH_LIMS::startup( array(
		'default_act'	=>  'index',
		'default_app'	=>  'default',
		'base_files'	=>	array(
				AH_PATH.'lib/ahlims_base.class.php',
				AH_PATH.'lib/public.class.php'
			)
	)
);
/**
 * 功能：初始化应用程序
 * 作者: Mr Zhou
 * 日期: 2015-09-29
 * 描述: 安恒LIMS启动程序类
*/
class AH_LIMS {
	private static $_app = '';//应用类
	private static $_act = '';//操作方法
	//禁止外部实例化
	private function __construct(){}
	/**
	 * 功能：
	 * 作者: Mr Zhou
	 * 日期: 2015-09-29
	 * 描述: 安恒LIMS启动程序
	*/
	static function startup($config = array()){
		//设置时区：亚洲上海（中国北京）
		date_default_timezone_set('Asia/Shanghai');
		
        /*加载基础文件*/
        foreach ($config['base_files'] as $file){
        	is_file($file) && include_once $file;
        }
		//设置默认的操作
		$default_act	=	isset($config['default_act']) ? $config['default_act'] : 'index';
		$default_app	=	isset($config['default_app']) ? $config['default_app'] : 'hyd';
		//接收操作请求
		self::$_app		=	isset($_GET['app']) ? trim($_GET['app']) : $default_app;
		self::$_act		=	isset($_GET['act']) ? trim($_GET['act']) : $default_act;
		//获取对象名
		$app_class_name	=	ucfirst(self::$_app).'App';
		if(!class_exists($app_class_name)){
			//加载指定的类文件
			$app_file		=	strtolower(APP_PATH . self::$_app.'.app.php');
			is_file($app_file) ? include_once $app_file : exit('缺少类文件：'.$app_file);
		}
		//实例化对象
		$app			=	new $app_class_name();
		//执行动作方法
		$app->do_action(self::$_act);
		//执行析构函数结束操作
		$app->__destruct();
	}
	//析构函数
	private function __destruct(){}
}
/**
 * 自动加载类文件
 * 参数: className string
 * */
function __autoload($className){
	$file_name = strtolower(substr($className,-3) == 'App' ? substr($className,0,-3) : $className);
	if(file_exists(AH_PATH.'/lib/'.$file_name.'.app.php')){
		include_once AH_PATH.'/lib/'.$file_name.'.app.php';
	}else if(file_exists(AH_PATH.'/app/'.$file_name.'.app.php')){
		include_once AH_PATH.'/app/'.$file_name.'.app.php';
	}else if(file_exists(APP_PATH.$file_name.'.app.php')){
		include_once APP_PATH.$file_name.'.app.php';
	}else{
		exit('缺少类文件：'.$className);
	}
}
/**
 * 格式化路径
 * 参数: dirname string
 * */
function format_dir($dirname){
	// Linux系统的目录分割是"/",Windows下是"\",统一为"/"
	return str_replace(DIRECTORY_SEPARATOR, '/', $dirname);
}
?>