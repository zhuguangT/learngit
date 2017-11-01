<?php
/**
 * 功能：用户登录
 * 作者: Mr Zhou
 * 日期: 2014-03-24
 * 描述
*/
//本页面不检查是否已登录
$checkLogin = false;
require "temp/config.php";
/*
	加一个判断是否已经登陆 如果已经登陆 则提示跳转至首页 或者提示退出登陆 重新登陆 
	否则下面的判断会退出登陆

	需要添加页面验证
*/

if(isset($_SESSION['u']['userid'])){
	header('location:index.php');
}elseif(empty($_POST['token_key'])){
	$_SESSION = array(); //清空$_SESSION，记录新的登陆信息
	$_SESSION['token_key']['login'] = md5(uniqid(rand()));	//加密令牌
}else{
	$nickname = trim($_POST['nickname']);		//用户昵称
	$password = md5(trim($_POST['password']));	//密码加密
	if( $_POST['token_key'] == $_SESSION['token_key']['login'] ){
		$u = $DB->fetch_one_assoc( "SELECT u.*,h.hub_name,h.is_zz FROM `users` u LEFT JOIN `hub_info` h ON u.fzx_id=h.id WHERE `nickname` = '{$nickname}' AND `password` = '{$password}' LIMIT 1" );
		//不知道用户密码的情况下，想用客户的账户登录，就把下面注释的sql打开
		//$u = $DB->fetch_one_assoc( "SELECT u.*,h.hub_name,h.is_zz FROM `users` u LEFT JOIN `hub_info` h ON u.fzx_id=h.id WHERE `nickname` = '{$nickname}' LIMIT 1" );
		/*验证是否能找出相同资料的用户，不能则未注册*/
		if( !$u ){
			if(isset($_REQUEST['ajax']) && '1' == $_REQUEST['ajax']){
				die(json_encode(array('error'=>'1','content'=>'您的用户名或密码不正确，请返回确认重试！')));
			}else{
				alert('您的用户名或密码不正确，请返回确认重试！',"login.php?goback=$_POST[goback]");
				exit();
			}
		}
		//审核配置
		$user_other=$DB->fetch_one_assoc("SELECT * FROM `user_other` WHERE `uid`='{$u['id']}'");
		empty($user_other['v1'])&&$user_other['v1']=0;
		empty($user_other['v2'])&&$user_other['v2']=0;
		empty($user_other['v3'])&&$user_other['v3']=0;
		empty($user_other['v4'])&&$user_other['v4']=0;
		//密码通过
		//unset($u['userid_img']);//关闭电子签名
		$u['test']		= $test;	//将test全局变量也放入权限数组。
		$u['lasturl']	= '';
		$u['password']	= '******';
		$u['user_other']= $user_other;
		$userid			= $u['userid'];
		$u['ip']		= $_SERVER["REMOTE_ADDR"]; 
		$u['lims_system_bar']	= $lims_system_bar;
		$_SESSION['u']	= $u;
		if($u['is_zz'] == 1){
				$sql	= "SELECT distinct v.`id`,v.`value_C` FROM `assay_value` AS v LEFT JOIN `xmfa` AS xf ON v.`id` = xf.`xmid` WHERE xf.id!='' ORDER BY CONVERT( `value_C` USING gbk )";
		}else{
				$sql	= "SELECT v.`id`,v.`value_C`,xf.`unit`,xf.`lxid`,xf.`fangfa`
						FROM `assay_value` AS v LEFT JOIN `xmfa` AS xf ON v.`id` = xf.`xmid`
						WHERE xf.`fzx_id`={$u['fzx_id']} AND xf.`act`=1 ORDER BY CONVERT( `value_C` USING gbk )";
		}
		$R = $DB->query($sql);
		//下面这段代码将全部化验项目存到两个数组中,key='id' value=中英文化验项目 名称,并将这两个数组注册为session变量,这样就可以在任意地方引用这些数据.
		$av_unit=$assayvalueC=array();
		while($r=$DB->fetch_assoc($R)){
			$assayvalueC[$r['id']]	= $r['value_C'];
			$av_unit[$r['id']][$r['lxid']][$r['fangfa']]	= $r['unit'];
		}
		//用户登录一次 记录一次 用户名字 ip地址 登录时间 以便 以后好查找
		$ip=$_SERVER["REMOTE_ADDR"];
		$sql = "INSERT INTO `userlog` SET `uid` = '".$u['id']."', `uname`= '".$u['userid']."',`uptime`='".date("Y-m-d H:i:s")."',`ip`='".$ip."'";
		$DB->query($sql);
		$_SESSION['av_unit']		= $av_unit;
		$_SESSION['assayvalueC']	= $assayvalueC;
		if($_POST['goback']){
			//$current_url = $_POST['goback'];
		}
		if(isset($_REQUEST['ajax']) && '1' == $_REQUEST['ajax']){
			die(json_encode(array('error'=>'0','content'=>'','uid'=>$u['id'])));
		}else{
			gotourl($rooturl);
			toexit();
		}
	}
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta charset="UTF-8">
	<title>LIMS 登陆</title>
	 <script src="js/jquery-2.1.0.min.js"></script>
	 <link href="css/lims/login/style.css" rel="stylesheet">
	 <link  type='text/css' href='css/lims/login/nomal.css'  rel='stylesheet' >
</head>
<body> 

	<div class="title">
	<img src="css/images/logo.png" alt="" ><div>
		<div class="login_two" style='width:850px;margin:0 auto;'><img src="./img/title_logo.jpg" style="width:120px;text-align:right;">兰州威立雅水务 ( 集团 ) 有限责任公司 水质中心
		<p>Water Quality Center · Lanzhou Veolia Water (Group) Co.,Ltd</p>国家城市供水水质监测网 兰州监测站<p>Lanzhou Station · National Urban Supply Water Quality Monitoring Network</p></div>
	</div>
	</div>
	<div class="box_login">
		<form  class="form-login" name="form1" action="login.php" method="post">
			<div class="login" style="margin-top:100px;">
				<div class="login_login">
					<div class="login_one">实验室信息管理系统
						<p>Laboratory Information Management System</p>
					</div>
					<div class="login_user">
						帐 号<input type="text" class="login_input" autofocus="autofocus" id="loginname" name="nickname" placeholder="账号...">
					</div>
					<div class="login_user">
						密 码<input type="password" class="login_input"  id="inputPassword" name="password" placeholder="密码..." >
						<input type="hidden" name="token_key" value="<?=$_SESSION['token_key']['login']?>" />
					</div>
					<input type="submit" class="submit" value="登       录"  name='submit'>
					
				</div>
			</div>
		</form>

	</div>

	<script>
	$(function(){
			$(".login").height( $(window).height() - 250 );
			$(".box_login").height( $(window).height() - 250 );
			$(window).resize(function() {
			  $(".login").height( $(window).height() - 250 );
			  $(".box_login").height( $(window).height() - 250 );
			});
		
		})
	
	</script>
</body>
</html>

