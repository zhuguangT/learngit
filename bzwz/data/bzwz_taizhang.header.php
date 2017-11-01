<?php
//标准物质台账模板 header

echo <<<html
<div style="text-align:center;width:auto;">
    <h1 style="font-size:1.1em;margin:0;padding:0;">{$wz_type}台账</h1>
    <span style="display:block;position:relative;margin:0;padding:0;font-size:1.1em;">
        <span style="position:absolute;top:-1.3em;right:15%;">{$file_code}</span>
    </span>
</div>
<table class="single" style="width:18cm">
<tr colspan="11"><td></td></tr>
<tr align="center">
<td colspan="2">年</td>
<td rowspan="2">样品编号</td>
<td rowspan="2">生产单位</td>
<td rowspan="2">有效期</td>
<td colspan="2">入库</td>
<td colspan="2">出库</td>
<td colspan="2">结存</td>
</tr>

<tr align="center">
<td>月</td>
<td>日</td>
<td>数量</td>
<td>单位</td>
<td>数量</td>
<td>单位</td>
<td>数量</td>
<td>单位</td>
</tr>
html;

?>