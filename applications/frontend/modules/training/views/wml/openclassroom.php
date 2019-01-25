现在有<?php echo $my_class_rooms;?>个培训班，最多开设<?php echo $max_class_rooms;?>个培训班。其中<?php echo $max_class_invite_pay;?>个必须通过邀请好友和钻石开通。<br />
<?php if (isset($cant_open_class)): ?>
你的培训班数已经达到上限，无法开班。
<?php else: ?>
需求: 培训中心等级(Lv<?php echo $condition['level'];?>)<br />
道具: 
<?php foreach ($condition['props'] as $pid => $item): ?>
<?php echo $item['name'];?> x <?php echo $item['num'];?>
<?php endforeach; ?>
<br />
<a href="<?php echo linkwml('training', 'openclass');?>">确认开班</a><br />
<?php endif; ?>
<?php if ($not_have_invite_class): ?>
邀请<?php echo $invite['invite']?>个好友加入游戏，开通一个培训班<?php echo $invite['days'];?>天。<br />
<a href="">邀请好友</a><br />
<?php else: ?>
邀请<?php echo $invite['invite']?>个好友加入游戏，开通一个培训班<?php echo $invite['days'];?>天。<br />
<a href="">邀请好友</a><br />
<?php endif; ?>
<?php if ($not_have_pay_class): ?>
花费<?php echo $pay['price'];?>个钻石开通一个培训班<?php echo $pay['days'];?>天。<br />
<a href="<?php echo linkwml('training', 'dbopen');?>">钻石开通</a><br />
<?php else: ?>
花费<?php echo $pay['price'];?>个钻石延长培训班<?php echo $pay['days'];?>天。<br />
<a href="<?php echo linkwml('training', 'dbopen');?>">钻石延长</a><br />
<?php endif; ?>
<a href="<?php echo $link_prev;?>">返回</a>
