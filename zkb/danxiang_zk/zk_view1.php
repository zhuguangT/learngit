<?php
/**
 * 功能：实验室与现场空白检测结果比较
 * 作者：Mr Zhou
 * 日期：2014-08-19
 * 描述：
*/
$sql = "SELECT ao.cyd_id,ao.tid,ao.bar_code,ao.hy_flag,ao.vd0,ao.vid,ao.vd28,ao._vd0,
			ao.ping_jun,ao.xiang_dui_pian_cha,ao.ping_jia,
			assay_pay.userid,assay_pay.userid2,xm.value_C,assay_pay.td3
		FROM `assay_order` AS ao
		LEFT JOIN `assay_pay` ON ao.tid = assay_pay.id
		LEFT JOIN `assay_value` xm ON ao.vid=xm.id 
		WHERE ao.hy_flag IN (1,21,41,61,-2) 
			AND ao.cyd_id IN( $ids )
			AND xm.is_xcjc='0'
			and xm.id=105
		ORDER BY ao.cyd_id,ao.hy_flag DESC";
$value = $DB->query($sql);
$hs = 29;//规定一页最大的行数
$arr = array();
while($v = $DB->fetch_assoc($value)){
	if($v['hy_flag'] > 0){
		//现场空白即全程序空白
		$arr['xc'][$v['cyd_id']][$v['vid']]['bar_code']	= $v['bar_code'];
		$arr['xc'][$v['cyd_id']][$v['vid']]['cyd_id']	= $v['cyd_id'];
		$arr['xc'][$v['cyd_id']][$v['vid']]['vd0']		= $v['vd0'];
		$arr['xc'][$v['cyd_id']][$v['vid']]['avg']		= $v['ping_jun'];
	}else if($v['hy_flag']==-2){
		//室内空白
		$arr['sn'][$v['cyd_id']][$v['vid']]['u1']		= $v['userid'];
		$arr['sn'][$v['cyd_id']][$v['vid']]['u2']		= $v['userid2'];
		$arr['sn'][$v['cyd_id']][$v['vid']]['tid']		= $v['tid'];
		$arr['sn'][$v['cyd_id']][$v['vid']]['avg']		= $v['ping_jun'];
		$arr['sn'][$v['cyd_id']][$v['vid']]['hgpd']		= $v['ping_jia'];
		$arr['sn'][$v['cyd_id']][$v['vid']]['xdpc']		= $v['xiang_dui_pian_cha'];
		$arr['sn'][$v['cyd_id']][$v['vid']]['value_C']	= $v['value_C'];
		$arr['sn'][$v['cyd_id']][$v['vid']][]			= array(
													'vd0'=>$v['vd0'],
													'_vd0'=>$v['_vd0'],
													'vd28'=>$v['vd28']);
	}
}
if(empty($arr['xc']) || empty($arr['sn'])){
	//die('没有相关数据');
}
$maxValue = 50;//相对偏差最大允许值
$i = 0;//页数

