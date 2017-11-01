<?php
/**
 * 功能：加标回收率检测结果评定
 * 作者：Mr Zhou
 * 日期：2014-08-19
 * 描述：
*/
$fzx_id = FZX_ID;
include('./get_zkfw.php');
$sql="SELECT ao.id, ao.vid, ao.water_type, ao.hy_flag, ao.vd0, COUNT( ao.vid ) AS tot, ao.`ping_jia` , ao.`xiang_dui_pian_cha` , xm.value_C, assay_pay.userid, assay_pay.userid2
	FROM `assay_order` AS ao
	LEFT JOIN assay_pay ON ao.tid = assay_pay.id
	LEFT JOIN `assay_value` xm ON ao.vid = xm.id
	WHERE ao.`cyd_id`
	IN ($ids)
	AND ao.sid>0 
	AND xm.`is_xcjc`='0'
	AND ( ao.`hy_flag` >=0 OR ao.`hy_flag` IN(-40,-60,-6,-46,-66) )
	GROUP BY ao.vid, ao.hy_flag, ao.ping_jia, ao.`xiang_dui_pian_cha`";
$maxtmp=$mintmp=$baogaoline=$bz1=$bz3=$bz2=$cdlmax=$cdlmin='';
$hs =29;
$hgs=0;
$value=$DB->query($sql);
while($v=$DB->fetch_assoc($value)){
	$arr[$v['value_C']]['user']=($v['userid2']=='')?$v['userid']:$v['userid']."/".$v['userid2'];
	$arr[$v['value_C']]['id']=$v['vid'];
	if($xm != $v['value_C']){
		$mintmp = $maxtmp = '';
		$arr[$v['value_C']]['hg'] = 1;
	}
	if($v['ping_jia']=='不合格' && (in_array($v['hy_flag'],array(-40,-60,-46,-66)))){
		$arr[$v['value_C']]['hg'] = 0;
	}
	
    if(in_array($v['hy_flag'],array(-40,-60,-46,-66))){
		$v['hy_flag']=-40;
    }
     
	$xm = $v['value_C'];
	
	// if($v['hy_flag']==-40 && $v['xiang_dui_pian_cha']!=''){
	// 	//第一次给 最大、最小相对偏差赋值
	// 	$maxtmp = $maxtmp == '' ? abs($v['xiang_dui_pian_cha']):$maxtmp;
	// 	$mintmp = $mintmp == '' ? abs($v['xiang_dui_pian_cha']):$mintmp;
	// 	$maxtmp = (abs($v['xiang_dui_pian_cha']) > $maxtmp)?abs($v['xiang_dui_pian_cha']):$maxtmp;
	// 	$mintmp = (abs($v['xiang_dui_pian_cha']) > $mintmp)?$mintmp:abs($v['xiang_dui_pian_cha']);
	// }
	// //如果结果为空或者为0的话统一为0.0
	// $maxtmp = empty($maxtmp) ? '0.0' : _round($maxtmp,1);
	// $mintmp = empty($mintmp) ? '0.0' : _round($mintmp,1);
	//获取精密度范围
	$zkfw = get_zkfw($v['vid'],$v['water_type'],$v['vd0'],'','jbhs');
	if($zkfw['jbhs']==''){
		$fw = '-';
	}else{
		$fw = $zkfw;
	}

	$arr[$v['value_C']]['fw'] = $fw;
	$arr[$v['value_C']]['nd'] = $v['vd0'];
	if($v['hy_flag']==-40){
		$arr[$v['value_C']][$v['hy_flag']]+=$v['tot'];//统计一共做了多少个加标的样品
	}else if( ($v['hy_flag']>=0 && $v['hy_flag']!=3) || $v['hy_flag'] == -6 )
		//去掉标准样品3
		$arr[$v['value_C']][0]+=$v['tot'];//统计项目的总数量
}

