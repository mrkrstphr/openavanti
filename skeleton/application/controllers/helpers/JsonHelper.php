<?php

class JsonHelper extends OpenAvanti\ControllerActionHelper
{
    public function process($data)
    {
        echo json_encode($data);
    }
}

?>
