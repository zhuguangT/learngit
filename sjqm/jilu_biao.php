
<?php echo $print_head;?>
<style type="text/css">
	table.single tr td{ height: 35px;line-height: 35px;border:1px solid black;}
</style>
<?php $i=0; foreach($data as $table_key => $ji_lu){?>
	<div style="text-align:center;width:26cm;margin:0 auto;">
		<div style="float:left;width:200px">&nbsp;</div>单位名称：<?php echo $u['hub_name'];?>
		<div style="float:right;width:200px">打印时间：<?php echo date('Y-m-d');?></div>
	</div>
	<h3 class="header smaller center title">领用记录表</h3>
	<table class="single" style="width:26cm;text-align:center" align="center">
		<tr>
			<td rowspan=2>序<br />号</td>
			<td colspan=2 align="right"><?php echo $ji_lu[0]['Y'];?>年</td>
			<td rowspan=2>名&nbsp;&nbsp;&nbsp;&nbsp;称</td>
			<td rowspan=2>别&nbsp;名</td>
			<td rowspan=2>级&nbsp;别</td>
			<td rowspan=2>规&nbsp;格</td>
			<td rowspan=2>生产厂家</td>
			<td colspan=2>领&nbsp;用</td>
			<td rowspan=2>领用人</td>
			<td rowspan=2>管理员</td>
		</tr>
		<tr>
			<td>月</td>
			<td>日</td>
			<td>数量</td>
			<td>单位</td>
		</tr>
		<?php foreach ($ji_lu as $key => $value) { ?>
		<tr>
			<td><?php echo empty($value) ? '&nbsp;':++$i; ?></td>
			<td><?php echo $value['m'];?></td>
			<td><?php echo $value['d'];?></td>
			<td><?php echo $value['name'];?></td>
			<td><?php echo $value['nice_name'];?></td>
			<td><?php echo $value['jibie'];?></td>
			<td><?php echo $value['guige'];?></td>
			<td><?php echo $value['changjia'];?></td>
			<td><?php echo $value['shuliang'];?></td>
			<td><?php echo $value['danwei'];?></td>
			<td><?php echo $value['user'];?></td>
			<td><?php echo $value['gl_user'];?></td>
		</tr>
		<?php } ?>
	</table>
	<div style="page-break-before: always;"></div>
<?php } echo $print_foot;?>