$data=array();//数据
$i=$j=$xms=$hgs=0;//表格数  行数  项目数  合格数
foreach ($arr as $value_C => $value) {
	if(key_exists('-40',$value)){
		$xms++;
		$value['hg']==1 && $hgs++;
		//测定率
		$cdl=_round($value[-40]/$value[0]*100,1);
		$cdlmax = ($cdlmax=='')?$cdlmax=$cdl:($cdlmax>=$cdl)?$cdlmax:$cdl;
		$cdlmin = ($cdlmin=='')?$cdlmin=$cdl:($cdlmin<=$cdl)?$cdlmin:$cdl;

		$data[$i]['data'][$j++] = array(
							'v'=>$value_C,
							'vid'=>$value['id'],
							'cdgs'=>$value[-40],
							'cdl'=>$cdl,
							'ry'=>$value['user'],
							'hsfw'=>$value['fw'],
							'hg'=>$value['hg']
						);
		$qu_yu = $j%$hs;
		if($qu_yu==0){
			$data[$i]['note'] = array();
			$i++;
			$j=0;
		}
	}
} 
$bz1 = "质控意见: $year 年 $month 月 $xun 检测项目要求平行样测定率为10%，各项目测定率在($cdlmin ~ $cdlmax)%,$xms 个项检测项目中";
$bz1 .= $hgs<$xms? $hgs.'个项目加标回收率合格。':"所有的加标回收率合格。";
$bz2 = "质量负责人： ";
$bz3 = "备注：";
$data[$i]['note']=array($bz1,$bz2,$bz3);
if($qu_yu!=0){
	for($qu_yu;$qu_yu<$hs;$qu_yu++)
		$data[$i]['data'][]=array();
}
if(intval($_GET['xz']!=1)){
	//质控表显示
	foreach($data as $tab_id=>$valist){
		foreach ($valist['data'] as $key => $value) {
			if(empty($value)){
				$baogaoline .= '<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
				continue;
			}
			$sfhg =($value['hg']==0)?'<font color="red">不合格</font>':'合格';

			if($_GET['print'])
				$link = '<td>'.$value['v'].'</td>';
			else
				$link = '<td><a href="baogao_zk.php?zk_type=6&cyd_id='.$_GET['cyd_id'].'&year='.$_GET['year'].'&month='.$_GET['month'].'&xun='.$_GET['xun'].'&vid='.$value['vid'].'" target="_blank">'.$value['v'].'</a></td>';
			$baogaoline.='<tr align="center" class="hang">'.$link.'<td>'.$value['cdgs'].'</td><td>'.$value['cdl'].'</td><td>'.$value['hsfw'].'</td><td>'.$value['ry'].'</td><td>'.$sfhg.'</td></tr>';
		}
		$bz1 = $valist['note'][0];
		$bz2 = $valist['note'][1];
		$bz3 = $valist['note'][2];
		if($xi%$hs==0){
			echo temp('zkb/danxiang_zk/baogao_zk_jbhs');
			$baogaoline = '';
		}
	}
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
foreach ($data as $tab_id => $tab_data) {
	//标题 合并单元格
	$rows++;
	$title = "$year 年 $month 月 $_GET[xun]加标回收率检测结果评定";
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
	$objPHPExcel->getActiveSheet()->setCellValue('B'.$rows,"检测数量(个)");
	$objPHPExcel->getActiveSheet()->setCellValue('C'.$rows,"测定率(%)");
	$objPHPExcel->getActiveSheet()->setCellValue('D'.$rows,"回收率范围(%)");
	$objPHPExcel->getActiveSheet()->setCellValue('E'.$rows,"检测人员");
	$objPHPExcel->getActiveSheet()->setCellValue('F'.$rows,"是否合格");
	foreach ($tab_data['data'] as $key => $value) {
		$rows++;
		if(empty($value)) continue;
		$sfhg =($value['hg']==0)?'不合格':'合格';
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$value['v']);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$rows,$value['cdgs'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$rows,$value['cdl'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$rows,$value['hsfw'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$rows,$value['ry']);
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$rows,$sfhg);
	}
	$last_cell = 'F'.$rows;
	$objPHPExcel->getActiveSheet()->getStyle('A'.($first_row+1).':'.'F'.($rows+3))->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
	$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//水平居中

	$rows++;
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':F'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$tab_data['note'][0]);
	$objPHPExcel->getActiveSheet()->getStyle($rows)->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getRowDimension($rows)->setRowHeight(33);//设置行高
	$objPHPExcel->getActiveSheet()->getStyle($rows)->getAlignment()->setWrapText(true); //自动换行
	$rows++;
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':F'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$tab_data['note'][1]);
	$rows++;
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':F'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$tab_data['note'][2]);
}