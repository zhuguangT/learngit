<?php
/** 功能：系统函数库
  * 作者：
  * 时间：
 **/
@session_start();
global $temps,$tables;
$mainversion="2.0";
//全局 include 文件路径
define( "__SITE_ROOT",  "$rootdir/" );
define( "__LOG_PATH",   "$rootdir/log/" );
define( "__TPL_ROOT",   "$rootdir/tpl/" );
define( "TPL_ROOT",     "$rootdir/tpl/" );
define( "INC_DIR",      "$rootdir/inc/" );
define( "__G_INC_PATH", "$rootdir/global_inc/");
define( "__G_LOG_FILE", "$rootdir/log/logs.txt");
//设置时区
date_default_timezone_set($date_default_timezone);
//
if( !ini_get( 'register_globals' ) ) {
	extract( $_GET, EXTR_SKIP );
	extract( $_POST, EXTR_SKIP );
}
//检测是否登陆
$checkLogin = (isset($checkLogin) && $checkLogin === false) ? false:true;
if(empty($_SESSION['u']['id']) && $checkLogin === true){
	nologin();
}else if(!empty($_SESSION['u']['id']) && $_SESSION['u']['ip'] != $_SERVER["REMOTE_ADDR"]  && $checkLogin === true){
    //换ip登录的情况,防止cookie劫持
    nologin();
}else if(!empty($_SESSION['u']['id']) && $_SESSION['u']['lims_system_bar']!=$lims_system_bar && $checkLogin === true){
    nologin();//同服务器多系统的情况
}
//默认开启防SQL注入 只要声明变量$addslashes_deep即表示本次请求取消防SQL注入
if(!isset($addslashes_deep)){
	$addslashes_deep = true;
}
if($addslashes_deep == true){
	$_GET       = addslashes_deep($_GET);
	$_POST      = addslashes_deep($_POST);
	//$_COOKIE    = addslashes_deep($_COOKIE);
	//$_SESSION   = addslashes_deep($_SESSION);
}

#error_reporting(E_STRICT);
//初始化数据库连接
$DB = new DB_MySQL;
$DB->servername = $server;
$DB->dbname     = $dbname;
$DB->dbpassword = $db_pass;
$DB->dbusername = $db_user;
$DB->connect();
//////////////
//变量初始化//
//////////////

/**
 * 化验单的宽度和高度
 */
$zong_biao="18cm";
$heng_biao="26cm";

define( "__E", "error.txt" );
define( "__G", "general.txt" );

$_header = "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";

$basename=basename($_SERVER["REQUEST_URI"]);
if($basename!='login.php' and $basename!='exit.php' and $basename!='main0.php' and $basename!='ok.php' and $basename !='index.php')
{
	if(strpos($basename,'.php')>0)
	{

		$goback=$REQUEST_URI = base64_encode( $_SERVER["REQUEST_URI"] );
		$_SESSION['u']['lasturl']=$_SERVER["REQUEST_URI"];
	}
}

$hour = (int)date('H');
if( $hour > 18 && $hour < 24 )      $wh =   '晚上好！';
if( $hour >=0 && $hour < 6 )        $wh =   '又开夜车啊，';
if( $hour >=6 && $hour < 11 )       $wh =   '上午好！';
if( $hour >= 11 && $hour <= 13 )    $wh =   '中午好!';
if( $hour > 13 && $hour<= 18 )      $wh =   '下午好！';

$current_time = date('Y-m-d 日' ) . c_week();

$thisweek=date('W');
$lastweek=date('W')-1;
$nextweek=date('W')+1;

$ymdstr=$today=$lasttime=date("Y-m-d");  //今天
$ymd2str=$tomorrow=date("Y-m-d",strtotime("+1 day")); //明天
$ymd3str=$yesterday=date("Y-m-d",strtotime("-1 day")); //昨天
$qian_tian=date("Y-m-d",strtotime("-2 day")); //前天

$ymstr=$this_month=date("Y-m");  //本月
$ym3str=$last_month=date("Y-m",strtotime("-1 month")); //上月
$ym2str=$last_last_month=date("Y-m",strtotime("+1 month")); //下月

$year_now=$this_year=date("Y"); //本年
$ystr=date("Y");   //年
$y2str=$ystr+1;    //下年
$y3str=$ystr-1;//上年
for($a=$begin_year;$a<=$ystr;$a++)  $select_year.="<option vale='$a'>$a</option>";
$timestr=date("H:i:s");
$AllTimeStr=$now=date("Y-m-d H:i:s");

$now = date('Y-m-d H:i:s');                       //现在
$today = date('Y-m-d');                           //本日 格式 2005-05-19
$tomorrow = date("Y-m-d",strtotime("+1 day"));    //明日
$yesterday = date("Y-m-d",strtotime("-1 day"));   //昨日

$today_of_last_week = date("Y-m-d",strtotime("-7 day")); //上周今日

$this_week = (int)date( 'W' );
$last_week = (int)date( 'W', time() - 7*24*3600 );
$next_week = (int)date( 'W', time() + 7*24*3600 );

$year_of_this_week = date( 'Y' );
$year_of_last_week = date( 'Y', time() - 7*24*3600 );
$year_of_next_week = date( 'Y', time() + 7*24*3600 );

$month_of_this_week = date( 'm' );
$month_of_last_week = date( 'm', time() - 7*24*3600 );
$month_of_next_week = date( 'm', time() + 7*24*3600 );

$this_month = date("Y-m");                        //本月
$last_month = date("Y-m",strtotime("-1 month"));  //上月
$this_year = date("Y");                           //本年
$last_year = $this_year-1;                        //去年
$last_last_year = $last_year-1;                   //前年;
$times = array("本日"=>"$today","昨日"=>"$yesterday","本周"=>"本周","上周"=>"上周","本月"=>"$this_month",
		"上月"=>"$last_month","本年"=>"$this_year","去年"=>"$last_year","前年"=>"$last_last_year","全部"=>"");

$helpkey = basename( $_SERVER["SCRIPT_NAME"] ) . basename( $_GET['action'] . $_GET['proc'] );

if ( $_SESSION[u][userid]!=''  )
{

	$u = $_SESSION['u'];//登陆后，会有一个数组$u
	$exitdisp = "<a href='$rooturl/exit.php?goback=$goback' target='_top' onclick='return confirm(\"您确认退出吗[Y/N]？\")'>[ {$u[userid]} ]=>>> 退出</a>　
		<a href='$rooturl/manual/faq.html' target='_blank'>[ 常见问答 ]</a>　
		<a href='$rooturl/manual/water.html' title='系统使用手册' target='_blank'>[ 使用手册 ]</a>　
		<a href='$rooturl/user/main$gx_main.php' title='个人任务'>[ 个人任务 ]</a>　$wh
		<a href='$rooturl/user/main$gx_main.php' title='个人任务'>[ {$u['userid']} ]</a>";
}

if($_SESSION['u']['userall']=='')
{
	$R = $DB->query("SELECT `id`, `userid` FROM `users` WHERE fzx_id='{$u['fzx_id']}' AND `group` !='0' AND `group` != '测试组' ORDER BY `userid` DESC");   /*找出用户资料*/
	while ( $r = $DB->fetch_assoc( $R ) ) {
		$_SESSION['u']['useralllist'] .= "<option value='{$r['userid']}'>{$r['userid']}</option>";
		$_SESSION['u']['userall'].="<option value='$r[id]'>$r[userid]</option>";
	}

	$R = $DB->query("SELECT userid from `users` where `group` = '0' order by `lasttime` desc");   /*找出组资料*/
	while ($r = $DB->fetch_assoc($R) )
		$_SESSION['u']['grouplist'] .= "<option value='{$r['userid']}'>{$r['userid']}</option>";


}
$R = $DB->query( "SELECT * from `settings`" );
while( $r = $DB->fetch_assoc($R) ){
	$setting[$r['field_name']] = $r['field_value'];
}
$useralllist=$_SESSION['u']['useralllist'];
$userall=$_SESSION['u']['userall'];
$grouplist=$_SESSION['u']['grouplist'];

/*
 * 读出权限信息，存放在数组 $qx 和 $qxname 中
 */
//include "$rootdir/temp/qx.php";

@header('Content-Type: text/html; charset=utf-8');//


/*
 *处理当前php的版本和修改时间信息
 */
if($cvs_ver!='')
{
//$Id: setenv.php,v 1.5 2007-03-11 08:12:33 weizhong Exp $'
$cvs_ver=strtr($cvs_ver,array('$Id: '=>'','Exp $'=>'',',v '=>' v'));
$aver=explode(' ',$cvs_ver);
$cvs_ver=array();
$cvs_ver['file']=$aver[0];
$cvs_ver[ver]=$aver[1];
$cvs_ver['date']="$aver[2] $aver[3]";
$cvs_ver['time']=strtotime($cvs_ver['date']);
$cvs_ver[modi]=$aver[4];
unset($aver);
}

////////////
//子程序区//
////////////

/*给定group数组,得到所有组权限的合集 sql*/
/*function get_qx($group){
	global $qx,$DB;
	for($i=0;$i<count($group);$i++){
		$aGroup=$DB->fetch_one_assoc("select * from `users` where `group`='0' and `userid`='{$group[$i]}'");
		for($j=0;$j<count($qx);$j++) if($gqx[$qx[$j]] || $aGroup[$qx[$j]]) $gqx[$qx[$j]]='1';
	}
	//print_rr($qx);
	$n=count($qx);
	for($i=0;$i<$n;$i++)
		$_uqx[$i]=($gqx[$qx[$i]])
			? "`$qx[$i]`='1'"
			: "`$qx[$i]`='0'";
	$_group=implode('|',$group);
	return implode(',',$_uqx).",`group`='$_group'";
}*/

function postget($name){
if($_POST[$name]!='') 
   return $_POST[$name]; 
else
   return($_GET[$name]);
}
function getpost($name){
if($_GET[$name]!='') 
   return($_GET[$name]);
else
   return $_POST[$name]; 
}

//显示一个警告框,然后返回上个页面
function back($msg=''){
	echo "<script>alert('$msg');back();</script>";
}
function goback($msg='',$goto='-1')
{
	once('goback');
	if($_GET[goback]!='') {
		$goback=base64_decode($_GET[goback]);
		unset($_GET[goback]);
		
		if($goback!='')    gotourl($goback,$msg);
	
toexit();
	}
	if($msg=='') echo "<script lanagent='javascript'>history.go($goto);</script>";
	else    echo "<script lanagent='javascript'>alert('$msg'); history.go($goto);</script>";
	toexit();
}

function backa($a,$b){
	if($a==0){
		echo $b;
		exit();
	}
	$aa=$_SERVER['HTTP_REFERER'];
	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
		<html> <head>";
	echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
		<meta http-equiv=\"refresh\" content=\"$a;URL=$aa\">  </head> <body>
		$b....$a秒后自动返回.
		</body> </html>";
	exit(0);
}//back function endl

//增强版本的echo,可以指定显示一个文本，在延时指定的秒数后转向引用页或指定url
function echoEx( $text, $seconds = 3, $url = '' ) {
	global $_header;
	if( !$seconds ) {
		echo $text.'<br />';
	}else{
		if(!$url) $url=$_SERVER['HTTP_REFERER'];//链接到当前页面的前一页面的 URL 地址。
		echo $_header .
			"<meta http-equiv=\"refresh\" content=\"$seconds;URL='$url'\">
			$text<br />....$seconds 秒后自动返回。如果没有自动返回, 请点击<a href=\"$url\">返回</a>";
		exit(0);
	}
}
//显示一个信息, 然后重定向
function msg_redirect( $msg, $url='' ) {
	echoEx( $msg, 2, $url );
}

function error_show( $text, $url = '' ) {
	echoEx( $text, 3, $url );
}

function _GetURL($isHost=''){
	global $_GET,$_SERVER;
	if ( $isHost == true ){
		$host = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['SCRIPT_NAME']}";
	}
	$sURL = '';
	foreach ( $_GET as $key => $value ){
		if ( $value != '')  $sURL .= $key.'='.$value.'&';
		else $sURL .= $key.$value . '&';
	}
	$sURL = preg_replace("/(.*)&$/","$1",$sURL);
	$sURL?$sURL = '?'.$sURL:"";
	return $host.$sURL;
}//_GetURL function endl

function loga($aa){
	global $logfile;
	$fp=fopen("$rooturl/log/log.txt",'a');
	fputs($fp,"\n[".date('Y-m-d H:i:s')."][$_SERVER[PHP_SELF]] ".$aa);
	fclose($fp);
}


