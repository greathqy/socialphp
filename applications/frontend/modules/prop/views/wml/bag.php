<?php $this->include_partial('_prop_navheader');?>
<?php if (!$props): ?>
没有物品。
<?php else: ?>
<?php if ($type == 1): //普通道具?>
<?php foreach ($props as $pid => $item): ?>
<a href="<?php echo linkwml('prop', 'detail', array('id' => $item['id']));?>"><?php echo $item['name'];?></a> <?php echo $item['count'];?>
<?php if ($gtype != 'other'): ?>
&nbsp;&nbsp;<a href="<?php echo linkwml('prop', 'consume', array('id' => $item['id']));?>">使</a>
<?php endif; ?>
<br />
<?php endforeach; ?>
<?php else: //服饰?>
<?php foreach ($props as $pid => $item): ?>
<?php if ($item['sid']): ?>
<a href="<?php echo linkwml('prop', 'dedetail', array('id' => $item['psid']));?>"><?php echo $item['prop']['name'];?></a>&nbsp;&nbsp;
<?php else: ?>
<a href="<?php echo linkwml('prop', 'dedetail', array('id' => $item['psid']));?>"><?php echo $item['prop']['name'];?></a>&nbsp;&nbsp;
<?php endif; ?>
<?php if ($item['sid']): ?>
(<a href="<?php echo linkwml('star', 'stardetail', array('id' => $item['sid']));?>"><?php echo $item['starinfo']['name'];?></a>)
<?php else: ?>
(<a href="<?php echo linkwml('prop', 'decorate', array('id' => $item['psid']));?>">装</a>)
<?php endif; ?>
<br />
<?php endforeach; ?>
<?php endif; ?>
<?php endif; ?>
<br />
<?php echo $pagination; ?>
<br />
<a href="<?php echo linkwml('prop', 'index', array('type' => $type));?>">逛商店</a><br />
<a href="<?php echo $link_prev;?>">返回</a>
