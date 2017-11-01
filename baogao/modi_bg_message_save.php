<?php
include("../temp/config.php");
if(empty($u['userid'])){
	nologin();
}
$fzx_id=$u['fzx_id'];
if($_POST['cyd_id']){
	//更新报告信息
	if(!empty($_POST['sites'])){
		foreach($_POST['sites'] as $key=>$value){
			$bg_bh= (int)$value['bg_bh'];
			$DB->query("UPDATE report SET `jcbz_id`='{$value['jcbz_id']}',water_type='".$value['water_type']."',year='".$value['year']."',wtdw='".$_POST['wtdw']."',xuhao='".$value['xuhao']."',bg_lx='".$value['bg_lx']."',xm_px='".$value['xm_px']."',bg_bh='".$bg_bh."',wt_dz='".$_POST['wt_dz']."',cy_place='".$value['cy_place']."',tel='".$_POST['tel']."',sj_date='".$_POST['sj_date']."',bg_dy_date='".$_POST['bg_dy_date']."',date_lx='".$_POST['date_lx']."',jy_lb='".$_POST['jy_lb']."',yp_sl='".$value['yp_sl']."',jy_jl='".$value['jy_jl']."',jcy_ypzb='".$value['jcy_ypzb']."',beizhu='".$value['beizhu']."',pj_yj='".$value['pj_yj']."',yp_zt='".$value['yp_zt']."' WHERE cyd_id='".$_POST['cyd_id']."' AND cy_rec_id='".$key."'");
		}
	}
	gotourl("modi_bg_message_list.php?cyd_id=".$_POST['cyd_id']."&cy_date=".$_POST['cy_date']);

}else{
    die('没有有效验收记录，无法修改检测报告信息！');

}

?>