<?php

class JsonHelper extends ControllerActionHelper
{
    public function process($data)
    {
        echo json_encode($data);
    }
}

?>