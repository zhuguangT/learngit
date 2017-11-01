<?php
/**
 * 功能：实验室平行样检测结果评定
 * 作者：Mr Zhou
 * 日期：2014-08-19
 * 描述：
*/
$sql="SELECT  ao.id,ao.vid,ao.hy_flag,ao.vd0,COUNT(ao.vid) AS tot,ao.`ping_jia`,ao.`xiang_dui_pian_cha`,xm.value_C,assay_pay.userid,assay_pay.userid2
FROM  assay_order AS ao
LEFT JOIN assay_pay ON ao.tid=assay_pay.id 
LEFT JOIN `assay_value` xm ON ao.vid=xm.id
LEFT JOIN cy_rec on cy_rec.id=ao.cid
WHERE  ao.`cyd_id`  IN ($ids) 
	AND (hy_flag IN(-20,-40,-60,-26,-66) OR hy_flag>0)
	AND xm.`is_xcjc`='0'
GROUP BY ao.vid ,ao.hy_flag,ao.ping_jia,ao.`xiang_dui_pian_cha`";
$arr=array();
$maxtmp=$mintmp=$baogaoline=$bz1=$bz3=$bz2=$cdlmax=$cdlmin='';
$hs = 28;
$value=$DB->query($sql);
while($v=$DB->fetch_assoc($value)){
	if($xm != $v['value_C']){
		$xm = $v['value_C'];
		$mintmp = $maxtmp = '';
		$arr[$v['value_C']]['flag'] = 0;
	}
	$arr[$v['value_C']]['id']=$v['vid'];
	$arr[$v['value_C']]['ry']=($v['userid2']=='')?$v['userid']:$v['userid']."/".$v['userid2'];

	if( $v['hy_flag']==-26 || $v['hy_flag']==-66)
		$v['hy_flag']=-20;

	$arr[$v['value_C']]['con']++;
	
	if($v['hy_flag']==-20){
		//第一次给 最大、最小相对偏差赋值
		$maxtmp = $maxtmp == '' ? abs($v['xiang_dui_pian_cha']):$maxtmp;
		$mintmp = $mintmp == '' ? abs($v['xiang_dui_pian_cha']):$mintmp;
		if($v['hy_flag']==-20 && $v['xiang_dui_pian_cha']!=''){
			$maxtmp = (abs($v['xiang_dui_pian_cha'])>=$maxtmp)?abs($v['xiang_dui_pian_cha']):$maxtmp;
			$mintmp = (abs($v['xiang_dui_pian_cha'])>=$mintmp)?$mintmp:abs($v['xiang_dui_pian_cha']);
		}
		//如果结果为空或者为0的话统一为0.00
		$maxtmp = empty($maxtmp) ? '0.00' : $maxtmp;
		$mintmp = empty($mintmp) ? '0.00' : $mintmp;
		//精密度范围
		$arr[$v['value_C']]['fw']=$mintmp."~".$maxtmp;
		//室内平行总数
		$arr[$v['value_C']][-20]+=$v['tot'];
		//合格总数
		$v['ping_jia']=='合格' && $arr[$v['value_C']]['hgs']+=$v['tot'];
	}else{
		//常规样品数
		$arr[$v['value_C']][0]+=$v['tot'];
	}
}
$n=0;
$cdlmax = $cdlmin = '';
foreach ($arr as $value_C => $value) {
	if(!key_exists('-20',$value)){
		unset($arr[$value_C]);
		continue;
	}
	//测定率
	$cdl=round($value[-20]/$value[0]*100,1);
	$arr[$value_C]['cdl'] = $cdl;
	$arr[$value_C]['hgl'] = round($value['hgs']*100/$value['-20'],2);
	$arr[$value_C]['hgl'] == 100 && $n++;
	$cdlmax = ($cdlmax=='') ? $cdlmax=$cdl:($cdlmax>=$cdl)?$cdlmax:$cdl;
	$cdlmin = ($cdlmin=='') ? $cdlmin=$cdl:($cdlmin<=$cdl)?$cdlmin:$cdl;
}
$bz1 = "质控意见: $year 年 $month 月 $xun 测项目要求平行样测定率为10%，各项目测定率在($cdlmin ~ $cdlmax)%,".$n."个检测项目平行样精密度合格率为100%。";
if($n<count($arr))
$bz1 .= (count($arr)-$n).'个检测项目平行样精密度合格率不为100%。';
$bz2 = "质量负责人： ";
$bz3 = "备注：";

