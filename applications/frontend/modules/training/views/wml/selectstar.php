选谁培训<a href="<?php echo linkwml('training', 'intro', array('id' => $class_id));?>"><?php $classinfo['name'];?></a>?<br />
<?php if (!$stars): ?>
你还没有明星，去<a href="<?php echo linkwml('recruit', 'candidates');?>">招募</a>吧<br />
<?php else: ?>
<?php foreach ($stars as $star): ?>
<a href="<?php echo linkwml('training', 'start', array('sid' => $star['id'], 'cid' => $class_id, 'rid' => $classroom_id, 'enhance' => $enhanced));?>"><?php echo $star['name'];?></a><br />
<?php echo $effect_text;?>: <?php echo $star['attrs'][$effect];?><br />
<?php echo $star['type_text'];?><br />
<?php endforeach; ?>
注意: 如果培训项目和艺人先天属性不符，培训效果会有折损。<br />
<?php endif; ?>
<a href="<?php echo $link_prev;?>">返回</a><br />
<a href="<?php echo linkwml('index', 'index');?>">回公司</a>
