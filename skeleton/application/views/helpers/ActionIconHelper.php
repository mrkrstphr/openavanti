<?php


class ActionIconHelper extends ViewHelper
{

    public function render($caption, $link, $icon)
    {
        echo "<a href=\"{$link}\" title=\"{$caption}\">" . 
		    "<img alt=\"{$caption}\" src=\"{$icon}\" title=\"{$caption}\" /> </a>";
    }

}

?>
