<?php
require_once "../temp/config.php";
$fzx_id = $u['fzx_id'];
if($_POST['ping']&&$_GET['flag']=='pingzi'&&$_GET['cid']&&$_GET['cyd_id']){
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
	gotourl($_SERVER['HTTP_REFFERER']);
}else{
	$cyd_id = $_POST['id'];
	$sqlrec = $DB->query("select * from cy_rec where cyd_id = '".$_POST['id']."'");
	while($rec = $DB->fetch_assoc($sqlrec)){
		if(!empty($_POST['ys_status'][$rec['id']])){
			$ys_zt = implode(',',$_POST['ys_status'][$rec['id']]);
			$uprec = $DB->query("update cy_rec set ys_zt='$ys_zt' where id = '".$rec['id']."'");
		}
	}
	if($_POST['yy']&&$_POST['mm']&&$_POST['dd']){
		$ys_date = $_POST['yy']."-".$_POST['mm']."-".$_POST['dd'];
		$_POST['cyd']['ys_date'] = $ys_date;
	}
	$rs_cy	= get_cyd( $cyd_id );
	$cy_json_arr= json_decode($rs_cy['json'],true);
	$cy_json_arr['userid_img']['ys_user']	= $u['userid_img'];//电子签名信息
	$_POST['cyd']['json']		= JSON($cy_json_arr);
	$_POST['cyd']['ys_user']	= $u['userid'];
	update_rec( 'cy', $_POST['cyd'], $cyd_id );
	$DB->query( "UPDATE cy SET status = '5' WHERE id = $_POST[id] AND status <= '5'");
	gotourl("cy_ys.php?cyd_id=$_POST[id]");
}


