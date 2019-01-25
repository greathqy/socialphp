<?php if (isset($failed)): ?>
升级失败，
<?php if (isset($level_not_meet)): ?>
公司级别不足。<br />
<?php elseif ($prop_not_meet): ?>
道具不足。<br />
<?php endif; ?>
<a href="<?php echo $link_prev;?>">返回</a>
<?php else: ?>
招聘中心升级成功。
<br />
<a href="<?php echo linkwml('recruit', 'candidates');?>">继续</a>
<?php endif; ?>
