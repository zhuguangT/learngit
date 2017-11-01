<?php
/*
 *功能：化验单与仪器打印出来的pdf关联查看和管理页面
 *作者：zhengsen
 *时间：2012-07-21
 *说明:其中一些值是通过ajax传过来的
 */
include "../temp/config.php";
get_int($_GET['tid']);
get_str($_GET['v']);
if(!$_GET['tid']){
	echo "链接错误，请联系系统维护人员";
	exit;
}
$tid = $_GET['tid'];
//修改图谱的类型,分量总量时全部关联
if($_GET['action']=="change"){
	$run=$DB->query("UPDATE pdf SET pdf_type='".$_GET['pdf_type']."',beizhu='".$_GET['tid']."' WHERE id='".$_GET['pid']."'");
	//分量总量的曲线关联的时候，只有有一个项目关联上，其他项目也自动关联上
    if($run&&$_GET['pdf_type']==0){
       //通过$tid 找vid
       $find_vid=$DB->fetch_one_assoc("SELECT vid,cyd_id FROM `assay_pay` WHERE id=".$tid);
       //如果改变的图谱类型是分量的情况下，将对应总量项目也给关联上
       $assay_value_id = $DB->fetch_one_assoc("SELECT pid FROM `assay_value` WHERE id=".$find_vid['vid']);
       if($assay_value_id['pid']>0){
       	$zongLid=$DB->fetch_one_assoc("SELECT id FROM `assay_pay` WHERE vid='".$assay_value_id['pid']."' AND cyd_id='".$find_vid['cyd_id']."'");
	       	if($zongLid['id']){
	       		$cunzai=$DB->query("SELECT * FROM `hydpdf` WHERE tid='".$zongLid['id']."' AND pid='".$_GET['pid']."'");
        		$num_rows_hydpdf=$DB->num_rows($cunzai);
        		if($num_rows_hydpdf<=0){
        		 $DB->query("INSERT INTO `hydpdf` (`tid`,`pid`) VALUES('".$zongLid['id']."','".$_GET['pid']."')");
        	    }
	       	}
       }
       //其他曲线关联
         //见project中的 #19729
      /* $xmarr7890=array("氯乙烯"=>'302',"二氯甲烷"=>"495","三氯甲烷"=>"496","四氯化碳"=>"280","二氯一溴甲烷"=>"499","四氯乙烯"=>'308',"二甲苯（总量）"=>"317","邻-二甲苯"=>'320',"间-二甲苯"=>"319","对-二甲苯"=>'318',"间，对-二甲苯"=>'651',"异丙苯"=>"324","1，1-二氯乙烯"=>"303","1，2-二氯乙烯（总量）"=>"304","顺-1，2-二氯乙烯"=>"305","反-1，2-二氯乙烯"=>'306',"1，2-二氯乙烷"=>'283',"苯"=>'315',"甲苯"=>'316',"氯苯"=>'335',"三溴甲烷"=>"497","六氯丁二烯"=>"301","丙烯腈"=>'313',"氯丁二烯"=>"300","1，1，1-三氯乙烷"=>"284","三氯乙烯"=>"307","一氯二溴甲烷"=>'498',"乙苯"=>"323","苯乙烯"=>'309',"环氧氯丙烷"=>'292');*/

      /*$xmarr7890=array('302',"495","496","280","499",'308',"317",'320',"319",'318','651',"324","303","304","305",'306','283',"苯"=>'315','316','335',"497","301",'313',"300","284","307",'498',"323",'309','292');*/

     /* $xmarr6890=array("硝基苯"=>"348","硝基氯苯（总量）"=>'353',"对-硝基氯苯"=>'354',"间-硝基氯苯"=>"355","邻-硝基氯苯"=>"356","二硝基苯（总量）"=>"349","对-二硝基苯"=>"350","间-二硝基苯"=>"351","邻-二硝基苯"=>"352","2，4-二硝基甲苯"=>"359","2，4-二硝基氯苯"=>"358","2，4，6-三硝基甲苯"=>"361","邻苯二甲酸二丁酯"=>"374","邻苯二甲酸二（2-乙基己基）酯"=>"376","甲基异莰醇-2"=>'330',"土臭素"=>"329");*/
    /*$xmarr6890=array("348",'353','354',"355","356","349","350","351","352","359","358","361","374","376",'330',"329");*/
  
   /* $xmarr6890=array("348",'353','354',"355","356","349","350","351","352","359","358","361","374","376",'330',"329");
    $xmarr7890=array('302',"495","496","280","499",'308',"317",'320',"319",'318','651',"324","303","304","305",'306','283','315','316','335',"497","301",'313',"300","284","307",'498',"323",'309','292');
        //6890
	      if(in_array($find_vid['vid'], $xmarr6890)){
	      	for($i=0;$i<count($xmarr6890);$i++){
	      		$get_tid=$DB->query("SELECT `tid` FROM `assay_order` WHERE cyd_id='".$find_vid['cyd_id']."' AND vid='".$xmarr6890[$i]."'");
	      		while($get_tid2=$DB->fetch_assoc($get_tid)){
	      			if($get_tid2['tid']){
			       		$cunzai=$DB->query("SELECT * FROM `hydpdf` WHERE tid='".$get_tid2['tid']."' AND pid='".$_GET['pid']."'");
		        		$num_rows_hydpdf=$DB->num_rows($cunzai);
		        		if($num_rows_hydpdf<=0){
		        		 $DB->query("INSERT INTO `hydpdf` (`tid`,`pid`,`note`) VALUES('".$get_tid2['tid']."','".$_GET['pid']."','曲线')");
		        	    }
		         	}
	      		}
	      	}
	      }
        //7890的
	    if(in_array($find_vid['vid'], $xmarr7890)){
	      	for($i=0;$i<count($xmarr7890);$i++){
	      		$get_tid=$DB->query("SELECT `tid` FROM `assay_order` WHERE cyd_id='".$find_vid['cyd_id']."' AND vid='".$xmarr7890[$i]."'");
	      		while($get_tid2=$DB->fetch_assoc($get_tid)){
	      			if($get_tid2['tid']){
			       		$cunzai=$DB->query("SELECT * FROM `hydpdf` WHERE tid='".$get_tid2['tid']."' AND pid='".$_GET['pid']."'");
		        		$num_rows_hydpdf=$DB->num_rows($cunzai);
		        		if($num_rows_hydpdf<=0){
		        		 $DB->query("INSERT INTO `hydpdf` (`tid`,`pid`,`note`) VALUES('".$get_tid2['tid']."','".$_GET['pid']."','曲线')");
		        	    }
		         	}
	      		}
	      	}
	    }*/
    }
	$arr       = array ('jg'=>1);
	echo json_encode($arr);
	exit;
}
//删除pdf文件及数据库的数据
if($_GET['action']=="del"){
	$qNum = $DB->query("DELETE FROM `pdf` WHERE id='".$_GET['pid']."'");
	if($qNum){
		if(!empty($_GET['files'])){
			$pdfFile = $global['pdf_file_way'].$_GET['files'];
			@unlink($pdfFile);//删除文件
		}
	}
	$DB->query("DELETE FROM yqdaoru  WHERE pid='".$_GET['pid']."'");
	$DB->query("DELETE FROM hydpdf  WHERE pid='".$_GET['pid']."'");
	$arr       = array ('jg'=>1);
	echo json_encode($arr);
	exit;
}

