<?php

    class UserSearch extends OpenAvanti\Form
    {
        public function init()
        {
            $this->addElement(new OpenAvanti\TextElement('search[last_name]', 'Last Name:', array(
                'size' => 25, 'maxlength' => 64
            )));
            
            $this->addElement(new OpenAvanti\TextElement('search[email_address]', 'E-mail:', array(
                'size' => 25, 'maxlength' => 64
            )));
            
            $this->addElement(new OpenAvanti\SelectElement('search[status]', 'Status:', array(
                'options' => User::$_statuses
            )));
            
        }
        
    } // UserSearch()

?>
