<?php if (isset($redeemed)): ?>
你成功卖出<?php echo $propinfo['name'];?>，获得<?php echo $redeem_price;?>金币。<br />
<a href="<?php echo linkwml('prop', 'bag', array('type' => 2));?>">继续</a>
<?php else: ?>
你确定要卖出<?php echo $propinfo['name'];?>吗？可得<?php echo $redeem_price;?>金币。<br />
<?php if ($propinfo['star_id']): ?>
正在被<a href="<?php echo linkwml('star', 'stardetail', array('id' => $propinfo['star_id']));?>"><?php echo $propinfo['star_detail']['name'];?></a>装备着。<br />
<?php endif; ?>
<a href="<?php echo linkwml('prop', 'redeem', array('id' => $propinfo['de_id'], 'confirm' => 1));?>">确认卖出</a><br />
<a href="<?php echo $link_prev;?>">返回</a>
<?php endif; ?>
