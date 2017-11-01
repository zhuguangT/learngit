<?php
     //详细仪器的代码
   include "../temp/config.php";
     $biaotuo='仪器详细信息';
      $yid=$_GET['yid'];
       if($yid!=''){
		$R=$DB->query("select * from `yiqi` where id=$yid ");
		$r=$DB->fetch_assoc($R);
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
	   }
      disp('yiqi_detail.html');
?>
