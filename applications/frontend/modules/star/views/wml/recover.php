对<a href="<?php echo linkwml('star', 'stardetail', array('id' => $star['id']));?>"><?php echo $star['name'];?></a>使用什么道具:<br />
<?php if (!$props): ?>
你什么道具都没有, 请<a href="<?php echo linkwml('prop', 'index', array('type' => 1));?>">去商店</a>购买。<br />
<?php else: ?>
<?php foreach ($props as $prop): ?>
<a href="<?php echo linkwml('prop', 'detail', array('id' => $prop['id']));?>"><?php echo $prop['name'];?></a> x<?php echo $prop['count'];?> <a href="<?php echo linkwml('prop', 'star_consume', array('sid' => $star['id'], 'id' => $prop['id']));?>">使</a><br />
<?php echo $prop['desc'];?><br />
<?php endforeach; ?>
<br />
<?php echo $pagination;?>
<?php endif; ?>
