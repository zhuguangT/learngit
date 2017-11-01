<?php
/**
 * 功能：采样记录表信息的填写、查看、打印
 * 作者：zhengsen
 * 时间：2014-04-15
*/
include '../temp/config.php';
require_once INC_DIR . "cy_func.php";
if($u[userid] == '') nologin();
$fzx_id=$u['fzx_id'];
$_SESSION['back_url'] = $current_url;
//导航
$trade_global['daohang'][]	= array('icon'=>'','html'=>'采样记录表','href'=>'./cy/cy_record.php?cyd_id='.$_GET[cyd_id]);
$_SESSION['daohang']['cy_record']	= $trade_global['daohang'];
$trade_global['js']		= array('date-time/bootstrap-datepicker.min.js','date-time/bootstrap-timepicker.min.js','boxy.js');
$trade_global['css']	= array('lims/main.css','datepicker.css','bootstrap-timepicker.css','lims/buttons.css','boxy.css');
//点击打印时的显示
if($_GET['print']){
	$print="<link href=\"$rooturl/css/lims/print.css\" rel=\"stylesheet\"/>";
}
else{
	$dayin="<br /><a class='btn btn-xs btn-primary' href=\"?cyd_id=$_GET[cyd_id]&print=1&ajax=1\" target='_blank'><i class='icon-print bigger-130'></i>打印</a>";
}
$cyd_id=get_int($_GET['cyd_id'] );
//查询出全部水样类型及名称
$water_type_name_arr= array();
$water_types_sql	= $DB->query("SELECT `id`,`lname` FROM `leixing` WHERE `fzx_id`='{$fzx_id}' OR `fzx_id`='0'");
while($rs_water_types = $DB->fetch_assoc($water_types_sql)){
	$water_type_name_arr[$rs_water_types['id']]	= $rs_water_types['lname'];
}
//查询一个批次下的所有项目
$value_arr=array();
$sql="select * from `cy_rec` where `cyd_id` ='$_GET[cyd_id]' order by id";
$res=$DB->query($sql);
$value_arr1 = array();
while($rs=$DB->fetch_assoc($res))
{
	$assay_values=explode('|',$rs['assay_values']);
	$value_arr=array_merge($assay_values,$value_arr);
	$value_arr1[$rs['id']]=$assay_values;
}
//查询本中心仪器信息
$yiqiarr = '';
$yiqisql = $DB->query("select * from yiqi where fzx_id='$fzx_id'");
while($yiqire = $DB->fetch_assoc($yiqisql)){
    $yiqiarr[$yiqire['id']] = $yiqire['yq_mingcheng'];
}
//获得cy表里的信息
$cyd	= get_cyd( $_GET['cyd_id'] );
$cyd_qz	= get_userid_img("cy",array("cy_user_qz","cy_user_qz2","sh_user_qz"),$_GET['cyd_id']);
if($cyd['json']!=''){
        $json   = json_decode($cyd['json'],true);
}else{
        $json   = array();
}
######判断有没有修改记录
$hy_shuyuan     = $DB ->query("select id from hy_shuyuan where cyd_id = '{$cyd['id']}'");
$shuyuan_rows   = $DB->num_rows($hy_shuyuan);
$show_xiuGaiJiLu= '';
if($shuyuan_rows>0){
        $show_xiuGaiJiLu = "<!--<p class=\"center no_print\">--><a class='btn btn-xs btn-primary no_print' href='$rooturl/cy/shuyuan_cy.php?cyd_id={$cyd['id']}' >查看修改记录</a><!--</p>-->";
}
######
//采样接收人签字后通知单的容器信息获取cy表的rq_info
    $rq_sql="SELECT * FROM `rq_value` WHERE vid!='' AND fzx_id='".$fzx_id."'  ORDER BY id";
    $rq_query=$DB->query($rq_sql);
    $rq_data=array();
    while($rq_rs=$DB->fetch_assoc($rq_query))
    {   
        $rq_data[$rq_rs['id']]['id'] = $rq_rs['id'];
        $rq_data[$rq_rs['id']]['rq_name']=$rq_rs['rq_name'];
        $rq_data[$rq_rs['id']]['bcj']=$rq_rs['bcj'];
        $rq_data[$rq_rs['id']]['rq_size']=$rq_rs['rq_size'];
        $rq_data[$rq_rs['id']]['vid']=$rq_rs['vid'];
        $rq_data[$rq_rs['id']]['fenlei']=$rq_rs['fenlei'];
        $rq_data[$rq_rs['id']]['mr_shu']=$rq_rs['mr_shu'];
    }
