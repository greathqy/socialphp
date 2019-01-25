招聘中心 <br />
目前已有 <?php echo $total;?> 位艺人投简历。<br />
<?php if (isset($stars) && $stars): ?>
<?php foreach ($stars as $i => $star): ?>
>><?php echo $star['name']; ?> <br />
<?php echo $star['type_text'];?> (<a href="<?php echo linkwml('tutorial', 'step4_levelintro', array('level' => $star['talent_text']));?>"><?php echo $star['talent_text']?>级</a>) <br />
<a title="查看详情" href="<?php echo linkwml('tutorial', 'step4_stardetail', array('id' => $star['_seq']));?>">查看详情</a><br />
<?php endforeach; ?>
<?php endif; ?>
距离下次刷新: <?php echo $next_refresh;?> <br />
