<?php if (isset($failed)): ?>
钻石不足, 插班失败，请<a href="">充值</a>。<br />
<?php elseif (isset($succ)): ?>
插班成功。<br />
<a href="<?php echo linkwml('training', 'attend', array('id' => $classroom_id));?>">继续</a>
<?php else: ?>
培训<?php echo $classroom_id;?>班<br />
该班名额已满，需等待<?php echo $training['classes'][$classroom_id]['still'];?>。
插班需要花费<?php $price?>钻石。<br />
<a href="<?php echo linkwml('training', 'bruteforce', array('id' => $classroom_id, 'confirm' => 1));?>">确认插班</a><br />
<a href="<?php echo $link_prev;?>">返回</a><br />
<a href="<?php echo linkwml('index', 'index');?>">回公司</a>
<?php endif; ?>
