<?php
//标准物质台账模板

echo <<<html
<html lang="zh-cn">
<head>
<meta charset="utf-8" />
<title>{$dwname} Ver$mainversion {$u['userid']} $now</title>
<meta name="keywords" content="LIMS" />
<meta name="description" content="LIMS" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link href="$rooturl/css/lims/main.css" rel="stylesheet" />
<link href="$rooturl/css/lims/print.css" rel="stylesheet" />
<script type="text/javascript" src="$rooturl/js/lims/jquery.js"></script>
<script>
function fenye(but){
	if(but.value=='另起一页') {
		$(but).parents('div').css("page-break-before","always")
		$(but).val('取消分页');
	}else{
		$(but).parents('div').css("page-break-before","")
		$(but).val('另起一页');
	}
}
</script>
</head>
<body style="background:#FFF">

<div style="text-align:center;width:auto;">
    <h1 style="font-size:1.1em;margin:0;padding:0;">{$wz_type}台账</h1>
    <span style="display:block;position:relative;margin:0;padding:0;font-size:1.1em;">
        <span style="position:absolute;top:-1.3em;right:15%;">{$file_code}</span>
    </span>
</div>
<table class="single" style="width:18cm;">
<tr><td colspan="11" class="noborder">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$wz_name}</td></tr>
<tr align="center">
<td colspan="2">{$year}年</td>
<td rowspan="2">样品编号</td>
<td rowspan="2">管理编号</td>
<td rowspan="2">生产单位</td>
<td rowspan="2">有效期</td>
<td colspan="3">入库</td>
<td colspan="3">出库</td>
<td colspan="2">结存</td>
</tr>

<tr align="center">
<td>月</td>
<td>日</td>
<td>数量</td>
<td>单位</td>
<td>入库人</td>
<td>数量</td>
<td>单位</td>
<td>领用人</td>
<td>数量</td>
<td>单位</td>
</tr>
html;

?>