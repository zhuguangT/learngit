<?php
/**
 * 功能：标准溶液，标准样品
 * 作者: Mr Zhou
 * 日期: 2014-10-17
 * 描述: 标准溶液，标准样品 添加,修改,删除
*/
define('__maxline__',10);
include ('../temp/config.php');
if(!empty($_FILES['upfile']['name'][0])){
    $file_name_new_arr = $file_name_old_arr = array();
    foreach($_FILES['upfile']['name'] as $key=>$value){
        if($_FILES['upfile']['size'][$key] < 100000000){
            $name_suffix= substr($value,0,strrpos($value,"."));
            $xxx    = explode('.',$_FILES['upfile']['name'][$key]);
            $cnt    = count($xxx);
            $newname= date(ymdhis)."_{$key}.".$xxx[$cnt-1]; 
            $path   = "./upfile/".$newname;
            $file_name_new_arr[] = $newname;
            $file_name_old_arr[] = $_FILES['upfile']['name'][$key];
            //将文件移入指定文件夹
            if(move_uploaded_file($_FILES['upfile']['tmp_name'][$key],$path)){

            }
        }else{
            echo "<script>alert('上传文件不能超过95M');history.go(-1);'</script>";
        }
    }
    if($_POST['action'] == '编辑完成'){
        $sql = "SELECT * FROM `bzwz` WHERE `id` = '{$_POST['wz_id']}'";
        $data = $DB->fetch_one_assoc($sql);
        if(!empty($data['dilution_method_file']) && $r['dilution_method_file'] != '[]' && $r['dilution_method_file'] != null){
            $file_name_new_arr = array_merge($file_name_new_arr , json_decode($data['dilution_method_file'], true));
            $file_name_old_arr = array_merge($file_name_old_arr , json_decode($data['dilution_method'], true));
        }    
        $file_name_new_json = json_encode($file_name_new_arr , JSON_UNESCAPED_UNICODE);
        $file_name_old_json = json_encode($file_name_old_arr , JSON_UNESCAPED_UNICODE);
         $xsff = ",`dilution_method` = '$file_name_old_json' , `dilution_method_file` = '$file_name_new_json'";
    }else{
         $file_name_new_json = json_encode($file_name_new_arr , JSON_UNESCAPED_UNICODE);
         $file_name_old_json = json_encode($file_name_old_arr , JSON_UNESCAPED_UNICODE);
    }   
}else{
    $xsff = '';
}
$fzx_id = FZX_ID;
$wz_name = empty($_GET['wz_name'])? '':$_GET['wz_name'];
$tabs = ($_GET['wz_type']=='标准溶液') ? '#tabs-1':'#tabs-2';
//导航
$trade_global['daohang'] = array(array('icon'=>'icon-home home-icon','html'=>'首页','href'=>$rooturl.'/main.php'),array('icon'=>'','html'=>$_GET['wz_type'],'href'=>$rooturl.'/bzwz/bzwz_list.php?wz_type='.$_GET['wz_type'].$tabs),array('icon'=>'','html'=>$_GET['wz_type'].$wz_name.$_GET['action'],'href'=>$current_url));
$trade_global['js'] = array('jquery.date_input.js');
$trade_global['css'] = array('lims/main.css','date_input.css');

//通过对数据库查询的到用户曾经填过的所有单位
$sql = "SELECT `wz_unit` FROM `bzwz_detail` GROUP BY `wz_unit`";
$re = $DB->query($sql);
$unit_select = "<select style='position: absolute; right: 9px;top: 6.6px;height: 28.5px' onchange='check_unit(this);'><opiton>请选择单位</option>";
while ($data = $DB->fetch_assoc($re)) {
    $unit_select .= "<option>{$data['wz_unit']}</option>";
}
$unit_select .="</select>";

