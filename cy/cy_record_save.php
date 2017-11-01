<?php
/**
  * 功能：采样记录表信息的保存
  * 作者：zhengsen
  * 时间：2014-04-21
**/
include '../temp/config.php';
$cyd_id = $_POST['cyd_id'];
$fzx_id = $u['fzx_id'];
if($_POST['ping']&&$_GET['flag']=='pingzi'&&$_GET['cid']&&$_POST['cyd_id']){
  $rq_sql="SELECT * FROM `rq_value` WHERE vid!='' AND fzx_id='".$fzx_id."'  ORDER BY id";
    $rq_query=$DB->query($rq_sql);
    $rq_data = array();
    $pingarr = array();
    while($rq_rs=$DB->fetch_assoc($rq_query))
    {
      $rq_data[$rq_rs['id']]['id'] = $rq_rs['id'];
        $rq_data[$rq_rs['id']]['rq_name']=$rq_rs['rq_name'];
        $rq_data[$rq_rs['id']]['bcj']=$rq_rs['bcj'];
        $rq_data[$rq_rs['id']]['rq_size']=$rq_rs['rq_size'];
        $rq_data[$rq_rs['id']]['vid']=$rq_rs['vid'];
        $rq_data[$rq_rs['id']]['fenlei']=$rq_rs['fenlei'];
        $rq_data[$rq_rs['id']]['mr_shu']=$rq_rs['mr_shu'];
    }
    //判断cy_rec是否有对应的数据
  if($_POST['ping'][$_GET['cid']]){
    foreach($_POST['ping'][$_GET['cid']] as $pk=>$pv){
      if($pv){
        if($rq_data[$pk]['fenlei']=='玻璃瓶'){//玻璃瓶和塑料瓶分开存
          $pingarr['玻璃瓶'][] = $rq_data[$pk]['rq_name'].':'.$pv;
        }else{
          $pingarr['塑料瓶'][] = $rq_data[$pk]['rq_name'].':'.$pv;
        }
      }
    }
  }else{
    $pingstr = '';
  }
  if($pingarr){
    $pingstr =JSON($pingarr);
  }
  if($pingstr){
    $upsql = $DB->query("update cy_rec set pingstr='$pingstr' where id = '".$_GET['cid']."'");
  }
}
//更新cy表里的数据
if($cyd_id ){
	update_record( 'cy', $_POST['cyd'], " id = $cyd_id" );
}
//把现场的数据更新到assay_order表里
if(!empty($_POST['xcjc'])){
	foreach($_POST['xcjc'] as $k=>$v){
			$up_order=$DB->query("update `assay_order` set `vd0`='".$v."',`_vd0`='".$v."' where id='".$k."'");
	}
}
$i = 0;
foreach( $_POST['d'] as $k=>$v) {
//对时间进行修正
	$v = my_trim($v);
	$cy_rec_data=$v;
	$cy_rec_data['cy_note']=$v['cy_note'];
	$str=array('.','。',';','：');
	$cy_rec_data['cy_time']=str_replace($str,':',$v['cy_time']);

	if($cy_rec_data['status'] == '-1'){
		$i++;
	}

        if(!empty($_POST['ys_status'][$k])){
           $cy_rec_data['ys_zt']= implode(',',$_POST['ys_status'][$k]);
        }else{
           $cy_rec_data['ys_zt']= ''; 
        }

	update_record( 'cy_rec', $cy_rec_data, " id = $k" );
	update_mission( $k, $cy_rec_data['status'] );
    
}

update_mission_status($cyd_id);
//如果是采样人签字
if(!empty($_POST['cy_user_qz'])){
	gotourl('cy_record_qz.php?action=cy_user_qz&cyd_id='.$cyd_id);	
}
//如果是样品接收人签字
elseif(!empty($_POST['ypjs_user_qz'])){
	gotourl('cy_record_qz.php?action=ypjs_user_qz&cyd_id='.$cyd_id);
}else{
	gotourl( $_SESSION['back_url'] );
}

function update_mission( $cid, $status ) {
    global $DB;
    $DB->query( "UPDATE mission SET status = '".$status."' WHERE cid = '".$cid."' " );
}

function update_mission_status($cyd_id) {
    global $DB;
    $cyd = get_cyd($cyd_id);
    if( $cyd['xc_exam_flag'] ){
        $sql = "SELECT * FROM mission WHERE cyd_id = '".$cyd_id."' AND reagent = '常规1'";
        $res = $DB->query($sql);
        while($row = $DB->fetch_assoc($res)){
            $av = elementsToArray($row['assay_element']);
            if(!array_diff($av,array('35','46','47'))){
                $DB->query("UPDATE mission SET status = '0' WHERE cid=$row[id] AND reagent = '常规1'");
            }
        }
        $DB->query("UPDATE mission m, cy_rec cr SET m.status = '0' WHERE m.cid = cr.id AND cr.cyd_id = '".$cyd_id."' AND m.reagent = 'DO'");
    }
}

?>