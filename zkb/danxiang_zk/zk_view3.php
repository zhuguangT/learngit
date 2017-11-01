<?php
/**
 * 功能：密码平行样检测结果评定
 * 作者：Mr Zhou
 * 日期：2014-08-19
 * 描述：
*/
$sql="SELECT ao.vid,ao.hy_flag,ao.vd0,COUNT(ao.vid) AS tot,ao.xiang_dui_pian_cha,xm.value_C,assay_pay.userid,assay_pay.userid2,ping_jia  
	FROM  `assay_order` AS ao LEFT JOIN assay_pay ON ao.tid=assay_pay.id 
	LEFT JOIN `assay_value` xm ON ao.vid=xm.id  
	LEFT JOIN cy_rec ON cy_rec.id=ao.cid 
	WHERE  ao.`cyd_id`  IN ($ids) 
	AND hy_flag IN (-6,5,25,45,65)
	AND xm.`is_xcjc`='0' 
	GROUP BY ao.vid,ao.hy_flag,ao.ping_jia,xiang_dui_pian_cha 
	ORDER BY vid, hy_flag DESC";
$i = 0;
$hs = 28;
$arr=$bz=array();
$baogaoline=$bz1=$bz3=$bz2=$cdl1=$cdl2=$cdl3=$xm='';
$value=$DB->query($sql);
while($v=$DB->fetch_assoc($value)){
	if($xm != $v['value_C']){
		$xm = $v['value_C'];
		$mintmp = $maxtmp = '';
		$arr[$v['value_C']][0] = 0;
		$arr[$v['value_C']]['-6'] = 0;
		$arr[$v['value_C']]['con'] = 0;
		$arr[$v['value_C']]['flag'] = 0;
	}
	//
	$arr[$v['value_C']]['con']++;
	
	if($v['hy_flag']==-6){
		//密码平行样的个数
		$arr[$v['value_C']]['-6']+=$v['tot'];
		//合格统计
		if($v['ping_jia'] =='合格')
			$arr[$v['value_C']]['flag']+=$v['tot'];
	}else {
		//非密码平行样的个数
		$arr[$v['value_C']][0]+=$v['tot'];
	}
	$arr[$v['value_C']]['id'] = $v['vid'];
	$arr[$v['value_C']]['ry'] = ($v['userid2']=='') ? $v['userid'] : $v['userid']."/".$v['userid2'];
	//第一次给 最大、最小相对偏差赋值
	$maxtmp = $maxtmp == '' ? abs($v['xiang_dui_pian_cha']):$maxtmp;
	$mintmp = $mintmp == '' ? abs($v['xiang_dui_pian_cha']):$mintmp;
	//获取最大、最小的相对偏差
	if($v['hy_flag']==-6 && $v['xiang_dui_pian_cha']!=''){
		$maxtmp = (abs($v['xiang_dui_pian_cha']) > $maxtmp)?abs($v['xiang_dui_pian_cha']):$maxtmp;
		$mintmp = (abs($v['xiang_dui_pian_cha']) > $mintmp)?$mintmp:abs($v['xiang_dui_pian_cha']);
	}
	//如果结果为空或者为0的话统一为0.00
	$maxtmp = empty($maxtmp) ? '0.00' : $maxtmp;
	$mintmp = empty($mintmp) ? '0.00' : $mintmp;
	//精密度范围
	$arr[$v['value_C']]['fw']= ($mintmp == $maxtmp) ? $mintmp : $mintmp."~".$maxtmp;
	//浓度
	$arr[$v['value_C']]['nd'] = $v['vd0'];
	//测定率
	$arr[$v['value_C']]['cdl'] = _round($arr[$v['value_C']]['-6']/$arr[$v['value_C']][0]*100,1);
}
$i = 0;//表格页数
$n = 0;//项目数
$xi = 0;//每张表里面已输出项目的行数
$hgs = 0;//合格数
$fei_100_hg = array();//合格率不是100的项目
$cdlMax = $cdlMin = '';
$data=array();
foreach($arr as $key=>$value){
	if($value['-6']>0){
		$xi++;
		$n++;
		//测定个数
		$cdgs = $value[-6];
		//测定率
		if($cdlMax == ''){
			$cdlMax = $cdlMin = $value['cdl'];
		}
		$cdlMax = $cdlMax < $value['cdl'] ? $value['cdl'] : $cdlMax;
		$cdlMin = $cdlMin > $value['cdl'] ? $value['cdl'] : $cdlMin;

		//合格率
		$hgl = round($value['flag']*100/$value[-6],2);
		//记录合格率不为100%的项目
		if($hgl != 100){
			$fei_100_hg[] = $key;
		}
		//合格数
		$hgl == 100 ? $hgs++ : $hgs;

		$data[$i][]=array(
					'id'=>$value['id'],
					'xm'=>$key,
					'cdgs'=>$cdgs,
					'cdl'=>$value['cdl'],
					'hsfw'=>$value['fw'],
					'hgl'=>$hgl,
					'ry'=>$value['ry']
				);
	}
	if($xi%$hs==0){
		$i++;
	}
}
$qu_yu = $xi%$hs;
if($qu_yu!=0){
	for($qu_yu;$qu_yu<$hs;$qu_yu++)
		$data[$i][] = array();
}

