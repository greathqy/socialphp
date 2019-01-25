<?php if (isset($succ)): ?>
解锁成功，培训中心增加 <?php echo $class['name'];?> 的培训项目。<br />
<?php else: ?>
解锁失败，
<?php if (isset($level_not_meet)): ?>
培训中心等级不够。<br />
<?php elseif ($prop_not_meet): ?>
道具不足。<br />
<?php endif; ?>
<?php endif; ?>
<a href="<?php echo $link_prev;?>">返回</a>
