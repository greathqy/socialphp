<?php if (isset($full)): ?>
你下面已经养了那么多人, 还是先<a href="<?php echo linkwml('star', 'index');?>">整理</a>一下你旗下的艺人吧。<br />
<a href="<?php echo linkwml('recruit', 'candidates');?>">返回</a>
<?php elseif (isset($nomoney)): ?>
你的钱不够，无法雇佣这个身价的明星!<br />
<a href="<?php echo linkwml('recruit', 'candidates');?>">返回</a>
<?php else: ?>
你成功雇佣 <?php echo $star['name'];?><br />
<a href="<?php echo linkwml('star', 'cname', array('id' => $star_id));?>">修改艺名</a> <a href="<?php echo linkwml('star', 'index');?>">沿用本名</a>
免费提供一次改名机会。<br />
<a href="<?php echo linkwml('star', 'stardetail', array('id' => $star_id));?>">看他属性</a><br />
<a href="<?php echo linkwml('star', 'index');?>">艺人列表</a><br />
<a href="<?php echo linkwml('recruit', 'candidates');?>">返回</a><br />
<a href="<?php echo linkwml('index', 'index');?>">回公司</a>
<?php endif; ?>