if($_GET['xun'] != '水源地'){
	$zhikong_string = '（总氮测定率为 '.$arr['总氮']['cdl'].'%、石油类测定率为 '.$arr['石油类']['cdl'].'%）';
}else {$zhikong_string = '';}
//测定率范围
$cdlFW = $cdlMin == $cdlMax ? $cdlmax : $cdlMin.'~'.$cdlMax;
//如果合格数和总个数相同则示所有的项目否则显示合格率
$hgs = round($hgs/$n*100,2);
$bz[0]  = '质控意见：'.$year.'年'.$month.'月'.$xun.'现场密码平行样测试项目为 '.$n.'项 测定率为 '.$cdlFW.'% '.$zhikong_string.' ，所有的项目均按要求完成了密码平行样的测定。本次检测项目密码平行样精密度一次性合格率为'.$hgs.'％';
$bz[0] .= $hgs == 100 ? '。' : '，其中合格率不为100%的项目有'.(implode(',', $fei_100_hg)).'。';
$bz[1]  = '质量负责人:';
$bz[2]  = '备注：';

if(intval($_GET['xz']!=1)){
//质控表显示
	foreach ($data as $tab_id => $tab_data) {
		$baogaoline = '';
		foreach ($tab_data as $key => $value) {
			if(empty($value)){
				$baogaoline.='<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
				continue;
			}
			if($_GET['print'])
				$link = '<td>'.$value['xm'].'</td>';
			else
				$link = "<td><a href=\"baogao_zk.php?zk_type=2&cyd_id=$_GET[cyd_id]&year=$_GET[year]&month=$_GET[month]&xun=$_GET[xun]&vid=$value[id]\" target=\"_blank\">".$value['xm']."</a></td>";
			$value['hgl'] = ($value['hgl']==100)?$value['hgl']:'<span class="red">'.$value['hgl'].'</span>';
			$baogaoline.='<tr align=center class="hang">'.$link.'<td>'.$value['cdgs'].'</td><td>'.$value['cdl'].'</td><td>'.$value['hsfw'].'</td><td>'.$value['hgl'].'</td><td>'.$value['ry'].'</td></tr>';
		}
		//最后一张表显示备注
		if(!isset($data[$tab_id+1])){
			$bz1 = $bz[0];
			$bz2 = $bz[1];
			$bz3 = $bz[2];
		}
		echo temp('zkb/danxiang_zk/baogao_zk_mmpx2');
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
	$title = "$year 年 $month 月 $_GET[xun]密码平行样检测结果评定";
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
	$objPHPExcel->getActiveSheet()->setCellValue('B'.$rows,"平行样检测数量");
	$objPHPExcel->getActiveSheet()->setCellValue('C'.$rows,"检测率(%)");
	$objPHPExcel->getActiveSheet()->setCellValue('D'.$rows,"精密度(%)");
	$objPHPExcel->getActiveSheet()->setCellValue('E'.$rows,"合格率(%)");
	$objPHPExcel->getActiveSheet()->setCellValue('F'.$rows,"检测人员");
	foreach ($tab_data as $key => $value) {
		$rows++;
		if(empty($value)) continue;
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$value['xm']);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$rows,$value['cdgs'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$rows,$value['cdl'].' ');
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$rows,$value['hsfw'].' ');
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
	if(empty($data[$tab_id+1]))
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$bz['0']);
	$rows++;
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':F'.$rows);
	if(empty($data[$tab_id+1]))
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$bz['1']);
	$rows++;
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$rows.':F'.$rows);
	if(empty($data[$tab_id+1]))
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$rows,$bz['2']);

	$rows+=2;
	$first_row = $rows;
}