foreach($arr['xc'] as $cyd_id=>$vv){
	$hgs = $j = 0;//合格数 行数
	foreach($arr['sn'][$cyd_id] as $vid=>$v){
		$xdpc = $xckb = $snkb = $avg = $pingjia = '';
		//相对偏差 现场空白 室内空白 平均 评价
		if(isset($v[0])&&isset($v[1])){
			//室内空白均值与现场空白的平均值
			$avg		= $vv[$vid]['avg'];
			$xckb		= $vv[$vid]['vd0'];
			$bar_code	= $vv[$vid]['bar_code'];
			if(''!=$v[0]['_vd0']&&''!=$v[1]['_vd0']){
				$snkb = round_value(($v[0]['_vd0']+$v[1]['_vd0'])/2,$v['tid']);
			}else{
				$snkb='';
			}
			//相对偏差
			$xdpc=$avg['xdpc'];
			if($vv['hgpd']=='合格'){
				$hgs++;
			}
			$pingjia = $vv['hgpd'];
		}else{
			continue;
		}
		//化验员
		$user = ($v['u2']=='') ? $v['u1'] : $v['u1'].'/'.$v['u2'];
		//数据
		$data[$i][] = array(
			'tid' => $v['tid'],
			'jcx' => $v['td3'],
			'xm'=>$v['value_C'],
			'sn'=>$snkb,
			'xc'=>$xckb,
			'avg'=>$avg,
			'xdpc'=>$xdpc,
			'xz'=>$maxValue,
			'pingjia'=>$pingjia,
			'user'=>$user,
			'bar_code'=>$bar_code
		);
		//现场空白样品编号
		$data[$i][0]['note'][0] = $bar_code;
		//表尾 注释
		if((++$j)%$hs==0){
			$i++;
		}
	}
	//表尾 注释
	$qu_yu = $j%$hs;
	if($qu_yu!=0){
		$sy = $hs - $qu_yu;
		for($xj=0;$xj<$sy;$xj++){
			$data[$i][] = array();	
		}
		$bz1 = "备注:“检测值”是浓度，低于检出限的，用检出限表示，".$j."个检测项目的现场空白与实验室空白比较，相对偏差";
		$bz1 .= $hgs == $j ? '全部在允许值范围内。' : '的合格率是'.round($hgs/$j*100,2).'%。';
		$data[$i][0]['note'][1] = $bz1;
		$data[$i][0]['note'][2] = '质量负责人：';
	}
	$i++;
}
//质控表显示
if(intval($_GET['xz']!=1)){
	foreach ($data as $tab_id => $tab_data) {
		$baogaoline='';
		foreach ($tab_data as $key => $value) {
			$value['pingjia'] == '不合格' && $value['pingjia'] = '<font color="red">不合格</font>';
			$url = $rooturl.'/huayan/assay_form.php?tid=';
			if(!$_GET['print']){
				$link = '<a href="'.$url.$value['tid'].'" target="_blank">'.$value['xm'].'</a>';
			}else{
				$link = $value['xm'];
			}
			$baogaoline .= '<tr align=center class="hang" title="检出限: '.$value['jcx'].'">
							<td>'.$link.'</td>
							<td>'.$value['sn'].'</td>
							<td>'.$value['xc'].'</td>
							<td>'.$value['avg'].'</td>
							<td>'.$value['xdpc'].'</td>
							<td>'.$value['xz'].'</td>
							<td>'.$value['pingjia'].'</td>
							<td>'.$value['user'].'</td></tr>';
		}
		$kkx = $tab_data[0]['note'][0];
		$bz1 = $tab_data[0]['note'][1];
		$bz2 = $tab_data[0]['note'][2];
		echo temp('zkb/danxiang_zk/baogao_zk_kb2');
	}
	exit();
}
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
	$title = "$year 年 $month 月  $_GET[xun]实验室与现场空白检测结果比较";
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
	$objPHPExcel->getActiveSheet()->setCellValue('B'.$rows,"实验室空白样检测值");
	$objPHPExcel->getActiveSheet()->setCellValue('C'.$rows,"现场空白样检测值".$tab_data[0]['bar_code']);
	$objPHPExcel->getActiveSheet()->setCellValue('D'.$rows,"两空白平均值");
	$objPHPExcel->getActiveSheet()->setCellValue('E'.$rows,"相对偏差(%)");
	$objPHPExcel->getActiveSheet()->setCellValue('F'.$rows,"相对偏差最大允许值(%)");
	$objPHPExcel->getActiveSheet()->setCellValue('G'.$rows,"结果评定");
	$objPHPExcel->getActiveSheet()->setCellValue('H'.$rows,"检测人员");
	foreach ($tab_data as $key => $value) {
		$rows++;
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$value['xm']);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$rows,$value['sn'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$rows,$value['xc'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$rows,$value['avg'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$rows,$value['xdpc'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$rows,$value['xz'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$rows,$value['pingjia']);
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$rows,$value['user']);
	}
	$last_cell = 'H'.$rows;
	$objPHPExcel->getActiveSheet()->getStyle('A'.($first_row+1).':'.'H'.($rows+2))->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
	$objPHPExcel->getActiveSheet()->getStyle('A'.$first_row.':'.$last_cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//水平居中
	
	$rows++;
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':H'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$data[$tab_id][0]['note']['1']);
	$rows++;
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':H'.$rows);
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$data[$tab_id][0]['note']['2']);

	$rows+=2;
	$first_row = $rows;
}