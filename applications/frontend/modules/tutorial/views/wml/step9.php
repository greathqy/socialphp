请选择培训项目:<br />
<?php if ($class == 'sing'): ?>
歌艺|
<?php else: ?>
<a href="<?php echo linkwml('tutorial', 'step9', array('type' => 2));?>">歌艺</a>|
<?php endif; ?>
<?php if ($class == 'charm'): ?>
魅力|
<?php else: ?>
<a href="<?php echo linkwml('tutorial', 'step9', array('type' => 3));?>">魅力</a>|
<?php endif; ?>
<?php if ($class == 'acting'): ?>
演技
<?php else: ?>
<a href="<?php echo linkwml('tutorial', 'step9', array('type' => 1));?>">演技</a>
<?php endif; ?>
<br />
<?php foreach ($classes as $cid => $conf): ?>
<?php echo $conf['name'];?><br />
金钱: -<?php echo isset($conf['require']['gb']) ? $conf['require']['gb'] : 0;?>金币<br />
<?php if ($class == 'sing'): ?>
歌艺: +<?php echo isset($conf['effect'][$class]) ? $conf['effect'][$class] : 0;?><br /> 
<?php elseif ($class == 'charm'): ?>
魅力: +<?php echo isset($conf['effect'][$class]) ? $conf['effect'][$class] : 0;?><br />
<?php elseif ($class == 'acting'): ?>
演技: +<?php echo isset($conf['effect'][$class]) ? $conf['effect'][$class] : 0;?><br />
<?php endif; ?>
冷却: <?php echo $conf['time'];?>分钟<br />
<?php endforeach;?>
<br />
<a href="<?php echo linkwml('tutorial', 'step10', array('class' => $class));?>">培训</a><br />
注意：如果培训项目和艺人先天属性不符，培训效果会有折损。<br />
