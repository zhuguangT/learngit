<?php
/**
 * 功能：分配测试任务列表
 * 作者：Mr Zhou
 * 时间：2017-03-02
**/
// 包含config
include_once '../temp/config.php';
$_GET['app'] = 'yply';
define('APP_PATH', $rootdir.'/xd_csrw/');
require_once '../huayan/ahlims.php';