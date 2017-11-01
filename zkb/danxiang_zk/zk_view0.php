<?php
/**
 * 功能：实验室两空白检测结果比较
 * 作者：Mr Zhou
 * 日期：2014-08-19
 * 描述：
*/
$sql="SELECT ao.tid, ao.vid, ao.vd0,ao2.vd28,
			ao2.vd0 AS vd0_2,ao.vd28 AS vd28_2,ao2.ping_jun,ao2.xiang_dui_pian_cha,ao2.ping_jia,
			assay_pay.userid, assay_pay.userid2, xm.value_C
		FROM `assay_order` AS ao
		LEFT JOIN `assay_pay` ON ao.tid=assay_pay.id
		LEFT JOIN `assay_value` xm ON ao.vid=xm.id  
		LEFT JOIN `assay_order` AS ao2 ON ao.tid=ao2.tid 
		WHERE ao.cyd_id IN ($ids) 
			AND ao.hy_flag = '-2' 
			AND ao.sid='-1' 
			AND ao.tid>0
			AND ao2.hy_flag = '-2' 
			AND ao2.sid='-2'
			AND xm.is_xcjc='0'
		ORDER BY ao.vid,ao.tid";
$value	= $DB->query($sql);
$arr	= $vid = array();
$hs		= 29;//行数
while($row=$DB->fetch_assoc($value)){
	//统计 记录做空白的项目的个数
	if(!in_array($row['vid'],$vid)){
		$i++;
		$vid[] = $row['vid'];
	}
	//信号值 代表空白检测值
	if($row['vd28']!=''&&$row['vd28_2']!=''){
		$row['vd0'] = $row['vd28'];
		$row['vd0_2'] = $row['vd28_2'];
	}
	//化验员
	$row['user'] = ($row['userid2']=='')?$row['userid']:$row['userid']."/".$row['userid2'];
	$arr[$row['tid']] = $row;

}
if(empty($arr)){
	die('没有相关数据');
}
$i=$j=$hgs=0;//页数 行数 合格数
foreach ($arr as $tid => $row) {
	$value = array();
	$value[0]		= $row['vd0'];
	$value[1]		= $row['vd0_2'];
	$value['t']		= $row['tid'];
	$value['avg']	= $row['ping_jun'];
	$value['max']	= 50;//淮委刘主任说相对偏差最大允许值是50并未说清楚为何
	$value['xdpc']	= $row['xiang_dui_pian_cha'];
	$value['vname'] = $row['value_C'];;
	if($row['ping_jia'] == '合格'){
		$hgs++;
	}
	$value['jgpd'] = $row['ping_jia'];
	if( $j % $hs == 0){
		$i++;
		$j=0;
	}
	$data[$i][$j++] = $value;
}
if(count($data[$i])<$hs){
	for($c=$j;$c<$hs;$c++){
		$data[$i][] = array();
	}
}
$hgl = round($hgs/count($arr)*100,2);
$bz['1'] = "检测值分别为峰高、光密度等，".count($arr)."个检测项目2个实验室空白比较，相对偏差";
$bz['1'] .= ($hgl == 100) ? '全部在允许值范围内。' : '的合格率是'.$hgl.'%。';
$bz['2'] = "质量负责人：";
//质控表显示
if(intval($_GET['xz']!=1)){
	$url = $rooturl.'/huayan/assay_form.php?tid=';
	foreach($data as $tab_id => $tab_data){
		$baogaoline = '';
		foreach ($tab_data as $key => $value) {
			if(!$_GET['print']){
				$link = '<a href="'.$url.$value['t'].'" target="_blank">'.$value['vname'].'</a>';
			}else{
				$link = $value['vname'];
			}
			$baogaoline.='<tr align="center" class="hang"><td>'.$link.'</td><td>'.$value[0].'</td><td>'.$value[1].'</td><td>'.$value['avg'].'</td><td>'.$value['xdpc'].'</td><td>'.$value['max'].'</td><td>'.$value['jgpd'].'</td><td>'.$value['user'].'</td></tr>';
		}
		if(!isset($data[$tab_id+1])){
			$hgl = round($hgs/count($arr)*100,2);
			$bz1 = $bz['1'];
			$bz2 = $bz['2'];
		}
		echo temp('zkb/danxiang_zk/baogao_zk');
	}
	exit();
}
//质控表下载
$rows = 0;//行数
$cols = 8;//总列数
$first_row =1;
$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(21);
$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(13);
$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(13);
$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(15);
foreach ($data as $tab_id => $tab_data) {
	//标题 合并单元格
	$rows++;
	$title = "$year 年 $month 月 实 验 室 两 空 白 检 测 结 果 比 较";
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':H'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$title);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$rows)->getFont()->setName('宋体')->setSize(15)->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);
	$objPHPExcel->getActiveSheet()->getRowDimension($rows)->setRowHeight(33);//设置行高
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$title);
	//第二行 名称
	$rows++;
	$objPHPExcel->getActiveSheet()->getRowDimension($rows)->setRowHeight(33);//设置行高
	$objPHPExcel->getActiveSheet()->getStyle($rows)->getAlignment()->setWrapText(true); //自动换行
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,"检测项目");
	$objPHPExcel->getActiveSheet()->setCellValue('B'.$rows,"实验室空白样检测值1");
	$objPHPExcel->getActiveSheet()->setCellValue('C'.$rows,"实验室空白样检测值2");
	$objPHPExcel->getActiveSheet()->setCellValue('D'.$rows,"实验室两空白平均值");
	$objPHPExcel->getActiveSheet()->setCellValue('E'.$rows,"相对偏差(%)");
	$objPHPExcel->getActiveSheet()->setCellValue('F'.$rows,"相对偏差最大允许值(%)");
	$objPHPExcel->getActiveSheet()->setCellValue('G'.$rows,"结果评定");
	$objPHPExcel->getActiveSheet()->setCellValue('H'.$rows,"检测人员");
	foreach ($tab_data as $key => $value) {
		$rows++;
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$value['vname']);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$rows,$value[0].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$rows,$value[1].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$rows,$value['avg'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$rows,$value['xdpc'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$rows,$value['max'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$rows,$value['jgpd']);
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$rows,$value['user']);
	}
	$last_cell = 'H'.$rows;
	$objPHPExcel->getActiveSheet()->getStyle('A'.($first_row+1).':'.'H'.($rows+2))->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
	$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//水平居中

	$rows++;
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':H'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$bz['1']);
	$rows++;
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':H'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$bz['2']);

	$rows+=2;
	$first_row = $rows;
}