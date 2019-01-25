<?php if ($type == 1): ?>
道具|
<?php else: ?>
<a href="<?php echo linkwml('prop', 'bag', array('type' => 1));?>">道具</a>|
<?php endif; ?>
<?php if ($type == 2): ?>
服饰
<?php else: ?>
<a href="<?php echo linkwml('prop', 'bag', array('type' => 2));?>">服饰</a>
<?php endif; ?>
<br />
<?php if ($type == 1): ?>
<?php if ($gtype =='star'): ?>
艺人用|
<?php else: ?>
<a href="<?php echo linkwml('prop', 'bag', array('type' => 1, 'gtype' => 'star'));?>">艺人用</a>|
<?php endif; ?>
<?php if ($gtype == 'boss'): ?>
老板用|
<?php else: ?>
<a href="<?php echo linkwml('prop', 'bag', array('type' => 1, 'gtype' => 'boss'));?>">老板用</a>|
<?php endif; ?>
<?php if ($gtype == 'other'): ?>
其他
<?php else: ?>
<a href="<?php echo linkwml('prop', 'bag', array('type' => 1, 'gtype' => 'other'));?>">其他</a>
<?php endif; ?>
<?php elseif ($type == 2): ?>
<?php if ($gtype == 'top'): ?>
上身|
<?php else: ?>
<a href="<?php echo linkwml('prop', 'bag', array('type' => 2, 'gtype' => 'top'));?>">上身</a>|
<?php endif; ?>
<?php if ($gtype == 'bottom'): ?>
下身|
<?php else: ?>
<a href="<?php echo linkwml('prop', 'bag', array('type' => 2, 'gtype' => 'bottom'));?>">下身</a>|
<?php endif; ?>
<?php if ($gtype == 'de'): ?>
配饰
<?php else: ?>
<a href="<?php echo linkwml('prop', 'bag', array('type' => 2, 'gtype' => 'de'));?>">配饰</a>
<?php endif; ?>
<?php endif; ?>
<br />
