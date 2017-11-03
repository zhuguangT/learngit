<?php
/** 功能：系统配置文件
  * 作者：
  * 时间：
 **/

$begin_year	= 2016;
$server		= '192.168.2.208';
$db_user	= 'root';		// MySQL用户名
$db_pass	= '63508790';		// MySQL密码
$dbname		= 'zwp_lz';	// MySQL数据库名
$charset	= 'utf-8';		//字符编码
$dwname		= '国家城市供水水质监测网兰州监测站';
$dw_biaozhi = 'qdzls';//根据此字段来识别包含那个个性配置文件
$key		= '19kNTLlLtRHps';		//key
$rootdir	= __DIR__ . '/../';
$rooturl	= $_SERVER['HTTP_HOST'].'/lhz/limstest';
$rooturl	= ($_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://').$rooturl;

$set_jingdu	= '-';			//实验室经度
$set_weidu	= '-';			//实验室纬度 实验室质控样.
$qzjb       = '已复核';     //最后审核名称  统计项目完成度

$lims_system_bar        = 'lims3.0';
// 设置session.name ，防止系统冲突
// ini_set('session.name', $lims_system_bar);
//数据库报错信息将会发送至此邮箱
$technicalemail			= '';
$date_default_timezone	= 'Asia/Shanghai';	//时区
//默认开启防SQL注入 将$addslashes_deep声明为false时表示本次请求取消防SQL注入[此配置仅在Apache配置为不防SQL注入的时候有效]
$addslashes_deep		= isset($addslashes_deep) ? false : true;
/*
if(empty($_GET['year'])){
        $_GET['year']  = '2015';
}
if(empty($_GET['month'])){
	$_GET['month']	= '07';
}
if(empty($_GET['y'])){
        $_GET['y']  = '2015';
}
if(empty($_GET['m'])){
        $_GET['m']  = '07';
}
if(empty($_GET['cy_date'])){
        $_GET['cy_date']  = '2015-07';
}*/

//包含系统必须的文件
require $rootdir.'/temp/mysql.php';
require $rootdir.'/temp/function.php';
require $rootdir.'/temp/definition.php';
require $rootdir.'/temp/debug_funcs.php';
require $rootdir.'/temp/global.'.$dw_biaozhi.'.inc.php';
if(file_exists($rootdir.'/temp/dhy_hyd.'.$dw_biaozhi.'.php')){
	require $rootdir.'/temp/dhy_hyd.'.$dw_biaozhi.'.php';
}
//超级管理员可以看到错误
//if($u['admin']){error_reporting(E_ERROR | E_WARNING | E_PARSE);ini_set('display_errors', '1');}
//系统常量
//$show_zt       = '演示';//演示的时候，会不显示 调试模式等按钮
 define("FZX_ID", $_SESSION['u']['fzx_id']);//分中心id
 //#####导航的统一处理
$this_page      = basename($_SERVER['REQUEST_URI']);
$this_page      = @explode('.', $this_page)[0];
$prev_page      = basename($_SERVER['HTTP_REFERER']);//点击本页导航和页面刷新、页面条件筛选时都会出现问题
$prev_page      = @explode('.',$prev_page)[0];
if(!empty($_SESSION['daohang'][$prev_page]) && $this_page!=$prev_page){
        //一级页面点击进入二级页面时，走这里
        $trade_global['daohang']        = $_SESSION['daohang'][$prev_page];
}else if(!empty($_SESSION['daohang'][$this_page])){
        //页面中切换条件时，或者直接点击导航的本页链接时，会走这里
        array_pop($_SESSION['daohang'][$this_page]);
        $trade_global['daohang']        = $_SESSION['daohang'][$this_page];
}else{
        $trade_global['daohang']        = array(
                array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        );
}
#######导航处理结束
?>
