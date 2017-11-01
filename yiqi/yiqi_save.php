<?php
  include "../temp/config.php";
//ajax删除仪器档案
if($_POST['handle'] == 'del_record'){
	$sql = "SELECT * FROM `yiqi` WHERE `id` = '{$_POST['id']}'";
	$data = $DB->fetch_one_assoc($sql);
	$record_new_arr = json_decode($data['yq_record'] , true);
	$record_old_arr = json_decode($data['yq_record_json'] , true);
	unset($record_new_arr[$_POST['key']]);
	unset($record_old_arr[$_POST['key']]);
		$record_file_ = json_encode($record_new_arr , JSON_UNESCAPED_UNICODE);
		$record_json_ = json_encode($record_old_arr , JSON_UNESCAPED_UNICODE);
		$sql = "UPDATE `yiqi` SET `yq_record` = '$record_file_' , `yq_record_json` = '$record_json_' WHERE `id` = '{$_POST['id']}'";
		if($DB->query($sql)){
			echo "ok";
		}else{
			echo "wrong";
		}
	die;
}
//ajax删除检定证书
if($_POST['handle'] == 'del_jdzs'){
	$id = $_POST['id'];
	$sql = "SELECT * FROM `yiqi` WHERE `id` = '$id'";
	$res = $DB->fetch_one_assoc($sql);
	$src = "./files/".$res['yq_jdzs_new'];
	if(unlink($src)){
		$sql = "UPDATE `yiqi` SET `yq_jdzs_new` = '' , `yq_jdzs_old` = '' WHERE `id` = '$id'";
		$DB->query($sql);
		if($DB->affected_rows()){
			echo "ok";
		}else{
			echo 'wrong';
		}
	}
	die;
}
//ajax动态删除图片
if($_POST['handle']=='delete_image'){
	$id = $_POST['id'];
	$sql = "SELECT * FROM `yiqi` WHERE `id` = $id";
	$res = $DB->fetch_one_assoc($sql);
	$src = $res['yq_image'];
	if(unlink($src)){
		$sql = "UPDATE `yiqi` SET `yq_image` = '' WHERE `id` = $id";
		$DB->query($sql);
		if($DB->affected_rows()){
			echo "ok";
		}else{
			echo 'wrong';
		}
	}

	exit;
}
//ajax检定完成
if($_POST['handle']=='jdwc'){
	 $id=$_POST['yid'];
	 $date=date("Y-m-d");
	 $xiaci_str='+'.$_POST['zhouqi'].' month';
	 $xiaci=date("Y-m-d",strtotime($xiaci_str));
     $sql = "UPDATE yiqi set yq_firstjianding='{$xiaci}',yq_jiandingriqi='{$date}' WHERE `id` = $id";
	 $DB->query($sql);
	 echo $xiaci;

	exit;
}
//ajax获得下次检定日期
if($_POST['handle']=='show_xc_jdriqi'){
	$yq_jiandingriqi = $_POST['yq_jiandingriqi'];
	$num_arr = explode('.',$_POST['yq_jdriqi']);
	$month = $num_arr[0];
	$last_year = date("Y" , strtotime("$yq_jiandingriqi +$month month"));
	$last_month = date("m" , strtotime("$yq_jiandingriqi +$month month"));
	$days = cal_days_in_month(CAL_GREGORIAN , $last_month , $last_year);
	$d = $num_arr[1];
	$d = $d/10;
	$day = floor($days * $d);
	$month_end =  date('Y-m-d' ,strtotime("$yq_jiandingriqi  +$month month"));
	$end = date('Y-m-d' , strtotime("$month_end +$day days"));
	echo $end;
	exit;
}
if($_GET['handle']=='delete_img'){
	$sql = "SELECT * FROM `yiqi` WHERE `id` = $_GET[id]";
	$data = $DB->fetch_one_assoc($sql);
	$file = $data['yq_image'];
	if(unlink($file)){
		$sql = "UPDATE `yiqi` SET `yq_image` = '' WHERE `id` = $_GET[id]";
		$DB->query($sql);
		echo '<script>history.go(-1);</script>';
	}

	exit;
}
//导航
$daohang= array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'仪器管理','href'=>"$rooturl/yiqi/hn_yiqimanager.php"),
	array('icon'=>'','html'=>'添加仪器','href'=>"$rooturl/yiqi/yiqi_save.php?yq_type={$_GET['yq_type']}")
);
$zhongwai="<label><input type='radio' name='yq_zhongwai' value='1' checked>国产kl</label>
             <label><input type='radio' name='yq_zhongwai' value='2' >进口</label>";
