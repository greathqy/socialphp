你要使用什么道具恢复体力?<br />
<?php if (!$props): ?>
你什么道具都没有, 请<a href="<?php echo linkwml('prop', 'index', array('type' => 1));?>">去商店</a>购买。<br />
<?php else: ?>
<?php foreach ($props as $prop): ?>
<a href="<?php echo linkwml('prop', 'detail', array('id' => $prop['id']));?>"><?php echo $prop['name'];?></a> x<?php echo $prop['count'];?> <a href="<?php echo linkwml('prop', 'boss_consume', array('id' => $prop['id']));?>">使</a><br />
<?php echo $prop['desc'];?><br />
<?php endforeach; ?>
<br />
<?php echo $pagination;?>
<?php endif; ?>