function toexit($aa=0){
	global $u,$temps,$tables,$rooturl,$rootdir;
	ob_flush();
	mysql_close();
	if($aa!=0) $u['debug']=$aa;
	if($u['debug']>0){
		$rootlen=strlen($rootdir)+1;
		$progfile=substr($_SERVER["SCRIPT_FILENAME"],$rootlen,strlen($_SERVER["SCRIPT_FILENAME"])-$rootlen);
		echo '<div id="debug">\r\n';

		if(count($temps)>0){
			echo "模板:";
			while($temp=each($temps))
			{
				echo "<a href=$rooturl/template.php?action=disp&name=$temp[key].html target=template title=修改模板>$temp[key]</a>:<a href=$rooturl/blue/file_edit.php?name=$progfile&line=$temp[value] target=_blank title=修改程序>$temp[value]</a>&nbsp;";
			}
		}
		if(count($tables)>0){
			echo "<br>数据库:";
			while($temp=@each($tables))
			{
				echo "<a href=$rooturl/temp/phpadmin.php?action=disp&name=$temp[key] target=template title=显示数据库表>$temp[key]</a>:<a href=$rooturl/blue/file_edit.php?name=$progfile&line=$temp[value] target=_blank title=修改程序>$temp[value]</a>&nbsp;";
			}
		}
		echo "<pre>";
		if(count($_GET)>0){
			echo "本页得到的_GET变量有:";
			print_r($_GET);
		}
		if(count($_POST)>0){
			echo "本页得到的_POST变量有:";
			print_r($_POST);
		}
		if(count($_COOKIE)>0){
			echo "本页得到的_COOKIE变量有:";
			print_r($_COOKIE);
		}
		if(count($_SESSION)>0){
			echo "本页得到的_SESSION变量有:";
			print_r($_SESSION);
		}
		echo "</pre></div>";
	}
	exit();
}

function temp($filename,$is_cy=false){ //mission_bar.html
	global $test,$temps,$rootdir,$rooturl,$u;
	extract($GLOBALS,EXTR_SKIP);

	if(substr($filename,0,4)=='head') $u[lasturl]=$_SERVER["REQUEST_URI"];
	if(substr($filename,-5)=='.html') $filename=substr($filename,0,-5);
	$d=debug_backtrace();
	$temps[$filename]=$d[0]['line'];
	//如果传入的不是链接，就默认到template文件夹下找模板
	$muban_url	= '';
	if($is_cy){
		$muban_url	= "$rootdir/$filename.html";
		$temp_fulename	= explode("/",$filename);
		$filename	= array_pop($temp_fulename);
	}else{
		$muban_url	= "$rootdir/template/$filename.html";
	}
	if(!file_exists("$muban_url")){
		$filename.='.html';
		if(!file_exists("$muban_url")) return("模版 $muban_url 没有找到! <a href='$rooturl/template.php?name=$filename'>点击生成</a>".$d[0]['file'].':'.$d[0]['line']);
	}
	$fp=fopen("$muban_url",'r');
	$re=fgets($fp,2000);
	if(substr($re,0,4)=='<!--') $re='';
	do{
		$temp_line=fgets($fp,200000);
		if(substr($temp_line,0,4)=='<!--') $temp_line='';
		$re=$re.$temp_line;
	}while(!feof($fp));
	fclose($fp);
	$re=str_replace('"','\"',$re);
	eval('$re="'.$re.'";');
	$content .= "调用子程序:". $d[0]['file'].":". $d[0]['line'];
	if($test>0) return "<!--".$filename."开始 $cntent-->\n".$re."<!--".$filename."结束 $content--> \n"; else return $re;
}
//没有调用文件提示 不解析变量
function bgtemp($filename){ //mission_bar.html
	global $test,$temps,$rootdir,$rooturl,$u;
	extract($GLOBALS,EXTR_SKIP);

	if(substr($filename,0,4)=='head') $u[lasturl]=$_SERVER["REQUEST_URI"];
	if(substr($filename,-5)=='.html') $filename=substr($filename,0,-5);
	//$d=debug_backtrace();
  //  $temps[$filename]=$d[0]['line'];
	if(!file_exists("$rootdir/template/$filename")){
		$filename.='.html'; 
		if(!file_exists("$rootdir/template/$filename")) return("模版$rootdir/template/".$filename."没有找到! <a href='$rooturl/template.php?name=$filename'>点击生成</a>".$d[0]['file'].':'.$d[0]['line']);
	}
	$fp=fopen("$rootdir/template/$filename",'r');
	$re=fgets($fp,2000);
	if(substr($re,0,4)=='<!--') $re='';
	do{
		$temp_line=fgets($fp,200000);
		//if(substr($temp_line,0,4)=='<!--') $temp_line='';
		$re=$re.$temp_line;
	}while(!feof($fp));
	fclose($fp);
   //$re=str_replace('"','\"',$re);
	// eval('$re="'.$re.'";');
	//$content .= "调用子程序:". $d[0]['file'].":". $d[0]['line'];
	if($test>0) return $re ; else return $re;
}

//没有调用文件提示 
function bg_temp($filename){ //mission_bar.html
	global $test,$temps,$rootdir,$rooturl,$u;
	extract($GLOBALS,EXTR_SKIP);

	if(substr($filename,0,4)=='head') $u[lasturl]=$_SERVER["REQUEST_URI"];
	if(substr($filename,-5)=='.html') $filename=substr($filename,0,-5);
	$d=debug_backtrace();
	$temps[$filename]=$d[0]['line'];
	if(!file_exists("$rootdir/template/$filename")){
		$filename.='.html'; 
		if(!file_exists("$rootdir/template/$filename")) return("模版$rootdir/template/".$filename."没有找到! <a href='$rooturl/template.php?name=$filename'>点击生成</a>".$d[0]['file'].':'.$d[0]['line']);
	}
	$fp=fopen("$rootdir/template/$filename",'r');
	$re=fgets($fp,2000);
	if(substr($re,0,4)=='<!--') $re='';
	do{
		$temp_line=fgets($fp,200000);
		if(substr($temp_line,0,4)=='<!--') $temp_line='';
		$re=$re.$temp_line;
	}while(!feof($fp));
	fclose($fp);
   $re=str_replace('"','\"',$re);
	eval('$re="'.$re.'";');
	//$content .= "调用子程序:". $d[0]['file'].":". $d[0]['line'];
	if($test>0) return $re ; else return $re;
}


function gettemplate($filename){ //mission_bar.html
	global $test,$temps,$rootdir,$rooturl,$u;
	if(substr($filename,0,4)=='head') $u[lasturl]=$_SERVER["REQUEST_URI"];
	if(substr($filename,-5)=='.html') $filename=substr($filename,0,-5);
	$d=debug_backtrace();
	$temps[$filename]=$d[0]['line'];
	if(!file_exists("$rootdir/template/$filename")){
		$filename.='.html';
		if(!file_exists("$rootdir/template/$filename")) return("模版$rootdir/template/".$filename."没有找到! <a href='$rooturl/template.php?name=$filename'>点击生成</a>".$d[0]['file'].':'.$d[0]['line']);
	}
	$fp=fopen("$rootdir/template/$filename",'r');
	$re=fgets($fp,2000);
	if(substr($re,0,4)=='<!--') $re='';
	do{
		$temp_line=fgets($fp,200000);
		if(substr($temp_line,0,4)=='<!--') $temp_line='';
		$re=$re.$temp_line;
	}while(!feof($fp));
	fclose($fp);
	$re=str_replace('"','\"',$re);
	$content .= "调用子程序:". $d[0]['file'].":". $d[0]['line'];
	if($test>0) return "<!--".$filename."开始 $cntent-->\n".$re."<!--".$filename."结束 $content--> \n"; else return $re;
}

function my_template( $tpl_name ) {
	$tpl_file = __TPL_ROOT.$tpl_name;
	if( !ext_name( $tpl_file ) )
		$tpl_file.='.html';
	if( !file_exists( $tpl_file ) ){
		$msg = "Can't find template: $tpl_file!";
		mylog( $msg, __E );
		return $msg;
	}
	$re = addslashes( file_get_contents( $tpl_file ) );
	$d = debug_backtrace();
	return "\n<!-- $tpl_name begin --> \n $re <!-- $tpl_name end -->\n";
	//    return $re;
}


function error_exit($msg='错误',$url=''){
	if($url=='') echo "<script> alert('$msg'); back();</script>";
	else echo "<script> alert('$msg'); location='$url';</script>";
	toexit();
}

/*重定向*/
/*
function goto( $url='',$msg='' ) {
	if( $url=='' )
		$url = $_SERVER['HTTP_REFERER'];
	if($msg=='')
	{
	   // ob_clean();
		//header("Location: ".$url);
		echo "<script>location = '$url';</script>";
	}

	else
		echo "<script>alert('$msg');location = '$url';</script>";
	exit();
}
*/
/*重定向*/
function gotourl($url='',$msg='',$sec=0)
{
	if($url=='')
		goback();
	if($msg=='')
	{
		//ob_clean();
		//header("Location:$url");
		echo "<script>location = '$url';</script>";
	}else{
		if($sec!=0)
		{
		echo temp('head');
			echo "<script lanagent='javascript'> alert('$msg'); window.location='$url';</script>";
		toexit();
	}
	echo temp('head');
		echo "<script lanagent='javascript'> alert('$msg'); window.location='$url';</script>";
	}
	toexit();
}
function alert($msg='错误',$url=''){
	if($url=='') echo "<script> alert('$msg'); back();</script>";
	else echo "<script> alert('$msg'); location='$url';</script>";
	toexit();
}

function noquanxian($aa=''){
	global $u,$qx,$qxname,$url,$_u_,$goback,$rooturl;
	if($aa){
		for($a=0;$a<count($qx);$a++) if($qx[$a]==$aa) break;
		if($qx[$a]==$aa) $aa=$qxname[$a];
		echo "<script lanagent=javascript> if(confirm('$u[userid] 无 [$aa] 权限!要换名登陆吗?')){
			location='$rooturl/exit.php?goback=$goback';}</script>";
		gotourl($url[$_u_][2]);
	}
	toexit();
}

function quanxian($quanxian,$ret='0')
{
global $u;
//如果$ret=1  此函数要返回1/0 ，而不是直接noquanxian()
if($quanxian=='') {
if($ret=='0')
noquanxian();
else
return 1;
}
//多个权限相或
$a=explode('|',$quanxian);
$count=count($a);
if($count>1) {
while($b=each($a))
if($u[$b[value]]==1) return 1;
if($ret=='0')
noquanxian($b[value]);
else 
return 0;
}

//多个权限相与
$a=explode('&',$quanxian);
$count=count($a);
if($count>1){
while($b=each($a))
if($u[$b[value]]=='') {
if($ret=='0')
noquanxian($b[value]);
else 
return 0;
}
return 1;
}

//单个权限
if($u[$quanxian]!=1) {
if($ret=='0')
noquanxian($quanxian);
else 
return 0;
}
return 1;
}

function error($err='',$msg=''){
	if($err){
		if($msg) $msg='<br>['.$msg.']<br>';
		echo $err.$msg;
	}
}
function errorend($err='',$msg=''){
	if($err!=''){
		if($msg!='') $msg='<br>['.$msg.']<br>';
		echo $err.$msg;
		toexit();
	}
}

function nologin(){
	global $rooturl,$goback;
	//先尝试退出，再登陆 Mr Zhou 14-03-31 修改
	if(!isset($_REQUEST['ajax'])){
		gotourl("$rooturl/exit.php");
		toexit();
	}else{
		$token_key = md5(uniqid(rand()));
		$_SESSION['token_key']['login'] = $token_key;
		die(json_encode(array('error'=>'3','content'=>'因长时间未操作您已退出系统，请重新登录','token_key'=>$token_key)));
	}
}

function pageft($totle,$displaypg=20,$url=''){
	global $firstcount,$pagenav;#,$_SERVER;
	$GLOBALS["displaypg"]=$displaypg;
	$page=$_GET["page"];
	if($page=='') $page=1;
	//如果$url使用默认，即空值，则赋值为本页URL：
	if(!$url){ $url=$_SERVER["REQUEST_URI"]; }
//总站https 不适应    
//if(!strpos($url,$_SERVER[HTTP_HOST])) $url="http://".$_SERVER[HTTP_HOST].$url;
	//URL分析：
	$parse_url=parse_url($url);
	$url_query=$parse_url["query"]; //单独取出URL的查询字串
	if($url_query)
	{
		//因为URL中可能包含了页码信息，我们要把它去掉，以便加入新的页码信息。
$url_query=preg_replace("/(^|&)page=$page/is","",$url_query);
		//将处理后的URL的查询字串替换原来的URL的查询字串：
		$url=str_replace($parse_url["query"],$url_query,$url);

		//在URL后加page查询信息，但待赋值：
		if($url_query) { $url.="&page"; }
		else {$url.="page";}
	}
	else
	{
		$url.="?page";
	}

	$lastpg=ceil($totle/$displaypg); //最后页，也是总页数
	$page=min($lastpg,$page);
	$prepg=$page-1; //上一页
	$nextpg=($page==$lastpg ? 0 : $page+1); //下一页
	$firstcount=($page-1)*$displaypg;

	//开始分页导航条代码：
	$pagenav="显示第<B>".($totle?($firstcount+1):0)."</B>-<B>".min($firstcount+$displaypg,$totle)."</B>条,共${totle}条  ";

	if($firstcount<0) $firstcount=0;
	//如果只有一页则跳出函数：
	if($lastpg<=1) return false;

	$pagenav.=" <a href='$url=1' title=首页><font face=webdings>9</font></a> ";
	if($prepg) $pagenav.=" <a href='$url=$prepg'title=前页><font face=webdings>7</font></a> "; else $pagenav.=" <font face=webdings color=cccccc>7</font> ";
	if($nextpg) $pagenav.=" <a href='$url=$nextpg'title=后页><font face=webdings>8</font></a> "; else $pagenav.=" <font face=webdings color=#cccccc>8</font> ";
	$pagenav.=" <a href='$url=$lastpg' title=尾页><font face=webdings>:</font></a> ";
	$pagenav.="到第 <select name='topage' size='1' onchange='window.location=\"$url=\"+this.value'>\n";
	for($i=1;$i<=$lastpg;$i++){
		if($i==$page) $pagenav.="<option value='$i' selected>$i</option>\n";
		else $pagenav.="<option value='$i'>$i</option>\n";
	}
	$pagenav.="</select>页, 共 ${lastpg}页";
	if($firstcount<0) $firstcount=0;
}



