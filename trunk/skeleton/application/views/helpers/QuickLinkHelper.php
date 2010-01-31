<?php


class QuickLinkHelper extends OpenAvanti\ViewHelper
{
    // Icon constants
    const IconSearch = '/images/icons/silk/magnifier.png';
    const IconAdd = '/images/icons/silk/add.png';
    const IconEdit = '/images/icons/silk/page_white_edit.png';

    public function render($link, $caption, $icon)
    {
        echo "<div class=\"quick-link\">\n" .
            "<a href=\"{$link}\" title=\"{$caption}\">" . 
		    "<img class=\"icon\" alt=\"{$caption}\" src=\"{$icon}\" title=\"{$caption}\" /></a>\n" .
            "<a href=\"{$link}\" title=\"{$caption}\">{$caption}</a>\n" .
            "</div>";
    }

}

?>