//显示瓶子分类
$rq_fenlei_td='';
$psql = $DB->query("select * from n_set where module_name ='pingzifenlei'");
while($pingzi = $DB->fetch_assoc($psql)){
    $rq_fenlei_td.="<td style='width:10%;'>".$pingzi['module_value1']."</td>";
}
if($rq_fenlei_td==''){
    echo "<script>if(confirm('项目没有设置保存的容器,点击确定去设置！')){location.href='$rooturl/system_settings/cy_rq_manage/rq_list.php';}else{location.href='$rooturl/cy/cyrw_list.php';}</script>";
} 

if(is_null($cyd['cy_note'])){
	$cyd['cy_note']='';
	$cy_rs=$DB->fetch_one_assoc("SELECT * FROM `cy` WHERE site_type='".$cyd['site_type']."' AND id!='".$cyd['id']."' AND save_flag='1' order by id desc");

	if(!empty($cy_rs['cy_note'])){
		$cyd['cy_note']=$cy_rs['cy_note'];
	}
	$DB->query("UPDATE `cy` SET cy_note='".$cyd['cy_note']."' WHERE id='".$cyd['id']."'");
}
$water_type_arr=explode(',',$cyd['water_type']);//这批样的水样类型
$cy_record_bt_arr=array();
$cy_record_bt_arrs=array();
foreach($water_type_arr as $key=>$value){
	$water_type_max_arr[]=get_water_type_max($value,$fzx_id);
}
$water_types=array_unique($water_type_max_arr);
if(count($water_types)=='1'){
	if(!empty($global['cy_record_bt'][$water_types[0]])){
		$cy_record_bt_arr=$global['cy_record_bt'][$water_types[0]];
	}else{
		$cy_record_bt_arr=$global['cy_record_bt']['moren'];
	}
}else{
	 foreach($water_types as $key=>$value){
		 if(empty($global['cy_record_bt'][$value])){
			$value='moren';
		 }
		$cy_record_bt_arrs=array_merge($cy_record_bt_arrs,$global['cy_record_bt'][$value]);
	 }
	 foreach($global['cy_record_bt_order'] as $key=>$value){
		if(in_array($value,$cy_record_bt_arrs)){
			$cy_record_bt_arr[$key]=$value;
		}
	 }
}
//print_rr($cy_record_bt_arr);
//循环cy_record_bt_arr显示采样记录表的表头信息
$cy_record_bt_str='';
if(!empty($cy_record_bt_arr)){
	foreach($cy_record_bt_arr as $key=>$value){
		$cy_record_bt_str.="<td rowspan='2' style='max-width:50px'>{$key}</td>";
	}
}
	//print_rr($cy_record_bt_arr);
