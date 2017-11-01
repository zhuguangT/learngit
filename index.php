<?php
/**
 * 功能：系统框架主页
 * 作者: Mr Zhou
 * 日期: 2014-03
 * 描述
*/
include_once("./temp/config.php");
?>
<!DOCTYPE html>
<html lang="zh-cn">
	<head>
		<meta charset="utf-8" />
		<title>LIMS</title>
		<meta name="keywords" content="LIMS" />
		<meta name="description" content="LIMS" />
		<link rel="shortcut icon" href="./favicon.ico">
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<!-- basic styles -->
		<link href="./css/bootstrap.min.css" rel="stylesheet" />
		<link rel="stylesheet" href="./css/font-awesome.min.css" />
		<!-- <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400,300" /> -->
		<!-- ace styles -->
		<link rel="stylesheet" href="./css/ace.min.css" />
		<link rel="stylesheet" href="./css/ace-rtl.min.css" />
		<link rel="stylesheet" href="./css/ace-skins.min.css" />
		<script src="./js/ace-extra.min.js"></script>
		<script type="text/javascript">
		<!--
		//获取本页面高度-banner高度-面包屑导航的高度
		function get_screen_height(){return document.documentElement.clientHeight-100;}
		//iframe 自适应函数
		function SetWinHeight(win) {win.height = get_screen_height();}
		//当窗口大小发生变化时更新iframe的宽高度
		window.onresize = function(){window.main.load_height();}
		//-->
		</script>
		<style type="text/css">
			.navbar .text-logo {color: #124363;}
			.navbar .text-logo,.navbar .text-slogan {color:#FFF;display: inline-block;font-family: 'Droid Sans';font-size: 24px;font-weight: 700;text-transform: uppercase;height: 20px;line-height: 20px;}.navbar .text-logo-element {color: #1A608F;}.bounceIn {animation-name: bounceIn;}.animated {animation-duration: 0.5s;animation-fill-mode: both;}#iframe_main {margin: 0;padding: 0;border: none;width: calc(100%);}
			.nav-user-name {
				color: #428bca;
				background-color: #FFF;
				border-radius: 100%;
				display: block;
				font-size: 30px;
				text-align: center;
				text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.14);
				margin: 2px 0 3px 0;
				padding: 0;
				position: relative;
				text-align: center;
				line-height: 35px;
				height:40px;
				width: 40px;
			}
			.ace-nav .nav-user-photo {
				width: 40px;
				height: 40px;
			}
		</style>
	</head>
	<body class="navbar-fixed breadcrumbs-fixed" style="overflow:hidden;padding-top:86px;">
		<div class="navbar navbar-default navbar-fixed-top" id="navbar">
			<script type="text/javascript">
				try{ace.settings.check('navbar' , 'fixed')}catch(e){}
			</script>

			<div class="navbar-container" id="navbar-container" style="height:41pxline-height:41px;margin-top:0px;padding-top:0px;padding-bottom:0px;">
				<div class="navbar-header pull-left" style="height:41px;line-height:41px;margin-top:0px;padding-top:0px;padding-bottom:0px;">
					<a href="#" class="navbar-brand" style="height:41px;line-height:41px;margin-top:0px;padding-top:0px;padding-bottom:0px;">
					<?php if($show_zt!='演示'){
							//echo '<img style="height:41px;width:40px;" src="./img/header_biaozhi.jpg"/>';
						}else{echo '<i class="icon-windows text-logo-element animated bounceIn"></i>';}
					?>
						<!--<i class="icon-windows text-logo-element animated bounceIn"></i>-->
						<span class="text-logo" style="font-size:24px;font-family:'楷体','黑体','宋体'"><?php if($show_zt!='演示'){echo $u['hub_name'];}else{echo "实验室信息管理系统";}?></span>

					</a><!-- /.brand -->
				</div><!-- /.navbar-header -->

				<div class="navbar-header pull-right" role="navigation">
					<ul class="nav ace-nav">
					<?php if($u['admin'] && $show_zt!='演示'){?>
						<li class="green" onclick="tiaoshi_fun($(this))" title="切换调试模式">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="icon-eye-close"></i>调试模式
							</a>
						</li>
						<script type="text/javascript">
							var tiaoshi_fun = function(obj){
								if(confirm('切换调试模式?')) {
									main.location.href='<?=$rooturl?>/admin/setenv.php?action=debug';
								}
								if(obj.find("i").is('.icon-eye-close')){
									obj.find("i").removeClass('icon-eye-close').addClass('icon-eye-open');
								}else{
									obj.find("i").removeClass('icon-eye-open').addClass('icon-eye-close');
								}
							};
						</script>
					<?php } ?>
					<?php
						$portrait = true;
						$file_src = '/img/user/'.$u['portrait'];
						if(''==$u['portrait'] || !file_exists($rootdir.$file_src)){
							$file_src = '/img/user/default.jpg';
						}
					?>
						<li class="light-blue">
							<a data-toggle="dropdown" href="javascript:;" class="dropdown-toggle">
								<span class="portrait">
									<?php if($portrait) { echo '<img class="nav-user-photo" src="'.$rooturl.$file_src.'" />'; } ?>
								</span>
								<span class="user-info">
									<small>欢迎光临,</small>
									<span class="user_nickname"><?=$u['userid']?></span>
								</span>

								<i class="icon-caret-down"></i>
							</a>

							<ul class="user-menu pull-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
								<li>
									<a href="./user_manage/userpass.php" target="main">
										<i class="icon-user"></i>
										帐户设置
									</a>
								</li>
								<li class="divider"></li>
								<li>
									<a href="<?=$rooturl.'/exit.php'?>" onclick="return confirm('您确认退出吗？')">
										<i class="icon-off"></i>
										退出
									</a>
								</li>
							</ul>
						</li>
					</ul><!-- /.ace-nav -->
				</div><!-- /.navbar-header -->
			</div><!-- /.container -->
		</div>
		<div class="main-container" id="main-container">
			<script type="text/javascript">
				try{ace.settings.check('main-container' , 'fixed')}catch(e){}
			</script>

			<div class="main-container-inner">
				<a class="menu-toggler" id="menu-toggler" href="#">
					<span class="menu-text"></span>
				</a>
				<div class="sidebar sidebar-fixed" id="sidebar" >
					<script type="text/javascript">
						try{ace.settings.check('sidebar' , 'fixed')}catch(e){}
					</script>
					<div class="sidebar-shortcuts" id="sidebar-shortcuts">
						<div class="sidebar-shortcuts-large" id="sidebar-shortcuts-large">
							<button class="btn btn-success">
								<i class="icon-signal"></i>
							</button>
							<button class="btn btn-info">
								<i class="icon-pencil"></i>
								<a href='./user_manage/userpass'></a>
							</button>
							<button class="btn btn-warning">
								<i class="icon-group"></i>
							</button>

							<button class="btn btn-danger">
								<i class="icon-cogs"></i>
							</button>
						</div>

						<div class="sidebar-shortcuts-mini" id="sidebar-shortcuts-mini">
							<span class="btn btn-success"></span>

							<span class="btn btn-info"></span>

							<span class="btn btn-warning"></span>

							<span class="btn btn-danger"></span>
						</div>
					</div><!-- #sidebar-shortcuts -->
					<?php
					include "./inc/get_menu.php";
					?>
					<ul class="nav nav-list" id="menu-list">
					<?php
						foreach($menu as $key => $m_parent){
					?>
						<li>
							<a href=<?=$m_parent['p']['url']==''?'#':$m_parent['p']['url']?> class="dropdown-toggle" title="<?=$m_parent['p']['title']?>">
								<i style="width: 50px" class="icon-<?=empty($m_parent['p']['icon'])?'desktop':$m_parent['p']['icon']?>"><span class="icon-bg btn-<?=$bg_color[$i++]?>"></span></i>
								<span style="font-weight: bold;" class="menu-text"> <?=$m_parent['p']['name']?> </span>
								<?php if(count($m_parent['c'])){?><b class="arrow icon-angle-down"></b><?php }?>
							</a>
					<?php if(count($m_parent['c'])){
								echo '<ul class="submenu">';
								foreach ($m_parent['c'] as $m_p_id => $m_child) {?>
									<li>
										<a href=<?=$m_child['url']?> title="<?=$m_child['title']?>">
											<i class="icon-double-angle-right"></i>
											<!-- <i class="icon-<?=$m_child['icon']?>"></i> -->
											<?php echo $m_child['name'];?>
										</a>
									</li>
							<?php } echo '</ul>'; }?>
						</li>
					<?php } ?>
					</ul><!-- /.nav-list -->

					<div class="sidebar-collapse" id="sidebar-collapse">
						<i class="icon-double-angle-left" data-icon1="icon-double-angle-left" data-icon2="icon-double-angle-right"></i>
					</div>

					<script type="text/javascript">
						try{ace.settings.check('sidebar' , 'collapsed')}catch(e){}
					</script>
				</div>

				<div class="main-content">
					<div class="breadcrumbs breadcrumbs-fixed" id="breadcrumbs">
						<script type="text/javascript">
							try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
						</script>

						<ul class="breadcrumb">
							<li>
								<i class="icon-home home-icon"></i>
								<a href="main.php" target="main">首页</a>
							</li>
						</ul><!-- .breadcrumb -->

						<!-- <div class="nav-search" id="nav-search">
							<form class="form-search">
								<span class="input-icon">
									<input type="text" placeholder="Search ..." class="nav-search-input" id="nav-search-input" autocomplete="off" />
									<i class="icon-search nav-search-icon"></i>
								</span>
							</form>
						</div> --><!-- #nav-search -->
					</div>
					<div class="page-content" id="page-content">
						<iframe id="iframe_main" src="<?=$current_url!=''?$current_url:'main.php'?>" name="main" onload="Javascript:SetWinHeight(this);" ></iframe>
					</div>
				</div><!-- /.main-content -->
			</div>
		</div><!-- /.main-container -->
		<script src="./js/jquery-2.1.0.min.js"></script>
		<script src="./js/bootstrap.min.js"></script>
		<script src="./js/typeahead-bs2.min.js"></script>
		<!-- page specific plugin scripts -->
		<script src="./js/jquery-ui-1.10.3.custom.min.js"></script>
		<script src="./js/jquery.ui.touch-punch.min.js"></script>
		<script src="./js/jquery.slimscroll.min.js"></script>
		<!-- ace scripts -->
		<script src="./js/ace-elements.min.js"></script>
		<script src="./js/ace.min.js"></script>
		<div class="btn-scroll-up">
			<a id="full-top"><img width="40" border="0" title="返回顶部" alt="返回顶部" src="img/back-top.png"></a>
			<br />
			<a id="full-btm"><img width="40" border="0" title="跳到底部" alt="跳到底部" src="img/back-btm.png"></a>
		</div>
		<script type="text/javascript">
			//菜单滚动条样式
			$('#menu-list').slimScroll({height: get_screen_height()-30,railVisible:true});
			$('#full-top').click(function(){window.main.fullTop();});
			$('#full-btm').click(function(){window.main.fullBtm();});
			//清除session['daohang']
			$("a[href*='php'][target='main']").click(function(){
				//清除session的导航部分
				$.post("unset_session_daohang.php?ajax=1");
			});
		</script>
	</body>
</html>
