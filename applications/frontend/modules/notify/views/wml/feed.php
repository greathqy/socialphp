<?php if (!$notifylist): ?>
还没有动态。<br />
<?php else: ?>
<?php foreach ($notifylist as $notify): ?>
[<?php echo date('m-d',$notify['time']);?>] <?php echo $notify['msg'];?><br />
<br />
<?php endforeach; ?>
<br />
<?php echo $pagination;?><br />
<?php endif; ?>
<a href="<?php echo $link_prev;?>">返回</a>