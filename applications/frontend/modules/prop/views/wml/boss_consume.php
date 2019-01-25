<?php if (isset($consumed)): ?>
成功<?php echo isset($propinfo['funny_desc']) ? $propinfo['funny_desc'] : $propinfo['desc'];?>
<br />
<?php else: //道具使用失败?>
<?php if (isset($cant_use)): ?>
<?php echo $propinfo['name'];?>已经超过当日使用次数限制, 不能再使用。<br />
<?php elseif ($prop_not_enough): ?>
<?php echo $propinfo['name'];?>数量不足，无法使用。<br/>
<?php endif; ?>
<?php endif; ?>
<a href="<?php echo linkwml('index', 'index');?>">继续</a>