if(count($arr)%$hs!=0){
	$sy = $hs - count($arr)%$hs;
	for($xj=0;$xj<$sy;$xj++){
		$arr[] = array();
	}
}
$data=$arr;
unset($arr);
if(intval($_GET['xz']!=1)){
	//质控表显示
	foreach($data as $key=>$value){
		if(empty($value)){
			$baogaoline.='<tr align=center class="hang"><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
			continue;
		}
		$cdgs = $value[-20];
		$cdl  = $value['cdl'];
		$hsfw = $value['fw'];
		$ry   = $value['ry'];
		$hgl  = $value['hgl'];
		if($_GET['print'])
			$link = "<td>$key</td>";
		else
			$link = "<td><a href=\"baogao_zk.php?zk_type=4&cyd_id=$_GET[cyd_id]&year=$_GET[year]&month=$_GET[month]&xun=$_GET[xun]&vid=$value[id]\" target=\"_blank\">$key</a></td>";

		$baogaoline.="<tr align=center class=\"hang\">$link<td>$cdgs</td><td>$cdl</td><td>$hsfw</td><td>$hgl</td><td>$ry</td></tr>";
	}

	echo temp('zkb/danxiang_zk/baogao_zk_syspx2');
	exit();
}
//质控表下载
$rows = 0;//行数
$cols = 6;//总列数
$first_row =1;
$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(21);
$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(13);
$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(13);
$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);

//标题 合并单元格
$rows++;
$title = "$year 年 $month 月 $_GET[xun]实验室平行样检测结果评定";
$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':F'.$rows);
$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$title);
$objPHPExcel->getActiveSheet()->getStyle('A'.$rows)->getFont()->setName('宋体')->setSize(15)->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);
$objPHPExcel->getActiveSheet()->getRowDimension($rows)->setRowHeight(33);//设置行高
$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$title);
//第二行 名称
$rows++;
$objPHPExcel->getActiveSheet()->getRowDimension($rows)->setRowHeight(33);//设置行高
$objPHPExcel->getActiveSheet()->getStyle($rows)->getAlignment()->setWrapText(true); //自动换行
$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,"检测项目");
$objPHPExcel->getActiveSheet()->setCellValue('B'.$rows,"平行样检测数量");
$objPHPExcel->getActiveSheet()->setCellValue('C'.$rows,"测定率(%)");
$objPHPExcel->getActiveSheet()->setCellValue('D'.$rows,"精密度(%)");
$objPHPExcel->getActiveSheet()->setCellValue('E'.$rows,"合格率(%)");
$objPHPExcel->getActiveSheet()->setCellValue('F'.$rows,"检测人员");
foreach ($data as $key => $value) {
	$rows++;
	if(empty($value)) continue;
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$key);
	$objPHPExcel->getActiveSheet()->setCellValue('B'.$rows,$value['-20'].' ');
	$objPHPExcel->getActiveSheet()->setCellValue('C'.$rows,$value['cdl'].' ');
	$objPHPExcel->getActiveSheet()->setCellValue('D'.$rows,$value['fw'].' ');
	$objPHPExcel->getActiveSheet()->setCellValue('E'.$rows,$value['hgl'].' ');
	$objPHPExcel->getActiveSheet()->setCellValue('F'.$rows,$value['ry'].' ');
}
$last_cell = 'F'.$rows;
$objPHPExcel->getActiveSheet()->getStyle('A'.($first_row+1).':'.'F'.($rows+3))->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//水平居中

$rows++;
$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':F'.$rows);
$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$bz1);
$objPHPExcel->getActiveSheet()->getStyle($rows)->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getRowDimension($rows)->setRowHeight(33);//设置行高
$rows++;
$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':F'.$rows);
$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$bz2);
$rows++;
$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':F'.$rows);
$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$bz3);
