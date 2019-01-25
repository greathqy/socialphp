<?php if (!$can_upgrade): ?>
你的培训中心已经升级到最高级，不能再升级了。<br />
<a href="<?php echo $link_prev;?>">返回</a>
<?php else: ?>
现在培训中心Lv<?php echo $cur_level;?>，升级之后可以提供更好的培训项目。<br />
要求:<br />
公司等级: Lv<?php echo $required['level'];?><br />
道具:
<?php foreach ($required['props'] as $pid => $prop): ?>
<?php echo $prop['name'];?> x <?php echo $prop['num'];?>&nbsp;&nbsp;
<?php endforeach; ?>
<br />
<a href="<?php echo linkwml('training', 'doupgrade');?>">确认升级</a><br />
<a href="<?php echo $link_prev;?>">返回</a>
<?php endif; ?>
