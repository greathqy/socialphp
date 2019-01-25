请选择培训项目:<br />
<?php if ($class == 'sing'): ?>
歌艺|
<?php else: ?>
<a href="<?php echo linkwml('training', 'attend', array('id' => $class_id, 'class' => 'sing'));?>">歌艺</a>|
<?php endif; ?>
<?php if ($class == 'charm'): ?>
魅力|
<?php else: ?>
<a href="<?php echo linkwml('training', 'attend', array('id' => $class_id, 'class' => 'charm'));?>">魅力</a>|
<?php endif; ?>
<?php if ($class == 'acting'): ?>
演技|
<?php else: ?>
<a href="<?php echo linkwml('training', 'attend', array('id' => $class_id, 'class' => 'acting'));?>">演技</a>|
<?php endif; ?>
<br />
<?php if (!$classes): ?>
你没有培训项目可以参加。<br />
<?php else: ?>
<?php foreach ($classes as $cid => $item): ?>
<a href="<?php echo linkwml('training', 'classintro', array('class' => $class, 'cid' =>$class_id, 'id' => $cid));?>"><?php echo $item['name'];?></a><br />
<?php endforeach; ?>
注意：如果培训项目和艺人先天属性不符，培训效果会有折损。<br />
<?php endif; ?>
<a href="<?php echo $link_prev;?>">返回</a><br />
<a href="<?php echo linkwml('index', 'index');?>">回公司</a>
