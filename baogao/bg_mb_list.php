<?php
/**
 * 功能：在线编辑列表显示
 * 作者：罗磊
 * 日期：2014-4-5
 * 描述 
*/
include '../temp/config.php';
$trade_global['daohang'][]	= array('icon'=>'','html'=>'检测报告模板','href'=>$current_url);
$_SESSION['daohang']['bg_mb_list']	= $trade_global['daohang'];
$trade_global['css'] = array('boxy.css','lims/buttons.css','lims/jbox.css');
$trade_global['js']  = array('boxy.js');
//查出所有模版格式 
 $sql='select * from report_template';

  $rows = $DB->query($sql);

     while( $row = $DB-> fetch_assoc($rows)){
          $i++;
          $mbid		= $row['id'];
		  $mbname	= $row['te_name'];  //模版名称
          $mbfm		= $row['fc'];       //模版封面
          $mbbt1	= $row['bt']; 		  // 模版数据页带表头
		  $mbbt2	= $row['content'];  // 模版数据页
          $mbsj		= $row['shuju'];      //数据行模板  
	      $mbsm		= $row['exp'] ;      //模版说明页（可能跟模版封面一个页面）
          $mbqm		= $row['audit'];     //签名模版
          $jiego	= $row['jiego'];     //模板组成结构
		  ($row['state'] == 1)?$mbzt ='启用中':$mbzt='已停用';
				$lx=$DB->fetch_one_assoc("SELECT lname FROM leixing WHERE id='".$row['water_type']."'");
				$water_type= $lx['lname'];  //水样类型
				if($row['water_type']==0){
					$water_type="无";
				}
		     //模版是否用
      
	  
	  $list .= temp('bg/bg_mb_line');

     }
 
   disp('bg/bg_mb_list');



?>
