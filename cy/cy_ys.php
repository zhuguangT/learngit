<?php
/**
 * 功能：采样验收记录表
 * 作者：xiewenhao
 * 时间：2014-07-14
*/
include '../temp/config.php';
require_once INC_DIR . "cy_func.php";
if($u[userid] == '') nologin();
$fzx_id=$u['fzx_id'];
$_SESSION['back_url'] = $current_url;
//导航
$trade_global['daohang'][] = array('icon'=>'','html'=>'采样验收记录表','href'=>"$rooturl/cy/cy_ys.php?cyd_id={$_GET['cyd_id']}");
$_SESSION['daohang']['cy_ys']	= $trade_global['daohang'];
if($_GET['cyd_id']>0)
{
    $cyd = get_cyd( $_GET['cyd_id'] );
    //将普通签名转换为电子签名
    $cyd    = get_userid_img('cy',array("cy_user","cy_user2","cy_user_qz","cy_user_qz2","jy_user","ys_user"),$_GET['cyd_id'],$cyd);
    $_GET['cyid']=$_GET[cyd_id];
}
//点击打印时的显示
if($_GET['print']){
    $bar='';
    $print="<link href=\"$rooturl/css/lims/print.css\" rel=\"stylesheet\" />";
}
else{
    $dayin='<a href="?cyd_id='.$_GET['cyd_id'].'&print=1&ajax=1" target="_blank" class="btn btn-primary btn-sm"><i class="icon-print bigger-160"></i>打印</a>';
} 
$fzx_id=$u['fzx_id'];

//采样人显示
if($cyd['cy_user2']!=''){
    $cyren = $cyd['cy_user'].'、'.$cyd['cy_user2'];
}else{
    $cyren = $cyd['cy_user'];
}
//接样人显示
$rens = array();
$rensql = $DB->query("select id,userid from users where nickname is not NULL and fzx_id='$fzx_id'");
while($ren = $DB->fetch_assoc($rensql)){
    $rens[$ren['id']] = $ren['userid'];
}
if($cyd['jy_user']!=''){
    $jy_user = $cyd['jy_user'];
}else{
    $jy_user  = "<select name='cyd[jy_user]'>";
    foreach($rens as $ming){
        $jy_user  .= "<option value='$ming'>$ming</option>";
    }
    $jy_user  .= "</select>";
}
//评价
if($cyd['ys_result'] == ''){
    $cyd['ys_result'] = '符合采样结果';
}
//验收日期
if($cyd['ys_date']==''){
    $yy =  date( "Y" );
    $mm =  date( "m" );
    $dd =  date( "d" );
    $neirong = "<td align='center' colspan='2'>采样人: $cyren</td><td align='center' colspan='3'>接样人: $jy_user</td><td align='center' colspan='13'>验收日期：&nbsp;<input type='text' value='$yy' size='5' name='yy'>&nbsp;年&nbsp;<input type='text' name='mm' value='$mm' size='2'>&nbsp;月&nbsp;<input type='text' name='dd' value='$dd' size='2'>&nbsp;日</td><td align='center' colspan='6'>验收人: $cyd[ys_user]</td>";
}else{
    $yy =  date( "Y",strtotime($cyd['ys_date']) );
    $mm =  date( "m",strtotime($cyd['ys_date']) );
    $dd =  date( "d",strtotime($cyd['ys_date']) );
    $neirong = "<td align='center' colspan='2'>采样人: $cyren</td><td align='center' colspan='3'>接样人: $jy_user</td><td align='center' colspan='13'>验收日期：&nbsp;$yy&nbsp;年&nbsp;$mm&nbsp;月&nbsp;$dd&nbsp;日</td><td align='center' colspan='6'>验收人: $cyd[ys_user]</td>";
}
//水样类型
$wt = array();
$wtsql = $DB->query("select * from leixing where act = '1'");
while($wtrow = $DB->fetch_assoc($wtsql)){
    $wt[$wtrow['id']] =  $wtrow['lname'];
}
$note = $cyd['note'];
$rwxz=$global['site_type'][$cyd['site_type']];