function ypdb($pd,$cs){
	$file=substr($cs[0],0,32);//"AYPGL_YPDB";
	$Type=substr($cs[1],0,4);//"YPDB";
	$Creation=substr($cs[2],0,4);;//"YPGL";
	$JiLuLength=$cs[3];//234;
	//$rwhl=9;              $pd[0][n]
	//$rwhs=134;            $pd[1][n]

	if($cs[4]=='1'){
		echo "PhpToPalm 接口程序 V0.2 by 刘世伟 (c)安恒公司  at 2003.08.10<hr><br><br>";
		echo '目前是调试模式.该模式下，数据并不会真正装入Palm手持机...<br>-------------------------------------<br>';
	}
	$ycount=count($pd)-2; //纪录个数

	if(strlen($file)>32){ //查文件名长度
		echo '文件名['.$file.']太长!';
		return;
	}
	if($cs[4]=='1'){
		echo '字段个数='.count($pd[0]).'<br>纪录个数='.$ycount."<br>\n";
	}

	for($a=0;$a<count($pd[0]);$a++){   //字段个数
		if($cs[4]=='1') echo '<br>字段'.$a.'开始于'.$pd[1][$a].'长度='.$pd[0][$a];
		if(($pd[0][$a]+$pd[1][$a]) >$JiLuLength){
			echo '第';
			echo $a+1;
			echo '字段越界:';
			echo $pd[0][$a].'+'.$pd[1][$a].'大于'.$JiLuLength;
			return;
		}
	}

	$filelegth=0x50+$ycount*8+$ycount*$JiLuLength;

	if($cs[4]<>'1'){
		header("Cache-control: private");
		header("Content-Type: application/octet-stream");
		header("Content-Length: ".$filelegth);
		header("Content-Disposition: filename=".$file.".PDBTEMP");
		flush();
		echo $file;
		for($a=strlen($file);$a<0x20;$a++) echo "\x00";
		echo "\x00\x08\x00\x00"."\xbb\x53\x37\x7c"."\xbb\x53\x37\x7c"."\xbb\x53\x37\x7c";
		echo "\x00\x00\x00\x00"."\x00\x00\x00\x00"."\x00\x00\x00\x00".$Type;
		echo $Creation."\x00\x00\x00\x00"."\x00\x00\x00\x00";
		lohi($ycount);
		echo "\x00\x00";

		for($a=0;$a<$ycount;$a++)
		{

			lohi(0x50+8*$ycount+$a*$JiLuLength);
			echo "\x40\x50";
			lohi( $a+1+0x5000);
			echo "\x00\x00";
		}
	}

	for($aa=0;$aa<$ycount;$aa++) //看每个纪录
	{
		if($cs[4]=='1') echo "<br><br>\n现在处理纪录".$aa.':';
		$linebuff=''; //输出缓冲区
		for($ab=0;$ab<$JiLuLength;$ab++) $linebuff=$linebuff.' '; //设置输出缓冲区

		for($ac=0;$ac<count($pd[0]);$ac++) //处理每一个字段
		{
			$g=$pd[$aa+2][$ac]; //取每个纪录的每个字段
			$alength=$pd[0][$ac]; //每个字段的长度
			$astart=$pd[1][$ac];  //每个字段的开始位置
			$g=strtr(substr($g,0,$alength),' ','_'); //检查长度，并替换字段内容中' '为'_'
			if($cs[4]=='1') echo '<br>字段'.$ac.'内容:['.$g."]";
			for ( $ab=0 ; $ab < $alength ; $ab++)
				$linebuff[$astart+$ab]=$g[$ab]; //将对应字段放入linebuff;
		}

		for($ab=0;$ab<$JiLuLength;$ab++)
			if($cs[4]<>'1') //测试标志
			{
				if($linebuff[$ab]==' ') printf("%c",0);  //将" "解释为 0
				else echo $linebuff[$ab];
			}
	}
	if($cs[4]=='1') echo "<br><br><hr>接口使用说明<br>-----------------------<br>"
		.'使用ypdb（）函数生成一个pdb文件。<br>'
			.'ypdb()函数有2个参数。第一个参数是一个2维数组字符串，放置需要放入pdb的数据，<br>'
			.'第二个参数，是一个1维数组，放的是pdb的数据结构,5个元素分别是:<br>'
			.'文件名，pdb的TYPE属性，pdb文件的Create属性，每个纪录的总长度，以及调试标<br>'
			.'志。调试标志=1时，不生成文件，而是显示一些信息。<br>'
			.'<br>'
			.'2维数组参数：<br>'
			.'每列是一个字段，字段个数可以任意。<br>'
			.'第一行，定义对应的每个字段的长度，第二行定义每个字段的偏移位置。<br>'
			.'从第三行开始，每行是一个数据记录。<br>';
}

function lohi($aa){
	printf("%c%c",intval($aa/0x100),intval($aa % 0x100));
}


function logmsg($msg,$txtFileName=''){
	global $u;
	$d=debug_backtrace();
	$_file=($txtFileName=='')?basename($d[1]['file'],'.php'):$txtFileName;
	if($fp=@fopen("$rootdir/log/_log_$_file.txt","a+"))
	{
		fputs($fp,"\n{$u[userid]}\n".date('y-n-j|H:i:s|').$d[1]['file'].':第'.$d[1]['line']."行:\n\"".$msg.'"');
		fclose($fp);
	}
}

//将保存的化验项目从 |22|21|4|3| 这种形式转化为 array(22,21,4,3)这种格式 //若传入的字符串为空或字符串中只含有分隔符,返回假
function elementsToArray($elements,$fen_ge_fu=','){
	$result = array();
	if( !$elements )
		return array();
	else{
		$element_list = explode( $fen_ge_fu, $elements );
		if( !$element_list )
			return array();
		for ( $i = 0; $i < count( $element_list ); $i++ ) {
			if( $element_list[$i] === '' )
				continue;
			$result[] = $element_list[$i];
		}
		return $result;
	}
}
//能自动去除空元素的 explode
	function my_explode( $seperator, $str ) {
		if( !$str )
			return array();
		else{
			$result = explode( $seperator, $str );
			if( !$result )
				return array();
			$temp = array();
			for( $i = 0;$i < count( $result ); $i++ ) {
				if( $result[$i] === '' )
					continue;
				$temp[] = $result[$i];
			}
			return $temp;
		}
	}

/*
 * $el: 传入的化验项目数组
 * $espe: 包含 F CL SO4 NO3N 特殊项目的数组
 * $r: 返回数组
 */
define("__F",1);
define("__CL",2);
define("__SO4",3);
define("__NO3N",4);
define("__FCNS",57);

	function format( $el ) {
		if( !$el )
			return false;
		$flag = 0;
		$espe = array(); #保存特殊项目 (1,2,3,4 )
			for( $i = 0; $i < count( $el ); $i++ ) {
				if( __F == $el[$i] || __CL == $el[$i] || __SO4 == $el[$i] || __NO3N == $el[$i] ) {
					$espe[] = $el[$i];
					$flag++;
					$c_value[] = $_SESSION['assayvalueC'][$el[$i]];
				} else
					$r[] = $el[$i];
			}
		if( !$flag || 1 == $flag )
			return $el;
		else{
			$r_c = implode( ',' ,$c_value );
			$espe_items = implode( '|', $espe );
			$r[-1] = array( $r_c => $espe_items );
		}
		return $r;
	}


function debuginfo(){
	print_rr($GLOBALS);
	exit();
}

function getput($msg){
	return($_GET[$msg].$_POST[$msg]);
}

//这个函数,用来去掉重复提交,2秒内的提交被cancel;
//一些网络传输问题会造成重复提交,在对库进行插入操作之前,调用这个函数,
//集中检查所有的数据库插入操作,是否有需要延迟的地方.添加本函数掉用

//可以指定时间，如果不指定秒数，则默认值是2秒*/
function once($seconds=2){
	if(time()<$_SESSION[lasttime]+$seconds) {
		$_SESSION[lasttime]=time();
		backa(2,"你刚才的提交已经成功，请不要重复提交。两次提交小于 $seconds 秒");
	}
	$_SESSION[lasttime]=time();
}

function bgcolor(){
	static $color="#cccccc";
	$colors[0]="#cccccc";
	$colors[1]="#dddddd";
	$color=($color==$colors[0])
		? $colors[1]
		: $colors[0];
	return $color;
}

//四舍六入五单双 $aFloatNumber:一个给定的浮点数,$c:保留位数,注意,它的返回值是一个字符串,可以有效的将0保留为多位小数,如0.00等.
function _round($afloat,$c){
	if($afloat==0) return 0;
	$r=explode('.',$afloat);
	$len=strlen($r[1]);
	if($c>=$len) return number_format($afloat,$c,'.','');
	else{
		$flag=$r[1]{$c};
		if($flag!=5) return number_format(round($afloat,$c),$c,'.','');
		else{
			//$a=substr($r[1],0,$c+1);
			//if($r[1]>$a) return number_format($afloat,$c,'.','');
			$a=substr($r[1],$c+1);
			if(intval($a)) return number_format($afloat,$c,'.','');
			else return number_format(round($afloat/2,$c)*2,$c,'.','');
		}
	}
}
/**
 * 功能：保留有效位数修约
 * 作者：Mr Zhou
 * 日期：2015-08-25
 * 参数：$num  待修约的数值
 * 参数：$decimals  要保留的有效位数
 * 参数：$max=10   最多保留几位小数
 * 参数：$is_kxjs=false    是否使用科学记数法
 * 参数：$is_dg=false  是否是递归调用
 * 描述：
*/
function round_yxws($num,$decimals=2,$max=10,$is_kxjs=false,$is_dg=false){
    $max = intval($max);
    $num = floatval($num);
    $decimals = intval($decimals);
    if($num==0){
        return _round($num,$decimals);
    }
    $start = false;  //判断是否开始计数
    $num = strval($num);//将数字转换为字符串
    $exp = explode('.',$num);//以小数点分割
    //带有小数并且整数位的位数不够时执行以下方式
    if(strlen($exp[0])<=$decimals || $is_dg==true){
        if(intval($exp[0])!=0){
            $start = true;//因为整数部分已计入有效位数，所以变为true
            $decimals -= strlen($exp[0]);//获取剩余需要保留的位数
        }
        if(intval($exp[1])==0){
            $i = $decimals;
        }else{
            //$i为小数部分的数据位置
            //$j为小数部分有效位数的计数器
            for($i=$j=0;$j<$decimals && $i<=$max;$i++){
                //当未遇到非0的数值时一直跳过
                if($start == false && '0'==$exp[1][$i]){
                    continue;
                }else{
                    //遇到非0数值时开始计数有效位数
                    $j++;
                    $start = true;
                }
            }
        }
        return _round($num,$i);
    }else{
        //整数或者整数部分的有效位数已足够时的修约
        //得到只留一位整数的数值进行修约
        $mi  = strlen($exp[0])-1;//获取科学计数法的幂次方数
        $num = $num/pow(10,$mi);
        $num = round_yxws($num,$decimals,$max,$is_kxjs,true);
        //加上幂次方得到最终修约结果
        $data = $num.'×10^'.$mi;
        //判断是否使用科学记数法
        return ($is_kxjs!=false) ? $data : get_round_yxws($data);
    }
}
/**
 * 功能：获取科学计数法数值结果
 * 作者：Mr Zhou
 * 日期：2015-08-25
 * 描述：
*/
function get_round_yxws($num){
    if(''===$num){
        return '';
    }
    $exp = explode('×10^',$num);
    if(count($exp)==1){
        return $num;
    }else{
        return $exp[0]*pow(10,$exp[1]);
    }

}
/**
 * 功能：将科学计数法转换为原始数字字符串
 * 作者：Mr Zhou
 * 日期：2016-12-27
 * 参数：$num 需要被转换的数字
 * 描述：
*/
function sc_to_num($num){
  // stripos 查找字符串首次出现的位置（不区分大小写）
  if( false === stripos($num, 'e') ){
    return $num; // 不是科学计算法的数字直接返回
  }else{
    $a = explode('e',strtolower($num));
    // 获取科学计数法的幂次方
    $double = intval($a[1]);
    // 截取科学计数法数字中小数点后的数字
    if( false === stripos($a[0], '.') ){
      $b[1] = '0';
    }else{
      $b = explode('.', $a[0]);
    }
    // 获取科学计数后保留的小数位数，用于数据准确位数还原
    $b = ( '0' == $b[1] ) ? 0 : strlen($b[1]);
    // 计算出转换后应保留小数位数
    $double = abs($b-$double);
    return bcmul($a[0], bcpow(10, $a[1], $double), $double);
  }
}

function window( $url ) {
	echo "<script language='javascript'>\nwindow.open('".$url."');\nback();\n</script>";
}

