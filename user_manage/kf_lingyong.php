<?php
include '../temp/config.php';
if($_POST['handle']=='like'){
    $like=$_POST['name'];
    if(!empty($like)){
        $sql="SELECT * FROM `sjqm` WHERE name LIKE '$like%'";
        $res=$DB->query($sql);
        while($data=$DB->fetch_assoc($res)){
            if($data['kucun']==0){
              $line.="<option class='fi' style='color:red;' disabled value='$data[id]'>$data[name]&nbsp;&nbsp;&nbsp;规格:$data[guige]&nbsp;&nbsp;&nbsp;级别:$data[jibie]&nbsp;&nbsp;&nbsp;生产批号:$data[pihao]</option>";
            }else{
                $line.="<option  onmouseover='$(this).css({\"background-color\":\"#A6FFFF\"});' onmouseout='$(this).css({\"background-color\":\"\"});'  class='fi' value='$data[id]'>$data[name]&nbsp;&nbsp;&nbsp;规格:$data[guige]&nbsp;&nbsp;&nbsp;级别:$data[jibie]&nbsp;&nbsp;&nbsp;生产批号:$data[pihao]&nbsp;&nbsp;&nbsp;库存:$data[kucun]</option>";
            }  
        }
        echo $line;
    }else{
        echo 'empty';
    }    
    die;
}else{
    $sql="SELECT * FROM sjqm ORDER BY convert(`name` USING gbk)";
    $res=$DB->query($sql);
    $line='';
    $line.="<option disabled selected>请选择</option>";
    while($data=$DB->fetch_assoc($res)){
       if($data['kucun']==0){
          $line.="<option style='color:red;' disabled value='$data[id]'>$data[name]&nbsp;&nbsp;&nbsp;规格:$data[guige]&nbsp;&nbsp;&nbsp;级别:$data[jibie]&nbsp;&nbsp;&nbsp;生产批号:$data[pihao]</option>";
        }else{
            $line.="<option onmouseover='$(this).css({\"background-color\":\"#A6FFFF\"});' onmouseout='$(this).css({\"background-color\":\"\"});' value='$data[id]'>$data[name]&nbsp;&nbsp;&nbsp;规格:$data[guige]&nbsp;&nbsp;&nbsp;级别:$data[jibie]&nbsp;&nbsp;&nbsp;生产批号:$data[pihao]&nbsp;&nbsp;&nbsp;库存:$data[kucun]</option>";
        }   
    } 
}
                   
    $user=$u['userid'];
disp("kf_lingyong");




