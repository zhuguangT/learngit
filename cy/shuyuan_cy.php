<?php 
/*
* 采样单修改记录查看页面
*/
include "../temp/config.php";
//导航
$trade_global['daohang'] = array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'采样任务列表','href'=>'./cy/cyrw_list.php'),
	array('icon'=>'','html'=>'采样记录表','href'=>'./cy/cy_record.php?cyd_id='.$_GET[cyd_id]),
	array('icon'=>'','html'=>'修改记录','href'=>$current_url)
);
$trade_global['css']    = array('lims/main.css','datepicker.css','bootstrap-timepicker.css');
$cyd_id	= get_int($_GET['cyd_id']);
if(empty($pdf_files)){//个性config里面配置的路径
	$pdf_files= '/home/files/';
}
//获取到MD5的值作为文件夹和文件的名称
$hymd	= $DB ->query("select * from hy_shuyuan where cyd_id = '$cyd_id' ORDER BY  `cishu` ASC");
//$hymds = $DB->fetch_assoc($hymd);
$sy	= '';
while($hymds= $DB->fetch_assoc($hymd)){//循环查出tid 的所有MD5值
	$hydev	  = substr($hymds['md5'],0,2);//获取到前两位作为文件夹名称
  	$hyfile   = substr($hymds['md5'],2,2);//获取到2到4位作为二级文件夹名称
  	$filename1= $pdf_files."shuyuan/".$hydev."/".$hyfile."/".$hymds['md5'].".gz";
	$filename = $pdf_files."shuyuan/".$hydev."/".$hyfile."/".$hymds['md5'];
	if(!file_exists($filename)){
		$command2 = "gunzip -c $filename1 > $filename";   //解压缩  
  		exec($command2,$out2,$status2);
	}
	$str	  = file_get_contents($filename);
  	if(!empty($str)){
      		if($hymds['cishu']<1){
         		$sy	.= "<fieldset style='width:27cm;padding:20px;margin:20px auto;border:2px solid #A8A8A8;'><legend><BLINK> {$hymds['userid']} 于 {$hymds['rdate']} 确认签字，采样单如下:</BLINK> </legend>";
	  
	  	}else{
          		$sy	.= "<fieldset style='width:27cm;padding:20px;margin:0 auto;border:2px solid #A8A8A8;'><legend>{$hymds['userid']} 于 {$hymds['rdate']} 修改了采样单，修改后采样单如下:</legend>";
    		} 
     		$sy	.= "<div style='margin:0 auto;padding:0;width:27cm;' class='center'>".$str."</div></fieldset><br />";//$str."</fieldset><br />";	  
		$str	 = '';
  	}
  	//$command2 = "gzip ".$pdf_files."shuyuan/".$hydev."/".$hyfile."/".$hymds['md5'];
  	//exec ($command2,$out2,$status2);
	//$rm 	= "rm $filename";
	//exec($rm);
}
disp('shuyuan_cy.html');

?>
