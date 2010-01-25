<?php

    class UserSearch extends Form
    {
        public function init()
        {
            $this->addElement(new TextElement('search[last_name]', 'Last Name:', array(
                'size' => 25, 'maxlength' => 64
            )));
            
            $this->addElement(new TextElement('search[email_address]', 'E-mail:', array(
                'size' => 25, 'maxlength' => 64
            )));
            
            /*$this->addElement(new SelectElement('search[status]', 'Status:', array(
                'options' => User::$_statuses
            )));*/
            
        }
        
    } // UserSearch()

?>