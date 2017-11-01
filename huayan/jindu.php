<?php
/**
 * 文件名：main.php
 * 功能：系统首页
 * 作者: Mr Zhou
 * 日期: 2014-03-31
 * 描述:
*/
include '../temp/config.php';
//导航
$trade_global['daohang'] = array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'任务进度','href'=>'./huayan/jindu.php')
);

disp('jindu.html');