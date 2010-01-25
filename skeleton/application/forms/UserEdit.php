<?php

    class UserEdit extends Form
    {
        public function init()
        {
            $this->addElement(new TextElement('first_name', '* First Name:', array(
                'size' => 25, 'maxlength' => 32
            )));
            
            $this->addElement(new TextElement('last_name', '* Last Name:', array(
                'size' => 25, 'maxlength' => 32
            )));
            
            $this->addElement(new TextElement('email_address', '* E-mail:', array(
                'size' => 30, 'maxlength' => 64
            )));
            
            $this->addElement(new TextElement('confirm_email_address', '* Confirm E-mail:', array(
                'size' => 30, 'maxlength' => 64
            )));
            
            $this->addElement(new PasswordElement('password', '* Password:', array(
                'size' => 25, 'maxlength' => 40
            )));
            
            $this->addElement(new PasswordElement('confirm_password', '* Confirm Password:', array(
                'size' => 25, 'maxlength' => 40
            )));
        }
        
    } // UserEdit()

?>
