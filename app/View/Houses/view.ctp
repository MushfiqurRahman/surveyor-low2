<div class="houses view">
<h2><?php  echo __('House');?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($house['House']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Area'); ?></dt>
		<dd>
			<?php echo $this->Html->link($house['Area']['title'], array('controller' => 'areas', 'action' => 'view', $house['Area']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Title'); ?></dt>
		<dd>
			<?php echo h($house['House']['title']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit House'), array('action' => 'edit', $house['House']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete House'), array('action' => 'delete', $house['House']['id']), null, __('Are you sure you want to delete # %s?', $house['House']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Houses'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New House'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Areas'), array('controller' => 'areas', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Area'), array('controller' => 'areas', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Representatives'), array('controller' => 'representatives', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Representative'), array('controller' => 'representatives', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Surveys'), array('controller' => 'surveys', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Survey'), array('controller' => 'surveys', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Representatives');?></h3>
	<?php if (!empty($house['Representative'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('House Id'); ?></th>
		<th><?php echo __('Superviser Id'); ?></th>
		<th><?php echo __('Superviser Name'); ?></th>
		<th><?php echo __('Name'); ?></th>
		<th><?php echo __('Br Code'); ?></th>
		<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($house['Representative'] as $representative): ?>
		<tr>
			<td><?php echo $representative['id'];?></td>
			<td><?php echo $representative['house_id'];?></td>
			<td><?php echo $representative['superviser_id'];?></td>
			<td><?php echo $representative['superviser_name'];?></td>
			<td><?php echo $representative['name'];?></td>
			<td><?php echo $representative['br_code'];?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'representatives', 'action' => 'view', $representative['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'representatives', 'action' => 'edit', $representative['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'representatives', 'action' => 'delete', $representative['id']), null, __('Are you sure you want to delete # %s?', $representative['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Representative'), array('controller' => 'representatives', 'action' => 'add'));?> </li>
		</ul>
	</div>
</div>
<div class="related">
	<h3><?php echo __('Related Surveys');?></h3>
	<?php if (!empty($house['Survey'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Campaign Id'); ?></th>
		<th><?php echo __('House Id'); ?></th>
		<th><?php echo __('Representative Id'); ?></th>
		<th><?php echo __('Rep Phone'); ?></th>
		<th><?php echo __('Mo Log Id'); ?></th>
		<th><?php echo __('Survey Counter'); ?></th>
		<th><?php echo __('Name'); ?></th>
		<th><?php echo __('Phone'); ?></th>
		<th><?php echo __('Age'); ?></th>
		<th><?php echo __('Occupation Id'); ?></th>
		<th><?php echo __('Brand Id'); ?></th>
		<th><?php echo __('Feedback Taken'); ?></th>
		<th><?php echo __('Feedback Skipped'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($house['Survey'] as $survey): ?>
		<tr>
			<td><?php echo $survey['id'];?></td>
			<td><?php echo $survey['campaign_id'];?></td>
			<td><?php echo $survey['house_id'];?></td>
			<td><?php echo $survey['representative_id'];?></td>
			<td><?php echo $survey['rep_phone'];?></td>
			<td><?php echo $survey['mo_log_id'];?></td>
			<td><?php echo $survey['survey_counter'];?></td>
			<td><?php echo $survey['name'];?></td>
			<td><?php echo $survey['phone'];?></td>
			<td><?php echo $survey['age'];?></td>
			<td><?php echo $survey['occupation_id'];?></td>
			<td><?php echo $survey['brand_id'];?></td>
			<td><?php echo $survey['feedback_taken'];?></td>
			<td><?php echo $survey['feedback_skipped'];?></td>
			<td><?php echo $survey['created'];?></td>
			<td><?php echo $survey['modified'];?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'surveys', 'action' => 'view', $survey['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'surveys', 'action' => 'edit', $survey['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'surveys', 'action' => 'delete', $survey['id']), null, __('Are you sure you want to delete # %s?', $survey['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Survey'), array('controller' => 'surveys', 'action' => 'add'));?> </li>
		</ul>
	</div>
</div>
