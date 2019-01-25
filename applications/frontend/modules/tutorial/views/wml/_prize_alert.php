<?php if(isset($prize['gb']) || isset($prize['props'])):?>
获得奖励:<br />
<?php if (isset($prize['gb'])): ?>
金币 x<?php echo $prize['gb'];?><br />
<?php endif; ?>
<?php if (isset($prize['props'])): ?>
<?php foreach($prize['props'] as $pid => $pinfo): ?>
<?php echo $pinfo['name'];?> x <?php echo $pinfo['num'];?><br />
<?php endforeach; ?>
<?php endif; ?>
<?php endif; ?>