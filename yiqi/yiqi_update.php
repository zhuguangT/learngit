<?php

     //保存仪器的代码
      include "../temp/config.php";
//导航
if(empty($_GET['from'])){
	$daohang= array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'仪器管理','href'=>"$rooturl/yiqi/hn_yiqimanager.php"),
		array('icon'=>'','html'=>'修改仪器','href'=>"$rooturl/yiqi/yiqi_update.php?action={$_GET['action']}&yid={$_GET['yid']}&page={$_GET['page']}")
	);
}else{
	$daohang= array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'实验室仪器一览','href'=>"$rooturl/yiqi/hn_yiqimanager_admin.php"),
		array('icon'=>'','html'=>'仪器详细','href'=>"$rooturl/yiqi/yiqi_update.php?action={$_GET['action']}&yid={$_GET['yid']}&page={$_GET['page']}&from=yilan")
	);
}

//获取项目名称
$xm_sql="SELECT * FROM assay_value WHERE 1";
$xm_query=$DB->query($xm_sql);
while($xm_rs=$DB->fetch_assoc($xm_query)){
	$all_xm_arr[$xm_rs[id]]=$xm_rs['value_C'];
}

$nowdate = date("Y-m-d");
$trade_global['daohang']= $daohang;
      $yid=$_GET['yid'];
      if(empty($_GET['from'])){
      	$biaotou='修改仪器';
      	$submit='<input class="btn btn-xs btn-primary" type="submit" value="保存"> <input class="btn btn-xs btn-primary" type="button" onclick="jdwc()" value="检定完成"> <input type="button" class="btn btn-xs btn-primary" onclick="history.go(-1);" value="取消"><input type="hidden" name="page" value="'.$page.'">';
  	  }else{
  	  	$biaotou='仪器信息';
  	  	$submit='<input class="btn btn-xs btn-primary" type="button" onclick="go_back();" value="返回">';
  	  }
      	   if($yid!=''){
	   $R=$DB->query("SELECT *,DATEDIFF(curdate(),`yq_qiyong`) AS DiffDate FROM `yiqi` where id=$yid ");
		$r=$DB->fetch_assoc($R);
		$id = $r['id'];
		$yq_mingcheng=$r['yq_mingcheng'];//yq_mingcheng 设备名称
		$yq_jiage=$r['yq_jiage'];//yq_jiage 设备原值
   	    $yq_sbbianhao=$r['yq_sbbianhao'];//yq_sbbianhao 资产编号
   	    $yq_xinghao=$r['yq_xinghao'];//yq_xinghao 型号
		$yq_chucangbh=$r['yq_chucangbh'];//yq_chucangbh 出厂编号
		$yq_zzcangjia=$r['yq_zzcangjia'];//yq_zzcangjia 制造厂家
		$yq_gouzhirq=$r['yq_gouzhirq'];//yq_gouzhirq 购置日期
		$yq_baoguanren=$r['yq_baoguanren'];//yq_baoguanren 保管人
		$yq_state=$r['yq_state'];//yq_state 状态
    $yq_state_arr=array('启用','准用','封存','报废');
    $yq_state_content=select_selected($yq_state,$yq_state_arr);
		$yq_type=$r['yq_type'];//yq_type 类别
		$yq_daima=$r['yq_daima'];//yq_daima 国家分类代码
		$yq_fenlei=$r['yq_fenlei'];//yq_fenlei 分类
		$yq_azriqi=$r['yq_azriqi'];//yq_azriqi 安装日期
		$yq_jdriqi=$r['yq_jdriqi'];//yq_jdriqi 检定日期
		$yq_caozuo=$r['yq_caozuo'];//yq_caozuo 操作规程链接
		$yq_sbdidian=$r['yq_sbdidian'];//yq_sbdidian 设备存放地点
		$yq_jgriqi=$r['yq_jgriqi'];//yq_jgriqi 接管（变更）时间

	    $yq_zhunquedu=$r['yq_zhunquedu'];// 准确度等级
	    $yq_celiang=$r['yq_celiang'];// 测量范围
	    $yq_qiyong=$r['yq_qiyong'];// 启用日期
	    $yq_liangxiren=$r['yq_liangxiren'];// 联系人
	    $yq_weixiutel=$r['yq_weixiutel'];// 维修电话
	    $yq_yunxingriqi=$r['yq_yunxingriqi'];// 运行日期
	    $yq_jiandingriqi=$r['yq_jiandingriqi'];// 近检定确定日期
	    $yq_firstjianding=$r['yq_firstjianding'];// 第一次检定日期
	    $yq_tixingriqi=$r['yq_tixingriqi'];// 提醒日期
      $yq_suyuan=$r['yq_suyuan'];//仪器溯源方式
      $yq_suyuan_arr=array('检定','校准','其他');
      $yq_suyuan_content=select_selected($yq_suyuan,$yq_suyuan_arr);
	    //青岛新增
	    $yq_guanlibm = $r['yq_guanlibm'];
	    $yq_shiyongbm = $r['yq_shiyongbm'];
	    $yq_xianzhi =  $r['yq_xianzhi'];
	    $yq_chandi =  $r['yq_chandi'];
	    $yq_fengcun =  $r['yq_fengcun'];
	    $yq_baofei =  $r['yq_baofei'];
	    $yq_beizhu = $r['yq_beizhu'];
	    $yq_ccdate = $r['yq_ccdate'];
        //兰州新增
        $yq_ruanjian =  $r['yq_ruanjian'];//软件设备名称
        $ruanjian_id =  $r['ruanjian_id'];//软件版本号
        $yq_file =  $r['yq_file'];//设备档案号
        $yq_jiliang = $r['yq_jiliang'];//是否属计量器具
        $yq_jiliang_arr=array('计量器具','非计量器具');
        $yq_jiliang_content=select_selected($yq_jiliang,$yq_jiliang_arr);
        $yq_jiliangbh = $r['yq_jiliangbh'];//计量器具编号
	    $yq_quanshudw = $r['yq_quanshudw'];
	    $yq_room=$r['yq_room'];
	    $yq_image_src = substr($r['yq_image'],8);
	    $yq_image_arr=explode('$&',substr($r['yq_image'],8));
	    $yq_image = $yq_image_arr[0];
        $zhongwai='';
        $yq_zhongwai = $r['yq_zhongwai'];//国产/进口分类
        if ($yq_zhongwai==1) {
             $zhongwai .="<label><input type='radio' name='yq_zhongwai' value='1' checked>国产</label>";
             $zhongwai .="<label><input type='radio' name='yq_zhongwai' value='2' >进口</label>";
        }else{
             $zhongwai .="<label><input type='radio' name='yq_zhongwai' value='1' >国产</label>";
             $zhongwai .="<label><input type='radio' name='yq_zhongwai' value='2' checked>进口</label>";
        }
	    if(!empty($r['yq_jdzs_new'])){
	    	$yq_jdzs = "<a href='./files/$r[yq_jdzs_new]'; target='__blank' >$r[yq_jdzs_old]</a>&nbsp;&nbsp;&nbsp;<a class=\"red icon-remove bigger-140\" onclick='delete_jdzs(this,$id);' style='cursor:pointer;'></a>";
	    }
	    $yq_jiandingfeiyong=$r['yq_jiandingfeiyong'];
	    $yq_jiandingdanwei=$r['yq_jiandingdanwei'];
	    $yq_guanli=$r['yq_guanli'];
	    $yq_list_show = $r['yq_list_show'];
        $yq_jishu_zb = $r['yq_jishu_zb'];
	    $yq_yjnx = $r['yq_yjnx'];//仪器预计使用年限
	   	$yq_sbglbh = $r['yq_sbglbh'];//仪器管理编号
	   	$yq_jxjl = $r['yq_jxjl'];//仪器检修记录
	    if(!empty($r['yq_record'])){
	    	$yq_record_old_arr = json_decode($r['yq_record'] , true);
 			$record_name_arr = json_decode($r['yq_record_json'] , true);
 			foreach($yq_record_old_arr as $key => $value){
 			$record_file .= "<a href='./record/$value'>$record_name_arr[$key]</a>&nbsp;&nbsp;<a class='red icon-remove bigger-140' onclick='del_record(this,$r[id],$key);' style='cursor:pointer;'></a></br>";
 			}
	    }
	    if(!empty($r['yq_zichan'])){
	    	$yq_zichan='<option selected value="'.$r['yq_zichan'].'">'.$r['yq_zichan'].'</option>
	    			    <option value="固定资产">固定资产</option>
       					<option value="低值易耗">低值易耗</option>';
	    }else{
	    	$yq_zichan='<option selected>请选择</option>
		        <option value="固定资产">固定资产</option>
		        <option value="低值易耗">低值易耗</option>';
	    }
	    	//使用年限
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
	    //检测项目
	    //获取该仪器的监测项目
	    $yq_xm = '';
		$yqsql = $DB->query("select xmid from xmfa where yiqi='$yid' group by xmid");
		while($xmre = $DB->fetch_assoc($yqsql)){
			if($xmre['xmid']){
	    		$yq_xm .= '、'.$all_xm_arr[$xmre['xmid']];
	    	}
		}
	    $yq_xm = substr($yq_xm,3);
	    }
   		//从第几页过来的
   		$page = $_GET['page'];
   	$sql="SELECT distinct(yq_type) FROM `yiqi`";
	$re=$DB->query($sql);
	$type='';
	$type="<option selected>$yq_type</option>";
	while($data=$DB->fetch_assoc($re)){
		$type.="<option >$data[yq_type]</option>";
	}
	if(!empty($yq_image)){
		$delete_png ="&nbsp;&nbsp;&nbsp;<a class=\"red icon-remove bigger-140\" onclick='delete_image(this,$id);' style='cursor:pointer;'></a>";     //href=\"javascript:if(confirm('你真的要删除么?\n一经删除,无法恢复!'))location.href='yiqi_save.php?handle=delete_img&id=$id'\"
	}

     disp('yiqi_save.html');
     //设置下拉框默认选中函数，因为模板不能映射if 故采用这种方法 参数1为下拉框选中的值 参数2为下拉框的数组
function select_selected($value,$arr){
  foreach($arr as $k=>$v){
    $arr_select='';
    if($value==$v){
        $arr_select="selected='selected'";
    }
    $select_selected_content.="<option value='$v' $arr_select>$v</opton>";
  }
  return $select_selected_content;
}

   ?>
