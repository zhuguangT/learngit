<?php
  //
  session_start();
 include "../temp/config.php";
$daohang= array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'仪器管理','href'=>"$rooturl/yiqi/hn_yiqimanager.php"),
        array('icon'=>'','html'=>'本月进入检定状态仪器','href'=>$_SESSION['url_stack'][0]),
  );
//查询近检仪器
if($_GET['handle']=='find_jj'){	
	$sql = "SELECT * FROM `yiqi`";
	$re = $DB->query($sql);
	$now = date('Y-m-d');
	while($data = $DB->fetch_assoc($re)){
		// print_rr($data);
		$tx_date = strtotime("$data[yq_jiandingriqi] +$data[yq_jdriqi]month -$data[yq_tixingriqi]day");
		// echo date('Y-m-d',$tx_date);
		if($tx_date<=strtotime($now) && !empty($data['yq_tixingriqi'])){ 
			$arr[]=$data;
		}
	}
	$xx=1;
	foreach($arr as $key=>$r){
		$lines.=temp('hn_yiqimanager_line.html');
		$xx++;
	}
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
$submit_jj = "<td><input type='button' class='btn btn-xs btn-primary' value='打印近检列表' name='print_jj' id='print_btn'/></td>";
	 disp('hn_yiqimanager.html');
	exit;
}

$trade_global['daohang']= $daohang;
$fzx_id         = $u['fzx_id'];
    $name = ($_GET['name'])?$_GET['name']:'';
	  $type = ($_GET['yq_type'])?$_GET['yq_type']:'';
	  $ren = ($_GET['ren'])?$_GET['ren']:'';
	  $state = ($_GET['state'])?$_GET['state']:'全部'; 
   $sqlf = ''; 
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
 // while($fl){
	// if($i < $total_page && $total_page != 1)
	// { 	
	// 	$i++;
	// 	if($i == $_GET['page'] && $i != 1)
	// 		$str .= "|  $i  ";
	// 	elseif($i == 1 && $_GET['page'] == 1){
	// 		$str .= "<a class=\"xu\" id=xu$i href=\"hn_yiqimanager.php?page=$i&yq_type=$_GET[yq_type]&name=$_GET[name]&ren=$_GET[ren]&state=$_GET[state]\">$i</a>  ";
	// 	}else
	// 		$str .= "|  <a class=\"xu\" id=xu$i href=\"hn_yiqimanager.php?page=$i&yq_type=$_GET[yq_type]&name=$_GET[name]&ren=$_GET[ren]&state=$_GET[state]\">$i</a>  ";
	// }else{
	// 	$fl=false;
	// }
 // }
 if($total_page == 1 || !$total_page || $_GET['jrjd'] == 'yes'){
 	$go = '<input type="hidden" value="go" id="btngo" >';
 }else{
 	$go = '<input type="text"  id="txtgo" size=2 ><input type="button" value="go" id="btngo" >';
 }
 // if($_GET['page'] < $total_page && !empty($_GET['page'])){
 // 	$page = $_GET['page'] + 1;
 // 	$nextpage = "<a href = "."hn_yiqimanager.php?page=$page&yq_type=$_GET[yq_type]&name=$_GET[name]&ren=$_GET[ren]&state=$_GET[state]".'>下一页</a>';
 // 	$str .= '|';
 // }else{
 // 	$nextpage = '';
 // }
 // if($_GET['page'] > 1 && !empty($_GET['page'])){
 // 	$page = $_GET['page'] - 1;
 // 	$prepage = "<a href = "."hn_yiqimanager.php?page=$page&yq_type=$_GET[yq_type]&name=$_GET[name]&ren=$_GET[ren]&state=$_GET[state]".">上一页</a>";
 // }else{
 // 	$prepage = '';
 // }
 if(!$_GET['beginpage'])
	$beginpage = 0;
 else		
	$beginpage = $_SESSION['yuan']+ $_GET['beginpage']*10;

	// $_SESSION['yuan'] = $beginpage;
	
	// if($_GET['page'])
	// 		$beginpage = ($_GET['page']-1)*$pagesize;
	// if($beginpage<0)
	// 	$beginpage =$_SESSION['yuan'] =  0;
	// if($beginpage>$tot)
	// 	$beginpage =$_SESSION['yuan'] =  $tot-$tot%$pagesize;		
	
	  $sql_type = "select distinct yq_type,yq_mingcheng,yq_baoguanren from `yiqi` WHERE `fzx_id`='{$fzx_id}'";
	  $sql_types = $DB->query($sql_type);
	  $type_str =$name_str = $ren_str = '';
	  $type_arr = $name_arr = $ren_arr = array();
	  while($sql_typer = $DB->fetch_assoc($sql_types)){
		//print_r($sql_typer);
		if(!in_array($sql_typer[yq_type],$type_arr)){
			$type_str .= "<option value=\"$sql_typer[yq_type]\" onselect =\"alert(1)\">$sql_typer[yq_type]</option>";
			$type_arr[] = $sql_typer[yq_type];
		}
		if(!in_array($sql_typer[yq_mingcheng],$name_arr)){
			$name_str .= "<option value=\"$sql_typer[yq_mingcheng]\">$sql_typer[yq_mingcheng]</option>";
			$name_arr[] = $sql_typer[yq_mingcheng];
		}
		if(!in_array($sql_typer[yq_baoguanren],$ren_arr)){
			$ren_str .= "<option value=\"$sql_typer[yq_baoguanren]\">$sql_typer[yq_baoguanren]</option>";
			$ren_arr[] = $sql_typer[yq_baoguanren];
		}
	  }
  //  $R=$DB->query("select * from yiqi  "); 
		//$sql.="select * from  yiqi where 1=1 $sqlf order by	yq_mingcheng,id limit $beginpage,$pagesize";
		$sql.="select * from  `yiqi` where 1=1 AND `fzx_id`='{$fzx_id}' $sqlf order by	yq_sbbianhao  asc,yq_mingcheng asc";//内部编号排序 
	 $R=$DB->query($sql); 
       $mc=$a;
       $bgr=$d;   
      $i=$j=1;
      $mingcheng=$x='';
    while($r=$DB->fetch_assoc($R)){
	  $operation= "<a href=\"javascript:if(confirm('你真的要删除么?\\n一经删除,无法恢复!')) 
		location='delete.php?action=删除&yid=$r[id]'\">删除</a> 
		|<a href=yiqi_update.php?action=修改&yid=$r[id]&page=$_GET[page]>修改</a>";
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
                $today2 = strtotime($today);
                //下次检定的日期
                $time2 = strtotime($r['yq_firstjianding']);
                if($c2 >= $time2 && $today2 <= $time2 && $r['yq_state']!='报废'){
                        $color='red';
                }else{
                        $color='';
                }
        }else{
                        $color='';
                }

		//$xh = ($_GET['page']-1) * $pagesize;
	 	$xx=$i++;
		$mingcheng = $r['yq_mingcheng'];
		//下次检定日期
		$xcjd_riqi = '';
		if(!empty($r['yq_jiandingriqi']) && !empty($r['yq_jdriqi']))
			$xcjd_riqi = date('Y-m',strtotime($r['yq_jiandingriqi']) + intval($r['yq_jdriqi']) * 31 * 24 *3600);
		else
			$xcjd_riqi = '';
 		if($color == 'red')
    		$lines.=temp('hn_yiqimanager_line.html');
    }
 
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
    disp('hn_yiqimanager.html');
?> 





