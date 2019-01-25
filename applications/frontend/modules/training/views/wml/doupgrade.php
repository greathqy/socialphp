<?php if (isset($failed)): ?>
<?php if (isset($level_not_meet)): ?>
公司等级不够，不能升级培训中心。<br />
<a href="<?php echo $link_prev;?>">返回</a>
<?php elseif (isset($prop_not_meet)): ?>
道具不足，不能升级培训中心。<br />
<a href="<?php echo $link_prev;?>">返回</a>
<?php endif; ?>
<?php elseif (isset($upgraded)): ?>
培训中心升级成功。<br />
<a href="<?php echo linkwml('training', 'index');?>">继续</a>
<?php endif; ?>
