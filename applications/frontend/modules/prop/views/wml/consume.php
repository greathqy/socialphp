<?php $this->include_partial('_prop_navheader');?>
<a href="<?php echo linkwml('prop', 'detail', array('id' => $propinfo['id']));?>"><?php echo $propinfo['name'];?></a>
&nbsp;&nbsp;X <?php echo $count;?>
<br />
道具说明:<br />
<?php echo $propinfo['desc'];?><br />
<a href="<?php echo linkwml('prop', 'selectstar', array('id' => $propinfo['id']));?>">使用</a><br />
<a href="<?php echo $link_prev;?>">返回</a>
