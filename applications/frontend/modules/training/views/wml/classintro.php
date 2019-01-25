<?php echo $classinfo['name'];?><br />
金币: -<?php echo $classinfo['require']['gb'];?><br />
<?php echo $effect_text;?>: + <?php echo $classinfo['effect'][$effect];?><br />
冷却: <?php echo $classinfo['time'];?><br />
<a href="<?php echo linkwml('training', 'selectstar', array('class' => $class, 'id' => $class_id, 'cid' => $classroom_id));?>">普通培训(100%)</a><br />
<a href="<?php echo linkwml('training', 'selectstar', array('class' => $class, 'id' => $class_id, 'cid' => $classroom_id, 'enhance' => 1));?>">强化培训(150%)</a><br />
强化培训消耗<?php echo $enhance_price;?>颗钻石。<br />
<br />
<a href="<?php echo $link_prev;?>">返回</a><br />
<a href="<?php echo linkwml('index', 'index');?>">回公司</a>
