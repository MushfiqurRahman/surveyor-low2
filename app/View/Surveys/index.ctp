<div class="surveys index">
    <?php echo $this->element('search_form');?>
	<h2><?php echo __('Surveys');?></h2>
        
        
    <!-- BEGIN EXAMPLE TABLE PORTLET-->
    <div class="portlet box red">
            <div class="portlet-title">
                    <h4><i class="icon-reorder"></i>Consumer Detail</h4>
                    <div class="tools">
                            <a href="javascript:;" class="collapse"></a>								
                            <a href="javascript:;" class="reload"></a>
                            <a href="javascript:;" class="remove"></a>
                    </div>
            </div>
            <div class="portlet-body">

                    <table class="table table-striped table-bordered" id="sample_1">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Region</th>
                                    <th class="hidden-phone">Area</th>
                                    <th class="hidden-phone">House</th>
                                    <th class="hidden-phone">BR Name</th>
                                    <th class="hidden-phone">BR Code</th>
                                    <th class="hidden-phone">SUP Name</th>
                                    <th class="hidden-phone">Consumer Name</th>
                                    <th class="hidden-phone">Phone No</th>
                                    <th class="hidden-phone">AGE</th>                                                    
                                    <th class="hidden-phone">Occupation</th>
                                    <th class="hidden-phone">Brand</th>
                                    <th class="hidden-phone">Permission Slip Date</th>
                                    <th class="hidden-phone">Is Correct</th>
                                    <th class="hidden-phone">Date</th>
                                    <th class="hidden-phone">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                    <?php
                    if( isset($surveys) && !empty($surveys) ){
//                        pr($surveys);
                        foreach($surveys as $srv){
                ?>
                            <tr class="odd gradeX">
                                <td><?php echo $srv['Survey']['id'];?></td>
                                <td><?php echo $srv['Representative']['House']['Area']['Region']['title'];?></td>
                                <td class="hidden-phone"><?php echo $srv['Representative']['House']['Area']['title'];?></td>
                                <td class="hidden-phone"><?php echo $srv['Representative']['House']['title'];?></td>
                                <td class="center hidden-phone"><?php echo $srv['Representative']['name'];?></td>
                                <td class="center hidden-phone"><?php echo $srv['Representative']['br_code'];?></td>
                                <td class="hidden-phone"><?php echo $srv['Representative']['superviser_name'];?></td>
                                <td class="hidden-phone"><?php echo $srv['Survey']['name'];?></td>
                                <td class="hidden-phone"><?php echo $srv['Survey']['phone'];?></td>
                                <td class="center hidden-phone"><?php echo $srv['Survey']['age'];?></td>                                                
                                <td class="hidden-phone"><?php echo $srv['Occupation']['title'];?></td>
                                <td class="hidden-phone"><?php echo $srv['Brand']['title'];?></td>
                                <td class="hidden-phone"><?php echo $srv['Survey']['permission_slip_date'];?></td>
                                <td class="hidden-phone">
                                    <?php
                                        if($srv['Survey']['is_sup']){
                                           echo $srv['Survey']['is_right']?'Yes':'No';
                                        }
                                    ?>
                                </td>
                                <td class="hidden-phone"><?php echo $srv['Survey']['created'];?></td>
                                <td class="hidden-phone">
                                    <?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $srv['Survey']['id'])); ?>
                                    <?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $srv['Survey']['id']), null, __('Are you sure you want to delete # %s?', $srv['Survey']['id'])); ?>
                                </td>
                            </tr>

                <?php

                        }                                                                    
                    }else{
                        echo 'Nothing found';
                    }
                ?>
                            </tbody>
                    </table>
        <div class="paging">
	<?php
            echo $this->Paginator->counter(array(
            'format' => __('Showing {:current} records out of {:count} total')
            ));
            echo '<br/>';
		echo $this->Paginator->prev('< ' . __('previous | '), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ' | '));
		echo $this->Paginator->next(__(' | next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
        </div>
    </div>
</div>
