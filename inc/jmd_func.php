<?php

//计算空白批内标准差
function calc_swb($r){
	$sum1=$sum2=0;
	for($i=0;$i<6;$i++){
		$a=$r['kb_c1'.$i];
		$b=$r['kb_c2'.$i];
		$sum1+=($a*$a+$b*$b);
		$sum2+=($a+$b)*($a+$b);
	}
	return _round(sqrt(($sum1-$sum2/2)/6),4);
}
//计算平均加标回收率
function calc_jb_p_($hyd_id){
	global $DB;
	logmsg(implode('|',$hyd_id),"h");
	$sum=0;
	for($i=0;$i<count($hyd_id);$i++){
		$R=$DB->query("select `xiang_dui_pian_cha` from `assay_order` where `tid`=$hyd_id[$i] and `sid`='-1004'");
		while($r=$DB->fetch_assoc($R)){
			$sum+=$r['xiang_dui_pian_cha'];
		}
	}
	return _round($sum/12,1);
}
/*计算批内变异与批间变异
*    $R:返回结果
*    $R[0] 批内变异
*    $R[1] 批间变异
*   $R[2] 变异显著性检验
*   $R[3] 总变异
*   $R[4] 总标准差
*   $R[5] 指标检出限
*   $R[6] 总标准差检验
*
*   $R[7] 大平均
*   $R[8] 标准差
*/
function calc_all($r,$flag,$jcx){
	define("_F005_",4.39);
	define("_F001_",8.75);
	for($i=0;$i<6;$i++){
		$a[0][$i]=$r[$flag.'_c1'.$i];
		$a[1][$i]=$r[$flag.'_c2'.$i];
		$p[$i]=($a[0][$i]+$a[1][$i])/2; //小平均
		$sum+=$a[0][$i]+$a[1][$i];
	}
	$R[7]=_round($sum/12,3);    //大平均
	for($i=0;$i<6;$i++){
		for($j=0;$j<2;$j++){
			$R[0]+=calc_temp($a[$j][$i],$p[$i],6);            //1)批内变异
			$temp+=calc_temp($a[$j][$i],$R[7],11);
		}
		$R[1]+=($p[$i]-$R[7])*($p[$i]-$R[7])*2/5;            //2)批间变异
	}
	$R[8]=_round(sqrt($temp),4);                                //标准差
	//变异显著性检验
	$R[0]=_round($R[0],6);
	$R[1]=_round($R[1],6);
	$temp=$R[1]/$R[0];
	if($temp<_F005_) $R[2]='NS';
	elseif($temp>_F001_) $R[2]='**';
	else $R[2]='*';
	//总变异
	$R[3]=_round($R[0]+($R[1]-$R[0])/2,6);
	//总标准差
	$R[4]=_round(sqrt($R[3]),6);
	$temp=_round($R[7]/20,6);
	//指标检出限
	$R[5]=($temp>$jcx) ? $temp : $jcx;
	$R[5]=_round($R[5],6);
	$R[6]=($R[4]<=$R[5]) ? "合格" : "不合格" ;
	return $R;
}

function calc_temp($a,$b,$n){
	return ($a-$b)*($a-$b)/$n;
}
