现在招聘中心Lv1，升级后可招募更高天赋的艺人。<br />
需求<br /> 
公司等级: Lv<?php echo $required['level'];?><br />
道具:<br />
<?php foreach ($required['props'] as $pid => $item): ?>
<?php echo $item['name'];?> x <?php echo $item['num'];?>&nbsp;&nbsp;
<?php endforeach; ?>
<br />
<br />
<a href="<?php echo linkwml('recruit', 'doupgrade');?>">确认升级</a><br />
<a href="<?php echo $link_prev;?>">返回</a><br />
<a href="<?php echo linkwml('index', 'index');?>">回公司</a>
