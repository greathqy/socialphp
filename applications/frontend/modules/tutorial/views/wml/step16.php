成功为<?php echo $star['name'];?>装备上<?php echo $prop['name'];?>, <br />
<?php echo $star['name'];?> 
<?php foreach ($prop['effect'] as $effect => $num): ?>
<?php if ($effect == 'charm'): ?>
魅力+<?php echo $num;?>&nbsp;&nbsp;
<?php elseif ($effect == 'sing'): ?>
歌艺+<?php echo $num;?>&nbsp;&nbsp;
<?php elseif ($effect == 'acting'): ?>
演技+<?php echo $num;?>&nbsp;&nbsp;
<?php endif; ?>
<?php endforeach; ?>
<br />
<?php $this->include_partial('_prize_alert');?>
<a href="<?php echo linkwml('tutorial', 'step17');?>"><?php echo $star['name'];?>现在准备就绪了, 安排他去打工吧。</a>