//给定参数名称,得到参数当前的值
function get_para( $para_name ) {
	global $DB;
	$r = $DB->fetch_one_assoc( "SELECT `field_value` FROM `settings` WHERE `field_name` = '$para_name'" );
	return  $r ? $r['field_value'] : false;
}
//更新名称为 $para_name 的参数的值为 $new_value
function set_para( $para_name, $new_value ) {
	global $DB;
	$sql = "UPDATE `settings` SET `field_value` = '$new_value' WHERE `field_name` = '$para_name'";
	e_( $sql );
	$DB->query( $sql );
}

function redo($act,$msg){
	global $url,$_u_;
	$p=parse_url(urldecode($url[$_u_][0]));
	$q=$p['query'];
	$q=preg_replace("/(^|&)action=$_GET[action]/is",'',$q);
	prompt($msg);
	$new_url=$p[scheme]."://".$p[host].$p[path].'?'.$q."&action=".$act;
	logmsg($new_url,"newurl");
	gotourl($new_url);
}
/*得到化验项目列表的中文名称列表*/
function get_c_items( $items, $xuhao = '' ) {
	global $DB;
	if( !is_array( $items ) )
		$items = elementsToArray( $items );
	sort($items);
	for($i=0;$i<count($items);$i++)
		if($xuhao)
			$cv[] = $_SESSION[assayvalueC][(int)$items[$i]] . "(<font color='blue'>$items[$i]</font>)";
		else
			$cv[] = $_SESSION[assayvalueC][(int)$items[$i]];
	$result = array();
	if( $cv )
		$result = implode(',',$cv);
	return $result;
}
/*
 * 给定一个化验项目数组,返回一个 固定试剂=>化验项目 的数组
 */

function get_reagent_items_array($assay_values){
	global $DB;
	$assay_elements=elementsToArray($assay_values);
	while(list(,$aItem)=each($assay_elements)){
		$s = $DB->fetch_one_assoc("select `reagent` from `assay_value` where `id`='$aItem'  LIMIT 1");
		if(!$s["reagent"])
			continue;
		$reagent_items[$s['reagent']] .= $aItem.'|';
	}
	return $reagent_items;
}

/*
 * 给出化验结果,项目序号,当前水质标准
 * 得到新的水质标准
 */
function calc_water_quality(&$fx_result, $vid, &$quality, $st_type = '') {
	$f = 'f'.$vid;
	if(function_exists($f) && $fx_result !== '') {
		$new_wq = ($vid == 44 || $vid == 43) ? $f($fx_result, $st_type) : $f($fx_result);
		if($new_wq > $quality)
			$quality = $new_wq;
		if($new_wq == 3)
			$fx_result = "<font color='green'>$fx_result</font>";
		if($new_wq == 4)
			$fx_result = "<font color='blue'>$fx_result</font>";
		if($new_wq ==5)
			$fx_result = "<font color='red'>$fx_result</font>";
		if($new_wq == 6)
			$fx_result = "<font color='red'>$fx_result</font>";
	}
}

function site_name($aSid,$aField=''){
	global $DB;
	if(!$aSid) return false;
	$r=$DB->fetch_one_assoc("select * from `sites` where `id`=$aSid");
	if(!$aField) return $r['site_name'];
	if($aField and $aField=='all') return $r;
	if($aField=='河名站名') return $r['river_name'].$r['site_name'];
	return $r[$aField];
}

//返回中文的星期几
function c_week($_time=''){
	$week=array('星期日','星期一','星期二','星期三','星期四','星期五','星期六');
	return ($_time=='')
		? $week[date('w')]
		: $week[date('w',strtotime($_time))];
}


//得到站点中文名称列表:
function get_site_list( $sites, $max = '' ) {
	global $DB;
	if( !is_array( $sites ) )
		$sites = elementsToArray( $sites );
	$sites = implode( ',', $sites );
	if(trim($sites)=='') return;

	$R = $DB->query("SELECT `site_name` FROM `sites` WHERE `id` IN ($sites) ORDER BY `id`");
	$i = 0;
	$site_name = array();
	while( $r = $DB->fetch_assoc( $R ) ) {
		$i++;
		$site_name[]=$r['site_name'];
		if( $i == $max )
			break;
	}
	return implode( ',', $site_name );
}

function quotes($content){
	if (get_magic_quotes_gpc()) return $content;
	else{
		if (is_array($content)){
			foreach ($content as $key=>$value) $content[$key] = addslashes($value);
			return $content;
		}
		else  return addslashes($content);
	}
}

	function size_kmg($file_size){
	//转换数值到kmg 字符串
		if( $file_size < 1024 )
			return $file_size.="B";
		if( $file_size >= 1024 && $file_size < 1024*1024 )
			return _round( $file_size/1024, 1 ) . "KB";
		if( $file_size >= 1048576 && $file_size < 1024*1024*1024 )
			return _round( $file_size/1024/1024, 1 ) . "MB";
		return _round( $file_size/1024/1024/1024, 3 ) . "GB";
	}


/*
 * 返回 1 表示将文本正确记录到了 $path.$log_file_name 文件中, 并记入了总 LOG 文件
 * 返回 -1 表示文本仅记录到了 $path.$log_file_name 中
 * 返回 -2 表示文本仅记录到了总 LOG 中
 * 返回 false 表示...该函数执行失败
 */
function mylog( $msg, $log_file_name = '' ) {
	global $rootdir;
	if ( is_array( $msg ) ) {
		$temp = '';
		foreach( $msg as $key => $value )
			$temp .= $key . '=>' . $value . "\n";
		$msg = $temp;
	}
	$path = '';
	$path = $rootdir."/log/";
	$debug_info = debug_backtrace();
	if ( !$log_file_name )
		$log_file_name = basename( $debug_info[0]['file'], '.php' ) . '.txt';

	$msg = date( 'y-n-j|His|' ) . $debug_info[0]['file'] . '@line:' . $debug_info[0]['line'] ."\n$msg\n";
	if ( $fp = fopen( $path.$log_file_name, "a+" ) ) {
		fwrite( $fp, $msg );
		fclose( $fp );
		if ( _log( $msg ) )
			return 1;
		return -1;
	} else {
		if ( _log( $msg ) )
			return 2;
	}
	return false;
}

/*将 $msg 记录到总LOG中*/
function _log($msg)
{
	if( defined( '__G_LOG_FILE' ) && $fp = fopen( __G_LOG_FILE, 'a+' ) ) {
		fwrite( $fp,$msg );
		fclose( $fp );
		return true;
	} else
		return false;
}

/*在脚本当前目录记录调试LOG*/
function loc_log( $msg, $file_name )
{
	if ( !$file_name )
		$file_name = 'log.txt';
	if ($fp = fopen( $file_name, 'a+' )) {
		fwrite( $fp, now() . ":" . $msg . "\n" );
		fclose( $fp );
		return true;
	} else
		return false;
}

function prompt( $msg ) {
	global $_header;
	$msg = addslashes($msg);
	echo $_header . "<script>alert('" . $msg . "');</script>";
}

function bad_request( $msg = '非法请求' ) {
	die( $msg );
}
//站点的统计参数
function convert_site_tjcs($ddtjcs,$site_group)
{
	$result = array();
	while( $g = each( $site_group ) ) {
		$tjcs_name = $g['value'];
		$re="0";
		foreach ($ddtjcs as $key1=>$value1){
			
			if($value1==$g['key']&&$value1!=''){
				$result[] = "<label><input type='checkbox' name = 'tjcs_name[]' checked='checked' value = '$value1' />$tjcs_name</label>";
				$re="1";
			}
		}
		if($re==0)
		$result[] = "<label><input type='checkbox' name = 'tjcs_name[]'  value = '$g[key]' />$tjcs_name</label>";
	}
	return $result;
}
//站点关联和未关联的项目
function convert_assay_value( $assay_value, $checked = "" )
{
	global $DB;
	$fzx_id=FZX_ID;
	$sql = "SELECT v.`id`,v.`value_C`,xf.`unit`,xf.`lxid`,xf.`fangfa`
			FROM `assay_value` AS v LEFT JOIN `xmfa` AS xf ON v.`id` = xf.`xmid`
			WHERE xf.`fzx_id` IN('$fzx_id','1')AND xf.`act`=1 AND xf.`mr`=1 ORDER BY CONVERT( `value_C` USING gbk )";
	$R = $DB->query($sql);
	while($r=$DB->fetch_assoc($R)){
		$assayvalueC[]	= $r['id'];
	}
	@$assay_value=array_intersect( $assay_value,$assayvalueC);
	$result = array();
	while( $v = each( $assay_value ) ) {
		$vid = (int)$v['value'];
		if($_SESSION['u']['admin']=='1'){ //查询是否管理员显示id 
				$iid="(".$vid.")";
		}
		$sql = "SELECT `id`,`value_C` FROM `assay_value` AS v WHERE id=$vid";
		$value=$DB->fetch_one_assoc($sql);
		   //$result[] = "<input type=\"checkbox\" $checked name=\"vid[]\" value=\"{$vid}\" />" . $_SESSION['assayvalueC'][$vid] . "$iid";
			$result[] = "<label><input type=\"checkbox\" $checked name=\"vid[]\" value=\"{$vid}\" />" . $value['value_C']. "$iid</label>";
	}
	return $result;
}
function convert_assay_value2( $assay_value, $checked = "" )
{
	global $DB;
	$fzx_id=FZX_ID;
	$sql = "SELECT v.`id`,v.`value_C`,xf.`unit`,xf.`lxid`,xf.`fangfa`
			FROM `assay_value` AS v LEFT JOIN `xmfa` AS xf ON v.`id` = xf.`xmid`
			WHERE xf.`fzx_id` IN('$fzx_id','1')AND xf.`act`=1 AND xf.`mr`=1 ORDER BY CONVERT( `value_C` USING gbk )";
	$R = $DB->query($sql);
	while($r=$DB->fetch_assoc($R)){
		$assayvalueC[]	= $r['id'];
	}
	@$assay_value=array_intersect( $assay_value,$assayvalueC);
	$result = array();
	while( $v = each( $assay_value ) ) {
		$vid = (int)$v['value'];
		if($_SESSION['u']['admin']=='1'){ //查询是否管理员显示id 
				$iid="(".$vid.")";
			}
			$sql = "SELECT `id`,`value_C` FROM `assay_value` AS v WHERE id=$vid";
			$value=$DB->fetch_one_assoc($sql);
			$result[] = "<input type=\"hidden\" name=\"vid[]\" value=\"{$vid}\" />" .$value['value_C']. "$iid";
	}
	return $result;
}
//站点的所属和未属批次
function convert_site_group( $site_group, $checked = '' )
{
	$result = array();
	while( $g = each( $site_group ) ) {
		$group_name = $g['value'];
		$result[] = "<label><input type='checkbox' name = 'group_name[]' $checked value = '$group_name' />$group_name<label>";
	}
	return $result;
}

function display_assay_values( $aArray, $cols = 5 )
{
	if( !$aArray )
		return "";
	echo '<tr>';
	$j = 0;
	reset( $aArray );
	while( list( , $assay ) = each( $aArray ) ) {
		$j++;
		echo ( $j % $cols == 0 )
			? "<td>{$assay}</td></tr>\n<tr>"
			: "<td>{$assay}</td>";
	}
	$k = $cols - $j % $cols;
	if( $k != $cols )
		echo "<td colspan='$k'></td></tr>";
}

function display_group( $GROUP='' )
{
	if( $GROUP=='' )
		return ;
	$i=0;
	echo "<tr>";
	reset( $GROUP );
	while( list( , $group ) = each( $GROUP ) ) {
		$i++;
		echo ( $i % 5 == 0 )
			? "<td>{$group}</td></tr>\n<tr>"
			: "<td>{$group}</td>";
	}
	$k = 5 - $i % 5;
	if( $k != 5 )
		echo "<td colspan='$k'></td></tr>";
}
/*
 *将传过来的数组转换成 下拉菜单的option
 * $data	：要转换的数组
 * $method	：0代表将option的value值与显示值相同 ， 1代表与数组的key值相同。
 * $selected：下拉菜单的默认值
 */
function disp_options( $data, $method = 0 ,$selected='') {
	//防止传入其他字符等影响判断
	$if_selected= "|".$selected."|";
	$options	= '';
	//去除空值
	foreach((array)$data as $key=>$value){
		//将key值作为 option的value值
		if($method == 0){
			$label_key	= $value;
		}else{
			$label_key	= $key;
		}
		//查看是否有默认值
		$is_selected	= '';
		if($selected != '||'){
			$is_label_key	= "|".$label_key."|";
			if($if_selected == $is_label_key){
				$is_selected	= " selected";
			}
		}
		if($value == '全部'){
			$label_key	= '';
		}
		$options	.= "<option label='$value' value='$label_key' $is_selected>$value</option>\n";
	}
	return $options;
}
/*
//method = 0
function disp_options( $data, $method = 0 ) {
	$options = "";
	reset( $data ); 
	while( list( $key, $value ) = each( $data ) ) {
		$label = $value;
		if( $value == '全部' )
			$value = '';
		$options .= ( $method )
			? "<option label='$label' value='$key'>$label</option>\n"
			: "<option label='$label' value='$value'>$label</option>\n";
		}
	return $options;
}
*/
function get_site_type_num( $site_type = '站网' ) {
global $site_flag;
return $site_flag[$site_type];
}

function get_site_type_text( $site_type = '0' ) {
global $flag_site;
return $flag_site[(int)$site_type];
}

