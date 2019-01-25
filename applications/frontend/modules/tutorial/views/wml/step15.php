<?php echo $star['name'];?>的服饰<br />
<?php if ($tab == 'top'): ?>
上装 |
<?php else: ?>
<a href="<?php echo linkwml('tutorial', 'step15', array('pos' => 'top'));?>">上装</a> |
<?php endif; ?>
<?php if ($tab == 'bottom'): ?>
下装 |
<?php else: ?>
<a href="<?php echo linkwml('tutorial', 'step15', array('pos' => 'bottom'));?>">下装</a> |
<?php endif; ?>
<?php if ($tab == 'de1'): ?>
配饰1 |
<?php else: ?>
<a href="<?php echo linkwml('tutorial', 'step15', array('pos' => 'de1'));?>">配饰1</a> |
<?php endif; ?>
<?php if ($tab == 'de2'): ?>
配饰2 |
<?php else: ?>
<a href="<?php echo linkwml('tutorial', 'step15', array('pos' => 'bottom'));?>">配饰2</a> |
<?php endif; ?>
<br />
<?php 
if ($tab == 'de1' || $tab == 'de2') {
	$tab = 'de';
}
?>
<?php if ($tab == $prop['pos']): ?>
<a href="<?php echo linkwml('tutorial', 'step15_propdetail');?>"><?php echo $prop['name'];?></a> (<a href="<?php echo linkwml('tutorial', 'step16');?>">装</a>)<br />
<?php endif; ?>
