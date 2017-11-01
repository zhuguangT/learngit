<?php 
/*
* 溯源报告、修改记录。
*/
include "../temp/config.php";
//导航
$trade_global['daohang']  = array(
    array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
    array('icon'=>'','html'=>'化验任务列表','href'=>'huayan/ahlims.php?app=pay_list'),
    array('icon'=>'','html'=>"化验单$tid",'href'=>"huayan/assay_form.php?tid={$tid}"),
    array('icon'=>'','html'=>'修改记录','href'=>$current_url)
    );
$trade_global['css']    = array('lims/main.css','datepicker.css','bootstrap-timepicker.css');
//$fzx_id=FZX_ID;
//echo "修改记录页面";
$tid	= get_int($_GET[tid]);
$xc	= $DB->fetch_one_assoc("select ap.*,bt.zongheng from assay_pay AS ap INNER JOIN bt as bt ON ap.fid=bt.fid where ap.id='$tid'");
$zongheng = $xc['zongheng'].'_biao';//表格纵横板式
$zongheng = $$zongheng;//表格纵横板式的宽度
//cy表的数据
$cy	= $DB->fetch_one_assoc("select * from cy where id='$xc[cyd_id]'");
$cy_user  = $cy['cy_user_qz'];
if(!empty($cy['cy_user_qz2'])){
  if(!empty($cy_user)){
    $cy_user.= "、".$cy['cy_user_qz2']; 
  }else{
    $cy_user  = $cy['cy_user_qz2'];
  }
}
//cy_rec表的数据
$cyr_sql	= $DB->query("select * from cy_rec where cyd_id='$xc[cyd_id]' AND `ys_result`!='合格'");
$ys_result  = '';
while ($rs_cyr = $DB->fetch_assoc($cyr_sql)) {
    if(empty($rs_cyr['ys_result'])){
      $rs_cyr['ys_result']  = '未填写验收结果';
    }
    $ys_result  .= "样品".$rs_cyr['bar_code']."：".$rs_cyr['ys_result']."<br>";
}
if($ys_result==''){
  $ys_result  = "合格";
}else{
  $ys_result  .= "其他样品均合格";
}
//溯源表数据
$R	= $DB->query("select *  from hy_shuyuan where tid='$tid' ORDER BY  `cishu` ASC");  //查看溯源表数据
if(empty($pdf_files)){//个性config里面配置的路径
        $pdf_files= '/home/files/';
}
//echo $_GET['tid'];
//获取到MD5的值作为文件夹和文件的名称
$hymd	= $DB ->query("select md5 from hy_shuyuan where tid = '$tid' ORDER BY  `cishu` ASC");
//$hymds = $DB->fetch_assoc($hymd);
while($hymds= $DB->fetch_assoc($hymd)){//循环查出tid 的所有MD5值
	$hydev    = substr($hymds['md5'],0,2);//获取到前两位作为文件夹名称
   	//echo $hydev;
  	$hyfile   = substr($hymds['md5'],2,2);//获取到2到4位作为二级文件夹名称
  	//echo "-----------".$hyfile;
  	$filename1= $pdf_files."shuyuan/".$hydev."/".$hyfile."/".$hymds['md5'].".gz";
	$command2 = "gzip -d $filename1";   //解压缩  
  	exec($command2,$out2,$status2);
	$filename = $pdf_files."shuyuan/".$hydev."/".$hyfile."/".$hymds['md5'];
 	// echo $filename."<br />";
    if( !file_exists($filename)){
      continue;
    }
  	if($str=@file_get_contents($filename)){
   		// print_rr($str);
      $row  = $DB->fetch_assoc( $R );
      if($row[cishu]<1){
       	//  $sy.="$xc[sign_01]于 $xc[sign_date_01]签字确认化验单为 </ br>";
        //$sy.="第一张表为原始记录表   {$row['rdate']}生成</ br>";
        $sy .= "<fieldset style='width:{$zongheng};padding:20px;margin:20px auto;border:2px solid #A8A8A8;'><legend><BLINK> {$row['userid']} 于 {$row['rdate']} 确认签字，化验单如下:</BLINK> </legend>";
	  	}else{
        //$sy.="{$row['userid']} 于 $row[rdate]修改了化验单。<br />修改理由：{$row['liyou']}</ br>";
        $sy .= "<fieldset style='width:{$zongheng};padding:20px;margin:0 auto;border:2px solid #A8A8A8;'><legend>{$row['userid']} 于 {$row['rdate']} 修改了化验单，修改后化验单如下:</legend>";
    	} 
     		// print_rr($str);
      $sy   .= "<div style='margin:0 auto;padding:0;width:{$zongheng};' class='center'>".$str."</div></fieldset><br />";
     	$str   = '';	  
  	}
  	$command2 = "gzip ".$pdf_files."shuyuan/".$hydev."/".$hyfile."/".$hymds['md5'];
  	exec ($command2,$out2,$status2);
}
 
disp('hyd/shuyuan_hy');

?>
