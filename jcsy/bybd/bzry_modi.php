<?php
/**
 * 功能：标准溶液新建,修改,计算,保存模块
 * 作者：Mr Zhou
 * 日期：2014-12-04
 * 描述：标准溶液新建,修改,计算,保存模块
*/
include "../../temp/config.php";
$fzx_id = FZX_ID;
//$_POST['zd'] 终点 $_POST['sd']始点 $sj:实际用量 $bz_nongdu:标定结果,标准溶液浓度
$today=date('Y-m-d');


$ziduan = array('fzx_id','vid','jcyj','jzry_id','jzry_name','jzry_pzrq','jzry_nd','jzry_nddw','bzry_id','bzry_name','bzry_pzrq','bzry_pznd','bzry_bdrq','bzry_bdff','zsj_name','mol_m','kb_sd','kb_zd','kb_yl','gongshi','bzry_notes','fx_user','table_type','jielun','beizhu');

//以下代码计算出滴定结果
/************************************************************************/
$len_arr = explode('.', trim($_POST['sd'][0]));
$len = strlen($len_arr[1]);
$sum=0;
$bd_nd = $tj = $yl = array();
if(''!=$_POST['kb_sd']&&''!=$_POST['kb_zd']){
    $kong_bai=$_POST['kb_yl']=floatval($_POST['kb_zd'])-floatval($_POST['kb_sd']);
}else{
    $kong_bai=0;
    $_POST['kb_sd']=$_POST['kb_zd']=$_POST['kb_yl']='-';
}
for($i=1;$i<=4;$i++){
    if(''==$_POST['tj'][$i]) break;
    //计算公式
    if(floatval($_POST['mol_m'])){
        $bd_nd[$i]=round_yxws((floatval($_POST['tj'][$i])*1000)/($_POST['yl'][$i])/$_POST['mol_m'],4);
    }else{
        $bd_nd[$i]=round_yxws((floatval($_POST['jzry_nd'])*floatval($_POST['tj'][$i]))/($_POST['yl'][$i]),4);
    }
    $tj[$i] = $_POST['tj'][$i];
    $yl[$i] = $_POST['yl'][$i];
    $sum1+=$bd_nd[$i];
    $dnd[1]=round_yxws($sum1/($i),4,10);
    $max1=($max1<$bd_nd[$i])?$bd_nd[$i]:$max1;
    $min1=($min1<$bd_nd[$i]&&$min1!=0)?$min1:$bd_nd[$i];
    $max=($max<$bd_nd[$i])?$bd_nd[$i]:$max;
    $min=($min<$bd_nd[$i]&&$min!=0)?$min:$bd_nd[$i];
}
$a=1;
for($i=5;$i<9;$i++){
    $_POST['tj'][$i];
    if(''==$_POST['tj'][$i]) break;
    $_POST['tj'][$i];
    //计算公式
    if(floatval($_POST['mol_m'])){
        $bd_nd[$i]=round_yxws((floatval($_POST['tj'][$i])*1000)/($_POST['yl'][$i])/$_POST['mol_m'],4);
    }else{
        $bd_nd[$i]=round_yxws((floatval($_POST['jzry_nd'])*floatval($_POST['tj'][$i]))/($_POST['yl'][$i]),4);
    }
    $tj[$i] = $_POST['tj'][$i];
    $yl[$i] = $_POST['yl'][$i];
    $sum2+=$bd_nd[$i];
    $dnd[2]=round_yxws($sum2/($a),4);
    $max2=($max2<$bd_nd[$i])?$bd_nd[$i]:$max2;
    $min2=($min2<$bd_nd[$i]&&$min2!=0)?$min2:$bd_nd[$i];
    $max=($max<$bd_nd[$i])?$bd_nd[$i]:$max;
    $min=($min<$bd_nd[$i]&&$min!=0)?$min:$bd_nd[$i];
    $a++;
}
$bzry_nongdu=round_yxws(($dnd[1]+$dnd[2])/2,4);
$djc[1]=_round(($max1-$min1)/$dnd[1],4);
$djc[2]=_round(($max2-$min2)/$dnd[2],4);
$bzry_jc=_round(($max-$min)/$bzry_nongdu,4);

$_POST['fzx_id'] = $fzx_id;
$sql_arr = array();
$sql_arr[] = "`yl`='".json_encode($yl)."'";
$sql_arr[] = "`tj`='".json_encode($tj)."'";
$sql_arr[] = "`bd_nd`='".json_encode($bd_nd)."'";
$sql_arr[] = "`dnd`='".json_encode($dnd)."'";
$sql_arr[] = "`bzry_nongdu`='".$bzry_nongdu."'";
$sql_arr[] = "`djc`='".json_encode($djc)."'";
$sql_arr[] = "`bzry_jc`='".$bzry_jc."'";
if('add'==$_POST['action']){
    $_POST['fx_user'] = $u['userid'];
}
if(''==$_POST['fx_user']){
    $_POST['fx_user'] = $u['userid'];
}
foreach($ziduan as $value){
        $sql_arr[] = "`$value`='".trim($_POST[$value])."'";
}
$sql_str = implode(',', $sql_arr);
/************************************************************************/
switch($_POST['act']){    //新建标准溶液保存
    case '保存':
        $sql = "INSERT INTO `jzry_bd` SET $sql_str ";
        $DB->query($sql);
        $id=$DB->insert_id();
        gotourl("bzry_bd.php?bd_id=$id&action=view");
        break;
    case '修改':
        $bd_id   = intval($_POST['bd_id']);
         $sql = "UPDATE  `jzry_bd` SET $sql_str WHERE `id`='$bd_id'";
        $DB->query($sql);
        gotourl("bzry_bd.php?bd_id=$bd_id&action=view");
        break;
}
?>