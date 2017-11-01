<?php
set_time_limit(0); 
include $rootdir."/inc/classes/phpexcel.php";
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
$objPHPExcel->getDefaultStyle()->getFont()->setSize(10); 
//设置单元格边线格式
$styleArray = 
	array('borders' => array('allborders' => array(
		'style' => PHPExcel_Style_Border::BORDER_THIN,
		'color' => array('argb' => '00000000'),),),);