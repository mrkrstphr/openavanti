<?php

    class RoleEdit extends Form
    {
        public function init()
        {
            $this->addElement(new HiddenElement('role_id'));

            $this->addElement(new TextElement('name', '* Name:', array(
                'size' => 25, 'maxlength' => 32
            )));

            $this->addElement(new TextElement('permission', '* Permission:', array(
                'size' => 10, 'maxlength' => 6
            )));
        }
        
    } // RoleEdit()

?>
