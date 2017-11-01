<?php
//标准物质台账模板

echo <<<html

<tr align="center">
<td>{$line['month']}</td>
<td>{$line['date']}</td>
<td>{$line['wz_bh']}</td>
<td>{$line['gl_bh']}</td>
<td>{$line['manufacturer']}</td>
<td>{$line['time_limit']}</td>
<td>{$line['in_amount']}</td>
<td>{$line['unit']}</td>
<td>{$line['op_man']}</td>
<td>{$line['out_amount']}</td>
<td>{$line['unit']}</td>
<td>{$line['op_man']}</td>
<td>{$line['jie_cun']}</td>
<td>{$line['unit']}</td>
</tr>
html;

?>