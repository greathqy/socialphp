<?php if ($allowed): ?>
恭喜你，培训班开通成功。<br />
<a href="<?php echo linkwml('training', 'index');?>">继续</a>
<?php else: ?>
<?php if (isset($level_not_meet)): ?>
培训中心等级还不够, 无法开班。<br />
<a href="<?php echo $link_prev;?>">返回</a>
<?php elseif ($prop_not_meet): ?>
道具不足，无法开班。需要道具：<br />
<?php foreach ($missed_props as $pid => $item): ?>
<?php echo $item['name'];?> x <?php echo $item['required'];?><br />
<?php endforeach;; ?>
<a href="<?php echo $link_prev;?>">返回</a>
<?php endif; ?>
<?php endif; ?>
