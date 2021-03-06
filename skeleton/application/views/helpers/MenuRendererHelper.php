<?php


class MenuRendererHelper extends OpenAvanti\ViewHelper
{

    public function render($menuName)
    {
        $menu = OpenAvanti\Registry::retrieve($menuName);
        
        if(is_null($menu) || !is_a($menu, "MenuContainer"))
            return '';
        
        $menuHtml = "<ul>\n";
        
        foreach($menu->getItems() as $menuItem)
        {
            $menuHtml .= "\t<li><a href=\"{$menuItem->url}\">{$menuItem->label}</a></li>\n";
        }
        
        $menuHtml .= "</ul>";
        
        return $menuHtml;
    }

}

?>
