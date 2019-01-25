选谁培训<?php echo $class['name'];?>?<br />
<a href="<?php echo linkwml('tutorial', 'step11', array('type' => $type));?>"><?php echo $star['name'];?></a><br />
<?php if (isset($class['effect']['charm'])): ?>
魅力: <?php echo $star['attrs']['charm'];?><br />
<?php endif; ?>
<?php if (isset($class['effect']['sing'])): ?>
歌艺: <?php echo $star['attrs']['sing'];?><br />
<?php endif; ?>
<?php if (isset($class['effect']['acting'])): ?>
演技: <?php echo $star['attrs']['acting'];?><br />
<?php endif; ?>
<?php echo $star['type_text'];?><br />
注意: 如果培训项目和艺人先天属性不符, 培训效果会有折损。<br />
<a href="<?php echo $link_prev;?>">返回</a>
