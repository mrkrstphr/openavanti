<?php

    class RoleSearch extends Form
    {
        public function init()
        {
            $this->addElement(new TextElement('search[name]', 'Name:', array(
                'size' => 25, 'maxlength' => 64
            )));
            
            $this->addElement(new SelectElement('search[status]', 'Status:', array(
                'options' => Role::$_statuses
            )));
            
        }
        
    } // RoleSearch()

?>