if(''==$_GET['action']){
    $_GET['action']='新增';
}
$action_style = ' class="btn btn-primary btn-sm" ';
$note = ($_GET['wz_type']=='标准溶液') ? '备注' : '浓度范围';
//查询出细分类型可供选择
$sql = "SELECT * FROM `bzwz` WHERE `wz_type` = '$_GET[wz_type]' GROUP BY `wz_type_subdivide`";
$re = $DB->query($sql);
$type_subdivide_select = "<option>请选择</option>";
while($data = $DB->fetch_assoc($re)){
	if(!empty($data['wz_type_subdivide'])){
		$type_subdivide_select .= "<option value='$data[wz_type_subdivide]'>$data[wz_type_subdivide]</option>";	
	}
}
if(!empty($_POST['action'])){
    $_GET['action']=$_POST['action'];
}
switch($_GET['action']){
    case '新增': //显示 标准物质登记表 画面
        $class      = 'class="class"';
        $_unit      = '<select name="单位"><option value="支">支</option><option value="瓶">瓶</option><option value="套">套</option></select>';
        $_action    = '<input '.$action_style.' type="submit" name="action" value="保存">';
        $_dilution_method   = '<textarea name="稀释方法" cols="110">'.$r['dilution_method'].'</textarea>';
        $item_list  = '<select name="vid[]"><option></option>';
        foreach ($_SESSION['assayvalueC'] as $key => $value) {
            $item_list.='<option value="'.$key.'">'.$value.'</option>';
        }
        $item_list.='</select>';
        for($i=0;$i<__maxline__;$i++){
            $lines.=temp('bzwz/bzwz_line');
        }
        disp('bzwz/bzwz');
        break;
    case '保存': //保存 新的 标准物质
        $sql = "INSERT INTO `bzwz` (`fzx_id`,`wz_type`,`wz_bh`,`wz_name`,`time_limit`,`manufacturer`,`amount`,`unit`,`dilution_method`,`dilution_method_file`,`create_man`,`create_date` , `wz_type_subdivide`,`limit_num`,`gl_bh`,`jiti`,`danjia`,`cp_gg`,`cc_tj`)
            VALUES ('$fzx_id','{$_POST['wz_type']}','{$_POST['编号']}','{$_POST['名称']}','{$_POST['有效期']}','{$_POST['生产单位']}','{$_POST['数量']}','{$_POST['单位']}','{$file_name_old_json}','{$file_name_new_json}','{$u['userid']}',curdate() , '{$_POST['wz_type_subdivide']}','{$_POST['提醒数量']}','{$_POST['gl_bh']}','{$_POST['jiti']}','{$_POST['danjia']}','{$_POST['cp_gg']}','{$_POST['cc_tj']}')";
        $DB->query($sql);
        $id=$DB->insert_id();
        foreach ($_POST['vid'] as $i => $_vid) {
            if(empty($_vid)){
                continue;
            }
            $_c_bound       = $_POST['c_bound'][$i];
            $_consistence   = $_POST['nong_du'][$i];
            $_eligible_bound= $_POST['bound'][$i];
            $_wz_unit = $_POST['wz_unit'][$i];
            $xuhao= intval($_POST['xuhao']);
            $sql = "INSERT INTO `bzwz_detail` (`wz_id`,`vid`,`consistence`,`eligible_bound`,`c_bound`,`create_date`,`xuhao`,`wz_unit`) 
                VALUES ($id,'$_vid','$_consistence','$_eligible_bound','$_c_bound',curdate(),'$xuhao','$_wz_unit')";
            $DB->query($sql);
        }
        $DB->query("INSERT INTO `bzwz_ls` (`wz_id`,`wz_type`,`op_type`,`amount`,`op_man`,`dealer`,`op_date`,`jie_cun`,`ls_note`) 
            VALUES ($id,'{$_POST['wz_type']}','入库','{$_POST['数量']}','{$u['userid']}','{$u['userid']}',curdate(),'{$_POST['数量']}','')");
        gotourl('bzwz_list.php?wz_type='.$_POST['wz_type'].$tabs);
        break;
    case '修改': //显示修改画面
        $_action= '<input '.$action_style.' type="submit" name="action" value="编辑完成">';
        $sql    = "SELECT * FROM `bzwz` WHERE `fzx_id`='$fzx_id' AND `id`='{$_GET['wz_id']}'";
        $r      = $DB->fetch_one_assoc($sql);
        if(!empty($r['dilution_method_file']) && $r['dilution_method_file'] != '[]'){
            $file_new_name_arr = json_decode($r['dilution_method_file'], true);
            $file_old_name_arr = json_decode($r['dilution_method'], true);
            foreach($file_new_name_arr as $key=>$value){
                        $files.="<a href='./upfile/{$value}' target=_blank;>{$file_old_name_arr[$key]}</a><span class='glyphicon glyphicon-remove red' style='float:right;right:20px;cursor:pointer;' onclick=delete_file(this,'{$value}','{$key}','{$r['id']}');></span></br>";
                    }
        }
        $_unit  ='<select name="单位"><option value="'.$r['unit'].'">'.$r['unit'].'</option><option value="支">支</option><option value="瓶">瓶</option><option value="套">套</option></select>';
        $_dilution_method='<textarea name="稀释方法" cols="110">'.$r['dilution_method'].'</textarea>';
        $sql    = "SELECT * from `bzwz_detail` where `wz_id`=$r[id] order by `id`";
        $RD     = $DB->query($sql);
        $n      = $DB->num_rows($RD);
        foreach ($_SESSION['assayvalueC'] as $key => $value) {
            $_item_list.='<option value="'.$key.'">'.$value.'</option>';
        }
        while($rd=$DB->fetch_assoc($RD)){
            $xuhao = $rd['xuhao'];
            $value_C=$_SESSION['assayvalueC'][$rd['vid']];
            $item_list="<select name=vid[]><option value=$rd[vid]>$value_C</option><option></option>".$_item_list."</select>";
            $lines .= temp('bzwz/bzwz_line');
        }
        $item_list="<select name=vid[]><option></option>".$_item_list."</select>";
        $k  = intval(__maxline__-$n);
        $rd = array();
        if($k>0){
            for($i=0;$i<$k;$i++){
                $lines .= temp('bzwz/bzwz_line');
            }
        }
        disp('bzwz/bzwz');
        break;
    case '编辑完成':
        if(!empty($xsff)){
            $xsff_sql = " , `dilution_method` = '{$file_name_old_json}' , `dilution_method_file` = '{$file_name_new_json}'";
        }else{
            $xsff_sql = '';
        }
        // echo $xsff_sql;die;
        $r=$DB->fetch_one_assoc("SELECT * FROM `bzwz` WHERE `id`='{$_POST['wz_id']}'");
        $DB->query("UPDATE `bzwz` SET `wz_bh`='{$_POST['编号']}' $xsff_sql , `wz_name`='{$_POST['名称']}' , `time_limit`='{$_POST['有效期']}' , `manufacturer`='{$_POST['生产单位']}' ,`amount`='{$_POST['数量']}',`unit`='{$_POST['单位']}',`modify_man`='{$u['userid']}' , `modify_date`=curdate() ,`limit_num` = '{$_POST['提醒数量']}', `wz_type_subdivide`='{$_POST['wz_type_subdivide']}',`gl_bh`='{$_POST['gl_bh']}',`jiti` = '{$_POST['jiti']}' , `danjia` = '{$_POST['danjia']}' , `cp_gg` = '{$_POST['cp_gg']}' , `cc_tj` = '{$_POST['cc_tj']}' WHERE `id`='{$_POST['wz_id']}'");
        if($r['amount']!=$_POST['数量']){
            $k=$r['amount']-$_POST['数量'];
            if($k>0) {
                $DB->query("INSERT INTO `bzwz_ls` (`wz_id`,`wz_type`,`op_type`,`amount`,`op_man`,`op_date`,`jie_cun`,`ls_note`) VALUES ('{$_POST['wz_id']}','{$_POST['wz_type']}','出库',$k,'{$u['userid']}',curdate(),'{$_POST['数量']}','{$_POST['wz_type']}盘亏')");
            }else{
                $DB->query("INSERT INTO `bzwz_ls` (`wz_id`,`wz_type`,`op_type`,`amount`,`op_man`,`op_date`,`jie_cun`,`ls_note`) VALUES ('{$_POST['wz_id']}','$_POST[wz_type]','入库',$k,'$u[userid]',curdate(),'$_POST[数量]','{$_POST['wz_type']}盘盈')");
            }
        }
        foreach($_POST['_id'] as $i => $value){
            $_id    = intval($_POST['_id'][$i]);
            $_vid   = intval($_POST['vid'][$i]);
            $_consistence    = trim($_POST['nong_du'][$i]);
            $_eligible_bound = trim($_POST['bound'][$i]);
            $_c_bound        = trim($_POST['c_bound'][$i]);
            $_wz_unit = $_POST['wz_unit'][$i];
            $xuhao= intval($_POST['xuhao']);
            if($_id){
                if($_consistence){
                    $DB->query("UPDATE `bzwz_detail` SET `wz_id`='{$_POST['wz_id']}',`vid`='$_vid',`consistence`='$_consistence',`eligible_bound`='$_eligible_bound',`c_bound`='$_c_bound',`xuhao`='$xuhao' ,`wz_unit` = '$_wz_unit' WHERE `id`=$_id");
                }else{
                    $value_C=$_SESSION['assayvalueC'][$_vid];
                    prompt("$value_C 项目信息不完整,从数据库中删除!");
                    $DB->query("delete from `bzwz_detail` where `id`=$_id");
                }
            }else{
                if($_consistence){
                    $DB->query("INSERT INTO `bzwz_detail` (`wz_id`,`vid`,`consistence`,`eligible_bound`,`c_bound`,`create_date`,`xuhao`,`wz_unit`) VALUES ('{$_POST['wz_id']}',$_vid,'$_consistence','$_eligible_bound','$_c_bound',curdate(),'$xuhao','$_wz_unit')");
                }
            }
        }
        gotourl("bzwz.php?action=修改&wz_id=$_POST[wz_id]&wz_type=$_POST[wz_type]");
    case '查看':
        $_action    = '<a class="blue icon-print bigger-130" href="bzwz.php?action=打印&wz_id='.$_GET['wz_id'].'&wz_type='.$_GET['wz_type'].'" target="_blank">打印</a>';
        $readonly   = 'readonly';
        $sql    = "SELECT * FROM `bzwz` WHERE `fzx_id`='$fzx_id' AND `id`='{$_GET['wz_id']}'";
        $r      = $DB->fetch_one_assoc($sql);
        if(!empty($r['dilution_method_file']) && $r['dilution_method_file'] != '[]'){
            $file_new_name_arr = json_decode($r['dilution_method_file'], true);
            $file_old_name_arr = json_decode($r['dilution_method'], true);
            foreach($file_new_name_arr as $key=>$value){
                        $files.="<a href='./upfile/{$value}' target=_blank;>{$file_old_name_arr[$key]}</a><span class='glyphicon glyphicon-remove red' style='float:right;right:20px;cursor:pointer;' onclick=delete_file(this,'{$value}','{$key}','{$r['id']}');></span></br>";
                    }
        }
        $_unit  = $r['unit'];
        $_dilution_method   = $r['dilution_method'];
        $sql    = "SELECT * FROM `bzwz_detail` WHERE `wz_id`='{$r['id']}' ORDER BY `id`";
        $RD     = $DB->query($sql);
        $n      = $DB->num_rows($RD);
        for($i=1;$rd=$DB->fetch_assoc($RD);$i++){
            $xuhao = $rd['xuhao'];
            $item_list=$_SESSION['assayvalueC'][$rd['vid']];
            $rd['consistence']      = (!$u['bzwz_manage']) ? '-' : $rd['consistence'];
            $rd['eligible_bound']   = (!$u['bzwz_manage']) ? '-' : $rd['eligible_bound'];
            $lines  .= temp('bzwz/bzwz_line');
        }
        $k  = intval(__maxline__-$n);
        $rd = array();
        $item_list  = '&nbsp;';
        if($k>0){
            for($i=0;$i<$k;$i++){
                $lines .= temp('bzwz/bzwz_line');
            }
        }
        disp('bzwz/bzwz');
        break;
    case '打印':$readonly   = 'readonly';
        $sql    = "SELECT * FROM `bzwz` WHERE `fzx_id`='$fzx_id' AND `id`='{$_GET['wz_id']}'";
        $r      = $DB->fetch_one_assoc($sql);
        if(!empty($r['dilution_method_file']) && $r['dilution_method_file'] != '[]'){
            $file_new_name_arr = json_decode($r['dilution_method_file'], true);
            $file_old_name_arr = json_decode($r['dilution_method'], true);
            foreach($file_new_name_arr as $key=>$value){
                        $files.="<a href='./upfile/{$value}' target=_blank;>{$file_old_name_arr[$key]}</a></br>";
                    }
        }
        $_unit  = $r['unit'];
        $_dilution_method   = $r['dilution_method'];
        $sql    = "SELECT * FROM `bzwz_detail` WHERE `wz_id`='{$r['id']}' ORDER BY `id`";
        $RD     = $DB->query($sql);
        $n      = $DB->num_rows($RD);
        for($i=1;$rd=$DB->fetch_assoc($RD);$i++){
            $xuhao = $rd['xuhao'];
            $item_list=$_SESSION['assayvalueC'][$rd['vid']];
            $rd['consistence']      = (!$u['bzwz_manage']) ? '-' : $rd['consistence'];
            $rd['eligible_bound']   = (!$u['bzwz_manage']) ? '-' : $rd['eligible_bound'];
            $lines.='<tr align=center>
                        <td align=left>'.$item_list.'</td>
                        <td>'.$rd['consistence'].'</td>
                        <td>'.$rd['eligible_bound'].'</td>
                        <td>'.$rd['c_bound'].'</td>
                    </tr>';
	
        }
        $k  = intval(__maxline__-$n);
        $rd = array();
        $item_list  = '&nbsp;';
        if($k>0){
            for($i=0;$i<$k;$i++){
                $lines .= temp('bzwz/bzwz_line');
            }
        }
        disp('bzwz/bzwz','head_print');
        break;
    case '删除':
        // $sql="SELECT wz_status FROM `bzwz` WHERE id='$_GET[wz_id]'";
        // $re=$DB->query($sql);
        // $data=$DB->fetch_assoc($re);
        // //wz_status 等于0的时候将其放入回收站中，等于1的时候，就不让他显示了
        // if($data['wz_status']==0){
        //     $sql="UPDATE `bzwz` SET `wz_status`=1 WHERE `id`='$_GET[wz_id]'";
        //     $DB->query($sql);
        //     if($_GET['wz_type']=='标准溶液'){
        //         $tabs = '#tabs-1';
        //     }else{
        //         $tabs = '#tabs-2';
        //     }
        //     gotourl("bzwz_list.php?wz_type=$_GET[wz_type]".$tabs);
        // }else if($data['wz_status']==1){
        //     $sql="UPDATE `bzwz` SET `wz_status`=2 WHERE `id`='$_GET[wz_id]'";
        //     $DB->query($sql);
        //     $sql="SELECT * FROM `bzwz` WHERE `wz_status`=1";
        //     $re=$DB->query($sql);
        //     $num=$DB->num_rows($re);
        //     if($num==0){
        //         echo "<script>alert('回收站已空！');window.colse();</script>";
        //     }else{
        //         gotourl("bzwz_rubbish.php?wz_type=$_GET[wz_type]");
        //     }
        // }
        // gotourl('bzwz_list.php?wz_type='.$_GET['wz_type'].$tabs);
        // break;
        $DB->query("DELETE FROM `bzwz` WHERE `id`='{$_GET['wz_id']}'");
        $DB->query("DELETE FROM `bzwz_detail` WHERE `wz_id`='{$_GET['wz_id']}'");
        $DB->query("DELETE FROM `bzwz_ls` WHERE `wz_id`='{$_GET['wz_id']}'");
        gotourl('bzwz_list.php?wz_type='.$_POST['wz_type'].$tabs);
        break;
    case "入库":
        $_action= '<input '.$action_style.' type="submit" name="action" value="入库提交" />';
        $_user  = '经手人';
        $user_  = $u['userid'];
        $date   = date('Y-m-d');
        $r      = $DB->fetch_one_assoc("SELECT * FROM `bzwz` WHERE `id`='{$_GET['wz_id']}'");
        disp('bzwz/bzwz_in_out');
        break;
    case "入库提交":
    	if(!is_numeric($_GET['数量'])){
    		echo "<script>alert('入库数量格式不正确！请重新输入！');history.go(-1);</script>";
    		die;
    	}
        $DB->query("UPDATE `bzwz` SET `amount`=`amount`+$_GET[数量] WHERE `id`='{$_GET['wz_id']}'");
        $sql    = "SELECT * FROM `bzwz` WHERE `id`='{$_GET['wz_id']}'";
        $r      = $DB->fetch_one_assoc($sql);
        $op_date= trim($_GET['date']) ? trim($_GET['date']):date('Y-m-d');
        $sql    = "INSERT INTO `bzwz_ls` (`wz_id`,`wz_type`,`op_type`,`amount`,`dealer`,`op_man`,`jie_cun`,`op_date`) VALUES ('{$_GET['wz_id']}','{$r['wz_type']}','入库','$_GET[数量]','$_GET[action_user]','$_GET[action_user]',$r[amount],'$op_date')";
        $DB->query($sql);
        echo "<script>history.go(-2);</script>";
        // gotourl('bzwz_list.php?wz_type='.$r['wz_type'].$tabs);
        break;
    case "出库":
        $_action='<input '.$action_style.' type="submit" name="action" value="出库提交" />';
        $_user='领用人';
        $user_  = $u['userid'];
        $date   = date('Y-m-d');
        $r=$DB->fetch_one_assoc("SELECT * FROM `bzwz` WHERE `id`='{$_GET['wz_id']}'");
        disp('bzwz/bzwz_in_out');
        break;
    case "出库提交":
    	get_int($_GET[数量]);
        $op_date= trim($_GET['date']) ? trim($_GET['date']):date('Y-m-d');
    	if($_GET[数量]<1) {$_GET[数量]=0;}
        $DB->query("UPDATE `bzwz` SET `amount`=`amount`-{$_GET['数量']} WHERE `id`='{$_GET['wz_id']}'");
        $r=$DB->fetch_one_assoc("SELECT * FROM `bzwz` WHERE `id`='{$_GET['wz_id']}'");
        $DB->query("INSERT INTO `bzwz_ls` (`wz_id`,`wz_type`,`op_type`,`amount`,`dealer`,`op_man`,`jie_cun`,`op_date`) VALUES ($_GET[wz_id],'$r[wz_type]','出库','$_GET[数量]','$_GET[action_user]','$_GET[action_user]',$r[amount],'$op_date')");
        echo "<script>history.go(-2);</script>";
        // gotourl('bzwz_list.php?wz_type='.$r['wz_type'].$tabs);
        break;
    case "还原":
        $DB->query("UPDATE `bzwz` SET `wz_status` = 0 WHERE id='$_GET[wz_id]'");
        $sql="SELECT * FROM `bzwz` WHERE `wz_status`=1 AND `wz_type`='$_GET[wz_type]'";
        $re=$DB->query($sql);
        $data=$DB->fetch_assoc($re);
        if(empty($data)){
            if($_GET['wz_type']=='标准溶液'){
                $tabs = '#tabs-1';
            }else{
                $tabs = '#tabs-2';
            }
            gotourl("bzwz_list.php?wz_type=$_GET[wz_type]".$tabs);
        }else{
            gotourl("bzwz_rubbish.php?wz_type=$_GET[wz_type]");
        }        
        break;
}
?>
