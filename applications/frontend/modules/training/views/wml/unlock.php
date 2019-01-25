你共有<?php echo $free_unlock_chances;?>次免费解锁机会。<br /><br />
<?php if ($unlocks): ?>
增加演技的培训<br />
<?php if (!isset($unlocks['acting']) || !$unlocks['acting']): ?>
	暂时没有增加演技课程。<br />
<?php else: ?>
	<?php foreach($unlocks['acting'] as $class): ?>
		<?php if ($class['unlocked']): ?>
			><?php echo $class['name'];?> 已解锁<br />
		<?php else: ?>
			><?php echo $class['name'];?> <a href="<?php echo linkwml('training', 'unlockintro', array('id' => $class['id']));?>">解锁</a><br />
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>

增加魅力的培训<br />
<?php if (!isset($unlocks['charm']) || !$unlocks['charm']): ?>
	暂时没有增加魅力的课程。<br />
<?php else: ?>
	<?php foreach ($unlocks['charm'] as $class): ?>
		<?php if ($class['unlocked']): ?>
			><?php echo $class['name'];?> 已解锁<br />
		<?php else: ?>
			><?php echo $class['name'];?> <a href="<?php echo linkwml('training', 'unlockintro', array('id' => $class['id']));?>">解锁</a><br />
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>

增加歌艺的培训<br />
<?php if (!isset($unlocks['sing']) || !$unlocks['sing']): ?>
	暂时没有增加歌艺的课程。<br />
<?php else: ?>
	<?php foreach($unlocks['sing'] as $class): ?>
		<?php if ($class['unlocked']): ?>
			><?php echo $class['name'];?> 已解锁<br />
		<?php else: ?>
			><?php echo $class['name'];?> <a href="<?php echo linkwml('training', 'unlockintro', array('id' => $class['id']));?>">解锁</a><br />
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>
<?php else: ?>
暂时没有课程可以解锁。<br />
<?php endif; ?>
<a href="<?php echo $link_prev;?>">返回</a>
