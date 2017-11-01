<?php echo $print_head;?>
<style type="text/css">
	table.single tr td{ height: 25px;line-height: 25px;border:1px solid black;}
	div{margin:0 auto;padding:0;}
	div table{margin: 5px 0 auto;width: 100%}
</style>
<?php $i=0; foreach($data as $table_key => $ji_lu){?>
	<div style="text-align:center;width:18cm;margin:0 auto;">单位名称：<?php echo $u['hub_name'];?><div style="float:right">打印时间：<?php echo date('Y-m-d');?></div></div>
	<h3 class="header smaller center title">库房物品一览表</h3>
	<table class="single" style="width:18cm;text-align:center" align="center">
		<tr>
			<td width="150">序&nbsp;&nbsp;号</td>
			<td>名&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;称</td>
			<td width="150">备&nbsp;&nbsp;注</td>
		</tr>
		<?php foreach ($ji_lu as $key => $value) { ?>
		<tr>
			<td><?php echo ($value['name']) ?  ++$i : ''; ?></td>
			<td><?php echo $value['name'];?></td>
			<td><?php echo $value['beizhu'];?></td>
		</tr>
		<?php } ?>
	</table>
	<div style="page-break-before: always;"></div>
<?php } echo $print_foot;?>