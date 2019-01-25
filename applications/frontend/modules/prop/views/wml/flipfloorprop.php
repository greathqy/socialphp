你有<?php echo $user_gb;?>金币<br />
<?php echo $user_db;?>钻石 <a href="">充值</a><br />
体力: <?php echo $user_power;?>/<?php echo $power_limit;?>
<?php if ($user_power < $power_limit): ?>
&nbsp;&nbsp;<a href="<?php echo linkwml('prop', 'bag', array('type' => 1, 'gtype' => 'boss'));?>">使用道具</a>
<?php endif; ?>
<br />
<?php if (isset($no_power)): ?>
你的体力不足，<a href="">使用道具</a><br />
<a href="<?php echo $link_prev;?>">返回</a><br />
<?php else: ?>
恭喜你，翻出了 <?php echo $propinfo['name'];?><br />
名气要求: Lv<?php echo $propinfo['starlevel'];?><br />
性别:&nbsp;&nbsp; 
<?php if ($propinfo['sex'] == 1): ?>
男
<?php elseif ($propinfo['sex'] == 2): ?>
女
<?php elseif ($propinfo['sex'] == 3): ?>
通用
<?php endif; ?>
<br />
属性:&nbsp;&nbsp;
<?php foreach ($propinfo['effect'] as $effect => $amount): ?>
<?php if ($effect == 'charm'): ?>
魅力+<?php echo $amount;?>&nbsp;&nbsp;
<?php elseif ($effect == 'sing'): ?>
歌艺+<?php echo $amount;?>&nbsp;&nbsp;
<?php elseif ($effect == 'acting'): ?>
演技+<?php echo $amount;?>&nbsp;&nbsp;
<?php endif; ?>
<?php endforeach; ?>
<br />
价值: <?php echo $propinfo['price'];?>金币<br />
<a href="<?php echo linkwml('prop', 'buy', array('id' => $propid, 'num' => 1));?>">购买</a><br />
<a href="<?php echo linkwml('prop', 'clothes', array());?>">继续翻找</a><br />
<?php endif; ?>
