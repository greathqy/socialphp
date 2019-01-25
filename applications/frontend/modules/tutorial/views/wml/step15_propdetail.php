<?php echo $star['name'];?>的服饰<br />
<?php echo $prop['name'];?><br />
装备要求:<br />
艺人名气: Lv<?php echo $prop['starlevel'];?><br />
性别: <?php echo $prop['sex_text'];?><br />
属性: 
<?php foreach ($prop['effect'] as $effect => $num): ?>
<?php if ($effect == 'charm'): ?>
魅力+<?php echo $num;?>&nbsp;&nbsp;
<?php elseif ($effect == 'sing'): ?>
歌艺+<?php echo $num;?>&nbsp;&nbsp;
<?php elseif ($effect == 'acting'): ?>
演技+<?php echo $num;?>&nbsp;&nbsp;
<?php endif; ?>
<?php endforeach; ?>
<br />
价值: <?php echo $prop['price'];?>金币<br />
现服饰比较:<br />
无<br />
<?php foreach ($prop['effect'] as $effect => $num): ?>
<?php if ($effect == 'charm'): ?>
魅力+<?php echo $num;?><br />
<?php elseif ($effect == 'sing'): ?>
歌艺+<?php echo $num;?><br />
<?php elseif ($effect == 'acting'): ?>
演技+<?php echo $num;?><br />
<?php endif; ?>
<?php endforeach; ?>
<br />
<a href="<?php echo linkwml('tutorial', 'step16');?>">装备</a>