//取消关联pdf、修改备注
if($_GET['rowid']){
	if($_GET['action']=='qxgl'){
		//先删掉对应 yqdaoru表中的数据 在删 hypdf表的数据
		if($_GET['pid']!=''){
			$DB->query("DELETE FROM `yqdaoru` where pid='".$_GET['pid']."'");
		}
		$DB->query("DELETE FROM `hydpdf` WHERE id = '".$_GET['rowid']."'");
		//清除图谱详情的数据
		$DB->query("UPDATE `pdf` SET `pdf_detail` = '' WHERE `id` ='".$_GET['pid']."'");
		$arr       = array ('jg'=>1);
	}else if($_GET['action']=='gl'){
		$DB->query("INSERT INTO `hydpdf` (`tid`,`pid`,`note`) VALUES('".$_GET['tid']."','".$_GET['rowid']."','".$_GET['beizhu']."')");
		$arr       = array ('jg'=>1);
	}
	//清空关联的数据
	else if($_GET['action']=='yqdaoru_del_sj'){
		//清除yqdaoru表中的数据
		if($_GET['pid']!=''){
			//清除表中的数据
			$DB->query("DELETE FROM `yqdaoru` where pid='".$_GET['pid']."'");
			//清除图谱详情的数据
			$DB->query("UPDATE `pdf` SET `pdf_detail` = '' WHERE `id` ='".$_GET['pid']."' ");
		}
		$arr       = array ('jg'=>1);

	}else{//修改
			//判断是修改pdf还是hydpdf的数据
			if($_GET['table']=="pdf"){
				$DB->query("UPDATE `pdf` SET beizhu='".$_GET['note']."'  WHERE id = '".$_GET['rowid']."'");
			}
			else{
				$DB->query("UPDATE `hydpdf` SET note='".$_GET['note']."' WHERE id = '".$_GET['rowid']."'");
			}
			$arr   = array ('jg'=>1);
	}
	echo json_encode($arr);
	exit;
}
else{
	//根据tid查询assay_pay的信息
	$h = $DB->fetch_one_assoc("SELECT id,userid,userid2,fid FROM assay_pay WHERE id='".$tid."'");
	
	$autoload_set_rs=$DB->fetch_one_assoc("SELECT * FROM yq_autoload_set s LEFT JOIN xmfa x ON s.yq_id=x.yiqi LEFT JOIN `yq_autoload_storeroom` y ON y.id=s.storeroom_id WHERE x.id='".$h['fid']."'");

	if($autoload_set_rs['printer']){
		$print_name=$autoload_set_rs['printer'];
	}
	//查询已经关联的pdf文件
	$R = $DB->query("SELECT h.*,p.cdate,p.pdf_detail,p.pdf_type FROM hydpdf h LEFT JOIN  pdf p ON h.pid=p.id WHERE h.tid='".$tid."'");
	$i = 1;
	//不是这张化验单的化验员不显示取消关联化验单
	if($u['userid']==$h['userid'] ||$u['userid']==$h['userid2'] || $u['admin']){
		$xs = '';
	}else{
		$xs = 'none';
	}
	$xzarr=array();
	$pdf_type_arr=array("0"=>"曲线","1"=>"数据");//1代表曲线图谱，2代表数据图谱
	while($r=$DB->fetch_assoc($R)){
		$pdf_detail_str='<span style="color:red">图谱类型：</span>';
		$pdf_detail_arr=array();
		$rcdate  = date('m-d  H：i',strtotime($r['cdate']));
		if(empty($r['note'])){
			$r['note']='无';
		}
		$pdf_detail_arr=json_decode($r['pdf_detail'],true);
		foreach($pdf_type_arr as $key=>$value){
			$ck='';
			if($r['pdf_type']==$key){
				$ck='checked=checked';
			}
			$pdf_detail_str.='&nbsp;&nbsp;<input type="radio"  name="'.$r['pid'].'"  value="'.$key.'" '.$ck.' onclick="change_pdf_type(this)">'.$value;
		}
		$pdf_detail_str.='<br/>';
		if(!empty($pdf_detail_arr['jcxm'])){
			$pdf_detail_str.='<span style="color:red">检测项目：</span><br/>'.$pdf_detail_arr['jcxm'].'<br/>';
		}
		if(!empty($pdf_detail_arr['bar_code'])){
			$pdf_detail_str.='<span style="color:red">样品编号：</span><br/>'.$pdf_detail_arr['bar_code'].'<br/>';
		}
		$gline  .= "  <tr align=center><td>$i</td><td nowrap>$rcdate</td><td  colspan=2 align=\"left\" style=\"font-size:10px\">".$pdf_detail_str."</td><td class=\"canclick\"  onclick=\"pdfbz($r[id],'$r[note]','hydpdf')\">".$r['note']."</td><td nowrap><a target=\"_blank\" href=\"$rooturl/huayan/view_pdf.php?ajax=1&pid=$r[pid]&handle=see\" >查看</a><a target=\"_blank\" href=\"$rooturl/huayan/view_pdf.php?ajax=1&pid=$r[pid]&handle=download\" >|下载</a><a style=\"display:$xs;cursor:pointer\"  onclick=\"pdf_qxgl('qxgl',$r[id],$r[pid])\" >|取消关联</a><a style=\"cursor:pointer\"  onclick=\"yqdaoru_del('yqdaoru_del_sj',$r[id],$r[pid])\">|清除数据</a></td></tr>";
		$xzarr[] = $r['pid'];
		$i++;
	}
	//print_rr($h);
	if($u['userid']==$h['userid'] || $u['userid']==$h['userid2'] || $u['admin']){
		//判断打印的电脑IP和仪器使用的ip 原因：区分分中心
		$ip=$_SERVER['REMOTE_ADDR'];//获取本机电脑的IP
		$startip=substr($ip,0,strripos($ip,'.')).'.1'; //IP 第一个
		$endip=substr($ip,0,strripos($ip,'.')).'.255'; //IP 最后一个
		if($print_name){
			if($u['admin']||$u['userid']==$h['userid']){
					$P=$DB->query("SELECT * FROM `pdf` WHERE (`cdate`>='".date("Y-m-d",strtotime("-30 day"))." 00:00:00') AND print_name ='".$print_name."' ORDER BY cdate DESC");
			}else{
				$P=$DB->query("SELECT * FROM `pdf` WHERE (`cdate`>='".date("Y-m-d",strtotime("-30 day"))." 00:00:00') AND `ip` between INET_ATON('".$startip."') AND INET_ATON('".$endip."') AND print_name ='".$print_name."' ORDER BY cdate DESC");
		    }
			$dyline  = '<tr align="center"><td colspan=7>最近打印文件'.$print_name.'</td></tr><tr align="center"><td>序号</td> <td nowrap>打印时间</td><td>图谱详情</td><td>已关联化验单</td><td>备注</td><td colspan=2>操作</td></tr>';
			$i = 1;
			while($row=$DB->fetch_assoc($P)){
				$hyd_arr=array();
				$dy_date= date('m-d  H：i',strtotime($row[cdate]));
				if(!in_array($row['id'],$xzarr)){
					//$tid_arr=array();
					$tis_str=$pdf_detail_str='';
					//已关联化验单 start
					$queryP = $DB->query("SELECT * FROM `hydpdf` WHERE pid='".$row['id']."'");
					while($rs=$DB->fetch_assoc($queryP)){
						$hyd_arr[]=$rs['tid'];
					}
					if(!empty($hyd_arr)){
						$gl='';
						$jj = 1;
						foreach ($hyd_arr as $value){
						    $gl.=$value;
						    if ($jj == 3){
						        $gl.='<br />';
						        $jj = 1;
						    }else{
						        $gl.=',';
						        $jj++;
						    }
						}
					}
					$numPdf = $DB->num_rows($queryP);
					if($numPdf<=0){
						$gl='未关联';
					}
					//end
					if(empty($row['beizhu'])){
						$row['beizhu']='无';
					}
					$pdf_detail_arr=json_decode($row['pdf_detail'],true);
					//if($u['admin']){print_rr($pdf_detail_arr);}
					/*$pdf_detail_str='<span style="color:red">图谱类型：</span><br/>';
					foreach($pdf_type_arr as $key=>$value){
						$ck='';
						if($row['pdf_type']==$key){
							$ck='checked=checked';
						}
						$pdf_detail_str.='&nbsp;&nbsp;<input type="radio"  name="'.$row['id'].'"  value="'.$key.'" '.$ck.' onclick="change_pdf_type(this)">'.$value;
					}
					$pdf_detail_str.='<br/>';*/
					$pdf_detail_str='';
					if(empty($pdf_detail_arr)){
						$pdf_detail_str='<center>无</center>';
					}
					if(!empty($pdf_detail_arr['jcxm'])){
						$pdf_detail_str.='<span style="color:red">检测项目：</span><br/>'.$pdf_detail_arr['jcxm'].'<br/>';
					}
					if(!empty($pdf_detail_arr['bar_code'])){
						$pdf_detail_str.='<span style="color:red">样品编号：</span><br/>'.$pdf_detail_arr['bar_code'].'<br/>';
					}
					/*if(!empty($tid_arr)){
						$pdf_detail_str.='<span style="color:red">关联化验单号：</span><br/>'.implode('、',$tid_arr);
					}*/
					$dyline.= "  <tr align=center><td>".$i."</td><td nowrap>".$dy_date."</td><td align=\"left\" style=\"font-size:10px\">".$pdf_detail_str."</td><td>".$gl."</td><td  class=\"canclick\"  onclick=\"pdfbz('$row[id]','$row[beizhu]','pdf')\">".$row['beizhu']."</td><td nowrap><a target=\"_blank\" href=\"$rooturl/huayan/view_pdf.php?ajax=1&pid=$row[id]&handle=see\" >查看</a><a target=\"_blank\" href=\"$rooturl/huayan/view_pdf.php?ajax=1&pid=$row[id]&handle=download\" >|下载</a><a style=\"cursor:pointer\"  onclick=\"pdfDel('$row[id]','$row[file]','del');\" >|删除</a><a style=\"cursor:pointer\"  onclick=\"pdf_gl('gl',$row[id],'$row[beizhu]')\" >|关联</a></td></tr>";
					$i++;
				}
			}
		}
	}
	disp('hyd/hydpdf');
}

//判断文件格式是否正确和文件是否存在
function read_pdf($file){
	if(strtolower(substr(strrchr($file,'.'),1)) != 'pdf'){
		echo '文件格式不对.';
		return;
    }
    if(!file_exists($file)){
    	echo '文件不存在';
    	return;
    }
	$file_name=basename($file);
	header("Content-Type:   application/mspdf");        
	header("Content-Disposition:   attachment;   filename=$file_name");        
	header("Pragma:   no-cache");        
	header("Expires:   0");   
	readfile($file);
}
?>
