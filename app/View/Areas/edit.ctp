<div class="areas form">
<?php echo $this->Form->create('Area'); ?>
	<fieldset>
		<legend><?php echo __('Edit Area'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('region_id');
		echo $this->Form->input('title', array('required' => true));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Area.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Area.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Areas'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Regions'), array('controller' => 'regions', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('List Houses'), array('controller' => 'houses', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New House'), array('controller' => 'houses', 'action' => 'add')); ?> </li>
	</ul>
</div>