function new_record( $table_name, $data ) {
	global $DB;
	$sql = "INSERT INTO $table_name SET ";
	while( list( $key, $value ) = each( $data ) ) {
		$value = mysql_real_escape_string($value);
		$sql .= "`$key` = '$value',";
	}
	$sql = rtrim( $sql, ',' );
	mysql_query( $sql );
	return mysql_insert_id();
}

function update_record( $table_name, $data, $where = '0' ) {
	global $DB;
	$sql = "UPDATE $table_name SET ";
	while( list( $key, $value ) = each( $data ) )
		$sql .= "`$key`='$value',";
	$sql = rtrim( $sql, ',' );
	$sql .= " WHERE $where" ;
	$DB->query( $sql );
	return $DB->affected_rows;
}

function update_rec( $table_name, $data, $id ) {
	global $DB;
	$sql = "UPDATE $table_name SET ";
	while( list( $key, $value ) = each( $data ) )
		$sql .= "`$key`='$value',";
	$sql = rtrim( $sql, ',' );
	$sql .= " WHERE id = $id" ;
	if($DB->query( $sql ))  return $DB->affected_rows;
	return false;
}

function my_trim( $vars ) {
	if( !is_array( $vars ) )
		$vars = trim($vars);
	while( list( $key, $value ) = each( $vars ) )
		$vars[$key] = trim( $value );
	reset( $vars );
	return $vars;
}


if(!function_exists("file_put_contents"))
{
function file_put_contents($filename,$data)
{
$fp=fopen($filename,"w");
fputs($fp,$data);
fclose($fp);
}
}

function get_cyd( $cyd_id='' ) {
	global $DB;
	if($cyd_id=='') return;
	$cyd = $DB->fetch_one_assoc("SELECT * FROM `cy` WHERE `id` = '{$cyd_id}'" );
	$cyd['flag'] = $cyd['status_text'] = get_cyd_status_text( $cyd['status'] );
	$cyd['site_type_text'] = get_site_type_text( $cyd['site_type'] );
	return $cyd;
}

function get_cyd_by_bh( $cyd_bh ) {
	global $DB;
	$cyd = $DB->fetch_one_assoc("SELECT * FROM `cy` WHERE `cyd_bh` = '{$cyd_bh}'" );
	$cyd['flag'] = $cyd['status_text'] = get_cyd_status_text( $cyd['status'] );
	$cyd['site_type_text'] = get_site_type_text( $cyd['site_type'] );
	return $cyd;
}

function get_cyd_status_text( $status_num ) {
	//采样单状态
	$flag = $cyd_status_data = array(
		'-1' => '无水',
		'0' => '采样任务未确认',
		'1' => '采样任务已下达',
		'2' => '采样任务已接受',
		'3' => '已采样',
		'4' => '样品已审核',
		'5' => '样品已接收',
		'6' => '测试任务已下达',
		'7' => '已完成化验',
		'8' => '报告已签发',
	);
	return $flag[$status_num];
}

function get_site_total( $cyd_id ) {
	$cyd = get_cyd( $cyd_id );
	return sizeof( elementsToArray( $cyd['sites'] ) );
}

//若提供采样单号, 取得某次采样所有普通样品检测项目
function get_all_assay_value( $cyd_id = 0, $xianchang_item = true ){
	global $DB;
	if($cyd_id){
		$sql = "SELECT assay_values FROM cy_rec WHERE cyd_id = $cyd_id AND status = 1 AND by_id is null";
		$R = $DB->query( $sql );
		while( $r = $DB->fetch_assoc($R) )
			$assay_values .= ',' . $r['assay_values'];
		return array_unique(elementsToArray($assay_values));
	}
	$result = array();
	$sql = "SELECT x.id,x.value_C FROM `xm` as x LEFT JOIN `assay_value` as av ON x.id=av.vid WHERE av.act ='1'  ORDER BY id";
	$R = $DB->query($sql);
	while($row = $DB->fetch_assoc($R)){
		$result[] = $row['id'];
	}
	return $result;
}

/*
#############若提供采样单号, 取得某次采样所有普通样品检测项目
function get_all_assay_value( $cyd_id = 0, $xianchang_item = true ){
	$RR="SELECT aj.value_C AS value_C,aj.vid AS vid FROM `assay_jcbz` AS aj INNER JOIN `assay_value` AS av ON aj.vid=av.vid INNER JOIN `xmfa` AS xf ON aj.vid=xf.xmid INNER JOIN `n_set` AS n ON aj.jcbz_bh_id=n.id INNER JOIN `leixing` AS lx ON n.module_value2=lx.id WHERE aj.fzx_id='".$fzx_id."' AND av.act='1' AND av.fzx_id='".$fzx_id."' AND xf.fzx_id='".$fzx_id."' AND n.fzx_id='".$fzx_id."' AND n.module_value2 IN ($leixing) AND n.module_value3='1' AND xf.lxid IN ($leixing) AND xf.mr='1'" ;
	
	global $DB;
	if($cyd_id){
		$sql = "SELECT assay_values FROM cy_rec WHERE cyd_id = $cyd_id AND status = 1 AND (by_id is null or by_id=0)";
		$R = $DB->query( $sql );
		while( $r = $DB->fetch_assoc($R) )
			$assay_values .= '|' . $r['assay_values'];
		return array_unique(elementsToArray($assay_values));
	}
	$result = array();
	$xian_chang_str = ($xianchang_item) ? " ,2" : "";
	$sql = "SELECT id FROM assay_value WHERE act IN (1 $xian_chang_str) and isoff='1' and cs_fangfa='1' ORDER BY id";
	$R = $DB->query($sql);
	while($row = $DB->fetch_assoc($R))
		$result[] = $row['id'];
	return $result;
}
*/

//取assay_value表中的所有实际有效的化验项目
function get_av_data() {
	global $DB;
	$sql = "SELECT id, value_c FROM assay_value WHERE act = '1' ORDER BY id";
	$res = $DB->query($sql);
	$result = array();
	while($row = $DB->fetch_assoc($res)) {
		$result[$row['id']] = $row['value_c'];
	}
	return $result;
}

//取共用一表格的化验项目的vid
function get_special_vid($status = 1) {
	global $DB;
	$sql = "SELECT id FROM assay_value WHERE you_ji_flag = '$status' ORDER BY id";
	$res = $DB->query( $sql );
	$result = array();
	while( $row = $DB->fetch_assoc( $res ) ) {
		$result[] = $row['id'];
	}
	return $result;
}

function get_water_types( $site_type = 0 ) {
	global $DB;
	$sql = "SELECT DISTINCT water_type FROM sites WHERE water_type != '' ";
	if( !is_null($site_type) )
		$sql .= " AND site_type = '$site_type' ";
	$sql .= " ORDER BY water_type ";
	$res = $DB->query( $sql );
	$result = array();
	while( $row = $DB->fetch_assoc( $res ) )
		$result[] = $row['water_type'];
	return $result;
}

//判断是否某站点无水
function is_no_water($wt){
	$wt = trim($wt);
	//未填写, 作有水处理
	if(strlen($wt) == 0) {
		return false;
	} else {
		return !is_numeric($wt);
	}
}
/**
 * 功能：将传过来的字符串（包含数组）转义一下：防止字符串注入
 * 作者：韩枫
 * 日期：2014-04-18
 * 参数1：[$string][无类型限制][含义：需要转义的字符串或者数组]
 * 返回值：$string转义 后的 字符串或者数组
 * 描述： 暂不提供 键值 的转义
*/
function get_str($string){  
	if(is_array($string)){
		foreach($string as $key=>$value){
			$string[$key] = get_str($value);
		}
	}else{
		if(!get_magic_quotes_gpc()){
			$string = addslashes($string);
		}
	}
	return $string;
}


/**
 * 功能：对变量中的特殊字符进行转义
 * 作者：Mr Zhou
 * 日期：2014-05-23
 * 参数：
 * 描述：如果magic_quotes_gpc开启，直接将字符串返回，如果未开启则用addslashes()函数转义
 *       参数如果是数组则递归转义
 * @param   $value
 */
function addslashes_deep( $value )
{
	if (empty($value) || get_magic_quotes_gpc()){
		return $value;
	}else{
		return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
	}
}

function get_int(& $string)
{//解决一下字符串的注入问题
if($string!='')
$string=intval(strtr($string,array('１'=>'1','２'=>'2','３'=>'3',
'４'=>'4','５'=>'5','６'=>'6','７'=>'7','８'=>'8','９'=>'9','０'=>'0')));
return  $string;
}


/**
 * 为充分利用cookie. 尽量在一个cookie中保存比较多的信息
 *
 * @param string $main_key
 * @param array $update
 * @param int $expire_time
 * @param string $path
 * @param string $domain
 */
function updateCookie($main_key, $update, $expire='', $path='/', $domain='') {
	if(defined('APP_DOMAIN')) {
		$domain = APP_DOMAIN;
	}
	if(!$expire) {
		$expire = strtotime('+10 year');
	}
	$cookie = unserialize($_COOKIE[$main_key]);
	foreach($update as $key=>$value) {
		$cookie[$key] = $value;
	}
	$cookie = serialize($cookie);
	setcookie($main_key, $cookie, $expire, $path, $domain);
}

//将数据json序列化后保存在cookie中.
function mySetCookie($key, $value, $expire='', $path='/', $domain='') {
	if(defined('APP_DOMAIN')) {
		$domain = APP_DOMAIN;
	}
	if(!$expire) {
		$expire = strtotime('+10 year');
	}
	$cookie = serialize($value);
	setcookie($key, $cookie, $expire, $path, $domain);
}

function myGetCookie($key) {
	if(!isset($_COOKIE[$key])) return '';
	return unserialize($_COOKIE[$key], true);
}

function feiqi($msg='901')
{
global $u,$dwname,$technicalemail;
$u[useralllist]='';
$u[grouplist]='';
@mail($technicalemail,'=?utf-8?B?'.base64_encode("[$dwname]废弃程序被运行 $msg").'?=',$_SERVER[REQUEST_URI]."\r\n".print_r($_SERVER,1).print_r($u,1).print_r($_SESSION,1));
die('运行出错!错误号:'.$msg);

}

function dfmto($str='')
{//度分秒->度
	if($str=='') return $str;
	if($str=='999'){ $str='';return $str;}
	$fh=substr($str,0,1);
	if($fh=='-') $str=substr($str,1);
	else unset($fh);
	$str=strtr($str,array('＋'=>'','+'=>'','度'=>'|','分'=>'|','秒'=>'|','.'=>'|',','=>'|','，'=>'|'));
	$a=explode('|',$str);
	if($fh=='-')
	$fl=0-floatval($a[0])-floatval($a[1]/60)-floatval("$a[2].$a[3]")/3600;
	else
	$fl=floatval($a[0])+floatval($a[1]/60)+floatval("$a[2].$a[3]")/3600;
	return $fl;
}

function todfm($fl='')
{//度->度分秒
	if($fl==='') return $fl;
	if($fl=='999'){ $str='';return $fl;}
	$fl=floatval($fl);
	if($fl<0)
	{
		$fh='-';
		$fl= -$fl;
	}
$du=intval($fl);
$fl=($fl-$du)*60;
$fen=intval($fl);
$miao=($fl-$fen)*60;
$miao=round($miao,4);
return("${fh}${du}度${fen}分${miao}秒");
}

