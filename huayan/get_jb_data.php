<?php
/**
 * 功能：
 * 作者: Mr
 * 日期: 2015-11-01 
 * 描述:这里是需要特殊处理数据的化验单函数
*/

function getjbData_217($c_biao,$v_biao,$ding_v,$c_shi,$c_shui,$v_shui,$v_jby,$tid,$r,$jbhs){
	global $DB,$global;
	$jb=array();
	$c_li	= @(($c_biao*$v_biao)/($ding_v)*1.56);		//获取理论浓度
	// $P		= @(($c_shi*$ding_v-$c_shui*$v_shui)/($c_biao*$v_biao)*100);
	$P		= @(($c_shi-$c_shui*($v_shui/$v_jby))/$c_li*100);
	$jb_jsgs= "理论浓度：$c_li	= (($c_biao*$v_biao)/($ding_v)*1.56); 加标回收：(($c_shi-$c_shui*($v_shui/$v_jby))/$c_li*100) = $P%";
	$jb['c_li']=$c_li;
	$jb['P']=$P;
	$jb['jb_jsgs']=$jb_jsgs;
	return $jb;
}
function getjbData_386($c_biao,$v_biao,$ding_v,$c_shi,$c_shui,$v_shui,$v_jby,$tid,$r,$jbhs){
	global $DB,$global;
	$jb=array();
	$c_li	= @(($c_biao*$v_biao)/($ding_v)*0.308);		//获取理论浓度
	// $P		= @(($c_shi*$ding_v-$c_shui*$v_shui)/($c_biao*$v_biao)*100);
	$P		= @(($c_shi-$c_shui*($v_shui/$v_jby))/$c_li*100);
	$jb_jsgs= "理论浓度：$c_li	= (($c_biao*$v_biao)/($ding_v)*0.308); 加标回收：(($c_shi-$c_shui*($v_shui/$v_jby))/$c_li*100) = $P%";
	$jb['c_li']=$c_li;
	$jb['P']=$P;
	$jb['jb_jsgs']=$jb_jsgs;
	return $jb;
}
//氯化铁
function getjbData_723($c_biao,$v_biao,$ding_v,$c_shi,$c_shui,$v_shui,$v_jby,$tid,$r,$jbhs){
	global $DB,$global;
	$jb=array();
	$sql="select * from `assay_pay` where `id`='{$tid}'";
	$pay=$DB->fetch_one_assoc($sql);
	$c=$pay['td16'];
	$c_shi =$jbhs['vd6'];
	$c_shui=$r['vd6'];
	// $P		= @(($c_shi*$ding_v-$c_shui*$v_shui)/($c_biao*$v_biao)*100);
	$hsl		= @((($c_shi-$c_shui)*$c*(159.68/2))/$v_biao);$hsl = _round($hsl, 4);
	$c_li	= $hsl;		//获取理论浓度
	$P=@($hsl/$c_biao*100);
	$Ps=Rounding_value($P,$vid,'J');
	$jb_jsgs= "回收量：((($c_shi-$c_shui)*$c*(159.68/2))/$v_biao) = $hsl  加标回收：$Ps=$hsl/$c_biao*100";
	$jb['c_li']=$c_li;
	$jb['P']=$P;
	$jb['jb_jsgs']=$jb_jsgs;
	return $jb;
}
//氯化铁的质量分数
function getjbData_679($c_biao,$v_biao,$ding_v,$c_shi,$c_shui,$v_shui,$v_jby,$tid,$r,$jbhs){
	global $DB,$global;
	$jb=array();
	$sql="select * from `assay_pay` where `id`='{$tid}'";
	$pay=$DB->fetch_one_assoc($sql);
	$c=$pay['td16'];
	$c_shi =$jbhs['vd6'];
	$c_shui=$r['vd6'];
	// $P		= @(($c_shi*$ding_v-$c_shui*$v_shui)/($c_biao*$v_biao)*100);
	$hsl		= @((($c_shi-$c_shui)*$c*(159.68/2))/$v_biao);
	$c_li	= $hsl;		//获取理论浓度
	$P=@($hsl/$c_biao*100);
	$Ps=Rounding_value($P,$vid,'J');
	$jb_jsgs= "回收量：((($c_shi-$c_shui)*$c*(159.68/2))/$v_biao) = $hsl  加标回收：$Ps=$hsl/$c_biao*100";
	$jb['c_li']=$c_li;
	$jb['P']=$P;
	$jb['jb_jsgs']=$jb_jsgs;
	return $jb;
}
//氧化铝
function getjbData_725($c_biao,$v_biao,$ding_v,$c_shi,$c_shui,$v_shui,$v_jby,$tid,$r,$jbhs){
	global $DB,$global;
	$jb=array();
	$sql="select * from `assay_pay` where id='{$tid}'";
	$pay=$DB->fetch_one_assoc($sql);
	$c=$pay['td17'];
	$c_shi =$jbhs['vd7'];
	$c_shui=$jbhs['vd6'];
	// $P		= @(($c_shi*$ding_v-$c_shui*$v_shui)/($c_biao*$v_biao)*100);
	$hsl		= @((($c_shi-$c_shui)*$c*(101.96/2))/$v_biao);
	$c_li	= $hsl;		//获取理论浓度
	$P=@($hsl/$c_biao*100);
	$Ps=Rounding_value($P,$vid,'J');
	$jb_jsgs= "回收量：((($c_shi-$c_shui)*$c*(101.96/2))/$v_biao) = $hsl  加标回收：$Ps=$hsl/$c_biao*100";
	$jb['c_li']=$c_li;
	$jb['P']=$P;
	$jb['jb_jsgs']=$jb_jsgs;
	return $jb;
}
//氧化铝的质量分数
function getjbData_793($c_biao,$v_biao,$ding_v,$c_shi,$c_shui,$v_shui,$v_jby,$tid,$r,$jbhs){
	global $DB,$global;
	$jb=array();
	$sql="select * from `assay_pay` where id='{$tid}'";
	$pay=$DB->fetch_one_assoc($sql);
	$c=$pay['td17'];
	$c_shi =$jbhs['vd7'];
	$c_shui=$jbhs['vd6'];
	// $P		= @(($c_shi*$ding_v-$c_shui*$v_shui)/($c_biao*$v_biao)*100);
	$hsl		= @((($c_shi-$c_shui)*$c*(101.96/2))/$v_biao);
	$hsl = _round($hsl, 3);
	$c_li	= $hsl;		//获取理论浓度
	$P=@($hsl/$c_biao*100);
	$Ps=Rounding_value($P,$vid,'J');
	$jb_jsgs= "回收量：((($c_shi-$c_shui)*$c*(101.96/2))/$v_biao) = $hsl  加标回收：$Ps=$hsl/$c_biao*100";
	$jb['c_li']=$c_li;
	$jb['P']=$P;
	$jb['jb_jsgs']=$jb_jsgs;
	return $jb;
}
//盐基度
function getjbData_605($c_biao,$v_biao,$ding_v,$c_shi,$c_shui,$v_shui,$v_jby,$tid,$r,$jbhs){
	global $DB,$global;
	$jb=array();
	$sql="select * from `assay_pay` where id='{$tid}'";
	$pay=$DB->fetch_one_assoc($sql);
	$c=$pay['td17'];
	$c_shi =$jbhs['vd5'];
	$c_shui=$jbhs['vd4'];
	// $P		= @(($c_shi*$ding_v-$c_shui*$v_shui)/($c_biao*$v_biao)*100);
	$hsl		= @((($c_shi-$c_shui)*$c*(204.22))/$v_biao);
	$hsl = _round($hsl, 3);
	$c_li	= $hsl;		//获取理论浓度
	$P=@($hsl/$c_biao*100);
	$Ps=Rounding_value($P,$vid,'J');
	$jb_jsgs= "回收量：((($c_shi-$c_shui)*$c*204.22)/$v_biao) = $hsl  加标回收：$Ps=$hsl/$c_biao*100";
	$jb['c_li']=$c_li;
	$jb['P']=$P;
	$jb['jb_jsgs']=$jb_jsgs;
	return $jb;
}
//还原性物质
function getjbData_685($c_biao,$v_biao,$ding_v,$c_shi,$c_shui,$v_shui,$v_jby,$tid,$r,$jbhs){
	global $DB,$global;
	$jb=array();
	$sql="select * from `assay_pay` where id='{$tid}'";
	$pay=$DB->fetch_one_assoc($sql);
	$c=$pay['td17'];
	$c_shi =$jbhs['vd6'];
	$c_shui=$r['vd6'];
	// $P		= @(($c_shi*$ding_v-$c_shui*$v_shui)/($c_biao*$v_biao)*100);
	$hsl		= @((($c_shi-$c_shui)*$c*0.05585*1000)/$v_biao);
	$hsl = _round($hsl, 4);
	$c_li	= $hsl;		//获取理论浓度
	$P=@($hsl/$c_biao*100);
	$Ps=Rounding_value($P,$vid,'J');
	$jb_jsgs= "回收量：((($c_shi-$c_shui)*$c*0.05585*1000)/$v_biao) = $hsl  加标回收：$Ps=$hsl/$c_biao*100";
	$jb['c_li']=$c_li;
	$jb['P']=$P;
	$jb['jb_jsgs']=$jb_jsgs;
	return $jb;
}
//氧化铁
function getjbData_683($c_biao,$v_biao,$ding_v,$c_shi,$c_shui,$v_shui,$v_jby,$tid,$r,$jbhs){
	global $DB,$global;
	$jb=array();
	$sql="select * from `assay_pay` where id='{$tid}'";
	$pay=$DB->fetch_one_assoc($sql);
	$c=$pay['td17'];
	$c_shi =$jbhs['vd6'];
	$c_shui=$jbhs['vd7'];
	// $P		= @(($c_shi*$ding_v-$c_shui*$v_shui)/($c_biao*$v_biao)*100);
	$hsl		= @((($c_shi-$c_shui)*$c*(159.68/2))/$v_biao);
	$hsl = _round($hsl, 3);
	$c_li	= $hsl;		//获取理论浓度
	$P=@($hsl/$c_biao*100);
	$Ps=Rounding_value($P,$vid,'J');
	$jb_jsgs= "回收量：((($c_shi-$c_shui)*$c*(159.68/2))/$v_biao) = $hsl  加标回收：$Ps=$hsl/$c_biao*100";
	$jb['c_li']=$c_li;
	$jb['P']=$P;
	$jb['jb_jsgs']=$jb_jsgs;
	return $jb;
}
//氨态氮
function getjbData_687($c_biao,$v_biao,$ding_v,$c_shi,$c_shui,$v_shui,$v_jby,$tid,$r,$jbhs){
	global $DB,$global;
	$jb=array();
	$sql="select * from `assay_pay` where id='{$tid}'";
	$pay=$DB->fetch_one_assoc($sql);
	$c=$pay['td17'];
	$c_shi =$jbhs['vd6'];
	$c_shui=$jbhs['vd7'];
	// $P		= @(($c_shi*$ding_v-$c_shui*$v_shui)/($c_biao*$v_biao)*100);
	$hsl		= @((($c_shi-$c_shui)*$c*(159.68/2))/$v_biao);
	$c_li	= $hsl;		//获取理论浓度
	$P=@($jbhs['vd7']/$c_biao*100);
	$Ps=Rounding_value($P,$vid,'J');
	$jb_jsgs= "加标回收：$Ps=($jbhs[vd7]/$c_biao*100)";
	$jb['c_li']=$c_li;
	$jb['P']=$P;
	$jb['jb_jsgs']=$jb_jsgs;
	return $jb;
}