// print_rr($_POST);die;
$trade_global['daohang']= $daohang;
$fzx_id         = $u['fzx_id'];
 if($_GET[id]){
	 $biaotou='仪器档案表';

	$sql="select * from `yiqi` where id =$_GET[id]";
	$rs = $DB->query($sql);
	while($r = $DB->fetch_assoc($rs)){
		 $yid=$r['yid'];
        $yq_mingcheng=$r['yq_mingcheng'];//yq_mingcheng 设备名称
   	    $yq_sbbianhao=$r['yq_sbbianhao'];//yq_sbbianhao 编号
   	    $yq_xinghao=$r['yq_xinghao'];//yq_xinghao 型号
		$yq_chucangbh=$r['yq_chucangbh'];//yq_chucangbh 出厂编号
		$yq_zzcangjia=$r['yq_zzcangjia'];//yq_zzcangjia 制造厂家
		$yq_gouzhirq=$r['yq_gouzhirq'];//yq_gouzhirq 购置日期
		$yq_baoguanren=$r['yq_baoguanren'];//yq_baoguanren 保管人
		$yq_state=$r['yq_state'];//yq_state 状态
		$yq_type=$r['yq_type'];//yq_type 类别
		$yq_daima=$r['yq_daima'];//yq_daima 国家分类代码
		$yq_fenlei=$r['yq_fenlei'];//yq_fenlei 分类
		$yq_azriqi=$r['yq_azriqi'];//yq_azriqi 安装日期
		$yq_jdriqi=$r['yq_jdriqi'];//yq_jdriqi 检定日期
		$yq_caozuo=$r['yq_caozuo'];//yq_caozuo 操作规程链接
		$yq_sbdidian=$r['yq_sbdidian'];//yq_sbdidian 设备存放地点
		//$yq_jgriqi=$r['yq_jgriqi'];//yq_jgriqi 接管（变更）时间
		$yq_zhunquedu=$r['yq_zhunquedu'];// 准确度等级
	    $yq_celiang=$r['yq_celiang'];// 测量范围
	    $yq_qiyong=$r['yq_qiyong'];// 启用日期
	    $yq_liangxiren=$r['yq_liangxiren'];// 联系人
	    $yq_weixiutel=$r['yq_weixiutel'];// 维修电话
	    //$yq_yunxingriqi=$r['yq_yunxingriqi'];// 运行日期
	    $yq_jiandingriqi=$r['yq_jiandingriqi'];// 近检定确定日期
	    $yq_firstjianding=$r['yq_firstjianding'];// 第一次检定日期
	    $yq_tixingriqi=$r['yq_tixingriqi'];// 提醒日期
	     //兰州新增
	    $yq_ruanjian =  $_POST['yq_ruanjian'];//软件设备名称
        $ruanjian_id =  $_POST['ruanjian_id'];//软件版本号
        $yq_file =  $_POST['yq_file'];//设备档案号
        $yq_jiliang = $_POST['yq_jiliang'];//是否属计量器具
        $yq_jiliangbh = $_POST['yq_jiliangbh'];//计量器具编号
        $yq_zhongwai = $_POST['yq_zhongwai'];//国产/进口
        $yq_suyuan=$_POST['yq_suyuan'];//仪器溯源方式
	    //青岛新增
	    $yq_guanlibm = $r['yq_guanlibm'];
	    $yq_shiyongbm = $r['yq_shiyongbm'];
	    $yq_xianzhi =  $r['yq_xianzhi'];
	    $yq_chandi =  $r['yq_chandi'];
	    $yq_fengcun =  $r['yq_fengcun'];
	    $yq_baofei =  $r['yq_baofei'];
	    $yq_beizhu = $r['yq_beizhu'];
	    $yq_ccdate = $r['yq_ccdate'];
	    $yq_quanshudw = $r['yq_quanshudw'];
	   	$yq_room=$r['yq_room'];
	   	$yq_image=$r['yq_image'];
	   	$yq_guanli=$r['yq_guanli'];
	   	$yq_list_show = $r['yq_list_show'];//仪器配置清单
	   	$yq_yjnx = $r['yq_yjnx'];//仪器预计使用年限
	   	$yq_sbglbh = $r['yq_sbglbh'];//仪器管理编号
	   	$yq_jxjl = $r['yq_jxjl'];//仪器检修记录
	   	$yq_jishu_zb = $r['yq_jishu_zb'];//设备技术指标
	}
 }else{
     //保存仪器的代码
 	//用yq_fenlei 存储档案位置  yq_daima 存储供应商
$biaotou='增加仪器';


        $yid=$_POST['yid'];
        $yq_mingcheng=$_POST['yq_mingcheng'];//yq_mingcheng 设备名称
        $yq_jiage=$_POST['yq_jiage'];//yq_jiage 设备价格
   	    $yq_sbbianhao=$_POST['yq_sbbianhao'];//yq_sbbianhao 编号
   	    $yq_xinghao=$_POST['yq_xinghao'];//yq_xinghao 型号
		$yq_chucangbh=$_POST['yq_chucangbh'];//yq_chucangbh 出厂编号
		$yq_zzcangjia=$_POST['yq_zzcangjia'];//yq_zzcangjia 制造厂家
		$yq_gouzhirq=$_POST['yq_gouzhirq'];//yq_gouzhirq 购置日期
		$yq_baoguanren=$_POST['yq_baoguanren'];//yq_baoguanren 保管人
		$yq_state=$_POST['yq_state'];//yq_state 状态
    $yq_state_arr=array('启用','准用','封存','报废');
    $yq_state_content=select_option($yq_state_arr);
		$yq_type=$_POST['yq_type'];//yq_type 类别
		$yq_daima=$_POST['yq_daima'];//yq_daima 国家分类代码
		$yq_fenlei=$_POST['yq_fenlei'];//yq_fenlei 分类
		$yq_azriqi=$_POST['yq_azriqi'];//yq_azriqi 安装日期
		$yq_jdriqi=$_POST['yq_jdriqi'];//yq_jdriqi 检定日期
		$yq_caozuo=$_POST['yq_caozuo'];//yq_caozuo 操作规程链接
		$yq_sbdidian=$_POST['yq_sbdidian'];//yq_sbdidian 设备存放地点
		//$yq_jgriqi=$_POST['yq_jgriqi'];//yq_jgriqi 接管（变更）时间
		$yq_zhunquedu=$_POST['yq_zhunquedu'];// 准确度等级
	    $yq_celiang=$_POST['yq_celiang'];// 测量范围
	    $yq_qiyong=$_POST['yq_qiyong'];// 启用日期
	    $yq_liangxiren=$_POST['yq_liangxiren'];// 联系人
	    $yq_weixiutel=$_POST['yq_weixiutel'];// 维修电话
	    //$yq_yunxingriqi=$_POST['yq_yunxingriqi'];// 运行日期
	    $yq_jiandingriqi=$_POST['yq_jiandingriqi'];// 近检定确定日期
	    $yq_firstjianding=$_POST['yq_firstjianding'];// 第一次检定日期
	    $yq_tixingriqi=$_POST['yq_tixingriqi'];// 提醒日期
	    $yq_suyuan=$_POST['yq_suyuan'];//仪器溯源方式
      $yq_suyuan_arr=array('检定','校准','其他');
      $yq_suyuan_content=select_option($yq_suyuan_arr);
	    //兰州新增
	    $yq_ruanjian =  $_POST['yq_ruanjian'];//软件设备名称
        $ruanjian_id =  $_POST['ruanjian_id'];//软件版本号
        $yq_file =  $_POST['yq_file'];//设备档案号
        $yq_jiliang = $_POST['yq_jiliang'];//是否属计量器具
        $yq_jiliang_arr=array('计量器具','非计量器具');
        $yq_jiliang_content=select_option($yq_jiliang_arr);
        $yq_jiliangbh = $_POST['yq_jiliangbh'];//计量器具编号
        $yq_zhongwai = $_POST['yq_zhongwai'];//国产/进口
	    //青岛新增
	    $yq_guanlibm = $_POST['yq_guanlibm'];
	    $yq_shiyongbm = $_POST['yq_shiyongbm'];
	    $yq_xianzhi =  $_POST['yq_xianzhi'];
	    $yq_chandi =  $_POST['yq_chandi'];
	    $yq_fengcun =  $_POST['yq_fengcun'];
	    $yq_baofei =  $_POST['yq_baofei'];
	    $yq_beizhu = $_POST['yq_beizhu'];
	    $yq_ccdate = $_POST['yq_ccdate'];
	    $yq_quanshudw = $_POST['yq_quanshudw'];
	    $yq_room=$_POST['yq_room'];
	    $yq_jiandingfeiyong=$_POST['yq_jiandingfeiyong'];
	    $yq_jiandingdanwei=$_POST['yq_jiandingdanwei'];
		$yq_zichan=$_POST['yq_zichan'];
		$yq_guanli=$_POST['yq_guanli'];
		$yq_list_show = $_POST['yq_list_show'];
		$yq_yjnx = $_POST['yq_yjnx'];//仪器预计使用年限
	   	$yq_sbglbh = $_POST['yq_sbglbh'];//仪器管理编号
	   	$yq_jxjl = $_POST['yq_jxjl'];//仪器检修记录
	   	$yq_jishu_zb = $_POST['yq_jishu_zb'];//设备技术指标

	    //检测项目
	    if($r['yq_xm']){
	    	$xm_arr = explode(',',$r['yq_xm']);
	    	foreach($xm_arr as $kk=>$vv){
	    		if($kk=='0'){
	    			$yq_xm .= $all_xm_arr[$vv];
	    		}else{
	    			$yq_xm .= '、'.$all_xm_arr[$vv];
	    		}
	    	}
	    }else{
	    	 $yq_xm = '';
	    }

	    if($_GET['yq_type']){
			$yq_type = $_GET['yq_type'];
	    }
		if(!empty($_FILES['yq_image']['name']) || !empty($_FILES['yq_record']['name']) || !empty($_FILES['yq_jdzs']['name']) ){
		//处理
			if(!empty($_FILES['yq_image']['name'])){
				if(file_exists($_FILES['yq_image']['tmp_name'])){//判断上传的文件是否存在
					$file = './files/'.$_FILES['yq_image']['name'].'$&'.time();
					if(move_uploaded_file($_FILES['yq_image']['tmp_name'],$file)){//把上传的文件重命名并移到系统upfile目录下
					   $yq_image=$file;
					   $yq_image_sql = "  yq_image = '$yq_image' , ";
					}
				}
		    }else{
		    	$yq_image_sql = '';
		    }
		    if(!empty($_FILES['yq_jdzs']['name'])){
				if(file_exists($_FILES['yq_jdzs']['tmp_name'])){//判断上传的文件是否存在
					$num = strrpos($_FILES['yq_jdzs']['name'], '.');
					$newname = time().substr($_FILES['yq_jdzs']['name'], $num );
					$file = './files/'.$newname;
					if(move_uploaded_file($_FILES['yq_jdzs']['tmp_name'],$file)){//把上传的文件重命名并移到系统upfile目录下
					   $yq_jdzs=$file;
					   $yq_jdzs_sql = "  yq_jdzs_new = '$newname' , `yq_jdzs_old` = '{$_FILES['yq_jdzs']['name']}' ,";
					}
				}
		    }else{
		    	$yq_jdzs_sql = '';
		    }
		    if(!empty($_FILES['yq_record']['name'][0])){
		    	if($_POST['yid']){
		    		$sql = "SELECT * FROM `yiqi` WHERE `id` = '{$_POST['yid']}'";
		    		$data = $DB->query($sql);
		    		if(empty($data['yq_record'])){
		    			foreach($_FILES['yq_record']['tmp_name'] as $key => $value){
			    		$xxx	= explode('.',$_FILES['yq_record']['name'][$key]);
						$cnt	= count($xxx);
			    		$newname= date(ymdhis).$u['fzx_id']."_{$key}.".$xxx[$cnt-1];
			    		$path	= "./record/".$newname;
			    		if(move_uploaded_file($value, $path)){
			    			$name_new_arr[] = $newname;
			    			$name_old_arr[] = $_FILES['yq_record']['name'][$key];
			    		}
			    	}
			    	$sql_set	= array();
					$name_new_json = json_encode($name_new_arr , JSON_UNESCAPED_UNICODE);
					$name_old_json = json_encode($name_old_arr , JSON_UNESCAPED_UNICODE);
					$a	= " `yq_record`='$name_new_json'";
					$b	= " , `yq_record_json`='{$name_old_json}'";
			    	$save_file_sql = $a.$b;
		    		}else{
		    			//已经有了文件后
		    			foreach($_FILES['yq_record']['tmp_name'] as $key => $value){
				    		$xxx	= explode('.',$_FILES['yq_record']['name'][$key]);
							$cnt	= count($xxx);
				    		$newname= date(ymdhis).$u['fzx_id']."_{$key}.".$xxx[$cnt-1];
				    		$path	= "./record/".$newname;
				    		if(move_uploaded_file($value, $path)){
				    			$name_new_arr[] = $newname;
				    			$name_old_arr[] = $_FILES['yq_record']['name'][$key];
				    		}
				    	}
				    	$old_arr = json_decode($data['yq_record'] , true);
				    	$new_arr = json_decode($data['yq_record_json'] , true);
				    	$name_new_arr = array_merge($name_old_arr , $old_arr);
				    	$name_old_arr = array_merge($name_old_arr , $new_arr);
				    	$sql_set	= array();
						$name_new_json = json_encode($name_new_arr , JSON_UNESCAPED_UNICODE);
						$name_old_json = json_encode($name_old_arr , JSON_UNESCAPED_UNICODE);
						$a	= "`yq_record`='$name_new_json'";
						$b	= " , `yq_record_json`='{$name_old_json}'";
				    	$save_file_sql = $a.$b;
		    		}
		    	}else{
		    		foreach($_FILES['yq_record']['tmp_name'] as $key => $value){
			    		$xxx	= explode('.',$_FILES['yq_record']['name'][$key]);
						$cnt	= count($xxx);
			    		$newname= date(ymdhis).$u['fzx_id']."_{$key}.".$xxx[$cnt-1];
			    		$path	= "./record/".$newname;
			    		if(move_uploaded_file($value, $path)){
			    			$name_new_arr[] = $newname;
			    			$name_old_arr[] = $_FILES['yq_record']['name'][$key];
			    		}
			    	}
			    	$sql_set	= array();
					$name_new_json = json_encode($name_new_arr , JSON_UNESCAPED_UNICODE);
					$name_old_json = json_encode($name_old_arr , JSON_UNESCAPED_UNICODE);
					$a	= ",`yq_record`='$name_new_json'";
					$b	= " , `yq_record_json`='{$name_old_json}'";
			    	$save_file_sql = $a.$b;
		    	}
		    }else{
		    	$save_file_sql = '';
		    }

			 if($yid!=''){
			 	$sql = "SELECT * FROM `yiqi` WHERE id = $yid";
			 	$data = $DB->fetch_one_assoc($sql);
			 	if(!empty($data['yq_image'])){
			 		unlink($data['yq_image']);
			 	}

				$DB->query("UPDATE  `yiqi` set yq_mingcheng='$yq_mingcheng',
				yq_jiage='$yq_jiage',
				yq_sbbianhao='$yq_sbbianhao',yq_xinghao='$yq_xinghao',yq_chucangbh='$yq_chucangbh',
				yq_zzcangjia='$yq_zzcangjia',yq_gouzhirq='$yq_gouzhirq',yq_baoguanren='$yq_baoguanren',
				yq_state='$yq_state',yq_type='$yq_type',yq_daima='$yq_daima',yq_fenlei='$yq_fenlei',
				yq_azriqi='$yq_azriqi',yq_jdriqi='$yq_jdriqi',yq_caozuo='$yq_caozuo',yq_sbdidian='$yq_sbdidian',
				yq_zhunquedu='$yq_zhunquedu',yq_celiang='$yq_celiang',yq_qiyong='$yq_qiyong',
			    yq_liangxiren='$yq_liangxiren',yq_weixiutel='$yq_weixiutel',
			    yq_jiandingriqi='$yq_jiandingriqi',yq_firstjianding='$yq_firstjianding',yq_tixingriqi='$yq_tixingriqi',yq_ruanjian='$yq_ruanjian',yq_suyuan='$yq_suyuan',ruanjian_id='$ruanjian_id',yq_file='$yq_file',yq_jiliang='$yq_jiliang',yq_jiliangbh='$yq_jiliangbh',
			    yq_guanlibm='$yq_guanlibm',yq_shiyongbm='$yq_shiyongbm',yq_xianzhi='$yq_xianzhi',yq_chandi='$yq_chandi',yq_zhongwai='$yq_zhongwai',yq_fengcun='$yq_fengcun',yq_baofei='$yq_baofei',yq_ccdate='$yq_ccdate',yq_quanshudw='$yq_quanshudw',yq_beizhu='$yq_beizhu',yq_room='$yq_room', $yq_image_sql yq_jiandingfeiyong='$yq_jiandingfeiyong',yq_jiandingdanwei='$yq_jiandingdanwei',yq_zichan='$yq_zichan',yq_guanli='$yq_guanli' , `yq_yjnx` = '$yq_yjnx' , `yq_sbglbh` = '$yq_sbglbh' , `yq_jxjl` = '$yq_jxjl',`yq_jishu_zb` = '$yq_jishu_zb' , $yq_jdzs_sql `yq_list_show` = '$yq_list_show'  $save_file_sql
				where id=$yid");
				gotourl("$rooturl/yiqi/yiqi_update.php?action=修改&yid=$yid&page=$_POST[page]");
			}
		}else{//如果没有文件上传，那么就不需要对image进行处理
			if($yid!=''){
				$DB->query("UPDATE  `yiqi` set yq_mingcheng='$yq_mingcheng',
				yq_jiage='$yq_jiage',
				yq_sbbianhao='$yq_sbbianhao',yq_xinghao='$yq_xinghao',yq_chucangbh='$yq_chucangbh',
				yq_zzcangjia='$yq_zzcangjia',yq_gouzhirq='$yq_gouzhirq',yq_baoguanren='$yq_baoguanren',
				yq_state='$yq_state',yq_type='$yq_type',yq_daima='$yq_daima',yq_fenlei='$yq_fenlei',
				yq_azriqi='$yq_azriqi',yq_jdriqi='$yq_jdriqi',yq_caozuo='$yq_caozuo',yq_sbdidian='$yq_sbdidian',
				yq_zhunquedu='$yq_zhunquedu',yq_celiang='$yq_celiang',yq_qiyong='$yq_qiyong',
			    yq_liangxiren='$yq_liangxiren',yq_weixiutel='$yq_weixiutel',
			    yq_jiandingriqi='$yq_jiandingriqi',yq_firstjianding='$yq_firstjianding',yq_tixingriqi='$yq_tixingriqi',yq_ruanjian='$yq_ruanjian',yq_suyuan='$yq_suyuan',ruanjian_id='$ruanjian_id',yq_file='$yq_file',yq_jiliang='$yq_jiliang',yq_jiliangbh='$yq_jiliangbh',yq_guanlibm='$yq_guanlibm',yq_shiyongbm='$yq_shiyongbm',yq_xianzhi='$yq_xianzhi',yq_chandi='$yq_chandi',yq_zhongwai='$yq_zhongwai',yq_fengcun='$yq_fengcun',yq_baofei='$yq_baofei',yq_ccdate='$yq_ccdate',yq_quanshudw='$yq_quanshudw',yq_beizhu='$yq_beizhu',yq_room='$yq_room',yq_jiandingfeiyong='$yq_jiandingfeiyong',yq_jiandingdanwei='$yq_jiandingdanwei', $yq_jdzs_sql yq_zichan='$yq_zichan',yq_guanli='$yq_guanli' , `yq_list_show` = '$yq_list_show',`yq_yjnx` = '$yq_yjnx' , `yq_sbglbh` = '$yq_sbglbh',`yq_jishu_zb` = '$yq_jishu_zb' , $yq_image_sql `yq_jxjl` = '$yq_jxjl'  where id=$yid");
				gotourl("$rooturl/yiqi/yiqi_update.php?action=修改&yid=$yid&page=$_POST[page]");
			}
		}
		if($yq_mingcheng!='' ){
			$max_px = "SELECT max(`px_id`) as m_p from `yiqi` WHERE `fzx_id`='{$fzx_id}' limit 1";
			$row = $DB->fetch_one_assoc($max_px);
			$max = $row['m_p'] + 1;
			$DB->query("INSERT into `yiqi` set yq_mingcheng='$yq_mingcheng',px_id='$max',fzx_id='{$fzx_id}',
			yq_jiage='$yq_jiage',
			yq_sbbianhao='$yq_sbbianhao',yq_xinghao='$yq_xinghao',yq_chucangbh='$yq_chucangbh',
			yq_zzcangjia='$yq_zzcangjia',
			yq_gouzhirq='$yq_gouzhirq',
			yq_baoguanren='$yq_baoguanren',
			yq_state='$yq_state',
			yq_type='$yq_type',
			yq_daima='$yq_daima',
			yq_fenlei='$yq_fenlei',
			yq_azriqi='$yq_azriqi',
			yq_jdriqi='$yq_jdriqi',
			yq_caozuo='$yq_caozuo',
			yq_sbdidian='$yq_sbdidian',
			yq_zhunquedu='$yq_zhunquedu' ,
		    yq_celiang='$yq_celiang' ,
		    yq_qiyong='$yq_qiyong' ,
		    yq_liangxiren='$yq_liangxiren' ,
		    yq_zhongwai='$yq_zhongwai',
		    yq_ruanjian='$yq_ruanjian',
		    yq_suyuan='$yq_suyuan',
		    ruanjian_id='$ruanjian_id',
		    yq_file='$yq_file',
		    yq_jiliang='$yq_jiliang',
		    yq_jiliangbh='$yq_jiliangbh',
		    yq_weixiutel='$yq_weixiutel' ,
		    yq_jiandingriqi='$yq_jiandingriqi' ,
		    yq_firstjianding	='$yq_firstjianding' ,
		    yq_tixingriqi='$yq_tixingriqi',
		    $yq_image_sql $yq_jdzs_sql
		    yq_guanlibm='$yq_guanlibm',yq_shiyongbm='$yq_shiyongbm',yq_xianzhi='$yq_xianzhi',yq_chandi='$yq_chandi',yq_fengcun='$yq_fengcun',yq_baofei='$yq_baofei',yq_ccdate='$yq_ccdate',yq_quanshudw='$yq_quanshudw',yq_beizhu='$yq_beizhu',yq_image='$yq_image',yq_room='$yq_room',yq_jiandingfeiyong='$yq_jiandingfeiyong',yq_jiandingdanwei='$yq_jiandingdanwei',yq_zichan='$yq_zichan',yq_guanli='$yq_guanli' , `yq_yjnx` = '$yq_yjnx' , `yq_sbglbh` = '$yq_sbglbh' , `yq_jxjl` = '$yq_jxjl',`yq_jishu_zb` = '$yq_jishu_zb' ,`yq_list_show` = '$yq_list_show'
		    $save_file_sql
			 ");
		 	gotourl("$rooturl/yiqi/hn_yiqimanager.php?page=$_POST[page]");
		}
}
	$submit='<input class="btn btn-xs btn-primary" type="submit" value="保存"> <input type="button" class="btn btn-xs btn-primary" onclick="history.go(-1)" value="取消"><input type="hidden" name="page" value="$page">';
	$sql="SELECT distinct(yq_type) FROM `yiqi`";
	$re=$DB->query($sql);
	$type='<option >请选择仪器类型</option>';
	while($data=$DB->fetch_assoc($re)){
		$type.="<option >$data[yq_type]</option>";
	}
	$yq_zichan='<option selected>请选择</option>
		        <option value="固定资产">固定资产</option>
		        <option value="低值易耗">低值易耗</option>';


//ajax计算下次检定日期
if($_POST['next_time']){
	$zhouqi=$_POST['zhouqi'];
	$time	= $_POST['date'];
	$n=date("Y-m-d",strtotime("$time +$zhouqi months"));
	if(!empty($zhouqi)){
		echo $n;
	}else{
		echo 'less';
	}
	exit;
}
   disp('yiqi_save.html');
   //生成下拉列表
function select_option($arr){
  foreach($arr as $k=>$v){
    $select_content.="<option value='$v'>$v</opton>";
  }
  return $select_content;
}
   ?>
