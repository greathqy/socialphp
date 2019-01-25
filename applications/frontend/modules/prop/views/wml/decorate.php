你要把 <a href="<?php echo linkwml('prop', 'dedetail', array('id' => $propinfo['de_id']));?>"><?php echo $propinfo['name'];?></a> 给谁装备? 点击艺人部位装备服饰。<br />
<?php if (!$stars): ?>
你还没有艺人，去<a href="<?php echo linkwml('recruit', 'candidates');?>">招聘</a>。<br />
<?php else: ?>
<?php foreach ($stars as $star): ?>
<?php echo $star['name'];?><br />
<?php if ($propinfo['pos'] == 'top'): ?>
<a href="<?php echo linkwml('prop', 'do_decorate', array('pid' => $propinfo['de_id'], 'sid' => $star['id'], 'pos' => 'top'));?>">上身</a>:
<?php if ($star['equip_detail']['top']): ?>
<?php echo $star['equip_detail']['top']['name'];?>
<?php else: ?>
无
<?php endif; ?>
<br />
<?php elseif ($propinfo['pos'] == 'bottom'): ?>
<a href="<?php echo linkwml('prop', 'do_decorate', array('pid' => $propinfo['de_id'], 'sid' => $star['id'], 'pos' => 'bottom'));?>">下身</a>:
<?php if ($star['equip_detail']['bottom']): ?>
<?php echo $star['equip_detail']['bottom']['name'];?>
<?php else: ?>
无
<?php endif; ?>
<br />
<?php elseif ($propinfo['pos'] == 'd1' || $propinfo['pos'] == 'd2'): ?>
<a href="<?php echo linkwml('prop', 'do_decorate', array('pid' => $propinfo['de_id'], 'sid' => $star['id'], 'pos' => 'd1'));?>">配饰1</a>:
<?php if ($star['equip_detail']['d1']): ?>
<?php echo $star['equip_detail']['d1']['name'];?>
<?php else: ?>
无
<?php endif; ?>
<br />
<a href="<?php echo linkwml('prop', 'do_decorate', array('pid' => $propinfo['de_id'], 'sid' => $star['id'], 'pos' => 'd2'));?>">配饰2</a>:
<?php if ($star['equip_detail']['d2']): ?>
<?php echo $star['equip_detail']['d2']['name'];?>
<?php else: ?>
无
<?php endif; ?>
<br />
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>
<a href="<?php echo $link_prev;?>">返回</a>
