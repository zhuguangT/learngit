<?php
  //
  session_start();
 include "../temp/config.php";
//导航
if($_GET['down']=='download'){
	header("Content-type: application/octet-stream;charset=gbk");
	header("Accept-Ranges: bytes");
	header("Content-Disposition: attachment; filename={$_GET['yq_type']}仪器.xls");
}
//批量修改类型
if($_POST['handle'] == 'up_type_ajax'){
	$old_type = $_POST['old_type'];
	$new_type = $_POST['new_type'];
	$sql = "UPDATE `yiqi` SET `yq_type` = '{$new_type}' WHERE `yq_type` = '{$old_type}'";
	if($DB->query($sql)){
		echo "ok";
	}else{
		echo "wrong";
	}
	die;
}

$trade_global['daohang'][]= array('icon'=>'','html'=>'设备管理','href'=>"$rooturl/yiqi/hn_yiqimanager.php");
$_SESSION['daohang']['hn_yiqimanager']	= $trade_global['daohang'];
$fzx_id         = $u['fzx_id'];
    $name = ($_GET['name'])?$_GET['name']:'';
	  $type = ($_GET['yq_type'])?$_GET['yq_type']:'';
	  $ren = ($_GET['ren'])?$_GET['ren']:'';
	  $state = ($_GET['state'])?$_GET['state']:'全部';
   $sqlf = ''; 
   if($_GET['guanlibm'] == '全部'){
   	$guanlibm = '';
   }
   if($_GET['xinghao']=='全部'){
   	$xinghao='';
   }
   if($_GET['name']=='全部'){
   	$name='';
   }	
   if($type=='全部'){
   	$type = '';
   }
	if($type=='' || empty($_GET['yq_type'])){
		//$yq_type = '计量仪器';
		$sqlf.=" ";
	}else{
		$sqlf.=" and  yq_type='$yq_type' ";
	}
	if ($name!=''){
	  $sqlf.=" and  yq_mingcheng like '$name%' ";
	}
	if($state!='' && $state!='全部'){
	  $sqlf.=" and   yq_state like '%$state%' ";
	}
	if($ren!=''){
	 $sqlf.=" and   yq_baoguanren like '$ren%' ";
	} 
	if($xinghao!=''){
	 $sqlf.=" and   yq_xinghao like '$xinghao%' ";
	}
	if($guanlibm!=''){
	 $sqlf.=" and   yq_guanlibm like '$guanlibm%' ";
	}

