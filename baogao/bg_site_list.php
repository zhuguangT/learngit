<?php
/**
 * 功能： 报告列表弹出层显示
 * 作者：zhengsen
 * 日期：2014-10-15
 * 描述：
*/
include '../temp/config.php';
include INC_DIR . "cy_func.php";
$divline="";
$fzx_id=$u['fzx_id'];

//ajax改变报告的模板
if($_POST['action']=='change_bg_mb'){
    if($_POST['rec_id']&&$_POST['te_id']){
        $jcbz_id    = $DB->fetch_one_assoc("SELECT jcbz_id FROM `report_template` WHERE `id`='{$_POST['te_id']}'");
        $query=$DB->query("UPDATE report SET te_id='".$_POST['te_id']."',`jcbz_id`='{$jcbz_id['jcbz_id']}' WHERE cy_rec_id='".$_POST['rec_id']."'");
        if($query){
            echo 1;
        }else{
            echo 0;
        }
    }
    exit();
}
//ajax改变报告的打印状态
if($_POST['action']=='change_print_status'){
    if($_POST['rec_id']&&isset($_POST['p_status'])){
        //查询当前cy表的进度
        $cy_status_arr  = $DB->fetch_one_assoc("SELECT * FROM cy WHERE id='".$_POST['cyd_id']."'");
        //只有化验已完成时，才能改报告为已完成状态
        if($cy_status_arr['status'] == '未打印' || in_array($cy_status_arr['status'],array('7','8'))){
            $query      = $DB->query("UPDATE report SET print_status='{$_POST['p_status']}' WHERE cy_rec_id='".$_POST['rec_id']."'");
            $print_num  = $DB->fetch_one_assoc("SELECT COUNT(id) FROM `report` WHERE cyd_id='{$_POST['cyd_id']}' AND `print_status`!=1");
                if($print_num <= 0){//更改cy表状态为报告已打印
                    $DB->query("UPDATE cy SET status='8' WHERE id='".$_POST['cyd_id']."'");
                }else{
                    $DB->query("UPDATE cy SET status='7' WHERE id='".$_POST['cyd_id']."'");//将cy表状态改回化验已完成的状态
                }
            echo "1";
        }else{
            echo '化验未完成,不能将报告改为已打印状态！';
        }
    }
    exit();
}
//向report初始化报告的信息
if(!empty($_GET['cyd_id'])){
    $query=$DB->query("SELECT * FROM report WHERE cyd_id='".$_GET['cyd_id']."'");
    $nums=mysql_num_rows($query);
    if(!$nums){
        $R=$DB->query("SELECT cr.*,c.cy_date,c.ys_date FROM cy c JOIN cy_rec cr ON c.id=cr.cyd_id where cyd_id='".$_GET['cyd_id']."' and zk_flag>=0 and sid>=0 ORDER BY cr.bar_code");
        while($row = $DB->fetch_assoc($R)){
            $temp_rs=array();
            $temp_rs=$DB->fetch_one_assoc("SELECT id FROM report_template WHERE  state > 0 AND water_type='".$row['water_type']."'");
            if(!empty($temp_rs)){
                $te_id= $temp_rs['id'];
            }else{
                $max_water_type=get_water_type_max($row['water_type'],$fzx_id);
                $temp_rs=$DB->fetch_one_assoc("SELECT id FROM report_template WHERE  state > 0 AND water_type='".$max_water_type."'");
                $te_id= $temp_rs['id'];
            }
            if(empty($te_id)){//默认一个模板
                $temp_rs=$DB->fetch_one_assoc("SELECT id FROM report_template WHERE  state > 0 LIMIT 1");
                $te_id = $temp_rs['id'];
            }            
            $DB->query(" INSERT INTO report(cyd_id,water_type,cy_rec_id,state,bg_date,te_id,tab_user)values('".$_GET['cyd_id']."','".$row['water_type']."','".$row['id']."','9',curdate(),'".$te_id."','".$u['userid']."')");  
         }
    }
    //查询当前cy表的进度
    $cy_status_arr=$DB->fetch_one_assoc("SELECT * FROM cy WHERE id='".$_GET['cyd_id']."'");
    if($cy_status_arr['status']==7){
         $DB->query("UPDATE cy SET status='8' WHERE id='".$_GET['cyd_id']."'"); 
    }
}
//打印状态
$print_status_arr=array(0=>"未打印",1=>"已打印");
//查询所有模板
$bgmb_list = array();
$cyd_id=$_GET['cyd_id'];//接受传过来的某个批次的id号    
$C = $DB->query("SELECT  c.*,s.water_type FROM cy_rec c LEFT JOIN `sites` s ON c.sid = s.id WHERE c.cyd_id='".$cyd_id."' AND sid>'0' AND zk_flag>'-1' order by c.bar_code");//查询某个批次下的站点