//查询一个批次下的所有项目
$value_arr=array();
$sql="SELECT * FROM `cy_rec` WHERE `cyd_id` ='".$_GET['cyd_id']."' ORDER BY id";
$res=$DB->query($sql);
while($rs=$DB->fetch_assoc($res))
{
    $assay_values=explode(',',$rs['assay_values']);
    $value_arr[$rs['id']]=$assay_values;
}

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
// }
//显示瓶子分类
$rq_fenlei_td='';
$psql = $DB->query("select * from n_set where module_name ='pingzifenlei'");
while($pingzi = $DB->fetch_assoc($psql)){
    $rq_fenlei_td.="<td style='width:12%;'>".$pingzi['module_value1']."</td>";
}
if($rq_fenlei_td==''){
    echo "<script>if(confirm('项目没有设置保存的容器,点击确定去设置！')){location.href='$rooturl/system_settings/cy_rq_manage/rq_list.php';}else{location.href='$rooturl/cy/cyrw_list.php';}</script>";
} 
//查询出一个批次下的所有站点信息  
$sql="SELECT cr.* FROM `cy_rec` cr LEFT JOIN `sites` s  ON cr.sid=s.id WHERE cyd_id ='".$_GET['cyd_id']."' AND cr.status>0 AND (zk_flag >=0 or zk_flag = '-6') and zk_flag <>'3' ORDER BY cr.id ASC";
$res=$DB->query($sql);
$i=1;
$xu=1;
$cy_js_line='';
//接收按钮
if($_GET['print'] || $cyd['ys_user']){
    $jieshou = '';
}else{
    $jieshou = "<td colspan='23'><input type='submit' value='同意接收' class='btn btn-primary btn-sm'></td>";
}
//如果是打印页面显示设置
if($_GET['print']){
    $pddy = "<input type='hidden' value ='1' id='pddy'>";
    $zz = "<td zz='zz'></td>";
    if(!empty($_GET['page_size'])){
        $page_size=$_GET['page_size'];
    }else{
        $page_size='12';//默认打印12行
    }
    $input_note="此处设置打印行数，默认12行";
    echo temp("cy_ys_print_head");
}else{
    $pddy = "";
    $zz = "";
}
//$yp_status
//处理样品
$meiye = '4';
$nums    = $DB->num_rows($res);//所有样品的数量
while( $row = $DB->fetch_assoc( $res ) )
{
    $cy_js_line.="<tr height='120px'>";
    if($_GET['print']){
        $xh=$xu;
    }else{
        $xh=$i; 
    }
    //水样类型处理
    $wtype ='';
    $wtype = $wt[$row['water_type']];
    if($wtype==''){
        $wtype ='纯水';
    }
    //样品状态处理
    $ypzt = '';

    if($row['ys_zt']){
        $cy_notes = explode(',',$row['ys_zt']);
        foreach($yp_status_xinxi as $yp){
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
            $ypzt = "<input type='checkbox' value='清澈透明' name='ys_status[$row[id]][]'>清澈透明&nbsp;<input type='checkbox' value='浑浊' name='ys_status[$row[id]][]'>浑浊&nbsp;<input type='checkbox' value='沉淀' name='ys_status[$row[id]][]'>沉淀&nbsp;<input type='checkbox' value='气泡' name='ys_status[$row[id]][]'>气泡&nbsp;<input type='checkbox' value='其他' name='ys_status[$row[id]][]'>其他&nbsp;<input type='checkbox' value='包装完好' name='ys_status[$row[id]][]'>包装完好&nbsp;<input type='checkbox' value='包装破损' name='ys_status[$row[id]][]'>包装破损"; 
        }
    }
    
    if($row['ys_result']==''){
        $row['ys_result']='合格';
    }
    //处理样品瓶子
    $pingshu = array();
    $pingzi = array();
    $tjstr = '';
    $newping = array();
    if($row['pingstr']){
         $pingstr = json_decode($row['pingstr'],ture);
         $pingzi1 = array_unique($pingstr['玻璃瓶']);
         $pingzi2 = array_unique($pingstr['塑料瓶']);
         if(!is_array($pingzi2)){
             $pingzi2 = array();
         }
         if(!is_array($pingzi1)){
             $pingzi1 = array();
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
        $tiji = array();
        foreach($rq_data as $pk=>$pv){
            $pingshu1 = array();
            foreach($value_arr[$row['id']] as $xm){
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
    if(!$tjstr){
        foreach($tiji as $tk=>$tp){
            if(strstr($tp['rq_size'],'mL')){
                $tp['rq_size'] = $tp['rq_size']/1000;
            }
            $tjstr += $tp['mr_shu']*$tp['rq_size'];
        } 
    }
    
    //在此将样品体积添加到与此站点对应的report表中
    if(!empty($tjstr)){
        $DB->query("UPDATE `report` SET `yp_sl` = ".$tjstr." WHERE `cyd_id` = ".$row['cyd_id']." AND `cy_rec_id` = ".$row['id']." ");
    }
    if($_GET['print']){
        $ys_re = $row['ys_result'];
    }else{
        $ys_re = "<textarea  name='ys_result' cols='6' rows='4'>{$row['ys_result']}</textarea>"; 
    }
      $tan = "<div id=\"cover$row[id]\" class=\"modal\" role=\"dialog\" style='background-color:rgba(15, 15, 15, 0.7)!important;'>
    <div class=\"modal-dialog\" style='width:800px;'>
      <div id=\"con\" class='modal-content' style='width:800px;height:400px;text-align:center;vertical-align:middle;'>
          <h3>修改瓶数</h3>
          <table>$pingshustr</table>
          <br/>
          <input type='button' onclick='pingajax(this)' value='保存' class='btn btn-xs btn-primary' cid='$row[id]' cyd_id='".$_GET['cyd_id']."'>
          <input type='button' onclick='guanbi(this)' value='关闭' class='btn btn-xs btn-primary' cid='$row[id]'>
          <br/>
      </div>
    </div>
</div>";
    $cy_js_line.="<td>$xh</td><td>{$row['site_name']}</td><td>{$wtype}</td><td>{$row['bar_code']}</td><td align='left'>$ypzt</td><td onclick='xiuping(this)' cid='$row[id]'>$pingstr2</td><td onclick='xiuping(this)' cid='$row[id]'><p>$pingstr1</p></td><td>$tjstr</td><td>$ys_re $tan</td>";
    $cy_js_line.="</tr>";
    if($_GET['print']){
       if($xu%$meiye==0){
            $cy_js_line.="</table><table align=\"center\" style=\"width:28cm;\"  cellspacing=\"0\" cellpadding=\"0\"><tr> $neirong</tr><tr align=\"center\">$jieshou</tr></table><br/><div class='fenye'></div><table  class=\"center\" style=\"width:28cm;\" border='1'  cellspacing=\"0\" cellpadding=\"0\"><caption style=\"font-size:24px; line-height:25px;\">
    <B>样品验收记录表</B>  
  </caption><tr><td style=\"width:2%;\" rowspan='2'>序号</td><td style=\"width:14%;\" rowspan='2'>采样地点</td><td style=\"width:5%;\" rowspan='2'>样品类型</td><td style=\"width:5%;\" rowspan='2'>样品编码</td>
            <td style=\"width:15%;\" rowspan='2'>样品状态</td><td style=\"width:25%;\" colspan='2'>样品数量</td><td style=\"width:5%;\" rowspan='2'>体积<p/>(L)</td><td style=\"width:8%;\" rowspan='2'>验收结论</td></tr><tr>$rq_fenlei_td</tr><tr>";
        } 
    }
    $i++;
    $xu++;
}
disp("cy_ys.html");
?>
