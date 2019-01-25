<?php $this->include_partial('_prop_navheader');?>
<?php echo $propinfo['name'];?>
<?php if (isset($propinfo['star_detail'])): ?>
(<a href="<?php echo linkwml('star', 'stardetail', array('id' => $propinfo['star_id']));?>"><?php echo $propinfo['star_detail']['name'];?></a>)
<?php endif; ?>
<br />
名气要求: <?php echo $propinfo['starlevel'];?><br />
性别: <?php echo $propinfo['sex_text'];?><br />
属性:
<?php if (isset($propinfo['effect']['charm'])): ?>
魅力+<?php echo $propinfo['effect']['charm'];?>
<?php endif; ?>
<?php if (isset($propinfo['effect']['acting'])): ?>
, 演技+<?php echo $propinfo['effect']['acting'];?>
<?php endif; ?>
<?php if (isset($propinfo['effect']['sing'])): ?>
, 歌艺+<?php echo $propinfo['effect']['sing'];?>
<?php endif; ?>
<br />
价值: <?php echo $propinfo['price'];?>金币<br />
<?php if ($propinfo['star_id']): ?>
<a href="<?php echo linkwml('prop', 'undecorate', array('id' => $propinfo['de_id']));?>">卸下</a> |
<?php else: ?>
<a href="<?php echo linkwml('prop', 'decorate', array('id' => $propinfo['de_id']));?>">装备</a> |
<?php endif; ?>
<a href="<?php echo linkwml('prop', 'redeem', array('id' => $propinfo['de_id']));?>">卖出</a> | <a href="<?php echo linkwml('prop', 'drop', array('id' => $propinfo['de_id']));?>">丢弃</a> | <a href="<?php echo $link_prev;?>">返回</a>