//循环列出每个站点的信息
while($c=$DB->fetch_assoc($C)){
    if(empty($c['water_type'])){
        $water_type_bh=substr($c['bar_code'],1,1);
        $water_type=array_search($water_type_bh,$global['bar_code']['water_type']);
        $water_type_max=$c['water_type']=get_water_type_max($water_type,$fzx_id);
    }else{
        $water_type_max=get_water_type_max($c['water_type'],$fzx_id);
    }

// 循环所有模板  设置站点的模板   
    $url    =   'cid='.$c['id'].'&cyd_id='.$cyd_id.'&sid='.$c['sid'];
    $re_rs=$DB->fetch_one_assoc("SELECT  te_id,print_status FROM report WHERE cyd_id='".$cyd_id."'  AND cy_rec_id='".$c['id']."' ");
    //判断该报告是否被退回过
    $back_warn='';
    if($re_rs['print_status']=='-1'){
        $back_warn="<span style=\"color:red\" id=".$c['id'].">(被退回)</span>";
    }
    $sql ="SELECT  * FROM report_template WHERE state = '1' ";

    $rows = $DB->query($sql);
    $bgmb_list  = '';  
    while($row=$DB->fetch_assoc($rows)){
        if( $row['id'] ==  $re_rs['te_id'] ||(empty($re_rs) && $row['water_type'] == $water_type_max) ){
            $bgmb_list  .= ' <option value ='.$row['id'].'  selected="selected">'.$row['te_name'].'</option>';  
         }else{
            $bgmb_list  .= ' <option value ='.$row['id'].'>'.$row['te_name'].'</option>';  
         }
    }

    //计算出每个站点的项目数
    $sql_vid_nums="SELECT * FROM assay_order WHERE cyd_id='".$cyd_id."' AND cid='".$c['id']."' GROUP BY vid";
    $query_vid_nums=$DB->query($sql_vid_nums);
    $vid_nums=mysql_num_rows($query_vid_nums);
    $z_nums=$vid_nums;//一个站点中所有的项目
    $sql_order="SELECT ao.*  FROM assay_order ao LEFT JOIN assay_pay ap ON ao.tid=ap.id  where ao.cid='".$c['id']."' AND ao.cyd_id='".$cyd_id."' AND ao.hy_flag>=0  AND ao.sid>0 AND ap.over='".$qzjb."' ";
    $query_order=mysql_query($sql_order);
    $y_nums=0;
    while($rs_order=$DB->fetch_assoc($query_order)){

        if(!empty($rs_order['vd0'])||$rs_order['vd0']=='0'){
            $y_nums++;
        }
    }
    
     if($c[water_type] !=''){
     $bzlx=$DB->fetch_one_assoc("SELECT  lname FROM leixing WHERE id='".$c['water_type']."'");
     $szlx= $bzlx['lname'];
    }
    //显示打印状态
    $print_sta_option='';
    foreach($print_status_arr as $key=>$value){
        if($key==$re_rs['print_status']){
            $print_sta_option.="<option value=".$key." selected=\"selected\">".$value."</option>";
        }else{
            $print_sta_option.="<option value=".$key.">".$value."</option>";
        }
    }
    $divline.=  '<tr align=center>
                
                    <td><label>'.$c['id'].'<input type="checkbox" name="cids[]" value='.$c['id'].'></label></td>
                    <td width="120">
                        '.$c['bar_code'].'
                 
                      
                    </td>
                    <td>'.$szlx.'</td>
                 
                    <td style="text-align:left;">'.$c['site_name'].'</td>
                    <td>'.$y_nums.'/'.$z_nums.'</td>
                    <td> 
                    <select  name="bgmb" onchange=change_bg_mb('.$c['id'].',this)>  
                     '.$bgmb_list.'
                    </select>  
                   </td>
                   <td>
                    <select name="print_status" onchange=change_print_status('.$c['id'].',this)>
                    '.$print_sta_option.'
                    </select>
                    <br/>'.$back_warn.'
                   </td>
                    <td id="state'.$c['id'].'" align="left">
                        <a href="'.$rooturl.'/baogao/bg_chakan.php?'.$url.'" target="_blank" class="btn btn-xs btn-primary " style="width:75px">查看报告</a>
                        <a href="'.$rooturl.'/baogao/bg_chakan.php?lx=2&'.$url.'" target="_blank" class="btn btn-xs btn-primary" style="width:75px">下载WORD</a> 
                        <button type="button" class="btn btn-xs btn-primary" onclick="bg_xm_set('.$cyd_id.','.$c[id].')" style="width:75px">项目设定</button> 
                    </td>
                </tr>';
}
    disp("bg/bg_site_list");
?>