function get_cy_rec_by_cid($cid) {
	global $DB;
	$sql = "SELECT * FROM cy_rec WHERE id = '$cid'";
	return $DB->fetch_one_assoc($sql);
}
//由 站点信息 或者委托信息 里的 一些 项目方法id (xmfa) 得到项目 的xmids
function get_xmids_byfid($fid) {
	global $DB;
$sql="select * from wtjc where id='$fid'";
$xy=$DB->fetch_one_assoc($sql);

return get_xmids_byids($xy[wtxm]);
}
//有项目方法id 的集合 1,2,1,21,2,   得到 项目 1|12|  
function get_xmids_byids($fids) {
	global $DB;
  $ysql="select * from xmfa where  id in($fids)";
	$row = $DB->query( $ysql );
	while($r=$DB->fetch_assoc($row))
	{
	$xmids[]=$r[xmid];
	}
	$arr_xmids=@join('|',$xmids);//得到了 项目的 信息
	
return $arr_xmids;
}
##################获取水样类型
function get_syleibie($lxid='') {
	global $DB;
	$sql_leixing = $DB->query("SELECT id,lname FROM `leixing` WHERE parent_id='0' AND act='1'");
	while($lx = $DB->fetch_assoc($sql_leixing))
	{
		if($lx['id']==$lxid){
			$lxlist .= "<option selected=\"selected\"  value=\"$lx[id]\">$lx[lname]</option><optgroup style=\"padding-left:21px\">";
		}else{
			$lxlist.="<option value='$lx[id]'>$lx[lname]</option><optgroup style=\"padding-left:21px\">";
		}
		$sql_xleixing = $DB->query("SELECT id as xid,lname,parent_id FROM `leixing` WHERE  parent_id!='0' AND act='1'");
		while($xlx = $DB->fetch_assoc($sql_xleixing))
		{
			if($lx['id']==$xlx['parent_id']){
				if($xlx['xid']==$lxid){
					
					$lxlist.="<option value='$xlx[xid]' selected=\"selected\">$xlx[lname]</option>";
				}else{
					$lxlist.="<option value='$xlx[xid]'>$xlx[lname]</option>";
				}
			}
		}
		$lxlist.="</optgroup>";
	}
	return $lxlist;
}
function get_syleibie2($lname='') {

	global $DB;
	$sql_leixing = $DB->query("SELECT id,lname FROM `leixing` WHERE parent_id='0'  AND act='1'");
	while($lx = $DB->fetch_assoc($sql_leixing))
	{
		if($lx['lname']==$lname){
			$lxlist .= "<option selected=\"selected\"  value=\"$lx[lname]\">$lx[lname]</option><optgroup style=\"padding-left:21px\">";
		}else{
			$lxlist.="<option value='$lx[id]'>$lx[lname]</option><optgroup style=\"padding-left:21px\">";
		}
		$sql_xleixing = $DB->query("SELECT id as xid,lname,parent_id FROM `leixing` WHERE  parent_id!='0'  AND act='1'");
		while($xlx = $DB->fetch_assoc($sql_xleixing))
		{
			if($lx['id']==$xlx['parent_id']){
				if($xlx['lname']==$lname){
					
					$lxlist.="<option value='$xlx[lname]' selected=\"selected\">$xlx[lname]</option>";
				}else{
					$lxlist.="<option value='$xlx[lname]'>$xlx[lname]</option>";
				}
			}
		}
		$lxlist.="</optgroup>";
	}
	return $lxlist;
}
function get_syleixing($lxid='',$bs=''){
	if($bs!='123'){
	$lxlist .= "<option selected=\"selected\"  value=\"$lx[id]\">请选择</option>";}
	global $DB;
	$fzx_id= FZX_ID;//中心
	$sql_leixing = $DB->query("SELECT id,lname FROM `leixing` WHERE parent_id='0' AND act='1'");
	while($lx = $DB->fetch_assoc($sql_leixing))
	{
		
		if($lx['id']==$lxid){
			$lxlist .= "<option selected=\"selected\"  value=\"$lx[id]\">$lx[lname]</option><optgroup style=\"padding-left:21px\">";
		}else{
			$lxlist.="<option value='$lx[id]'>$lx[lname]</option><optgroup style=\"padding-left:21px\">";
		}
		$sql_xleixing = $DB->query("SELECT id as xid,lname,parent_id FROM `leixing` WHERE  parent_id!='0' AND fzx_id=$fzx_id AND act='1'");
		while($xlx = $DB->fetch_assoc($sql_xleixing))
		{
			if($lx['id']==$xlx['parent_id']){
				if($xlx['xid']==$lxid){
					
					$lxlist.="<option value='$xlx[xid]' selected=\"selected\">$xlx[lname]</option>";
				}else{
					$lxlist.="<option value='$xlx[xid]'>$xlx[lname]</option>";
				}
			}
		}
		$lxlist.="</optgroup>";
	}
	return $lxlist;
}
/**
*   输出模板文件
*   edited by Mr Zhou
*   增加$trade_global数组的输出 该数组中定义了html初始化时用到的内容 如果是数组格式则转为json格式
*/
function disp($temp='',$head='',$bottom='')
{   
	global $trade_global;
	if(is_array($trade_global)){
		$trade_global = json_encode($trade_global);
	}
	$head = (''==$head) ? 'head':$head;
	$bottom = (''==$bottom) ? 'bottom':$bottom;
	echo temp($head);
	if($temp!='') echo temp($temp);
	if($temp1!='') echo temp($temp1);
	echo temp($bottom);
	toexit();
}
//的到某个站点最近5次化验数据  在审核时候 好对比 。 $sid 是站点id  $gs 是 获取的 数据的个数  vid 是 化验项目id
function get_vd0_desc($sid,$vid,$gs='5') {
	global $DB;
$sql="SELECT vd0,modify_time   FROM `assay_order` WHERE `sid` ='$sid' and  `vid` ='$vid'  and `hy_flag`>-1 ORDER BY `id`  DESC  LIMIT $gs";
$lx=$DB->query($sql);
while($r=$DB->fetch_assoc($lx))
{
$sj=date('Y-m-d',strtotime($r['modify_time']));
$vd0desc.="<br />$sj ：$r[vd0] ";
}
return $vd0desc;
}
//给出pdf 地址 返回 pdf 的数据 数组
  function pdftoarr($file){
	exec("pdftotext $file  /tmp/pdf.txt ");
	exec('cat /tmp/pdf.txt ',$arr);
	return $arr;
	}
//得到所有的化验员
function get_all_hy_user() {
	global $DB;
	$fzx_id= FZX_ID;//中心
	$sql = "SELECT * FROM users WHERE fzx_id =$fzx_id AND `group` LIKE '%化验员%'";
	$res = $DB->query( $sql );
	$result = array();
	while( $row = $DB->fetch_assoc( $res ) )
		$result[] = $row['userid'];
	return $result;
}

function get_user( $uid ) {
	global $DB;
	return $DB->fetch_one_assoc( "SELECT * FROM users WHERE id = $uid " );
}
/*
 *快捷获得不同分组下的关联分量
 *功    能：传入要搜索的字符串和被搜索的字符串，返回被搜索字符串中 在同样分类下的分量
 *格式限制：被搜索的字符串格式： "|&分量1&分量2&分量3&|&分量2&分量4&分量5&|"（不同分类用|隔开，不同分量用&隔开）
 *例    子：传入 分量1 ==>返回 array('分量1','分量2','分量3');传入 分量2 ==>返回 array('分量1','分量2','分量3','分>量4','分量5');
 */
function in_str($string,$search){
    if(empty($string) || empty($search)){
        return false;
    }
    //从传入被搜索字符串($search)中查找分隔符中间是否存在 要搜索的字符串（$string）
    if(stristr($search,"&{$string}&")){
        $tmp_related_value_arr  = explode('|',trim($search,'|'));
        $chongfu_arr    = array();
        //循环每个 分隔符“|” 中间，是否存在 要搜索的字符串（$string）
        foreach ($tmp_related_value_arr as $key => $value) {
            if(stristr($value,"&{$string}&")){
                $tmp_related_value  = explode('&', trim($value,'&'));
                foreach ($tmp_related_value as $key => $value) {
                    if(!in_array($value,$chongfu_arr)){
                        $chongfu_arr[]  = $value;
                    }
                }
            }
        }
        return $chongfu_arr;
    }else{
        return false;
    }
}
#######################获得所有方法
function get_fangfa($fangfa='',$act_fangfa=''){
	//print_rr($act_fangfa);
	global $DB;
	$res = $DB->fetch_one_assoc("SELECT count(id) AS sl FROM `assay_method` WHERE id='$fangfa'");
	if($res[sl]==''||$res[sl]=='0'||$res[sl]==NULL){
		$valuefangfa .= "<option  value='' title='未设置' selected=\"selected\">未设置</option>";
	}
			$sql_assay_value = $DB->query("SELECT * FROM `assay_method` ORDER BY method_name ");
			while($rs_assay_value = $DB->fetch_assoc($sql_assay_value))
			{
				
				if($fangfa==$rs_assay_value['id']){
					$valuefangfa .= "<option  value='".$rs_assay_value['id']."' title='".$rs_assay_value['id']."' selected=\"selected\">".$rs_assay_value['method_number'].'['.$rs_assay_value['method_name'].']'."</option>";
				}else{
					$valuefangfa .= "<option  value='".$rs_assay_value['id']."' title='".$rs_assay_value['id']."' >".$rs_assay_value['method_number'].'['.$rs_assay_value['method_name'].']'."</option>";
				}
			}
		return $valuefangfa;
}
##############获得启用的方法
function get_act_fangfa($xmid='',$fangfa='',$lx){
	global $DB;
	$fzx_id= FZX_ID;//中心
	$res = $DB->fetch_one_assoc("SELECT count(id) AS sl FROM `assay_method` WHERE id='$fangfa'");
	if($res[sl]==''||$res[sl]=='0'||$res[sl]==NULL){
		$valuefangfa .= "<option  value='' title='未设置' selected=\"selected\">未设置</option>";
	}
	$sql_assay_value = $DB->query("SELECT * FROM `xmfa` JOIN assay_method ON xmfa.fangfa= assay_method.id  WHERE xmid='$xmid' AND lxid='$lx' AND act='1' AND fzx_id =$fzx_id ORDER BY method_name ");
	while($rs_assay_value = $DB->fetch_assoc($sql_assay_value))
	{
		if($fangfa==$rs_assay_value['id']){
			$valuefangfa .= "<option  value='".$rs_assay_value['id']."' title='".$rs_assay_value['id']."' selected=\"selected\">".$rs_assay_value['method_number'].'['.$rs_assay_value['method_name'].']'."</option>";
		}else{
			$valuefangfa .= "<option  value='".$rs_assay_value['id']."' title='".$rs_assay_value['id']."' >".$rs_assay_value['method_number'].'['.$rs_assay_value['method_name'].']'."</option>";
		}
	}
	return $valuefangfa;
}
/*
#######################获得所有模板
function get_bgline($fangfa=''){
	global $DB;
	$res = $DB->fetch_one_assoc("SELECT count(id) AS sl FROM `assay_method` WHERE id='$fangfa'");
	if($res[sl]==''|$res[sl]=='0'|$res[sl]==NULL){
		$valuefangfa .= "<option  value='' title='未设置' selected=\"selected\">未设置</option>";
	}
			
	
	$table_name = $DB->query("SELECT id,table_cname FROM `bt` WHERE `fzx_id` = $fzx_id ORDER BY CONVERT( `table_cname` USING gbk ) ASC ");
	while($r=$DB->fetch_assoc($table_name))
	{
		$bgnames[$r['id']] = $r['table_cname'];
	}
			
			
			$sql_assay_value = $DB->query("SELECT * FROM `assay_method` ORDER BY method_name ");
			while($rs_assay_value = $DB->fetch_assoc($sql_assay_value))
			{
				
				if($fangfa==$rs_assay_value['id']){
					$valuefangfa .= "<option  value='".$rs_assay_value['id']."' title='".$rs_assay_value['id']."' selected=\"selected\">".$rs_assay_value['method_number'].'['.$rs_assay_value['method_name'].']'."</option>";
				}else{
					$valuefangfa .= "<option  value='".$rs_assay_value['id']."' title='".$rs_assay_value['id']."' >".$rs_assay_value['method_number'].'['.$rs_assay_value['method_name'].']'."</option>";
				}
			}
		return $valuefangfa;
}*/
#######################获得所有的仪器
function get_yiqi($yiqi=''){
	global $DB;
	$fzx_id= FZX_ID;//中心
	$res = $DB->fetch_one_assoc("SELECT count(id) AS sl FROM `yiqi` WHERE fzx_id ='$fzx_id'  AND id='$yiqi'");
	if($res[sl]==''||$res[sl]=='0'||$res[sl]==NULL){
			$valueyiqi .= "<option  value='' selected=\"selected\">未设置</option>";
	}else{$valueyiqi .= "<option  value=''>未设置</option>";}
	
			$sql_value_yiqi = $DB->query("SELECT * FROM `yiqi` WHERE fzx_id='$fzx_id' ORDER BY id");
			while($rs_value_yiqi=$DB->fetch_assoc($sql_value_yiqi))
			{
				
				if($yiqi==$rs_value_yiqi['id']){
					$valueyiqi .= "<option  value='".$rs_value_yiqi['id']."' selected=\"selected\">".$rs_value_yiqi['yq_mingcheng'].'('.$rs_value_yiqi['yq_xinghao'].')'."</option>";
				}else{
				$valueyiqi .= "<option  value='".$rs_value_yiqi['id']."'>".$rs_value_yiqi['yq_mingcheng'].'('.$rs_value_yiqi['yq_xinghao'].')'."</option>";}
			}
	
			return $valueyiqi;
}
#######################获得有化验权限的人员
function get_hyqx_user($user=''){
	global $DB;
	$fzx_id= FZX_ID;//中心
	$res = $DB->fetch_one_assoc("SELECT count(id) AS sl FROM users WHERE fzx_id =$fzx_id AND hua_yan='1' AND id='$user'");
	if($res[sl]==''||$res[sl]=='0'||$res[sl]==NULL){
			$valueuser .= "<option  value='' selected=\"selected\">未设置</option>";
	}else{$valueuser .= "<option  value='' >未设置</option>";}
	$res = $DB->query("SELECT * FROM users WHERE fzx_id =$fzx_id AND `group`!='0' AND hua_yan='1'");
	$result = array();
	while( $row = $DB->fetch_assoc( $res ) ){
		
		if($user==$row['id']){
			$valueuser .= "<option  value='".$row['id']."' selected=\"selected\">".$row['userid']."</option>";
		}else{
			$valueuser .= "<option  value='".$row['id']."'>".$row['userid']."</option>";
		}
	}
	return $valueuser;
			
}
// 传入数组返回 json数据（对中文和特殊字符进行特殊处理）
function JSON($array,$addslashes=false) {
    //如果特殊情况下，已经将json数组转义处理过，可以跳过此流程
    if($addslashes == false){
        //对json数组进行特殊转义，先进行特殊符号的全部转义（防止数据库插入错误），再对双引号和反斜线再次进行转义（防止json_decode转换时出现错误）
        $array  = JSON_addslashes($array);
    }
    //将数组中的中文转换成16进制，并在前面增加标识符%。防止中文在转换时出错
    arrayRecursive($array, 'urlencode', true);
    //将数组转换为json字符串
    $json   = json_encode($array);
    //再将转换成16进制的中文转换回 中文状态
    return urldecode($json);
}
/*
 *对json的字符串的 双引号和反斜线再次进行转义，防止json_decode函数将json字符串转换为数组时出错
 *传入参数：$array json转义之前的数组,$addslashes传入的数组是否已经被php转义过一次
 *传出参数：对双引号、反斜线 转以后的数组
 *原理：数据库插入时，需要反转义一次。json_decode使用时，除单引号外，其他特殊字符也需要反转义一次
 */
