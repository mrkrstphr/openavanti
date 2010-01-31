<?php

    class RoleSearch extends OpenAvanti\Form
    {
        public function init()
        {
            $this->addElement(new OpenAvanti\TextElement('search[name]', 'Name:', array(
                'size' => 25, 'maxlength' => 64
            )));
            
            $this->addElement(new OpenAvanti\SelectElement('search[status]', 'Status:', array(
                'options' => Role::$_statuses
            )));
            
        }
        
    } // RoleSearch()

?>