//查询出本批次下的现场项目
$sql_xc_jcxm="SELECT value_C,vid FROM `assay_pay` as ap LEFT JOIN `assay_value` as av on ap.vid=av.id where ap.is_xcjc='1' and ap.cyd_id='".$_GET['cyd_id']."' order by vid";
$query_xc_jcxm=$DB->query($sql_xc_jcxm);
$xc_jcxm='';
$xc_jcxm_arr=array();
while($rs_xc_jcxm=$DB->fetch_assoc($query_xc_jcxm))
{
	
	$xc_jcxm_arr[$rs_xc_jcxm['vid']]=$rs_xc_jcxm['value_C'];
}
if(!empty($xc_jcxm_arr)){
	$xc_jcxm_total = count($xc_jcxm_arr);
	foreach($xc_jcxm_arr as $k=>$v){
		$xc_jcxm.='<td style="max-width:80px">'.$v.'</td>';
	}
}else{
	$xc_jcxm='<td style="max-width:80px">无</td>';
}
$res = $DB->query("SELECT cr.*, cy.water_type, s.note,s.st_type,s.water_type,cy.site_type,cy.jcwc_date FROM `cy_rec` cr 
    LEFT JOIN cy ON cy.id = cr.cyd_id 
    LEFT JOIN sites s ON cr.sid = s.id
    WHERE cr.`cyd_id` = '$cyd_id' AND cr.sid > -1000  ORDER BY cr.id");
$i   = 1;//非打印页面显示的序号
$xu  = 1;//打印页面显示的序号
$cy_record_lines='';
//如果是打印页面显示设置
if($_GET['print']){
	if(!empty($_GET['page_size'])){
		$page_size=$_GET['page_size'];
	}else{
		$page_size='9';//默认打印8行
	}
	$input_note="此处设置打印行数，默认9行";
	echo temp("cy_tzd_print_head");
}
//显示采样人或者签字按钮
//if($cyd['save_flag']=='1'){
	$cy_user_text='';
	if(!empty($cyd['cy_user_qz'])&&!empty($cyd['cy_user_qz2'])){
		$cy_user_text=$cyd_qz['cy_user_qz'].'、'.$cyd_qz['cy_user_qz2'];;//$cyd['cy_user_qz'].'、'.$cyd['cy_user_qz2'];
	}
	else if(!empty($cyd['cy_user_qz'])){
		$cy_user_text=$cyd_qz['cy_user_qz'];//$cyd['cy_user_qz'];
	}
	else{
		$cy_user_text=$cyd_qz['cy_user_qz2'];//$cyd['cy_user_qz2'];
	}
	if(($cyd['cy_user']==$u['userid']||$cyd['cy_user2'] == $u['userid'] || $u['admin']) &&$u['userid']!=$cyd['cy_user_qz']&&$u['userid']!=$cyd['cy_user_qz2']){
		if(!empty($cy_user_text)){
			if(!empty($json['退回'])){
				$cy_user_text  .= "、<input class=\"btn btn-xs btn-primary\" type='submit' value='签字' id='cy_user_qz' name='cy_user_qz' onclick=\"return $(this).qbox({title:'修改理由(签字后修改理由将不能修改)',src:'{$rooturl}/huayan/hyd_huitui.php?action=cyd_modify&id={$cyd['id']}&button_name='+this.name,w:600,h:230});\">";
            }else{
				$cy_user_text	=$cy_user_text."、<input class=\"btn btn-xs btn-primary\" type='submit' value='签字' id='cy_user_qz' name='cy_user_qz'>";
			}
		}else{
			if(!empty($json['退回'])){
				$cy_user_text	= "<input class=\"btn btn-xs btn-primary\" type='submit' value='签字' id='cy_user_qz' name='cy_user_qz' onclick=\"return $(this).qbox({title:'修改理由(签字后修改理由将不能修改)',src:'{$rooturl}/huayan/hyd_huitui.php?action=cyd_modify&id={$cyd['id']}&button_name='+this.name,w:600,h:230});\" >";
			}else{
				$cy_user_text	= "<input class=\"btn btn-xs btn-primary\" type='submit' value='签字' id='cy_user_qz' name='cy_user_qz'>";
			}
		}
	}
	//显示样品审核人或者签字按钮
	if(!empty($cyd['sh_user_qz'])){
		$sh_user_text =$cyd_qz['sh_user_qz'];//$cyd['sh_user_qz'];
	}else{
		//status等于3证明是已经采样
		if(!empty($cyd['cy_user_qz']) || !empty($cyd['cy_user_qz2'])){
			if($u['userid'] != $cyd['cy_user'] &&$u['userid']!=$cyd['cy_user2'] && $u['ypjs'] || $u['admin']){
				$sh_user_text="<input class=\"btn btn-xs btn-primary\" type='submit' value='签字'  name='ypjs_user_qz'>";
			}else{
				$sh_user_text="";
			}
		}
	}
//}
$nums    = $DB->num_rows($res);//所有样品的数量
while($row = $DB->fetch_assoc($res)){
	//样品状态处理
    $ypzt = '';
    if($row['ys_zt']){
        $cy_notes = explode(',',$row['ys_zt']);
        foreach($yp_status_xinxi1 as $yp){
            if($_GET['print']){
                if(in_array($yp,$cy_notes)){
                   $ypzt .= ','.$yp;
                }
            }else{
                if(in_array($yp,$cy_notes)){
                   $ypzt .= "<input type='checkbox' value='$yp' name='ys_status[$row[id]][]' checked>$yp&nbsp;&nbsp;"; 
                }else{
                   $ypzt .= "<input type='checkbox' value='$yp' name='ys_status[$row[id]][]'>$yp&nbsp;&nbsp;";
                }
            }     
        }
        if(substr($ypzt,0,1)==','){
            $ypzt = substr($ypzt,1);
        }
    }else{
        if($_GET['print']){

        }else{
            $ypzt = "<input type='checkbox' value='清澈透明' name='ys_status[$row[id]][]'>清澈透明&nbsp;<input type='checkbox' value='浑浊' name='ys_status[$row[id]][]'>浑浊&nbsp;<input type='checkbox' value='沉淀' name='ys_status[$row[id]][]'>沉淀&nbsp;<input type='checkbox' value='气泡' name='ys_status[$row[id]][]'>气泡&nbsp;<input type='checkbox' value='其他' name='ys_status[$row[id]][]'>其他"; 
        }
    }
    //处理样品瓶子
    $pingshu = array();
    $pingzi = $newping = array();
    $tjstr = '';
    if($row['pingstr']){
         $pingstr = json_decode($row['pingstr'],ture);
         $pingzi1 = array_unique($pingstr['玻璃瓶']);
         $pingzi2 = array_unique($pingstr['塑料瓶']);
         if(!is_array($pingzi2)){
             $pingzi2 = array();
         }
         if(!is_array($pingzi1)){
             $pingzi2 = array();
         }
         $quanping =  array_merge($pingzi1,$pingzi2);
         foreach($quanping as $pz){//分解瓶子字符串
            if($pz){
                $new1 = explode(':',$pz);
                $newping[$new1[0]] = $new1[1];
            }
         }
         //显示修改弹出层
         foreach($rq_data as $pk=>$pv){
             $pingshu1 = array();
             foreach($newping as $rname=>$rge){
                if($rname==$pv['rq_name']){
                    $pingshu1[]  = "<td>".$pv['rq_name'].":</td><td><input type='text' name='ping[$row[id]][$pv[id]]' id='".$pv[id]."' value=".$rge.">&nbsp;&nbsp;</td>";
                    if(strstr($pv['rq_size'],'mL')){
                        $pv['rq_size'] = $pv['rq_size']/1000;
                    }
                    $tjstr += $pv['rq_size']*$rge;
                }
             }
             if($pingshu1){
                    $pingshu1 = array_unique($pingshu1);
                    $pingshu = array_merge_recursive($pingshu1,$pingshu);
             }else{
                $pingshu[] = "<td>".$pv['rq_name'].":</td><td><input type='text' name='ping[$row[id]][$pv[id]]' id='".$pv[id]."' value=''>&nbsp;&nbsp;</td>";
             }
         }

         $ii = '1';
         $pingshustr = "<tr>";
         foreach($pingshu as $pvv){
            if($ii%2==0){
                $pingshustr .=$pvv.'</tr><tr>';
            }else{
                $pingshustr .=$pvv;
            }
            $ii++;
         }
         $pingshustr .= "</tr>";
         $pingstr1 = implode('、',$pingzi1);
         $pingstr2 = implode('、',$pingzi2);
    }else{
        foreach($rq_data as $pk=>$pv){
            $pingshu1 = array();
            foreach($value_arr1[$row['id']] as $xm){
                $xm = ",".$xm.",";
                $pvarr = explode(',',$pv['vid']);
                foreach($pvarr as $ppv){
                	if($ppv){
                		$pvid = ",".$ppv.",";
		                if(strstr($xm,$pvid)){
		                    $pingzi[$pv['fenlei']][$pv['id']] = $pv['rq_name'].":".$pv['mr_shu'];
		                    $pingshu1[$pv['id']]  = "<td>".$pv['rq_name'].":</td><td><input type='text' name='ping[$row[id]][$pv[id]]' id='".$pv[id]."' value=".$pv['mr_shu'].">&nbsp;&nbsp;</td>";
		                    $tiji[$pv['id']]['mr_shu']  = $pv['mr_shu'];
		                    $tiji[$pv['id']]['rq_size']  = $pv['rq_size'];
		                }
                	}
                }
            }
            if($pingshu1){
                $pingshu1 = array_unique($pingshu1);
                $pingshu = array_merge_recursive($pingshu1,$pingshu);
            }else{
                $pingshu[] = "<td>".$pv['rq_name'].":</td><td><input type='text' name='ping[$row[id]][$pv[id]]' id='".$pv[id]."' value=''>&nbsp;&nbsp;</td>";
            }
        }
        $ii = '1';
        $pingshustr = "<tr>";
        foreach($pingshu as $pvv){
            if($ii%2==0){
                $pingshustr .=$pvv.'</tr><tr>';
            }else{
                $pingshustr .=$pvv;
            }
            $ii++;
        }
        $pingshustr .= "</tr>";
        $pingzi1 = array_unique($pingzi['玻璃瓶']);
        $pingzi2 = array_unique($pingzi['塑料瓶']);
        $pingstr1 = implode('、',$pingzi1);
        $pingstr2 = implode('、',$pingzi2);
   }
   foreach($tiji as $tk=>$tp){
            if(strstr($tp['rq_size'],'mL')){
                $tp['rq_size'] = $tp['rq_size']/1000;
            }
            $tjstr += $tp['mr_shu']*$tp['rq_size'];
    }
    if($_GET['print']){
        $ys_re = $row['ys_result'];
    }else{
        $ys_re = "<textarea  name='ys_result' cols='6' rows='4'>{$row['ys_result']}</textarea>"; 
    }
     $tan = "<div id=\"cover$row[id]\" class=\"modal\" role=\"dialog\" style='background-color:rgba(15, 15, 15, 0.7)!important;'>
    <div class=\"modal-dialog\" style='width:800px;'>
	      <div id=\"con\" class='modal-content' style='width:800px;text-align:center;vertical-align:middle;'>
	          <h3>修改瓶数</h3>
	          <table>$pingshustr</table>
	          <br/>
	          <input type='button' onclick='pingajax(this)' value='保存' class='btn btn-xs btn-primary' cid='$row[id]' cyd_id='".$_GET['cyd_id']."'>
	          <input type='button' onclick='guanbi(this)' value='关闭' class='btn btn-xs btn-primary' cid='$row[id]'>
	          <br/>
	      </div>
    </div>
</div>";
	$cy_ping="<td onclick='xiuping(this)' cid='$row[id]'>$pingstr2</td><td onclick='xiuping(this)' cid='$row[id]'><p>$pingstr1</p></td>";
    //无水站点的处理
    $row['water_type_name']	= $water_type_name_arr[$row['water_type']];
	if($row['status']=='-1'){
	$act="<select name=\"d[$row[id]][status]\" id=\"ctt$xu\" style=\"display:none;width:100%;\" class=\"wushui\" onFocus=\"show_wus($i)\" onchange=\"hide_wus($xu)\" >
			<option value=\"1\">有水</option>
			<option value=\"-1\"selected = \"selected\">无水</option>
		</select>";
	}else{
		$act="<select name=\"d[$row[id]][status]\" id=\"ctt$xu\" style=\"display:none;width:100%;\" onFocus=\"show_wus($i)\"  onchange=\"hide_wus($xu)\"  >
			<option value=\"1\" selected = \"selected\">有水</option>
			<option value=\"-1\">无水</option>
		</select>";
	}
	//采样时间的默认
	if(empty($row['cy_date'])||$row['cy_date']=='0000-00-00'){
		$row['cy_date']=date('Y-m-d');
	}
	$j=1;
	$cy_record_line='';
	$z_nums=0;
	$lx=$row['site_type'];
    $row['cy_time'] = substr( $row['cy_time'], 0, 5 );
	if($row['cy_time']=='00:00'){
		$row['cy_time']='';
	}
	$vid_value=explode(',',$row['assay_values']);
	  //获取json转换为数组
	$json_arr = json_decode($row[json],true);
	//显示现场项目
	if(!empty($xc_jcxm_arr)){
		//print_rr($xc_xm_td);
		$xc_xm_td='';
        $faarr = $yiarr = array();
		foreach($xc_jcxm_arr as $k=>$v){
            //查询方法表的方法和仪器信息
			$xmfa = $DB->fetch_one_assoc("select * from xmfa where xmid='$k' and fzx_id='$fzx_id' and act='1' and mr='1' limit 1");
            if($xmfa){
                $faxinxi = $DB->fetch_one_assoc("select * from assay_method where id='".$xmfa['fangfa']."'");
                $faarr[$v] = $faxinxi['method_number'].$faxinxi['method_name'];
                $yiarr[$v] = $yiqiarr[$xmfa['yiqi']]; 
            }
			if($j=='1'){
				if(in_array($k,$vid_value)){
					$rs_xcjc=$DB->fetch_one_assoc("SELECT id,vd0 FROM `assay_order` where cyd_id='".$cyd_id."' and cid='".$row['id']."' and vid='".$k."'");
					$xc_xm_td.="<td style='max-width:100px;'><input size='4' type='text' name='xcjc[".$rs_xcjc['id']."]' value='".$rs_xcjc['vd0']."' ></td>";
				}else{
					$xc_xm_td.="<td>-</td>";
				}
			}else{
				if(in_array($k,$vid_value)){
					$rs_xcjc=$DB->fetch_one_assoc("SELECT id,vd0 FROM `assay_order` where cyd_id='".$cyd_id."' and cid='".$row['id']."' and vid='".$k."'");
					$xc_xm_td.="<td style='max-width:100px;'><input size='4' type='text' name='xcjc[".$rs_xcjc['id']."]' value='".$rs_xcjc['vd0']."' ></td>";
				}else{
					$xc_xm_td.="<td>-</td>";
				}
			}
			$j++;
		}
	}else{
		$xc_xm_td = '<td>-</td>';
	}
	if(!empty($cy_record_bt_arr)){
		foreach($cy_record_bt_arr as $key=>$value){
			if($value=='cy_way'){
				if($_GET['print']){
					$cy_record_line.="<td>{$row['cy_way']}</td>";
				}else{
					if(!$row['sid']){
						$cy_record_line.="<td></td>";
						continue;
					}
					$cy_record_line.="<td><select name='d[{$row[id]}][cy_way]'>";
					foreach($global['cy_way'] as $key=>$value){
							if($row['cy_way']==$value){
								$cy_record_line.="<option selected='selected'>{$value}</option>";
							}else{
								$cy_record_line.="<option>{$value}</option>";
							}
						}
						$cy_record_line.="</select></td>";
				}
			}elseif($value=='gg_zb'){
				
				if(empty($row[$value])){
					$row[$value]='清澈,无色无味';
				}
				$cy_record_line.="<td ><textarea  type='text' name='d[{$row[id]}][{$value}]' style=\"width:60px;height:55px;overflow:hidden;font-size:12px\" >{$row[$value]}</textarea></td>";
			}elseif($value=="tian_qi"){
				if($_GET['print']){
					$cy_record_line.="<td>{$row['tian_qi']}</td>";
					continue;
				}
				$cy_record_line.="<td><select style='width:55px' name='d[{$row[id]}][$value]'>";
					foreach($global['tian_qi'] as $key=>$value){
							if($row['tian_qi']==$value){
								$cy_record_line.="<option selected='selected'>{$value}</option>";
							}else{
								$cy_record_line.="<option>{$value}</option>";
							}
						}
						$cy_record_line.="</select></td>";
			}
			else{
				if(!empty($row[$value])){
					$len=preg_replace('/[\xe0-\xef][\x80-\xbf]{2}/',"aa",$row[$value]);
					$len=strlen($len);
				}else{
					$len=1;
				}
				$cy_record_line.="<td><input type='text' size='{$len}'  name='d[{$row[id]}][{$value}]'  value='{$row[$value]}'></td>";
			}
		}
	}
    //显示底部的仪器和方法信息
    //放在这里判断可能会出问题，还需要测试，目前只能放在这里，因为下面补全空白行时就开始加载模板了，以后写程序时模板最后都放在最后再加载，哪怕多循环一次，否则很多全局的地方都不能处理。
$fastr = $yistr = '';
foreach($faarr as $fk=>$fv){
    $fastr .= ','.$fk."使用方法".$fv;
}
foreach($yiarr as $yk=>$yv){
    if($yv){
        $yistr .= ",".$yk."使用仪器".$yv;
    }
}
$fastr = substr($fastr,'1');
$yistr = substr($yistr,'1');
	//总行数
	$guding_td_num	= 9;//固定的td个数
	$cols_num=count($cy_record_bt_arr)+$guding_td_num+$xc_jcxm_total;
	$cy_record_lines.=temp('cy_record_line.html');
	$cy_record_bt_nums=count($cy_record_bt_arr);//自定义表头信息的个数
	$add_xcjc_td_nums=$cy_record_bt_nums+3;//从这个td开始要增加现场项目的td
	if($_GET['print']&&($xu==$page_size||$i==$nums)){
		if($i==$nums){
			if($page_size-$xu){
				$td_nums=$cy_record_bt_nums+$guding_td_num;//空白行应该增加的td个数
				$add_tr_nums=$page_size-$xu;//增加的空白行个数
				for($k=0;$k<$add_tr_nums;$k++){
					$k_td_xh=$xu+$k+1;//空白行的序号
					$cy_record_lines.="<tr height='35px'><td>".$k_td_xh."</td>";
					for($j=0;$j<$td_nums;$j++){
						if($j==$add_xcjc_td_nums&!empty($xc_jcxm_arr)){
							$cy_record_lines.="";
							for($m=0;$m<count($xc_jcxm_arr);$m++){
								if($m=='0'){
									$cy_record_lines.="<td>&nbsp;</td>";
								}else{
									$cy_record_lines.="<td>&nbsp;</td>";
								}
							}
							$cy_record_lines.="";
						}else{
							$cy_record_lines.="<td></td>";
						}
					}
					$cy_record_lines.="</tr>";
				}
			}
			$line_qz = "<table align=\"center\" style=\"width:80%\"> <tr> <td align=\"center\" width=\"50%\">采样人：$cy_user_text</td><td align=\"center\">样品审核人：$sh_user_text</td> </tr> <tr> 
	<td align=\"center\">日期：$cyd[cy_user_qz_date]</td><td align=\"center\">日期：$cyd[sh_user_qz_date]</td> </tr> </table> ";
			echo temp("cy_record");
		}else{
			$line_qz = "<table align=\"center\" style=\"width:80%\"> <tr> <td align=\"center\" width=\"50%\">采样人：$cy_user_text</td><td align=\"center\">样品审核人：$sh_user_text</td> </tr> <tr> 
	<td align=\"center\">日期：$cyd[cy_user_qz_date]</td><td align=\"center\">日期：$cyd[sh_user_qz_date]</td> </tr> </table> ";
			echo temp("cy_record");
		}
				$cy_record_lines='';
				$xu=0;
	}
	$i++;
	$xu++;
}


/*if( $cyd['sh_user_qz'] && $u['userid'] != 'admin'){
    $_GET['print'] = 1;
}*/
if(($cyd['cy_user2'] == $u['userid']  || $cyd['cy_user'] == $u['userid']) && ($cyd['status']<5 || ((empty($cyd['cy_user_qz']) || (!empty($cyd['cy_user2']) && empty($cyd['cy_user_qz2']))) && !empty($json['退回']))) && $_GET['print']!=1 || $u['admin']){
	$save_button='<input type="submit" value="保存" class="Noprint btn btn-xs btn-primary no_print" onfocus="yanzheng()">';
}

if(empty($cyd['cy_user_qz_date'])||$cyd['cy_user_qz_date']=='0000-00-00'){
	$cyd['cy_user_qz_date']='';//采样人签字时间
}
if(empty($cyd['sh_user_qz_date'])||$cyd['sh_user_qz_date']=='0000-00-00'){
	$cyd['sh_user_qz_date']='';//样品接收人签字时间
}
$huiTuiButton   = '';
//采样员签字后，admin和审核人可以看到退回按钮
if(($cyd['cy_user_qz']!='' || $cyd['cy_user_qz2']!='') && (($u['userid'] != $cyd['cy_user_qz'] && $u['userid'] != $cyd['cy_user_qz2'] && $u['ypjs']) || $u['admin'])){
        $huiTuiButton   = "<a href=\"#\" title='回退采样单到>采样员未签字状态' onclick=\"return $(this).qbox({title:'采样单退回(将采样单退回到采样员“未签字”状态)',src:'$rooturl/huayan/hyd_huitui.php?action=cyd&id={$cyd['id']}',w:600,h:230});\"style=\"position:fixed;right:80px;bottom:15px;\" class=\"button blue\"> 退回&nbsp;采样单 </a>";
}
$tuiHuiTiShi    = '';
if(!empty($json['退回']) && $cyd['sh_user_qz']==''){
        $jsonTuiHui     = end($json['退回']);//取出最新一次退回的结果
        if(!empty($jsonTuiHui['xiuGaiLiYou'])){
                $xiuGaiLiYou    = "<tr><td>修改理由：</td><td>{$jsonTuiHui['xiuGaiLiYou']}</td></tr>";
        }else{
                $xiuGaiLiYou    = '';
        }
        $tuiHuiTiShi    = "<style>.tuiHui td{border:#000 1px solid;}</style><table class='tuiHui' cellpadding=\"10\"  style=\"margin:auto;width:100%;color:red;border-collapse: collapse;text-align:left;\">
        <caption style='color:red;'>此采样单被退回</caption>
        <tr>
                <td width=70 nowrap>退 回 人：</td>
                <td>{$jsonTuiHui['tuiHuiUser']}</td>
        </tr>
        <tr>
                <td nowrap>退回时间：</td>
                <td>{$jsonTuiHui['tuiHuiTime']}</td>
        </tr>
        <tr>
                <td nowrap>退回原因：</td>
                <td>{$jsonTuiHui['tuiHuiReason']}</td>
        </tr>
        $xiuGaiLiYou
</table>";
}
//echo $huiTuiButton;
//现场采样记录表的采样记录
if((!empty($cyd['cy_user_qz2']) && $cyd['cy_user_qz2']==$u['userid']) || (!empty($cyd['cy_user_qz']) && $cyd['cy_user_qz']==$u['userid'])){//admin的修改不记录，admin看到的页面不一样。 || ((!empty($cyd['cy_user_qz2']) || !empty($cyd['cy_user_qz'])) && $u['userid']=='admin')){
        $html   = temp("cy_record.html");
		if(is_array($json['退回'])){
			$jsonTuiHui     = end($json['退回']);//取出最新一次退回的结果
		}
        if(!empty($jsonTuiHui['xiuGaiLiYou'])){
                $xiuGaiLiYou    = addslashes_deep($jsonTuiHui['xiuGaiLiYou']);
        }else{
                $xiuGaiLiYou    = '';
        }
        cy_shuyuan($cyd['id'],$u['userid'],$html,$xiuGaiLiYou);
}
############采样人签字部分。为了避免签字部分的改变 被记录到 “修改记录”中，这里将签字部分改成变量的方式
//<a href=\"$rooturl/caiyang/caiyang_sh.php?action=采样人签字&cyd_id=$cyd[id]\">签字</a>
$line_qz	= "
	<input type=\"hidden\" name=\"current_user\" value=\"{$u['userid']}\" />
	<table align=\"center\" style=\"width:80%\"> 

	<tr> 
	<td align=\"center\" width=\"50%\">采样人：$cy_user_text</td><td align=\"center\">样品审核人：$sh_user_text</td> 
	</tr> 
	<tr> 
	<td align=\"center\">日期：$cyd[cy_user_qz_date]</td><td align=\"center\">日期：$cyd[sh_user_qz_date]</td> 
	</tr> 
	</table> 
	<p class=\"center\">{$save_button} $show_xiuGaiJiLu</p>";
if(empty($_GET['print'])){
	disp("cy_record.html");
}
?>
