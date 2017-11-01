<?php
/**
 * 功能：标准溶液标定列表程序
 * 作者：Mr Zhou
 * 日期：2014-12-04
 * 描述：
*/
include "../../temp/config.php";
$fzx_id = FZX_ID;
if($u['is_zz']&&intval($_GET['fzx_id'])){
    $fzx_id = intval($_GET['fzx_id']);
}
$n=strpos($current_url,'&table_type');
$current_url=!empty($n)?substr($current_url,0,$n):$current_url;
$table_type_01 = $table_type_02 = '';
if( '02' != $_GET['table_type'] ){
    $table_type_01 = 'selected';
}else{
    $table_type_02 = 'selected';
}
//导航
$trade_global['daohang'] = array(
    array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
    array('icon'=>'','html'=>'标准溶液列表','href'=>$rooturl.'/jcsy/bybd/bzry_list.php'),
    array('icon'=>'','html'=>'标准溶液标定','href'=>$current_url),
);
$trade_global['js']         = array('date-time/bootstrap-datepicker.min.js','date-time/bootstrap-timepicker.min.js','typeahead-bs2.min.js','lims/key.js','lims/hyd.js');
$trade_global['css']        = array('lims/main.css','datepicker.css','bootstrap-timepicker.css');
$bd_id = intval($_GET['bd_id']);
$r = array();
$jh_arr = explode("','",$u['user_other']['v1']);
$fh_arr = explode("','",$u['user_other']['v2']);
$sh_arr = explode("','",$u['user_other']['v3']);
if($bd_id){
    $sql = "SELECT * FROM `jzry_bd` WHERE `id`='$bd_id' AND `fzx_id`=$fzx_id LIMIT 1";
    $r=$DB->fetch_one_assoc($sql);
    if($r['sign_01']==''){
        if( '' == $r['fx_qz_date']){
            $sign_01='<input type="submit" name="fx_qz" value="签字">'; 
        }else{
            $sign_01='<input type="button" name="fx_qz" value="签字">'; 
        }
        
    }else{
        $sign_01=$r['sign_01'];
        if($r['jh_user']=='' && ( $u['admin'] || in_array($r['vid'], $jh_arr) ) ){
            $jh_user='<input type="submit" name="jh_qz" value="签字">';
        }else{
            $jh_user=$r['jh_user'];
            if($r['fh_user']=='' && ( $u['admin'] ||  in_array($r['vid'], $fh_arr))){
                $fh_user='<input type="submit" name="fh_qz" value="签字">';
            }else{
                $fh_user=$r['fh_user'];
                if($r['sh_user']=='' && ( $u['admin'] ||  in_array($r['vid'], $sh_arr))){
                    $sh_user='<input type="submit" name="sh_qz" value="签字">';
                }
            }
        }
    }
        
}
$dw=$r['jzry_nddw'];
$line = '';
$r['sd'] = json_decode($r['sd'],true);
$r['zd'] = json_decode($r['zd'],true);
$r['yl'] = json_decode($r['yl'],true);
$r['tj'] = json_decode($r['tj'],true);
$r['bd_nd'] = json_decode($r['bd_nd'],true);
$r['dnd'] = json_decode($r['dnd'],true);
$r['djc'] = json_decode($r['djc'],true);
for($i=1;$i<=8;$i++){
    $yl[$i] = $r['yl'][$i];
    $tj[$i] = $r['tj'][$i];
    $bd_nd[$i]      = $r['bd_nd'][$i];
    $dnd[$i]= $r['dnd'][$i];
    $djc[$i]= $r['djc'][$i];
}
//项目列表
$xm_list = '';
$xm_list = '<option value="">请选择分析项目</option>';
$sql = "SELECT v.id,v.value_C FROM `assay_value` v LEFT JOIN `jzry` j ON v.id=j.vid WHERE j.fzx_id='$fzx_id' AND `sj_yxrq`>=curdate() GROUP BY j.vid ORDER BY CONVERT( `value_C` USING gbk )";
$query = $DB->query($sql);
while ($row=$DB->fetch_assoc($query)) {
    $selected = ($row['id']==$r['vid']) ? 'selected' : '';
    $xm_list .= '<option '.$selected.' value="'.$row['id'].'">'.$row['value_C'].'</option>';
}
//是否允许切换表格
$display='';
$table_type = (''==$r['table_type'])?'01':$r['table_type'];
if(''!=$_GET['table_type']){
    $table_type = $_GET['table_type'];
}
switch($_GET['action']){
    case 'add':
        $save_button='<input type="submit" name="act" value="保存" />';
        $r['bzry_bdrq']=date('Y-m-d');
        $r['beizhu']="每人四平行测定结果极差的相对值不得大于重复性临界极差的相对值0.15%，两人共八平行测定结果极差的相对值不得大于重复性临界极差的相对值0.18%";
        break;
    case 'view':
    case 'edit':
        if( empty($r['id']) ){
            gotourl('bzry_list.php','标定液不存在！');
        }
        $edit_able = 0;
        $print="<center><a href='bzry_bd.php?bd_id=$_GET[bd_id]&action=print' target='_blank' class='btn btn-xs btn-primary'>打印</a></center>";
        //允许修改
        if((!$r['jh_user'] && $u['userid']==$r['fx_user']&&''==$r['sign_01']) || $u['admin']){
            $displayclass= 'class="inputc hyd"';
            $edit_able = 1;
            $save_button='<input type="submit" name="act" value="修改" />';
        }else{
            $displayclass = 'class="noinputc hyd"';
            $display = 'style="display:none"';
            echo $print_data['xm'] = $_SESSION['assayvalueC'][$r['vid']];
            $print_data['bzry_name'] = $r['bzry_name'];
            $print_data['jzry_name'] = $r['jzry_name'];
        }
        //校核人、复核人、审核人 在化验员 签字后 可以看到"回退按钮"
        if( FZX_ID == $r['fzx_id'] && !empty($r['sign_01']) ){
            $a=$b=$c=$d=false;
            //未复核并且具有校核该项目的权限
            (empty($r['fh_user']) && in_array($r['vid'],$jh_arr)) && $b=true;
            //未审核并且具有复核该项目的权限
            (empty($r['sh_user']) && in_array($r['vid'],$fh_arr)) && $b=true;
            //未**核并且具有审核该项目的权限
            (empty($r['xh_user']) && in_array($r['vid'],$sh_arr)) && $c=true;
            $u['admin'] && $d=true;
            if($a || $b || $c || $d ){
                $tuihui_button = '<button type="button" class="btn btn-xs btn-primary tui_Hui_'.$r['id'].'" title="" >退回标定记录</button>';
            }
        }
        //被退回的化验单的 回退原因的显示 默认是关闭状态
        $huiTuiShow = '';
        $payJson    = json_decode($r['json'],true);
        if(empty($r['fh_user'])&&$payJson['退回']!=''){
            $huiTuiLiYou= end($payJson['退回']);
            $huiTuiShow = '<div class="panel hyd_tuihui">
                <div class="panel-heading">
                    <h4 class="panel-title" style="text-align:left;">
                        <a href="#collapseTwo_'.$r['id'].'" data-parent="#accordion_'.$r['id'].'" data-toggle="collapse" class="accordion-toggle collapsed">
                            <i data-icon-show="icon-angle-right" data-icon-hide="icon-angle-down" class="bigger-110 icon-angle-right"></i>
                            &nbsp;化验单退回信息
                        </a>
                    </h4>
                </div>
                <div id="collapseTwo_'.$r['id'].'" class="panel-collapse collapse" style="color:red;text-align:left;">
                    <div class="panel-body">
                        <dl class="dl-horizontal">
                            <dt>退 回 人：</dt><dd>'.$huiTuiLiYou['tuiHuiUser'].'</dd>
                            <dt>退回时间：</dt><dd>'.$huiTuiLiYou['tuiHuiTime'].'</dd>
                            <dt>退回原因：</dt><dd>'.$huiTuiLiYou['tuiHuiReason'].'</dd>
                            <dt>修改理由：</dt><dd>'.$huiTuiLiYou['xiuGaiLiYou'].'</dd>
                        </dl>
                    </div>
                </div>
            </div>';
        }
        break;
    case 'print':
        $edit_able = 0;
        $display = 'style="display:none"';
        $print_data['xm'] = $_SESSION['assayvalueC'][$r['vid']];
        $print_data['bzry_name'] = $r['bzry_name'];
        $print_data['jzry_name'] = $r['jzry_name'];
        disp('jcsy/bybd/bzry_bd_'.$table_type,'head_print');
        break;
}
disp('jcsy/bybd/bzry_bd_'.$table_type);
?>