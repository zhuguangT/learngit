<?php
//退回原因填写和处理页面
include "../temp/config.php";
//include "../inc/cy_func.php";
if(!$_GET['id']){
        echo "链接错误，请联系系统维护人员";
        exit;
}
if($_POST['action']=='hyd' && !empty($_POST['yuanYin'])&&!empty($_POST['id'])){//化验单退回
	$jueSe = '';
	$rsPay = $DB->fetch_one_assoc("select cyd_id,json from `assay_pay` where id=".$_POST['id']);
	if(empty($rsPay['sign_02']))$jueSe = '校核人';
	elseif(empty($rsPay['sign_03'])){
		if($u['userid']==$rsPay['sign_02'])$jueSe = '校核人';
		else $jueSe = '复核人';
	}
	elseif(empty($rsPay['sign_04'])){
		if($u['userid']==$rsPay['sign_03'])$jueSe = '复核人';
                else $jueSe = '审核人';
	}
	if($rsPay['json']!='')$json = json_decode($rsPay['json'],true);
	else $json = array();
	if(stristr($_POST['yuanYin'],"\r\n")){//转换换行符
                $_POST['yuanYin'] = str_replace("\r\n","<br />",$_POST['yuanYin']);
        }
	$json['退回'][]= array(
		'tuiHuiUser'  => $u['userid'],
        	'tuiHuiTime'  => date("Y-m-d H:m:s"),
        	'tuiHuiReason'=> $_POST['yuanYin'],
		'tuiHuiJueSe' => $jueSe
	);
	//以下是对json中 特殊符号的转义“\”和“"”需要进行两次转义，“'”需要进行一次转义
	if(!get_magic_quotes_gpc()){
		$find_arr       = array("\\","\"","'");
		$replace_arr    = array("\\\\\\\\","\\\\\\\"","\\'");
	}else{
		$find_arr       = array("\\","\"");
		$replace_arr    = array("\\\\","\\\\\"");
	}
	foreach($json['退回'] as $key=>$value){
	        $json['退回'][$key]['tuiHuiReason']     = str_replace($find_arr,$replace_arr,$value['tuiHuiReason']);
	}
	$jsonStr = JSON($json);
	//将化验单退回到已开始状态
	//$clear_sign_date = "sign_date_01=null,sign_date_012=null,sign_date_02=null,sign_date_03=null,sign_date_04=null,";
	$DB->query("UPDATE `assay_pay` SET sign_01='', sign_012='',sign_02='',sign_03='',sign_04='',over='已开始',json='$jsonStr' where id='{$_POST['id']}'");
	$yxrow=$DB->affected_rows();
	
	if($yxrow==1){
		//将化验已完成状态(7)改成(6)已生成化验并将已完成的化验单数减一
		$sql	= "UPDATE `cy` SET `status` = 6,`hyd_wc_count` = `hyd_wc_count` - 1 WHERE id='".$rsPay['cyd_id']."'";
		$yxrow	= $DB->query($sql) ? 1 : 0;
	}
	if('1'==$_POST['ajax']){
		die(json_encode(($yxrow==1) ? array('error'=>'0') : array('error'=>'1')));
	}else{
		if($yxrow==1)echo "<script>parent.location.reload();</script>";
		else echo "回退失败，请刷新页面重试！";
	}
	
}else if($_POST['action']=='cyd' && !empty($_POST['yuanYin'])&&!empty($_POST['id'])){//采样单退回
	$rsCyd	= $DB->fetch_one_assoc("SELECT json FROM `cy` WHERE id='".$_POST['id']."'");
	if($rsCyd['json']!=''){
		$json	= json_decode($rsCyd['json'],true);
	}else{
		$json	= array();
	}
	if(stristr($_POST['yuanYin'],"\r\n")){//转换换行符
		$_POST['yuanYin'] = str_replace("\r\n","<br />",$_POST['yuanYin']);
	}
	//$_POST['yuanYin']	= get_str(get_str($_POST['yuanYin']));
	$json['退回'][]	= array(
                'tuiHuiUser'  => $u['userid'],
                'tuiHuiTime'  => date("Y-m-d H:m:s"),
                'tuiHuiReason'=> $_POST['yuanYin'],
        );
	//以下是对json中 特殊符号的转义“\”和“"”需要进行两次转义，“'”需要进行一次转义
	if(!get_magic_quotes_gpc()){
		$find_arr	= array("\\","\"","'");
		$replace_arr	= array("\\\\\\\\","\\\\\\\"","\\'");
	}else{
		$find_arr       = array("\\","\"");
                $replace_arr    = array("\\\\","\\\\\"");
	}
	foreach($json['退回'] as $key=>$value){
		$json['退回'][$key]['tuiHuiReason']	= str_replace($find_arr,$replace_arr,$value['tuiHuiReason']);
		$json['退回'][$key]['xiuGaiLiYou']	= str_replace($find_arr,$replace_arr,$value['xiuGaiLiYou']);
	}
	$jsonStr= JSON($json);
	$DB->query("UPDATE `cy` SET `cy_user_qz`='',`cy_user_qz2`='',`sh_user_qz`='',json='".$jsonStr."' WHERE id='".$_POST['id']."'");
	$yxrow	= $DB->affected_rows();
	if($yxrow==1){
		echo "<script>parent.location.reload();</script>";
	}else{
		echo "回退失败，请刷新页面重试！";
	}
}else if($_POST['action']=='cyd_modify' && !empty($_POST['yuanYin']) && !empty($_POST['id'])){//采样人填写修改理由
	$rsCyd	= $DB->fetch_one_assoc("SELECT cy_user2,json FROM `cy` WHERE id='".$_POST['id']."'");
        if($rsCyd['json']!=''){
                $json	= json_decode($rsCyd['json'],true);
        }else{
                $json	= array();
        }
        if(stristr($_POST['yuanYin'],"\r\n")){//转换换行符
                $_POST['yuanYin'] = str_replace("\r\n","<br />",$_POST['yuanYin']);
        }
	$end_json	= count($json['退回'])-1;
	if(!empty($rsCyd['cy_user2'])){
		if(!empty($json['退回'][$end_json]['xiuGaiLiYou'])){
			$json['退回'][$end_json]['xiuGaiLiYou'] .= "<br />".$u['userid'].":".$_POST['yuanYin'];
		}else{
			$json['退回'][$end_json]['xiuGaiLiYou']  = $u['userid'].":".$_POST['yuanYin'];
		}
	}else{
		$json['退回'][$end_json]['xiuGaiLiYou'] = $_POST['yuanYin'];
	}
	//以下是对json中 特殊符号的转义“\”和“"”需要进行两次转义，“'”需要进行一次转义
        if(!get_magic_quotes_gpc()){
                $find_arr	= array("\\","\"","'");
                $replace_arr	= array("\\\\\\\\","\\\\\\\"","\\'");
        }else{
                $find_arr	= array("\\","\"");
                $replace_arr	= array("\\\\","\\\\\"");
        }
        foreach($json['退回'] as $key=>$value){
                $json['退回'][$key]['tuiHuiReason']     = str_replace($find_arr,$replace_arr,$value['tuiHuiReason']);
		$json['退回'][$key]['xiuGaiLiYou']	= str_replace($find_arr,$replace_arr,$value['xiuGaiLiYou']);
        }
        $jsonStr= JSON($json);
	$DB->query("UPDATE `cy` SET json='".$jsonStr."' WHERE id='".$_POST['id']."'");
	//$DB->query("UPDATE `cy` SET `cy_user_qz`='{$u['userid']}',`cy_user_qz_date`=curdate(),json='".$jsonStr."' WHERE id='".$_POST['id']."'");
        $yxrow  = $DB->affected_rows();
        if($yxrow==1){
		if(empty($_POST['button_name'])){
			$_POST['button_name']	= 'cy_user_qz';
		}
        //echo "<script>parent.location.reload();</script>";
		//返回并以签字人的身份提交现场采样记录表
		echo "<script>var form = window.parent.document.forms[\"cyrec\"];var newInput = document.createElement(\"input\");newInput.type='hidden';newInput.name='{$_POST['button_name']}';newInput.value='签字';form.appendChild(newInput);form.submit();</script>";
        }else{
                echo "签字失败，请刷新页面重试！";
        }
}else if($_POST['action']=='bzqx' && !empty($_POST['yuanYin']) && !empty($_POST['id'])){//曲线退回
	//取出数据库中原有json信息
	$rsCyd	= $DB->fetch_one_assoc("SELECT `json` FROM `standard_curve` WHERE id='".$_POST['id']."'");
	if($rsCyd['json']!=''){
		$json	= json_decode($rsCyd['json'],true);
	}else{
		$json	= array();
	}
	//将退回原因等信息“转义”后附加到json中
	if(stristr($_POST['yuanYin'],"\r\n")){//转换换行符
		$_POST['yuanYin'] = str_replace("\r\n","<br />",$_POST['yuanYin']);
	}
	//$_POST['yuanYin']	= get_str(get_str($_POST['yuanYin']));
	$json['退回'][]	= array(
		'tuiHuiUser'  => $u['userid'],
		'tuiHuiTime'  => date("Y-m-d H:m:s"),
		'tuiHuiReason'=> $_POST['yuanYin'],
	);
	//以下是对json中 特殊符号的转义“\”和“"”需要进行两次转义，“'”需要进行一次转义
	if(!get_magic_quotes_gpc()){
		$find_arr	= array("\\","\"","'");
		$replace_arr= array("\\\\\\\\","\\\\\\\"","\\'");
	}else{
		$find_arr	= array("\\","\"");
		$replace_arr= array("\\\\","\\\\\"");
	}
	foreach($json['退回'] as $key=>$value){
		$json['退回'][$key]['tuiHuiReason']	= str_replace($find_arr,$replace_arr,$value['tuiHuiReason']);
		//$json['退回'][$key]['xiuGaiLiYou']	= str_replace($find_arr,$replace_arr,$value['xiuGaiLiYou']);
	}
	$jsonStr= JSON($json);
	//将新的json内容存储到数据库中
	$DB->query("UPDATE `standard_curve` SET `sign_01`='',`sign_02`='',`sign_03`='',`sign_04`='',`status`='被退回',json='".$jsonStr."' WHERE id='".$_POST['id']."'");
	//判断存储状态并返回页面
	$yxrow	= $DB->affected_rows();
	if('1'==$_POST['ajax']){
		die(json_encode(($yxrow==1) ? array('error'=>'0','content'=>'') : array('error'=>'1','content'=>'回退失败，请刷新页面重试！')));
	}else{
		if($yxrow==1)echo "<script>parent.location.reload();</script>";
		else echo "回退失败，请刷新页面重试！";
	}
}else if($_POST['action']=='bzqx_modify' && !empty($_POST['yuanYin']) && !empty($_POST['id'])){//分析人填写修改理由
	//取出数据库中原有json信息
	$rsCyd	= $DB->fetch_one_assoc("SELECT json FROM `standard_curve` WHERE id='".$_POST['id']."'");
	if($rsCyd['json']!=''){
		$json	= json_decode($rsCyd['json'],true);
	}else{
		$json	= array();
	}
	//将退回原因等信息“转义”后附加到json中
	if(stristr($_POST['yuanYin'],"\r\n")){//转换换行符
		$_POST['yuanYin'] = str_replace("\r\n","<br />",$_POST['yuanYin']);
	}
	$end_json	= count($json['退回'])-1;
	$json['退回'][$end_json]['xiuGaiLiYou'] = $_POST['yuanYin'];
	//以下是对json中 特殊符号的转义“\”和“"”需要进行两次转义，“'”需要进行一次转义
	if(!get_magic_quotes_gpc()){
		$find_arr	= array("\\","\"","'");
		$replace_arr= array("\\\\\\\\","\\\\\\\"","\\'");
	}else{
		$find_arr	= array("\\","\"");
		$replace_arr= array("\\\\","\\\\\"");
	}
	foreach($json['退回'] as $key=>$value){
		$json['退回'][$key]['tuiHuiReason']	= str_replace($find_arr,$replace_arr,$value['tuiHuiReason']);
		$json['退回'][$key]['xiuGaiLiYou']	= str_replace($find_arr,$replace_arr,$value['xiuGaiLiYou']);
	}
	$jsonStr= JSON($json);
	$DB->query("UPDATE `standard_curve` SET json='".$jsonStr."' WHERE id='".$_POST['id']."'");
	$yxrow  = $DB->affected_rows();
	if('1'==$_POST['ajax']){
		die(json_encode(($yxrow==1) ? array('error'=>'0','content'=>'') : array('error'=>'1','content'=>'签字失败，请刷新页面重试！')));
	}else{
		if($yxrow==1)echo "<script>parent.location.reload();</script>";
		else echo "签字失败，请刷新页面重试！";
	}
}else if($_POST['action']=='bd' && !empty($_POST['yuanYin'])&&!empty($_POST['id'])){//标定记录退回
	$rsCyd	= $DB->fetch_one_assoc("SELECT `json` FROM `jzry_bd` WHERE id='".$_POST['id']."'");
	if($rsCyd['json']!=''){
		$json	= json_decode($rsCyd['json'],true);
	}else{
		$json	= array();
	}
	if(stristr($_POST['yuanYin'],"\r\n")){//转换换行符
		$_POST['yuanYin'] = str_replace("\r\n","<br />",$_POST['yuanYin']);
	}
	//$_POST['yuanYin']	= get_str(get_str($_POST['yuanYin']));
	$json['退回'][]	= array(
                'tuiHuiUser'  => $u['userid'],
                'tuiHuiTime'  => date("Y-m-d H:m:s"),
                'tuiHuiReason'=> $_POST['yuanYin'],
        );
	//以下是对json中 特殊符号的转义“\”和“"”需要进行两次转义，“'”需要进行一次转义
	if(!get_magic_quotes_gpc()){
		$find_arr	= array("\\","\"","'");
		$replace_arr	= array("\\\\\\\\","\\\\\\\"","\\'");
	}else{
		$find_arr       = array("\\","\"");
                $replace_arr    = array("\\\\","\\\\\"");
	}
	foreach($json['退回'] as $key=>$value){
		$json['退回'][$key]['tuiHuiReason']	= str_replace($find_arr,$replace_arr,$value['tuiHuiReason']);
		$json['退回'][$key]['xiuGaiLiYou']	= str_replace($find_arr,$replace_arr,$value['xiuGaiLiYou']);
	}
	$jsonStr= JSON($json);
	$DB->query("UPDATE `jzry_bd` SET `sign_01`='',`jh_user`='',`fh_user`='',`sh_user`='',json='".$jsonStr."' WHERE id='".$_POST['id']."'");
	$yxrow	= $DB->affected_rows();
	die(json_encode(($yxrow==1) ? array('error'=>'0','content'=>'') : array('error'=>'1','content'=>'回退失败，请刷新页面重试！')));
}else if($_POST['action']=='bd_modify' && !empty($_POST['yuanYin']) && !empty($_POST['id'])){
	//标定人填写修改理由
	$rsCyd	= $DB->fetch_one_assoc("SELECT `json` FROM `jzry_bd` WHERE id='".$_POST['id']."'");
    if($rsCyd['json']!=''){
            $json	= json_decode($rsCyd['json'],true);
    }else{
            $json	= array();
    }
    if(stristr($_POST['yuanYin'],"\r\n")){//转换换行符
            $_POST['yuanYin'] = str_replace("\r\n","<br />",$_POST['yuanYin']);
    }
	$end_json	= count($json['退回'])-1;
	$json['退回'][$end_json]['xiuGaiLiYou'] = $_POST['yuanYin'];
	//以下是对json中 特殊符号的转义“\”和“"”需要进行两次转义，“'”需要进行一次转义
        if(!get_magic_quotes_gpc()){
                $find_arr	= array("\\","\"","'");
                $replace_arr	= array("\\\\\\\\","\\\\\\\"","\\'");
        }else{
                $find_arr	= array("\\","\"");
                $replace_arr	= array("\\\\","\\\\\"");
        }
        foreach($json['退回'] as $key=>$value){
                $json['退回'][$key]['tuiHuiReason']     = str_replace($find_arr,$replace_arr,$value['tuiHuiReason']);
		$json['退回'][$key]['xiuGaiLiYou']	= str_replace($find_arr,$replace_arr,$value['xiuGaiLiYou']);
        }
        $jsonStr= JSON($json);
		$fx_qz_date = empty($_POST['fx_qz_date']) ? date('Y-m-d') : $_POST['fx_qz_date'];
		$DB->query("UPDATE `jzry_bd` SET `sign_01`='{$u['userid']}',`fx_qz_date`='{$fx_qz_date}', `json`='".$jsonStr."' WHERE id='".$_POST['id']."'");
        $yxrow  = $DB->affected_rows();
        die(json_encode(($yxrow==1) ? array('error'=>'0','content'=>'') : array('error'=>'1','content'=>'签字失败，请刷新页面重试！')));
}
//区分采样单退回和化验单退回输入框里的提醒
if($_GET['action'] == 'cyd'){
	$placeholder	= '请在此处填写退回此采样单的原因';
	$button_value	= '确认退回';
}else if($_GET['action'] == 'hyd'){
	$placeholder    = '请在此处填写退回此化验单的原因';
	$button_value	= '确认退回';
}else if($_GET['action'] == 'cyd_modify'){
	$placeholder	= '请填写修改此采样单的理由';
	$button_value	= '确认修改';
}else if($_GET['action'] == 'bzqx'){
	$placeholder	= '请在此处填写退回此曲线记录表的原因';
	$button_value	= '确认退回';
}else if($_GET['action'] == 'bzqx_modify'){
	$placeholder	= '请在此处填写修改此曲线记录表的理由';
	$button_value	= '确认修改';
}
echo temp('huitui.html');
?>
