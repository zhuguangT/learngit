<?php
//得到一个 sid 一个 viｄ 返回最近2两年的数据趋势数据

include "../temp/config.php";

$fzx_id	= $_SESSION['u']['fzx_id'];
if(!empty($_GET['fzx']) && $_GET['fzx']!='undefined'){
	$fzx_id	= $_GET['fzx'];
}
$sid	= $_GET['sid'];
$vid	= $_GET['vid'];
//$xm_rs	= $DB->fetch_one_assoc("SELECT * FROM `assay_value` WHERE `id`='{$vid}'");
//$xm		= $xm_rs['value_C']
$xm		= $_SESSION['assayvalueC'][$vid];
$sitename = '';
$where_sql	= '';
if($fzx_id != '全部'){
	$where_sql	= " AND cy.fzx_id='{$fzx_id}' ";
}
$sql	= "SELECT ao.vd0,ao.id,cy.cy_date,ao.site_name  FROM  `assay_order` as ao ,cy  WHERE cy.id=ao.cyd_id and ao.sid =  '$sid' AND ao.vid ='$vid' and ao.hy_flag>=0 $where_sql ORDER BY  `cy`.`cy_date` ASC ";
$R		= $DB->query($sql);
while($r= $DB->fetch_assoc($R)){
	$t	= date('"y年m月-d号"',strtotime($r['cy_date']));
	$t	= str_replace('-','<br>',$t);
	$tarr[]	= $t;
	if($r['vd0']!=''){
		$v	= (float)$r['vd0'];
		$varr2[]	= $v;
	}else{
		$v	= '';
	}
	$varr[]	= $v;
	$sitename	= $r['site_name'];
}
if(count($tarr)<2){
	$tarr[]	= $t	= str_replace('-','<br>',date('"y年m月-d号"'));

}
$tstr	= @implode(',',$tarr);
$vstr	= @implode(',',$varr);//用途也没看出来？
//$w=explode()
if(!empty($varr2)){
	sort($varr2);
	//值包含大于号小于号时会不会出问题？
	$miny	= current($varr2);
	$maxy	= end($varr2);
}else{
	$miny	= 0;
	$maxy	= 0;
}
if($miny == $maxy){
	if((int)$maxy != 0){
		$miny	= 0;
	}else{
		$maxy	= 1;
	}
}
$cha	= ($maxy-$miny)/5;
$maxy	= $maxy+$cha;
$miny	= $miny-$cha;
$weiarr	= explode('.',$miny);
$w		= strlen($weiarr[1]);//看Y轴要保留小数点后几位
if($miny<0){
	$miny	= 0;
}
if($maxy==0){//防止卡死
	$maxy	= 5;
	$cha	= 1;
}
$title	= "$sitename ($xm) 数据趋势图";
?>
{
	"type":"line",
	"title":{
	    "text":"<?php echo $title; ?>"
	},
	"scale-x":{
	     "values":[<?php echo $tstr; ?>],
	      "zooming":1,     
	},
	"scale-y":{
	    "values":"<?php echo "$miny:$maxy:$cha"; ?>",  
	    "decimals":<?php echo $w;?>,   
	     "label":{
	        "text":"检测值"
	    }
	},
	"plot":{
	    "tooltip-text":"检测值:%v  "
	},
	"series":[
	    {
	        "values":[<?php echo $vstr; ?>],
	    }
	]
}
