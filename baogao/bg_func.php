<?php
/**
 * 功能：报告相关函数
 * 作者: zhengsen
 * 日期: 2015-05-07
 * 描述:
*/

/**********************START****************************/

/**
 *功能：判断该站点的水质类别
 *参数：water_type:水样类型 ，vid:项目id， result:结果值， intent:目标水质
 *还需完善
 **/
function water_pingjia($water_type,$vid,$result,$intent=''){
	global $DB;
	//获取用哪个水样类型的标准
	$jcbz_bh	= $DB->fetch_one_assoc("SELECT group_concat(case when `module_value3`='1' then `id` end) as moren,group_concat(id) as jcbz_bh_id,group_concat(`module_value1`) as jcbz_name,group_concat(`module_value4`) as jcbz_fuhao FROM `n_set` WHERE `module_name`='jcbz_bh' AND `module_value2`='{$water_type}' ");
	//如果站点里选择的是小类这里要获取大类的标准信息
	if(empty($jcbz_bh['jcbz_bh_id'])){
		$water_type	= get_water_type_max($water_type,$rsSite['fzx_id']);
		$jcbz_bh	= $DB->fetch_one_assoc("SELECT group_concat(case when `module_value3`='1' then `id` end) as moren,group_concat(id) as jcbz_bh_id,group_concat(`module_value1`) as jcbz_name,group_concat(`module_value4`) as jcbz_fuhao FROM `n_set` WHERE `module_name`='jcbz_bh' AND `module_value2`='{$water_type}' ");

	}
	$xz_arr	= array();
	$jcbz_name_arr	= array_combine(explode(',',$jcbz_bh['jcbz_bh_id']),explode(',',$jcbz_bh['jcbz_name']));//不同标准的名称
	$jcbz_fuhao_arr	= array_combine(explode(',',$jcbz_bh['jcbz_bh_id']),explode(',',$jcbz_bh['jcbz_fuhao']));//不同标准的符号（如:I类）
	if(!empty($jcbz_bh)){
		$jcbz_arr	= array();
		$lei		= 0;
		$sql_jcbz	= $DB->query("SELECT * FROM `assay_jcbz` WHERE `jcbz_bh_id` in ({$jcbz_bh['jcbz_bh_id']}) AND `vid`='{$vid}' ORDER BY jcbz_bh_id");
		while($rs_jcbz = $DB->fetch_assoc($sql_jcbz)){
			if(stristr($jcbz_name_arr[$rs_jcbz['jcbz_bh_id']],'一类')){
				$lei	= 1;
			}else if(stristr($jcbz_name_arr[$rs_jcbz['jcbz_bh_id']],'二类')){
				$lei	= 2;
			}else if(stristr($jcbz_name_arr[$rs_jcbz['jcbz_bh_id']],'三类')){
				$lei	= 3;
			}else if(stristr($jcbz_name_arr[$rs_jcbz['jcbz_bh_id']],'四类')){
				$lei	= 4;
			}else if(stristr($jcbz_name_arr[$rs_jcbz['jcbz_bh_id']],'五类')){
				$lei	= 5;
			}else{
				$lei	= $jcbz_fuhao_arr[$rs_jcbz['jcbz_bh_id']];
			}
			//每个项目不同类别下的标准名称
			if(!empty($jcbz_fuhao_arr[$rs_jcbz['jcbz_bh_id']])){
				$jcbz_arr[$rs_jcbz['vid']][$lei]['name']	= $jcbz_fuhao_arr[$rs_jcbz['jcbz_bh_id']];
			}else{
				$jcbz_arr[$rs_jcbz['vid']][$lei]['name']	= $jcbz_name_arr[$rs_jcbz['jcbz_bh_id']];
			}
			//每个项目不同类别下的标准限值
			if(!empty($rs_jcbz['panduanyiju'])){
				$jcbz_arr[$rs_jcbz['vid']][$lei]['xz']	= $rs_jcbz['panduanyiju'];
			}else{
				$jcbz_arr[$rs_jcbz['vid']][$lei]['xz']	= $rs_jcbz['xz'];
			}
			//每个项目不同类别下的计量单位
			$jcbz_arr[$rs_jcbz['vid']][$lei]['dw']	= $rs_jcbz['dw'];
			//$xz_arr[$rs_jcbz['vid']][$lei]	= $jcbz_arr[$rs_jcbz['vid']][$lei]['name'].":".$rs_jcbz['xz']."\n";
			//如果站点没有设置目标水质，就按照检测标准表默认的标准走
			if(empty($intent) && $rs_jcbz['jcbz_bh_id']==$jcbz_bh['moren']){
				$intent	= $lei;
			}
		}
	}
	if(!empty($jcbz_arr)){
		ksort($jcbz_arr[$vid]);//按照排序，这样I类水会最先判断
		foreach ($jcbz_arr[$vid] as $key => $bz_arr) {
			//判断单位是否一样
			//判断是否符合标准
			$tmp_quality	= $key;//评价结果
			$is_chaobiao	= is_chaobiao($vid,$water_type,$bz_arr['xz'],$result);//合格判断
			//目标水质的判定结果
			if($key	== $intent){
				$chaobiao_beishu	= $is_chaobiao;
			}
			//与目标水质进行判断
			if($is_chaobiao['status'] == 0){
				//单一标准合格或 多类标准符合的情况
				if((int)$key >0){
					$quality_name	= $jcbz_arr[$vid][$key]['name'];//评价结果名称：几类水
				}else{
					$quality_name	= "合格";
				}
				break;
			}else{
				if((int)$key >0){//劣五类
					$tmp_quality	= '6';
					$quality_name	= ">Ⅴ类";//评价结果名称：几类水
				}else{
					$quality_name	= "不合格";
				}
			}
		}
		//超标判断（含：超标倍数）
		if(!empty($intent) && empty($chaobiao_beishu)){
			$chaobiao_beishu	= is_chaobiao($vid,$water_type,$jcbz_arr[$vid][$intent]['xz'],$result);
		}
	}else{//该项目，在该标准下没有限制
		$tmp_quality	= $quality_name	= '无标准';
		$chaobiao_beishu= array('0','');
	}
	//评价结果、评价结果名称、是否符合目标水质、超标倍数
	return array("pingjia_result"=>$tmp_quality,"pingjia_name"=>$quality_name,"status"=>$chaobiao_beishu['status'],"beishu"=>$chaobiao_beishu['beishu']);
}
/**
 * 功能：根据检测限制判断检测结果是否合格
 * 作者：Mr Zhou
 * 日期：2015-05-07
 * 参数：$vid 项目id
 * 参数：$water_type 水样类型
 * 参数：$jcxz 检测限值
 * 参数：$result 检测结果
 * 返回值：
 * 描述：
*/
function is_chaobiao($vid,$water_type=0,$jcxz,$result,$is_eglish=0) {
	$over = 0;
	$jcxz = trim($jcxz);
	//替换特殊中文字符
	$jcxz = str_replace(array('＜','＞','~'), array('<','>','～'), $jcxz);
	//转换科学计数法(仅适用于一个比较符号的情况)
	if(stristr($jcxz,"E")){
		preg_match("/(<|≤|>|≥)/",$jcxz,$pp_jcxz);
		//去掉比较符号
		$jcxz	= str_replace(array('<','>','≤','≥'), '', $jcxz);
		//获取转换后应该保留几位小数
		$count	= strlen(explode('E',$jcxz)[0]) + abs(explode('E',$jcxz)[1]);
		$jcxz	= $pp_jcxz[0].number_format($jcxz,$count);
	}
	//找出限值中的数字
	$xz_arr	= array();
	$xz_arr	= preg_split('/[^\d.]/',$jcxz,-1, PREG_SPLIT_NO_EMPTY);
    //如果限制为空或者设置为'-'直接判断为合格
	if('' == $result || '' == $jcxz || '-' == $jcxz || '<' == $result[0] || '未检出' == $result){
		$over = 0;
	}else if(in_array($vid, array(1,95,96))){
		//以下项目特殊处理
		//1总大肠（不得检出），95臭和味（无异臭、异味），96肉眼可见物（无）
		if(1==$vid){
			$over = (floatval($result)==0 || '未检出'==$result) ? 0 : 1;
		}else if(95==$vid){
			//嗅和味超过一级（包括一级）就算不合格
			$over = (floatval($result[0])==0) ? 0 : 1;
		}else if(96==$vid){
			$over = ('无'==$result) ? 0 : 1;
		}
	}else if(count($xz_arr) == 1 && preg_match("/(<|≤|>|≥)/",$jcxz)){
		preg_match("/(<|≤|>|≥)/",$jcxz,$pp_jcxz);
		switch ($pp_jcxz[0]) {
			case '<':
				$over = ($result<$xz_arr[0])	? 0 : 1;
				break;
			case '≤':
				$over = ($result<=$xz_arr[0])	? 0 : 1;
				break;
			case '>':
				$over = ($result>$xz_arr[0])	? 0 : 1;
				break;
			case '≥':
				$over = ($result>=$xz_arr[0])	? 0 : 1;
				break;
			default :
				if($is_eglish){
					return array('status'=>'0','info'=>'Can not determine');
				}else{
					return array('status'=>'0','info'=>'无法判定');
				}
		}
	}else if(count($xz_arr)==2 && $vid=='99'){
		//如果限值中有两个数，一般认为是不小于前一个数，不大于后一个数
		$over = ($result>=$xz_arr[0]&&$result<=$xz_arr[1]) ? 0 : 1;
	}else if(494==$vid){
		//三卤甲烷总量小于1为合格
		$over = (floatval($result)<1) ? 0 : 1;
	}else if (count($xz_arr)==2 && preg_match("/\d+(\.\d+)?～\d+(\.\d+)?/",$jcxz)){
		// 数字~数字的情况处理，就是 大小等于前面的数 小于等于后面的数
		$over = ($result>=$xz_arr[0]&&$result<=$xz_arr[1]) ? 0 : 1;
	}else if(count($xz_arr)==2 && preg_match("/^(<|≤|>|≥)\d+(\.\d+)?\|(<|≤|>|≥)\d+(\.\d+)?/",$jcxz)){
		//处理 <5.5|>9 这种情况的标准的判断（暂时不兼容 1.1~1.2|2.1~2.2）
		preg_match_all("/(<|≤|>|≥)/",$jcxz,$pp_jcxz);
		switch ($pp_jcxz[0][0]) {
			case '<':
				$over= ($result<$xz_arr[0])	? 0 : 1;
				break;
			case '≤':
				$over= ($result<=$xz_arr[0])	? 0 : 1;
				break;
			case '>':
				$over = ($result>$xz_arr[0])	? 0 : 1;
				break;
			case '≥':
				$over = ($result>=$xz_arr[0])	? 0 : 1;
				break;
			default :
		}
		if($over =='1'){
			switch ($pp_jcxz[0][1]) {
				case '<':
					$over = ($result<$xz_arr[1])	? 0 : 1;
					break;
				case '≤':
					$over = ($result<=$xz_arr[1])	? 0 : 1;
					break;
				case '>':
					$over = ($result>$xz_arr[1])	? 0 : 1;
					break;
				case '≥':
					$over = ($result>=$xz_arr[1])	? 0 : 1;
					break;
				default :
			}
		}
	}else{
		if($is_eglish){
			return array('status'=>'0','info'=>'Can not determine');
		}else{
			return array('status'=>'0','info'=>'无法判定');
		}
	}
	//根据水样类型来获取不同的合格与否的提示
	if($is_eglish){
		$info = array(
			'0' =>array('meet','inferior '),
			'5' =>array('qualified','unqualified')
		);
	}else{
		$info = array(
			'0' =>array('符合','劣于'),
			'5' =>array('合格','不合格')
		);
	}
	//如果该水样类型没有设置，则默认成0
	if(empty($info[$water_type])){
		$water_type = 0;
	}
	//超标倍数判断(范围的情况暂时没法定)
	$beishu	= '';
	//$over为1时，项目超标
	if($over == '1' && !empty($xz_arr[0])){
		$beishu 	= _round(($result/$xz_arr[0]),2);
	}
	return array('status'=>$over,'info'=>$info[$water_type][$over],'beishu'=>$beishu);
}
/**
 * 功能：报告编号处理
 * 作者：zhengsen
 * 日期：2015-08-18
 * 返回值：处理后的报告编号
 * 描述：
*/
function get_bgbh($bh){
	if(!empty($bh)){
		$bh=(int)$bh;
		if($bh<10){
			$bh='00'.$bh;
		}elseif($bh>9&&$bh<100){
			$bh='0'.$bh;
		}
		return $bh;
	}
}
/**
 * 功能：根据报告统计周期 返回 时间选择控件
 * 返回值：时间选择控件的html代码
 * 描述：
*/
function choose_date_html($set_content,$type='月报'){
	switch ($type) {
		case '日报':
			if(empty($set_content['before_days'])){
				$set_content['before_days']	= 0;
			}
			$choose_date_html	= "起始日期：获取<input type='number' field='result_set' name='choose_date[before_days]' value='{$set_content['before_days']}' min='0' class='before_days' style='width:100px;' />天前的数据(默认当天)";
			/*$choose_date_html	= "起始日期：获取<div class=\"ace-spinner touch-spinner\" style=\"width: 140px;\">
													<div class=\"input-group\">
														获取<input type=\"text\" class=\"input-mini spinner-input form-control\" id=\"spinner2\" maxlength=\"5\">天前的数据(默认当天)
													</div>
												</div>";
												*/
			break;
		case '周报':
			$week1		= $set_content['week1'];
			$week2		= empty($set_content['week2'])?'7':$set_content['week2'];
			$week_type	= $set_content['week_type'];
			//天数
			$week1_options	= $week2_options	= '';
			$week_arr	= array('周一','周二','周三','周四','周五','周六','周日');
			foreach($week_arr as $value){
				$selected1		= ($week1==$value)?'selected':'';
				$selected2		= ($week2==$value)?'selected':'';
				$week1_options	.= "<option value='{$value}' {$selected1}>{$value}</option>";
				$week2_options	.= "<option value='{$value}' {$selected2}>{$value}</option>";
			}
			$selected_month		= ('上周'==$week_type)?'selected':'';
			$choose_date_html	= "起始日期:<select name='choose_date[week_type]' field='result_set' ><option value='本周'>本周</option></select>
											<select name='choose_date[week1]' id='week1' field='result_set' >{$week1_options}</select><span style='letter-spacing:20px'>&nbsp;</span>
									终止日期:<select field='result_set'><option value='本周'>本周</option></select>
											<select name='choose_date[week2]' id='week2' field='result_set'>{$week2_options}</select>";
			break;
		case '月报':
			$day1		= $set_content['day1'];
			$day2		= $set_content['day2'];
			$month_type	= $set_content['month_type'];
			//天数
			$day1_options	= $day2_options	= '';
			for($t=1;$t<=31;$t++){
				if($t<10){
					$t	= '0'.$t;
				}
				$selected1		= ($day1==$t)?'selected':'';
				$selected2		= ($day2==$t)?'selected':'';
				$day1_options	.= "<option value='{$t}' {$selected1}>{$t}</option>";
				$day2_options	.= "<option value='{$t}' {$selected2}>{$t}</option>";
			}
			$selected1_month		= ('上月'==$month_type)?'selected':'';
			$selected2_month        = ('本月'==$month_type)?'selected':''; 
			$choose_date_html	= "起始日期:<select name='choose_date[month_type]' field='result_set'><option value=''>--请选择--</option><option value='本月' {$selected2_month}>本月</option><option value='上月' {$selected1_month}>上月</option></select>
											<select name='choose_date[day1]' id='day1' field='result_set'><option value=''>--请选择--</option>{$day1_options}</select><span style='letter-spacing:20px'>&nbsp;</span>
									终止日期:<select><option value='本月'>本月</option></select>
											<select name='choose_date[day2]' id='day2' field='result_set'><option value=''>--请选择--</option>{$day2_options}</select>";
			break;
		case '年报':
			//起始时间
			if(!empty($set_content['begin_date'])){
				if(substr_count($set_content['begin_date'],'-') < 2){
					$set_content['begin_date']	= date('Y')."-".$set_content['begin_date'];
				}
				$begin_date	= $set_content['begin_date'];
			}else{
				$begin_date	= date('Y-01-01');
			}
			//终止时间
			if(!empty($set_content['end_date'])){
				if(substr_count($set_content['end_date'],'-') < 2){
					$set_content['end_date']	= date('Y')."-".$set_content['end_date'];
				}
				$end_date	= $set_content['end_date'];
			}else{
				$end_date	= date('Y-12-31');
			}
			$choose_date_html	= "起始日期:<input type=\"text\" size=\"10\" name=\"choose_date[begin_date]\" id=\"begin_date\" field='result_set' class=\"date-picker\" value=".$begin_date." />
									终止日期:<input type=\"text\" size=\"10\" name=\"choose_date[end_date]\" id=\"end_date\" field='result_set' class=\"date-picker\"   value=".$end_date." />";
			break;
		default:
			//起始时间
			if(!empty($set_content['begin_date'])){
				$begin_date	= $set_content['begin_date'];
			}else{
				$begin_date	= date('Y-m-d',strtotime('- 1 month'));
			}
			//终止时间
			if(!empty($set_content['end_date'])){
				$end_date	= $set_content['end_date'];
			}else{
				$end_date	= date('Y-m-d');
			}
			$choose_date_html	= "起始日期:<input type=\"text\" size=\"10\" name=\"choose_date[begin_date]\" id=\"begin_date\" class=\"date-picker\" field='result_set' value=".$begin_date." />
									终止日期:<input type=\"text\" size=\"10\" name=\"choose_date[end_date]\" id=\"end_date\"  class=\"date-picker\"  field='result_set' value=".$end_date." />";
			break;
	}
	return $choose_date_html;
}