<?php
include "../temp/config.php";
$fzx_id = FZX_ID;
$xin = $_POST['quanstr'];
// $xin = explode(',',$xinxi);
// $aa= '0';
// foreach($xin as $v){
// 	$v1 = explode(':',$v);
// 	$one = explode('.',$v1[1]);
// 	$csql = $DB->query("select id,bar_code,cyd_id from cy_rec where bar_code = '{$one[1]}'");
// 	while($row = $DB->fetch_assoc($csql)){
// 		$cfzx = $DB->fetch_one_assoc("select id,fzx_id from cy where id = '{$row['cyd_id']}'");
// 		if($cfzx['fzx_id'] == $fzx_id){
// 			$aa= '1';
// 		}
// 	}
// 	if(!$aa){
// 		$upsql = "update cy_rec set bar_code = '{$one[1]}' where id = '{$one[2]}' and bar_code = '{$one[0]}'";
// 		$DB->query($upsql);
// 	}else{
// 		$cun[$one[2]] = $one[1].','.$one[0];
// 	}
// }
$aa = '0';
$v1 = explode(':',$xin);
$one = explode('.',$v1[1]);
$csql = $DB->query("select id,bar_code,cyd_id from cy_rec where bar_code = '{$one[1]}'");
while($row = $DB->fetch_assoc($csql)){
	$cfzx = $DB->fetch_one_assoc("select id,fzx_id from cy where id = '{$row['cyd_id']}'");
	if($cfzx['fzx_id'] == $fzx_id){
		$aa= '1';
	}
}
if(!$aa){
	$upsql = "update cy_rec set bar_code = '{$one[1]}' where id = '{$one[2]}' and bar_code = '{$one[0]}'";
	$DB->query($upsql);
}else{
	$cun[$one[2]] = $one[1].','.$one[0];
}
if(empty($cun)){
	echo 'ok';
}else{
	echo json_encode($cun);
}
?>