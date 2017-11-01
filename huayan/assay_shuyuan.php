<?php
/**
 * 功能：溯源报告、修改记录
 * 作者: Mr Zhou
 * 日期: 2015-06-14 
 * 描述: 溯源报告、修改记录
*/
include "../temp/config.php";
$tid  = intval($_GET['tid']);
$xc   = $DB->fetch_one_assoc("SELECT ap.*,bt.`zongheng` FROM `assay_pay` AS ap INNER JOIN `bt` ON ap.`fid`=bt.`fid` WHERE ap.`id`='$tid'");
$zongheng = $xc['zongheng'].'_biao';//表格纵横板式
$zongheng = $$zongheng;//表格纵横板式的宽度
//cy表的数据
$cy	= $DB->fetch_one_assoc("SELECT * FROM `cy` WHERE `id`='{$xc['cyd_id']}'");
$cy_user  = $cy['cy_user_qz'];
if(!empty($cy['cy_user_qz2'])){
  if(!empty($cy_user)){
    $cy_user.= "、".$cy['cy_user_qz2']; 
  }else{
    $cy_user  = $cy['cy_user_qz2'];
  }
}
//cy_rec表的数据
$cyr_sql	= $DB->query("SELECT * FROM `cy_rec` WHERE `cyd_id`='{$xc['cyd_id']}' AND `ys_result`!='合格'");
$ys_result  = '';
while ($rs_cyr = $DB->fetch_assoc($cyr_sql)) {
    if(empty($rs_cyr['ys_result'])){
      $rs_cyr['ys_result']  = '未填写验收结果';
    }
    $ys_result  .= "样品".$rs_cyr['bar_code']."：".$rs_cyr['ys_result']."<br>";
}
if($ys_result==''){
  $ys_result  = '合格';
}else{
  $ys_result  .= '其他样品均合格';
}
//溯源表数据
$R	= $DB->query("SELECT * FROM `hy_shuyuan` WHERE tid='$tid' ORDER BY  `cishu` ASC");  //查看溯源表数据
//个性config里面配置的路径
empty($pdf_files) && $pdf_files= '/home/files/';
$syHtml = '';
//获取到MD5的值作为文件夹和文件的名称
$hymd	= $DB ->query("SELECT `md5` FROM `hy_shuyuan` WHERE `tid` = '$tid' ORDER BY  `cishu` ASC");
//循环查出tid 的所有MD5值
while($hymds= $DB->fetch_assoc($hymd)){
  $hydev    = substr($hymds['md5'],0,2);//获取到前两位作为文件夹名称
  $hyfile   = substr($hymds['md5'],2,2);//获取到2到4位作为二级文件夹名称
  $filename1= $pdf_files."shuyuan/".$hydev."/".$hyfile."/".$hymds['md5'].".gz";
  $command2 = "gzip -d $filename1";   //解压缩  
  exec($command2,$out2,$status2);
  $filename = $pdf_files."shuyuan/".$hydev."/".$hyfile."/".$hymds['md5'];
  if($str   = @file_get_contents($filename)){
    $row    = $DB->fetch_assoc( $R );
    if($row['cishu']<1){
      $syHtml  .= "<fieldset style='width:{$zongheng};padding:20px;margin:20px auto;border:2px solid #A8A8A8;'><legend><blank> {$row['userid']} 于 {$row['rdate']} 确认签字，化验单如下:</blank> </legend>";
  	}else{
      $syHtml  .= "<fieldset style='width:{$zongheng};padding:20px;margin:0 auto;border:2px solid #A8A8A8;'><legend>{$row['userid']} 于 {$row['rdate']} 修改了化验单，修改后化验单如下:</legend>";
  	}
    $syHtml    .= "<div style='margin:0 auto;padding:0;width:{$zongheng};' class='center'>".$str."</div></fieldset><br />";
   	$str    = '';	  
  }
  $command2 = 'gzip '.$pdf_files.'shuyuan/'.$hydev.'/'.$hyfile.'/'.$hymds['md5'];
  exec ($command2,$out2,$status2);
}
$header = '原始记录表修改记录';
$syHtml = preg_replace('/<script.*>(.*)<\/script>/isU','',$syHtml);
echo temp('hyd/dataSuYuan');
?>