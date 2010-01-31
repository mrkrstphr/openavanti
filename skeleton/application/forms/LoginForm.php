<?php

    class LoginForm extends OpenAvanti\Form
    {
        public function init()
        {
            $this->addElement(new OpenAvanti\TextElement('email', 'Email Address:', array(
                'size' => 40, 'maxlength' => 64
            )));
            
            $this->addElement(new OpenAvanti\PasswordElement('password', 'Password:', array(
                'size' => 30, 'maxlength' => 32
            )));
        }
        
    } // LoginForm()

?>