function JSON_addslashes($array,$addslashes=false){
    if(is_array($array)){
        foreach ($array as $key => $value) {
            $array[$key]    = JSON_addslashes($value,$addslashes);
        }
        return $array;
        //return array_map('JSON_addslashes', $array);
    }else{
        //对双引号和反斜线进行两次转义
        if($addslashes == false){
            //将一条反斜线转换成4条反斜线,双引号变成3条反斜线+双引号，单引号变成一条反斜线+单引号，null不处理
            return str_replace(array("\r\n","\\","\"","'"),array("<br>","\\\\\\\\","\\\\\\\"","\\'"),$array);
        }else{
            //除单引号外，再次进行转义
            return addslashes(str_replace(array("\r\n","\'"),array("<br>","'"),$array));
        }
    }
}

 /**************************************************************
	 *
	 *  使用特定function对数组中所有元素做处理
	 *  @param  string  &$array     要处理的字符串
	 *  @param  string  $function   要执行的函数
	 *  @return boolean $apply_to_keys_also     是否也应用到key上
	 *  @access public
	 *
	 *************************************************************/

  function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
	{
		static $recursive_counter = 0;
		if (++$recursive_counter > 1000) {
			die('possible deep recursion attack');
		}
		if($array=='')
		$array=array();
		foreach ((array)$array as $key => $value) {
			if (is_array($value)) {
				arrayRecursive($array[$key], $function, $apply_to_keys_also);
			} else {
				$array[$key] = $function($value);
			}
	  
			if ($apply_to_keys_also && is_string($key)) {
				$new_key = $function($key);
				if ($new_key != $key) {
					$array[$new_key] = $array[$key];
					unset($array[$key]);
				}
			}
		}
		$recursive_counter--;
	}