//分页
if($_GET['down']!='download'){
 $sqlx = "select count(*) as tot from  `yiqi` where 1=1 AND `fzx_id`='{$fzx_id}' $sqlf";
  $rsx = $DB->query($sqlx);
  
  $rx = $DB->fetch_assoc($rsx); 
  $tot = $rx['tot'];	
 $pagesize = 40;
	$fl=true;	
	$i = 0;
	$str = '';
	//总页数
 $total_page = ceil($tot / $pagesize);
 while($fl){
		if($i < $total_page && $total_page != 1)
		{ 	
			$i++;
			if($i == $_GET['page']){
				$str_style = "style='color:red;'";
				$str .="<li><a $str_style  href='#'>$i</a></li>";
			}
			elseif($i == 1 && $_GET['page'] == 1){
				$str_style = '';
				$str .= "<li><a $str_style  href='hn_yiqimanager.php?page={$i}&yq_type=$_GET[yq_type]&name=$_GET[name]&ren=$_GET[ren]&state=$_GET[state]&fzx=$_GET[fzx]'>$i</a></li>";
			}else{
				$str_style = '';
				$str .= "<li><a $str_style href='hn_yiqimanager.php?page={$i}&yq_type=$_GET[yq_type]&name=$_GET[name]&ren=$_GET[ren]&state=$_GET[state]&fzx=$_GET[fzx]'>$i</a></li>";
			}
		}else{
			$fl=false;
		}
	 }
 if($total_page == 1 || !$total_page){
 	$go = '<input type="hidden" value="go" id="btngo" >';
 }else{
 	$go = '<input type="text"  id="txtgo" size=2 ><input type="button" value="go" id="btngo" >';
 }
 if($_GET['page'] <= 0)
 	$_GET['page'] = 1;
 if($_GET['page'] < $total_page && !empty($_GET['page'])){
 	$page = $_GET['page'] + 1;
 	$nextpage = "<li class='next_page'><a href = "."hn_yiqimanager.php?page=$page&yq_type=$_GET[yq_type]&name=$_GET[name]&ren=$_GET[ren]&state=$_GET[state]".'><span aria-hidden="true">下一页</span></a></li>';
 }else{
 	$nextpage = '';
 }
 if($_GET['page'] > 1 && !empty($_GET['page'])){
 	$page = $_GET['page'] - 1;
 	$prepage = "<li class='prev_page'><a href = "."hn_yiqimanager.php?page=$page&yq_type=$_GET[yq_type]&name=$_GET[name]&ren=$_GET[ren]&state=$_GET[state]"."><span aria-hidden='true'>上一页</a></span></li>";
 }else{
 	$prepage = '';
 }
  if(!$_GET['beginpage'])
		$beginpage = 0;
	 else		
		$beginpage = $_SESSION['yuan']+ $_GET['beginpage']*10;

		$_SESSION['yuan'] = $beginpage;
		
		if($_GET['page'])
				$beginpage = ($_GET['page']-1)*$pagesize;
		if($beginpage<0)
			$beginpage =$_SESSION['yuan'] =  0;
		if($beginpage>$tot)
			$beginpage =$_SESSION['yuan'] =  $tot-$tot%$pagesize;	
			$douhao = ',';
			$limit = "limit";	
}else{
	$beginpage = $pagesize = $limit = $douhao = '';
}
//分页结束
	 //仪器显示分类
		$fenlei_arr = array();
		$sql_fenlei = $DB->query("select * from n_set where module_name='yiqi'");
		while($xianshi = $DB->fetch_assoc($sql_fenlei)){
			$fenlei_arr[$xianshi['module_value2']] = $xianshi['module_value1'];
		}
	  $sql_type = "select distinct yq_type,yq_mingcheng,yq_baoguanren,yq_xinghao,yq_state , yq_guanlibm from `yiqi` WHERE `fzx_id`='{$fzx_id}' ";
	  $sql_types = $DB->query($sql_type);
	  $type_str =$name_str = $ren_str = $xinghao_str=$state_str=$guanlibm_str='';
	  $type_arr = $name_arr = $ren_arr = $xinghao_arr=$state_arr=$guanlibm_arr=array();
	  while($sql_typer = $DB->fetch_assoc($sql_types)){
		// print_r($sql_typer);
		if(!in_array($sql_typer['yq_type'],$type_arr)){
			$type_str .= "<option value=\"$sql_typer[yq_type]\" onselect =\"alert(1)\">$sql_typer[yq_type]</option>";
			$type_arr[] = $sql_typer['yq_type'];
		}
		if(!in_array($sql_typer['yq_mingcheng'],$name_arr)){
			$name_str .= "<option value=\"$sql_typer[yq_mingcheng]\">$sql_typer[yq_mingcheng]</option>";
			$name_arr[] = $sql_typer['yq_mingcheng'];
		}
		if(!in_array($sql_typer['yq_baoguanren'],$ren_arr)){
			$ren_str .= "<option value=\"$sql_typer[yq_baoguanren]\">$sql_typer[yq_baoguanren]</option>";
			$ren_arr[] = $sql_typer['yq_baoguanren'];
		}
		if(!in_array($sql_typer['yq_xinghao'],$xinghao_arr)){
			$xinghao_str .= "<option value=\"$sql_typer[yq_xinghao]\">$sql_typer[yq_xinghao]</option>";
			$xinghao_arr[] = $sql_typer['yq_xinghao'];
		}
		if(!in_array($sql_typer['yq_guanlibm'],$guanlibm_arr)){
			$guanlibm_str .= "<option value=\"$sql_typer[yq_guanlibm]\" onselect =\"alert(1)\">$sql_typer[yq_guanlibm]</option>";
			$guanlibm_arr[] = $sql_typer['yq_guanlibm'];
		}
	  }
  //  $R=$DB->query("select * from yiqi  "); 
		//$sql.="select * from  yiqi where 1=1 $sqlf order by	yq_mingcheng,id limit $beginpage,$pagesize";
	  //判断是否存在字段 yq_sbbianhao 如果存在 说明当前项目有内部编号（淮委） 按内部编号排序 如果没有 则按名称yq_mingcheng排序拼音
		$neibu = 'Describe yiqi yq_sbbianhao';
		$neibu_ = $DB->fetch_one_assoc($neibu);
		if(is_array($neibu_)) //说明有
			$sql.="select * from  `yiqi` where 1=1 AND `fzx_id`='{$fzx_id}' $sqlf order by	px_id,(SUBSTRING_INDEX(yq_sbbianhao,'-',1)+0) asc,(SUBSTRING_INDEX(SUBSTRING_INDEX(yq_sbbianhao,'-',2),'-',-1)+0) asc
  $limit $beginpage $douhao $pagesize";//内部编号排序 
  		else
  			$sql.="select * from  `yiqi` where 1=1 AND `fzx_id`='{$fzx_id}'  $sqlf order by	px_id,CONVERT(yq_mingcheng USING gbk ) COLLATE gbk_chinese_ci ASC  $limit $beginpage $douhao $pagesize";//手动排序
		/*
		if($asql!=''){
			$R=$DB->query($asql); 
			    while($r=$DB->fetch_assoc($R)){
					//	print_r($r);
					$tixing=$r['yq_tixingriqi'];// 提醒天数3
$zuijin=$r['yq_jiandingriqi'];// 最近
$zhouqi=$r['yq_jdriqi'];//yq_jdriqi 12
echo $zhouqi;
$year=date("Y",strtotime($zuijin));
$yue=date("m",strtotime($zuijin));
$day=date("d",strtotime($zuijin));
$a=$yue+$zhouqi;
$nianjisuan=floor($a/12);//计算出是几年
if($nianjisuan<1){
$yue=$yue+$zhouqi;
}else{
$year=($nianjisuan-1)+$year; 
}
$a= date("$year-$yue-$day ",time());
$tx= strtotime($a)-(3600*24*$tixing);
$t=date("Y-m-d",$tx);//提醒日期
$a= date('Y-m-d',time());
	  $operation= "<a href=\"javascript:if(confirm('你真的要删除么?\\n一经删除,无法恢复!')) 
		location='delete.php?action=删除&yid=$r[id]'\">删除</a> 
		|<a href=yiqi_update.php?action=修改&yid=$r[id]>修改</a>|<a href=yiqi_detail.php?action=详细&yid=$r[id]>详细</a>";
		  //  if($r['yq_state']=='启用') $color='green'; 
		   // elseif($r['yq_state']=='准用') $color='blue';
		  //  else $color='red';	    
		if($a>$t)$lines.=temp('hn_yiqimanager_line.html');
	$i++;
    }
    disp('hn_yiqimanager.html');
			}
	   else
	   * 
	   */
	 $R=$DB->query($sql); 
       $mc=$a;
       $bgr=$d;   
      $i=$j=1;
      $mingcheng=$x='';
    while($r=$DB->fetch_assoc($R)){
    	//仪器分类显示
    	$fenlei_op = "";
    	foreach($fenlei_arr as $kk=>$fen){
			if($kk==$r['yq_xianshi']){
				if($_GET['down']=='download'){
					$fenlei = $fen;
				}
				$fenlei_op .= "<option value='".$kk."' selected>".$fen."</option>";
			}else{
				$fenlei_op .= "<option value='".$kk."'>".$fen."</option>";
			}
		}
	  $operation= "<a class='green icon-edit bigger-130' href=yiqi_update.php?action=修改&yid=$r[id]&page=$_GET[page]></a> | <a class='red icon-remove bigger-140' href=\"javascript:if(confirm('你真的要删除么?\\n一经删除,无法恢复!')) 
		location='delete.php?action=删除&yid=$r[id]'\"></a> 
		";
		//    if($r['yq_state']=='启用') $color='green'; 
		  //  elseif($r['yq_state']=='准用') $color='blue';
                //		    else $color='red';		    
	if($r['yq_firstjianding']!='' && $r['yq_tixingriqi']!=''){
		$rq = $r['yq_firstjianding'];
		$strx = '+'.$r['yq_tixingriqi'].' days';
		$c1 =  strtotime($strx);
		$date = date("Y-m-d",$c1);
		$c2 = strtotime($date);
		if(strpos($rq,'-')!==false)
			$rqarr = explode('-',$rq);
		if(strpos($rq,'.')!==false)		
			$rqarr = explode('.',$rq);
		if(strpos($rq,'/')!==false)	
			$rqarr = explode('/',$rq);
		$today0 = time();
		$today1 = date("Y-m-d",$today0);
		$today2 = strtotime($today1);
		//下次检定的日期
		$time2 = strtotime($r['yq_firstjianding']);
		if($c2 >= $time2 && $today2 <= $time2 && $r['yq_state']!='报废'){//今天加提醒大于下次检定日期,且今天日期小于下次检定日期
			$color='red';
		}elseif($today2 >= $time2){
			$color='black';
		}else{
			$color='';
		}	
	}else{
			$color='';
		}
			//使用年限
		$nowdate = date("Y-m-d");
	    if($r['yq_qiyong']){
	    	$yq_synx = (strtotime($nowdate)-strtotime($r['yq_qiyong']))/(31*24*3600);
	    	if($yq_synx>12){
	    		$yq_synx = number_format($yq_synx/12, 1);
	    		$yq_synx .= '年';
	    	}elseif($yq_synx<1){
	    		$yq_synx = floor($yq_synx*31);
	    		$yq_synx .= '天';
	    	}else{
	    		$yq_synx = number_format($yq_synx,1);
	    		$yq_synx.= '个月';
	    	}
	    }else{
	    	$yq_synx = '';
	    }
		
	// if($r['yq_mingcheng']==$mingcheng){		
	// 	$x=$i.'.'.$j;
	// 	$j++;
	// }else{
	// 	$i++;
	// 	$j=1;
	// 	$x='';
	// }
	// if($x!='')
	// 	$xx=$x;
	// 	else
		//$xh = ($_GET['page']-1) * $pagesize;
	 	//$xx=$xh + ($i++);
		$xx = $r['px_id'];
		$mingcheng = $r['yq_mingcheng'];
		//下次检定日期
		$xcjd_riqi = $r['yq_firstjianding'];

		if(empty($type)){
			$type_label_line = "<td>$r[yq_type]</td>";
		}else{
			$type_label_line = '';
		}
    	$lines.=temp('hn_yiqimanager_line.html');
    }
    	
   if(!empty($_GET['name'])){
	$selected_name="<option selected>$_GET[name]</option>"; 
}else{
	$selected_name='';
}
if(!empty($_GET['xinghao'])){
	$selected_xinghao="<option selected>$_GET[xinghao]</option>";
}else{
	$selected_xinghao="";
}
if(!empty($_GET['guanlibm'])){
	$selected_guanlibm="<option selected>$_GET[guanlibm]</option>";
}else{
	$selected_guanlibm="";
}
$state = " <option  $qb>$state</option>";
if(!empty($_GET['state'])){
	$selected_status = "
			 <option>$_GET[state]</option>
			 <option  $qb>全部</option>
             <option  $hg>启用</option>
             <option  $zy>准用</option>
             <option $ty>封存</option>
             <option $bf>报废</option>";
}else{
	$selected_status = "
			 <option  $qb>全部</option>
             <option  $hg>启用</option>
             <option  $zy>准用</option>
             <option $ty>封存</option>
             <option $bf>报废</option>";
}


//获取标签页label
$sql_label = "SELECT * FROM `yiqi` GROUP BY `yq_type`";
$re = $DB->query($sql_label);
$i = 0;
while ($data = $DB->fetch_assoc($re)) {
	if($data['yq_type']==''){
		$data['yq_type']='全部';
	}
	// print_rr($data);
	$label .= <<<ETF
	<li class='label_li'>
      <a href="#tabs-$i" onclick="location='hn_yiqimanager.php?yq_type=$data[yq_type]#tabs-$i'" style="min-width:100px;">$data[yq_type]</a>
      <span id='hide_span' class="glyphicon glyphicon-pencil blue" aria-hidden="true" style="position: absolute;right: 5px;z-index: 999;top: 13px;display:none;cursor:pointer;" title="修改类型" onclick="up_type(this);"></span>
    </li>
ETF;
$i++;
if(empty($type)){
			$type_label = "<th>类别</th>";
		}else{
			$type_label = '';
		}
}
    if($_GET['down']=='download'){
		disp('hn_yiqimanager_download.html');
	}else{
		disp('hn_yiqimanager.html');
	}
?> 





