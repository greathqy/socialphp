<?php echo $class['name'];?><br />
解锁条件:<br />
培训中心: Lv<?php echo $class['unlock_require']['level'];?><br />
道具: 
<?php foreach($class['unlock_require']['props'] as $pid => $item): ?>
<?php echo $item['name'];?> x <?php echo $item['num'];?>&nbsp;&nbsp;
<?php endforeach; ?>
<br />
<a href="<?php echo linkwml('training', 'dounlock', array('id' => $class['id']));?>">确认解锁</a><br />
<a href="<?php echo $link_prev;?>">返回</a>