//系统的水样类型对应的字母，返回数组
function get_all_wtbh(){
	global $DB;
	$wtbh = array();
	$allwt = $DB->query("select * from leixing where bar_code_mark<>''");
	while($wt = $DB->fetch_assoc($allwt)){
		$wtbh[$wt['id']] = $wt['bar_code_mark'];
	}
	return $wtbh;
}
/*
 * 根据不同数据表结构统计获取电子签名信息
 *传入参数：$table_name数据表名称、$fields_name签名的字段名称（可以是字符串和数组）、$id数据表的id，$old_arr需要直接替换数组中的签名为电子签名
 *传出结果：带有html标签（img标签）的电子签名图片或者空
*/
function get_userid_img ($table_name,$fields_name,$id,$old_arr=array()){
    global $DB,$rooturl;
    if(!empty($table_name) || !empty($fields_name) || !empty($id)){
        //将字段转换为sql获取的内容
        if(is_array($fields_name)){
            $select_fields  = "`".implode("`,`",$fields_name)."`,`json`";//,`".implode("_img`,`",$fields_name)."`";
        }else{
            $select_fields  = " `{$fields_name}`,`json`";//`{$fields_name}_img` ";
            $fields_name[]  = $fields_name;
        }
        //获取对应的签名信息，以及json信息
        $rs_fields    = $DB->fetch_one_assoc("SELECT {$select_fields} FROM `{$table_name}` WHERE `id`='{$id}'");
        if(!empty($rs_fields['json']) && stristr($rs_fields['json'],"userid_img")){
            $rs_fields['json_arr']  = json_decode($rs_fields['json'],true);
        }
        //第4个参数传入数组时，直接将数组中的文字签名替换为电子签名（键名都是字段名称）
        if(!empty($old_arr)){
            $return_userid_img  = $old_arr;
        }else{
            $return_userid_img  = array();
        }
        $json_modify        = "no";
        foreach($fields_name as $value_field){
            //如果文字签名和电子签名同时存在，才将电子签名显示出来
            if(!empty($rs_fields[$value_field]) && !empty($rs_fields['json_arr']['userid_img'][$value_field])){
                $return_userid_img[$value_field]    = "<img class='userid_img' style='width:90px;height:40px;' src='{$rooturl}{$rs_fields['json_arr']['userid_img'][$value_field]}' />";
            }else{
                $return_userid_img[$value_field]    = $rs_fields[$value_field];
                ###########以下为历史数据测试时使用
                /*if(!empty($rs_fields[$value_field])){
                    //检查是否有重名情况
                    $query_user = $DB->query("SELECT `userid_img` FROM `users` WHERE `userid`='{$rs_fields[$value_field]}'");
                    $num_rows   = $DB->num_rows($query_user);
                    $new_userid_img = $DB->fetch_one_assoc("SELECT `userid_img` FROM `users` WHERE `userid`='{$rs_fields[$value_field]}' LIMIT 1");
                    if($num_rows=='1' && !empty($new_userid_img['userid_img'])){
                        //将电子签名插入到数据库里
                        $json_modify    = 'yes';
                        $rs_fields['json_arr']['userid_img'][$value_field]  = $new_userid_img['userid_img'];
                        $return_userid_img[$value_field]    = "<img class='userid_img' style='width:90px;height:40px;' src='{$rooturl}{$new_userid_img['userid_img']}' />";
                    }else if($num_rows>'1'){
                        $return_userid_img["{$value_field}_error"]  = "有重名情况";
                    }
                }*/
                #############
            }
        }
        if($json_modify == 'yes'){
            $DB->query("UPDATE `{$table_name}` SET `json`='".JSON($rs_fields['json_arr'])."' WHERE `id`='{$id}'");
        }
        return $return_userid_img;
    }else{
        return '确少必要参数';
    }
}
/*
 *根据cid来判断该站点的检测结果，阴阳离子平衡是否合理
 *传入参数：cy_rec表的id
 *传出参数：arr(
[1]=>"各项目以及对应的结果值",
[2]=>"判定结果（是否合理）",
[3]=>根据标准的计算结果,
[4]=>"判断依据",
)
*/
function ion_balance($cy_rec_id,$color=true){
	global $DB,$rooturl;
	if($cy_rec_id > 0){
		$panding_yiju	= "10";//判断依据，标准上是10%
		//初始化返回的数组，应包含每个项目的结果//以下项目暂未添加125总碱度、181氟化物、154铁、157锰
		$return_arr	= array('172'=>array("count_value"=>"39.0983","old_vd0"=>"不检测","count_ion"=>"0"),	//钾172
							'162'=>array("count_value"=>"22.98976928","old_vd0"=>"不检测","count_ion"=>"0"),//钠162
							'173'=>array("count_value"=>"20.039","old_vd0"=>"不检测","count_ion"=>"0"),		//173钙
							'174'=>array("count_value"=>"12.1525","old_vd0"=>"不检测","count_ion"=>"0"),	//174镁
							'182'=>array("count_value"=>"35.453","old_vd0"=>"不检测","count_ion"=>"0"),		//182氯化物
							'190'=>array("count_value"=>"48.0313","old_vd0"=>"不检测","count_ion"=>"0"),	//190硫酸根
							'186'=>array("count_value"=>"14.0067","old_vd0"=>"不检测","count_ion"=>"0"),	//186硝酸盐氮 //先换算到硝酸根在进行计算原子量
							'655'=>array("count_value"=>"62.0049","old_vd0"=>"不检测","count_ion"=>"0"),	//655硝酸根离子
							'189'=>array("count_value"=>"30.00445","old_vd0"=>"不检测","count_ion"=>"0"),	//189碳酸盐
							'188'=>array("count_value"=>"61.01684","old_vd0"=>"不检测","count_ion"=>"0"),	//188重碳酸盐
							'125'=>array("count_value"=>"100.0869","old_vd0"=>"不检测","count_ion"=>"0"),//125总碱度
							);
		$yang_vid		= array('172','162','173','174');//阴离子的vid//$yin_vid		= array('182','190','186','655','189','188');//阳离子的vid
		$important_vid	= array_flip(array('172','162','173','174','182','190','655','189','188'));//这里的离子缺少一个，就不能进行合理性判断
		$yang_count_ion	= $yin_count_ion	= array();
		$sql_where		= " AND `vid` in (".implode(',',array_keys($return_arr)).") ";
		//去assay_order表里查询数据
		$ion_select		= $DB->query("SELECT * FROM `assay_order` WHERE `cid`='{$cy_rec_id}' $sql_where AND `hy_flag`>=0 AND `sid`>0 ");
		//判断项目检测是否包含8大离子，如果不包含就直接返回结果
		while($ion_rs	= $DB->fetch_assoc($ion_select)){
			//有平均值取平均值
			$vd0		= !empty($ion_rs['ping_jun'])?$ion_rs['ping_jun']:$ion_rs['vd0'];
			$old_vd0	= $vd0;
			if(empty($old_vd0) && $old_vd0!='0'){
				$old_vd0= '未检测';
				$vd0	= '0';
			}else if(stristr($vd0,"<")){//小于检出限时，用0参与计算//用原始值参与计算
				$vd0	= '0';//$ion_rs['_vd0'];
			}
			if($ion_rs['vid'] == '186'){//硝酸盐氮转换为硝酸根离子
				$round_num	= stristr($vd0,'.')?strlen(explode('.',$vd0)[1]):0;
				$ion_rs['vid']	= '655';//转换为硝酸根离子
				if($old_vd0 != '未检测'){
					$old_vd0	= _round($vd0*62/14,$round_num);//返回硝酸根的值
				}
				$return_arr[$ion_rs['vid']]['count_ion']= $vd0/$return_arr['186']['count_value'];
			}else if($ion_rs['vid'] == '125'){//将总碱度转换为总碳酸盐
				$round_num	= stristr($vd0,'.')?strlen(explode('.',$vd0)[1]):0;
				$vd0	= $vd0*(61.09/50.04);
				$return_arr[$ion_rs['vid']]['count_ion']= $vd0/61.09;
				$return_arr[$ion_rs['vid']]['change_vd0']	= _round($vd0,$round_num);//返回重碳酸根的值
			}else{
				$return_arr[$ion_rs['vid']]['count_ion']= $vd0/$return_arr[$ion_rs['vid']]['count_value'];
			}
			$return_arr[$ion_rs['vid']]['tid']		= $ion_rs['tid'];
			$return_arr[$ion_rs['vid']]['old_vd0']	= ($color==true)?"<a  target='_blank' href='$rooturl/huayan/assay_form.php?tid={$ion_rs['tid']}'>{$old_vd0}</a>":$old_vd0;
			if(in_array($ion_rs['vid'], $yang_vid)){//阳离子结果
				$yang_count_ion[$ion_rs['vid']]	= $return_arr[$ion_rs['vid']]['count_ion'];
			}else{//阴离子结果
				$yin_count_ion[$ion_rs['vid']]	= $return_arr[$ion_rs['vid']]['count_ion'];
			}
			if($old_vd0!='未检测' && array_key_exists($ion_rs['vid'],$important_vid)){
				unset($important_vid[$ion_rs['vid']]);
			}
		}
		if(empty($return_arr['189']['count_ion']) && empty($return_arr['188']['count_ion'])){
			$return_arr['188']		= $return_arr['125'];
			$return_arr['188']['old_vd0']	= $return_arr['125']['change_vd0'];
			unset($important_vid['188']);
			unset($important_vid['189']);
		}
		//以下算法的原始值已经进行了修约，不好
		$yin	= array_sum($yin_count_ion);
		$yang	= array_sum($yang_count_ion);
		$return_arr['sum_yin']			= $yin;//阴离子加和
		$return_arr['sum_yang']			= $yang;//阳离子加和
		$return_arr['sum_yin_xiuyue']	= _round($yin,2);
		$return_arr['sum_yang_xiuyue']	= _round($yang,2);
		
		@$result= ($yin - $yang)*100/($yin+$yang);
		$return_arr['count_result']	= _round($result,2)."%";
		//按照计算公式进行判定//合理性判断，10%
		$return_arr['panding_yiju']	= "（∑阴离子毫摩尔-∑阳离子毫摩尔）*100%/(∑阴离子毫摩尔+∑阳离子毫摩尔) <±10%";//#15094
		if(empty($important_vid)){
			if(abs($result) < $panding_yiju){
				$return_arr['result']	= ($color ==true)?"<font style='color:#428BCA;'>合理</font>":"合理";
			}else{
				$return_arr['result']	= ($color ==true)?"<font style='color:red;'>不合理</font>":"不合理";//不加颜色处理，会影响不同调用页面的布局
			}
		}else{
			$return_arr['result']	= '数据不充分';//"缺少必要离子的检测值";
		}
		/*   一次性修约的公式，如果需要可以考虑优化
			//(((K浓度/39.0983)+(Na浓度/22.98976928)+(Ca浓度/20.039)+(Mg浓度/12.1525))-((Cl浓度/35.453)+(SO4浓度/48.0313)+(NO3N浓度/14.0067)+(HCO3浓度/61.01684)+(CO3浓度/30.00445)))/
			//(((K浓度/39.0983)+(Na浓度/22.98976928)+(Ca浓度/20.039)+(Mg浓度/12.1525))+((Cl浓度/35.453)+(SO4浓度/48.0313)+(NO3N浓度/14.0067)+(HCO3浓度/61.01684)+(CO3浓度/30.00445)))
			$yang	= ($return_arr['172']['vd0']/39.0983)+($return_arr['162']['vd0']/22.98976928)+($return_arr['173']['vd0']/20.039)+($return_arr['174']['vd0']/12.1525);
			if(!empty($return_arr['665']['vd0'])){//硝酸盐
				$yin	= ($return_arr['182']['vd0']/35.453)+($return_arr['190']['vd0']/48.0313)+($return_arr['665']['vd0']/62.0049)+($return_arr['188']['vd0']/61.01684)+($return_arr['189']['vd0']/30.00445);
			}else{//硝酸盐氮
				$yin	= ($return_arr['182']['vd0']/35.453)+($return_arr['190']['vd0']/48.0313)+($return_arr['186']['vd0']/14.0067)+($return_arr['188']['vd0']/61.01684)+($return_arr['189']['vd0']/30.00445);
			}*/
	}else{
		$return_arr['result']	= "id有问题";
	}
	//组成数组返回结果
	return $return_arr;
}
/*
 *根据cid来判断该站点的检测结果，溶解性总固体是否合理
 *传入参数：cy_rec表的id
 *传出参数：arr(
[1]=>"各项目以及对应的结果值",
[2]=>"判定结果（是否合理）",
[3]=>根据标准的计算结果,
[4]=>"判断依据",
)
*/
function solid_balance($cy_rec_id,$color=true){
	global $DB,$rooturl;
	if($cy_rec_id > 0){
		$panding_yiju   = "8";//判断依据，标准上是10%
		//初始化返回的数组，应包含每个项目的结果//以下项目暂未添加125总碱度、181氟化物、154铁、157锰
		$return_arr = array(
							'103'=>array("count_value"=>"","old_vd0"=>"不检测","count_ion"=>"0"),      //103总硬度
							'173'=>array("count_value"=>"20.039","old_vd0"=>"不检测","count_ion"=>"0"),        //173钙
							'125'=>array("count_value"=>"100.0869","old_vd0"=>"不检测","count_ion"=>"0"),//125总碱度
							'182'=>array("count_value"=>"35.453","old_vd0"=>"不检测","count_ion"=>"0"),        //182氯化物
							'190'=>array("count_value"=>"48.0313","old_vd0"=>"不检测","count_ion"=>"0"),   //190硫酸根
							'186'=>array("count_value"=>"14.0067","old_vd0"=>"不检测","count_ion"=>"0"),   //186硝酸盐氮 //先换算到硝酸根在进行计算原子量
							'193'=>array("count_value"=>"","old_vd0"=>"不检测","count_ion"=>"0"),   //193二氧化硅
							'197'=>array("count_value"=>"","old_vd0"=>"不检测","count_ion"=>"0"),   //197二氧化硅
							'174'=>array("count_value"=>"12.1525","old_vd0"=>"不检测","count_ion"=>"0"),   //174镁
							'162'=>array("count_value"=>"22.98976928","old_vd0"=>"不检测","count_ion"=>"0"),//钠162
							'100'=>array("count_value"=>"","old_vd0"=>"不检测","count_ion"=>"0"),//100溶解性总固体
							);
		$important_vid  = array_flip(array('103','173','125','182','190','186','193','174','162','100'));//这里的离子缺少一个，就不能进行合理性判断
		$solid_count_ion = array();
		$sql_where      = " AND `vid` in (".implode(',',array_keys($return_arr)).") ";
		//去assay_order表里查询数据
		$ion_select     = $DB->query("SELECT * FROM `assay_order` WHERE `cid`='{$cy_rec_id}' $sql_where AND `hy_flag`>=0 AND `sid`>0 ");
		//判断项目检测是否包含8大离子，如果不包含就直接返回结果
		while($ion_rs   = $DB->fetch_assoc($ion_select)){
			$return_arr['bar_code'] = $ion_rs['bar_code'];
			//有平均值取平均值
			$vd0        = !empty($ion_rs['ping_jun'])?$ion_rs['ping_jun']:$ion_rs['vd0'];
			$old_vd0    = $vd0;
			if(empty($old_vd0) && $old_vd0!='0'){
				$old_vd0= '未检测';
				$vd0    = '0';
			}else if(stristr($vd0,"<")){//小于检出限时，用0参与计算//用原始值参与计算
				$vd0    = '0';//$ion_rs['_vd0'];
			}
			if($ion_rs['vid'] == '186'){//硝酸盐氮转换为硝酸根离子
				$round_num  = stristr($vd0,'.')?strlen(explode('.',$vd0)[1]):0;
				$return_arr[$ion_rs['vid']]['count_ion']= _round(62.01*$vd0/14.01,$round_num);
			}else if($ion_rs['vid'] == '125'){
				$round_num  = stristr($vd0,'.')?strlen(explode('.',$vd0)[1]):0;
				$return_arr[$ion_rs['vid']]['count_ion']= _round((61*(60/122)*$vd0/50.04),$round_num);
			}else if($ion_rs['vid'] == '197'){//偏硅酸代替二氧化硅（兰州提出共性）
				$ion_rs['vid']  = '193';
				$return_arr[$ion_rs['vid']]['count_ion']= $vd0;
			}else{
				$return_arr[$ion_rs['vid']]['count_ion']= $vd0;
			}
			$return_arr[$ion_rs['vid']]['tid']      = $ion_rs['tid'];
			$return_arr[$ion_rs['vid']]['old_vd0']  = ($color==true)?"<a  target='_blank' href='$rooturl/huayan/assay_form.php?tid={$ion_rs['tid']}'>{$old_vd0}</a>":$old_vd0;
			$return_arr[$ion_rs['vid']]['vd0']      = $vd0;
			if($ion_rs['vid'] != '100' && $ion_rs['vid'] != '103'){
				$solid_count_ion[$ion_rs['vid']]  = $return_arr[$ion_rs['vid']]['count_ion'];
			}
			if($old_vd0!='未检测' && array_key_exists($ion_rs['vid'],$important_vid)){
				unset($important_vid[$ion_rs['vid']]);
			}
		}
		//以下算法的原始值已经进行了修约，不好
		if(!empty($return_arr['100']['old_vd0'])){
			$lilun_solid    = array_sum($solid_count_ion);//理论溶固体值
			$shi_solid  = $return_arr['100']['vd0'];
			$result= ($lilun_solid - $shi_solid)*100/($shi_solid+0);//化学偏差
			$return_arr['count_result'] = _round($result,1)."%";
			$return_arr['result_num'] = $lilun_solid;
			$min_divide	= (100 - $panding_yiju)/100;
			$max_divide	= (100 + $panding_yiju)/100;
			$return_arr['min_result'] = _round(($lilun_solid*$min_divide),0);//."~"._round(($lilun_solid/1.05),0);
			$return_arr['max_result'] = _round(($lilun_solid*$max_divide),0);//_round((1.05*$lilun_solid),0)."~"._round(($lilun_solid/0.95),0);
		}
		//按照计算公式进行判定//合理性判断，5%
		$return_arr['panding_yiju'] = "（理论溶固体值-实际溶固体值）*100%/(实际溶固体值) <±{$panding_yiju}%";//#15094
		if(empty($important_vid) || (count($important_vid)==1 && $important_vid['193']!='')){
			if(abs($result) < $panding_yiju){
				$return_arr['result']   = ($color ==true)?"<font style='color:#428BCA;'>合理</font>":"合理";
			}else{
				$return_arr['result']   = ($color ==true)?"<font style='color:red;'>不合理</font>":"不合理";//不加颜色处理，会影响不同调用页面的布局
			}
		}else{
			$return_arr['result']   = '数据不充分';//"缺少必要离子的检测值";
		}
	}else{
		$return_arr['result']   = "id有问题";
	}
	//组成数组返回结果
	return $return_arr;
}
/*
 *根据cid来判断该站点的检测结果，总硬度是否合理
 *传入参数：cy_rec表的id
 *传出参数：arr(
[1]=>"各项目以及对应的结果值",
[2]=>"判定结果（是否合理）",
[3]=>根据标准的计算结果,
[4]=>"判断依据",
)
//理论总硬度=（钙/20+镁/12）*50.04
//当（实际总硬度-理论总硬度）*100%/（理论总硬度 ）＜±10%时，判定为合理
*/
function zongyingdu_balance($cy_rec_id,$color=true){
	global $DB,$rooturl;
	if($cy_rec_id > 0){
		$panding_yiju	= "10";//判断依据，标准上是10%
		//初始化返回的数组，应包含每个项目的结果
		$return_arr	= array(
							'173'=>array("count_value"=>"20.039","old_vd0"=>"不检测","count_ion"=>"0"),		//173钙
							'174'=>array("count_value"=>"12.1525","old_vd0"=>"不检测","count_ion"=>"0"),	//174镁
							'103'=>array("count_value"=>"","old_vd0"=>"不检测","count_ion"=>"0"),      //103总硬度
							);
		$important_vid  = array_flip(array('173','174','103'));//这里的离子缺少一个，就不能进行合理性判断
		$sql_where      = " AND `vid` in (".implode(',',array_keys($return_arr)).") ";
		//去assay_order表里查询数据
		$ion_select     = $DB->query("SELECT * FROM `assay_order` WHERE `cid`='{$cy_rec_id}' $sql_where AND `hy_flag`>=0 AND `sid`>0 ");
		//判断项目检测是否包含8大离子，如果不包含就直接返回结果
		while($ion_rs   = $DB->fetch_assoc($ion_select)){
			$return_arr['bar_code'] = $ion_rs['bar_code'];
			//有平均值取平均值
			$vd0        = !empty($ion_rs['ping_jun'])?$ion_rs['ping_jun']:$ion_rs['vd0'];
			$old_vd0    = $vd0;
			if(empty($old_vd0) && $old_vd0!='0'){
				$old_vd0= '未检测';
				$vd0    = '0';
			}else if(stristr($vd0,"<")){//小于检出限时，用0参与计算//用原始值参与计算
				$vd0    = '0';//$ion_rs['_vd0'];
			}
			$return_arr[$ion_rs['vid']]['count_ion']= $vd0;
			$return_arr[$ion_rs['vid']]['tid']      = $ion_rs['tid'];
			$return_arr[$ion_rs['vid']]['old_vd0']  = ($color==true)?"<a  target='_blank' href='$rooturl/huayan/assay_form.php?tid={$ion_rs['tid']}'>{$old_vd0}</a>":$old_vd0;
			$return_arr[$ion_rs['vid']]['vd0']      = $vd0;
			if($old_vd0!='未检测' && array_key_exists($ion_rs['vid'],$important_vid)){
				unset($important_vid[$ion_rs['vid']]);
			}
		}
		//如果钙镁均有，计算理论总硬度
		$lilun_yingdu	= '';
		if(!array_key_exists('173', $important_vid) && !array_key_exists('174', $important_vid)){
			$lilun_yingdu	= ($return_arr['173']['vd0']/$return_arr['173']['count_value']+$return_arr['174']['vd0']/$return_arr['174']['count_value'])*50.04;//理论总硬度=（钙/20+镁/12）*50.04
			$lilun_yingdu	= _round($lilun_yingdu,2);
			$return_arr['lilun_yingdu']	= $lilun_yingdu;//理论总硬度
			$return_arr['lilun_yingdu_xiangxigongshi']	= "({$return_arr['173']['vd0']}/{$return_arr['173']['count_value']})+{$return_arr['174']['vd0']}/{$return_arr['174']['count_value']})*50.04";//详细计算公式
		}
		$return_arr['lilun_yingdu_gongshi']	= "(钙/{$return_arr['173']['count_value']})+镁/{$return_arr['174']['count_value']})*50.04";//表头显示公式
		//按照计算公式进行判定
		$return_arr['panding_yiju'] = "（（实际总硬度-理论总硬度）*100%/（理论总硬度 ）＜±{$panding_yiju}%";
		if(empty($important_vid)){
			$shiji_yingdu	= $return_arr['103']['vd0'];//实际总硬度
			$result= ($shiji_yingdu - $lilun_yingdu)*100/($lilun_yingdu+0);//总硬度校核结果
			$return_arr['count_result'] = _round($result,1)."%";
			if(abs($result) < $panding_yiju){
				$return_arr['result']   = ($color ==true)?"<font style='color:#428BCA;'>合理</font>":"合理";
			}else{
				$return_arr['result']   = ($color ==true)?"<font style='color:red;'>不合理</font>":"不合理";//不加颜色处理，会影响不同调用页面的布局
			}
		}else{
			$return_arr['result']   = '数据不充分';//"缺少必要离子的检测值";
		}
	}else{
		$return_arr['result']   = "id有问题";
	}
	//组成数组返回结果
	return $return_arr;
}
//根据base64_encode加密的方式，加密成url可传递的字符串
function urlsafe_b64encode($string) {
	$data = base64_encode($string);
	$data = str_replace(array('+','/','='),array('-','_',''),$data);
	return $data;
}
//针对上面函数的加密，而写的解密函数
function urlsafe_b64decode($string) {
	$data = str_replace(array('-','_'),array('+','/'),$string);
	$mod4 = strlen($data) % 4;
	if ($mod4) {
		$data .= substr('====', $mod4);
	}
	return base64_decode($data);
}
?>
