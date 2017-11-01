<?php
$title = str_replace(' ', '', $title);
$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel); // 用于 2007 格式
$objWriter->setOffice2003Compatibility(true);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$title.'.xlsx"');
header('Cache-Control: max-age=0');
$objWriter->save("php://output");