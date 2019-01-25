<?php if (isset($nomoney)): ?>
你的钱不够, 请<a href="">充值</a>
<?php else: ?>
你买到了<?php echo $num;?>件<?php echo $prop['name'];?><br />
<a href="<?php echo $link_prev;?>">返回</a><br />
<a href="<?php echo linkwml('prop', 'bag', array('type' => $type));?>">去背包</a>
<?php endif; ?>
