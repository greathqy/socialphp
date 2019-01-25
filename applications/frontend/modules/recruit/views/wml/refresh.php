<?php if (isset($confirmed) && $confirmed): ?>
<?php if (isset($nomoney)): ?>
宝石不足, 请<a href="">充值</a><br />
<a href="<?php echo linkwml('recruit', 'candidates');?>">返回</a>
<?php endif;?>
<?php else: ?>
需要花费 <?php echo $amount;?> 宝石, 重新发布招聘广告, 将出现不同的三个艺人。<br />
<a href="<?php echo linkwml('recruit', 'refresh', array('confirm' => 1));?>">确认刷新</a><br />
<a href="<?php echo linkwml('recruit', 'candidates');?>">返回</a><br />
<a href="<?php echo linkwml('index', 'index');?>">回公司</a>
<?php endif; ?>
