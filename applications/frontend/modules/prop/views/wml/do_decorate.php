<?php if (isset($level_not_enough)): ?>
<?php echo $star['name'];?>名气未能达到装备要求，不可以装备此道具。<br />
<a href="<?php echo $link_prev;?>">返回</a><br />
<?php elseif (isset($sex_miss_match)): ?>
<?php echo $star['name'];?>性别未达要求, 不可以装备此道具。<br />
<a href="<?php echo $link_prev;?>">返回</a><br />
<?php else: ?>
<?php if (!$removed): ?>
给<?php echo $star['name'];?>装备<?php echo $decorated['name'];?>成功。<br />
<?php else: ?>
给<?php echo $star['name'];?>装备上<?php echo $decorated['name'];?>，换下<?php echo $removed['name'];?>。<br />
<?php endif; ?>
<a href="<?php echo linkwml('prop', 'bag', array('type' => 2));?>">继续</a>
<?php endif; ?>
