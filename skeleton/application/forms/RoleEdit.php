<?php

    class RoleEdit extends OpenAvanti\Form
    {
        public function init()
        {
            $this->addElement(new OpenAvanti\HiddenElement('role_id'));

            $this->addElement(new OpenAvanti\TextElement('name', '* Name:', array(
                'size' => 25, 'maxlength' => 32
            )));

            $this->addElement(new OpenAvanti\TextElement('permission', '* Permission:', array(
                'size' => 10, 'maxlength' => 6
            )));
        }
        
    } // RoleEdit()

?>
