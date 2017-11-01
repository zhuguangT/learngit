<?php
/*
	功能：增加不同报告类型的 moren 基本数据
	作者：高龙
	时间：2016/5/25
	描述：同时给总中心和其他分中心增加 不同报告类型的 moren 基本数据
*/
	include '../temp/config.php';//包含配置文件

	//导航
	$trade_global['daohang'][] = array('icon'=>'','html'=>'增加报告统计列表','href'=>"./data_statistics/zjbgtj_lb.php");
	$_SESSION['daohang']['zjbgtj_lb']	= $trade_global['daohang'];

	$zhouqi_lx = array(//报告周期类型数组
		'年报' => "`year`='moren'",
		'月报' => "`month`='moren'",
		'周报' => "`week`='moren'",
		'日报' => "`day`='moren'",
	);
	if(!empty($_POST['name_str']) && !empty($_POST['baogao_name']) && !empty($_POST['count_type'])){
		$name_str = $_POST['name_str'];
		$baogao_name = $_POST['baogao_name'];
		$count_type = $_POST['count_type'];
		$pxzh = $DB->fetch_one_assoc("SELECT max(`px`) as zuida FROM `baogao_list` WHERE 1");//获取排序的最大值
		if(!empty($pxzh['zuida'])){//设置排序的值
			$px = $pxzh['zuida']+1;
		}else{
			$px = 1;
		}
		$moren = $zhouqi_lx[$_POST['count_type']];//确定给那个周期的报告添加 moren 值
		$gx_set = '{"show_note":"yes","result_mb_name":"jibenzhan","result_xmb_name":"tjbg_month_xbg","mbjbls":"10","xmbjbls":"1","mbtd":["ccbh","dmmc","bh","y","r","s","f","sw","ll","qw"],"xmbtd":["ccbh"],"canshu":{"jichu":["title","bumen","tb_date"]}}';//设置报告个性设置的默认值

		$fzxid = $DB->query("SELECT `id` FROM `hub_info` WHERE 1");//查询总中心和所有分中心的id号
		while($fzxid_arr = $DB->fetch_assoc($fzxid)){//为各个中心插入数据
			$result = $DB->query("INSERT INTO `baogao_list` SET `fzx_id`={$fzxid_arr['id']} , `px`={$px} , `name_str`='{$name_str}' , `baogao_name`='{$baogao_name}' ,".$moren.", `gx_set`='{$gx_set}' ");
		}
		if($result){//用于判定是否添加成功
			$text = '添加成功';
			$url  = "./zjbgtj_lb.php";
			error_show($text,$url);
		}else{
			$text = '添加失败';
			$url  = "./zjbgtj_lb.php";
			error_show($text,$url);
		}

	}else{
		$jgj = $DB->query("SELECT `name_str` FROM `baogao_list` group by `name_str`");
		if($DB->num_rows($jgj)){
			$namestrs="<select id='namestr'>";
			while ($mark = $DB->fetch_assoc($jgj)) {
				$namestrs.="<option value='".$mark['name_str']."'>".$mark['name_str']."</option>";
			}
			$namestrs.="</select>&nbsp;&nbsp;";
		}
		$namestr = "不同报告的标示：<input type='text' id='name_str' name='name_str' />&nbsp;&nbsp;";
		$baogaoname =  "报告名称：<input type='text' name='baogao_name' />&nbsp;&nbsp;";
		$counttype = "报告周期类型：
			<select name='count_type'>
				<option value='年报'>年报</option>
				<option value='月报'>月报</option>
				<option value='周报'>周报</option>
				<option value='日报'>日报</option>
			</select>
		";
		$add = "<input type='submit' value='添加' />";
		$title = '增加报告统计列表';
		disp("any_data/zjbgtj_lb");
	}

?>