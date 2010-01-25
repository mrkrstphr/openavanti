<?php

    class LoginForm extends Form
    {
        public function init()
        {
            $this->addElement(new TextElement('email', 'Email Address:', array(
                'size' => 40, 'maxlength' => 64
            )));
            
            $this->addElement(new PasswordElement('password', 'Password:', array(
                'size' => 30, 'maxlength' => 32
            )));
        }
        
    } // LoginForm()

?>