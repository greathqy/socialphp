招聘中心 <br />
目前已有 <?php echo $total;?> 位艺人投简历。<br />
<?php if (isset($stars) && $stars): ?>
<?php foreach ($stars as $i => $star): ?>
>><?php echo $star['name']; ?> <br />
<?php echo $star['type_text'];?> (<a href="<?php echo linkwml('star', 'levelintro', array('level' => $star['talent_text']));?>"><?php echo $star['talent_text']?>级</a>) <br />
<a title="查看详情" href="<?php echo linkwml('recruit', 'stardetail', array('id' => $star['_seq']));?>">查看详情</a><br />
<?php endforeach; ?>
<?php endif; ?>
距离下次刷新: <?php echo $next_refresh;?> <br />
<a title="手动刷新" href="<?php echo linkwml('recruit', 'refresh');?>">手动刷新</a>|
<a title="升级" href="<?php echo linkwml('recruit', 'upgrade');?>">升级</a>|
<a title="离开此地" href="<?php echo linkwml('index', 'street');?>">离开此地</a>
<br />
<a title="回公司" href="<?php echo linkwml('index', 'index');?>">回公司</a>
