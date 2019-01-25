<?php if (isset($failed)): ?>
<?php if (isset($nogb)): ?>
金币不足, 无法培训。<br />
<a href="<?php echo $link_prev;?>">返回</a>
<?php elseif (isset($nodb)): ?>
钻石不足，请<a href="">充值</a>。<br />
<a href="<?php echo $link_prev;?>">返回</a>
<?php endif; ?>
<?php else: ?>
培训成功！<a href="<?php echo linkwml('star', 'stardetail', array('id' => $star['id']));?>"><?php echo $star['name'];?></a><br />
<?php foreach ($effect as $key => $item): ?>
<?php echo $item['name'];?> +<?php echo $item['amount'];?><br />
冷却时间: <?php echo $time;?>
<?php endforeach; ?>
<?php endif; ?>
<a href="<?php echo linkwml('training', 'index');?>">返回培训中心</a>
