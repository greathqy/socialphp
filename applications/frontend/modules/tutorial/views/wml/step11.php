培训成功! 
<?php if(isset($effect)):?>
<a href="<?php echo linkwml('tutorial', 'step4_stardetail');?>"><?php echo $star['name'];?></a>
<?php foreach ($effect as $key => $conf): ?>
<?php echo $conf['name'];?>+<?php echo $conf['amount'];?>&nbsp;&nbsp;
<?php endforeach; ?>
<br />
冷却时间: <?php echo $time;?>
<?php endif;?>
<br />
<?php $this->include_partial('_prize_alert');?>
你的艺人还缺少一些合适的服饰，去<a href="<?php echo linkwml('tutorial', 'step12');?>">服饰店</a>购买吧。
