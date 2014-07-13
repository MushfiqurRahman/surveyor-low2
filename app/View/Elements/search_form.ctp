<h3>Search Survey</h3>
<?php 
    echo $this->Form->create('Survey',array(
        'action' => 'search_result',
        'type' => 'get'
    ));
    echo $this->Form->input('br_code', array('type' => 'text', 
        'placeholder' => 'Search By BR Code', 'required' => true));
    echo $this->Form->input('is_sup', array('type' => 'select', 'label' => 'Is SUP Survey?',
        'options' => array(0 => 'No', 1 => 'Yes')));
    
    echo $this->Form->input('is_br', array('type' => 'select', 'label' => 'Is BR Survey?',
        'options' => array(0 => 'No', 1 => 'Yes')));
    echo $this->Form->end('Search');
?>