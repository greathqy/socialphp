<?php if (isset($droped)): ?>
你成功丢弃<?php echo $propinfo['name'];?><br />
<a href="<?php echo linkwml('prop', 'bag', array('type' => 2));?>">继续</a>
<?php else: ?>
你确定要丢弃<?php echo $propinfo['name'];?>吗？<br />
<?php if ($propinfo['star_id']): ?>
该道具正在被<a href="<?php echo linkwml('star', 'stardetail', array('id' => $propinfo['star_id']));?>"><?php echo $propinfo['star_detail']['name'];?></a>装备中。<br />
<?php endif; ?>
<a href="<?php echo linkwml('prop', 'drop', array('id' => $propinfo['de_id'], 'confirm' => 1));?>">确认丢弃</a><br />
<a href="<?php echo $link_prev;?>">返回</a>
<?php endif; ?>
