<?php
/**
 * 功能：更新化验单
 * 作者：铁龙
 * 日期：2014-04-14
 * 描述: 化验单的计算和保存,真正的修约保存在 assay_pay_modi.php
*/
include '../temp/config.php';
require './assay_form_func.php';
require './zhikong_func.php';
$fzx_id = FZX_ID;
if(count($_POST['mission'])<1){
	die('line里面没有mission 或者没有化验内容！');
}
$token_key = $_SESSION['token_key']['hyd'][$_POST['tid']];
if(!empty($token_key) && $token_key == $_POST['token_key']){
	//非多合一
	if(!intval($dhy_arr[$_POST['vid']])){
		include './assay_pay_modi.php';
	}else{
		$zhu_vid = $dhy_arr[$_POST['vid']];	//主项目vid
		//获取多合一化验单列表
		$vid_hyd = get_hyd_id($_POST['cyd_id'],$_POST['vid']);
		//需要修改的 assay_order 表的id列表
		$mission = $_POST['mission'];
		$mi_flip = array_flip($mission);
		$flag_id = array_flip($vid_hyd[$_POST['vid']]['oid']);
		/*//多合一化验单存在相同站点相同flag的两条及以上数据时会丢失数据
		foreach ($mission as $key => $value) {
			$flag_key[$flag_id[$value]]=$key;
		}*/
		foreach ($flag_id as $key => $value) {
			$flag_key[$value]=$mi_flip[$key];
		}
		// echo '<div style="text-align:left">';
		// print_rr($dhy_arr);
		// if($u['admin']){echo 'vid_hyd<br />';print_rr($vid_hyd);echo 'mission<br />';print_rr($mission);echo 'mi_flip<br />';print_rr($mi_flip);echo 'flag_id<br />';print_rr($flag_id);echo 'flag_key<br />';print_rr($flag_key);}
		// echo '</div>';
		// die;
		//多合一化验单保存
		foreach ($vid_hyd as $vid => $pay) {
			$_POST['tid'] = $pay['tid'];
			$_POST['vid'] = $pay['vid'];
			if( is_array($dhy_arr['vd'][$zhu_vid][$pay['vid']]) ){
				foreach ($dhy_arr['vd'][$zhu_vid][$pay['vid']] as $vd_new => $vd_old) {
					$_POST[$vd_new] = $_POST[$vd_old];
				}
			}
			foreach ($flag_key as $key => $id) {
				$_POST['mission'][$id] = $pay['oid'][$key];
			}
			include './assay_pay_modi.php';
		}
	}
	if(''!=trim($_POST['goto_url'])){
		header('location:'.trim($_POST['goto_url']));
	}else{
		echo json_encode(array('error'=>'0','content'=>''));
	}
}else{
	// 重新生成加密令牌
	$token_key = $_SESSION['token_key']['hyd'][$_POST['tid']] = md5(uniqid(rand()));
	echo json_encode(array('error'=>'2','content'=>'页面已失效，请刷新页面后重新提交！','token_key'=>$token_key));
}
?>