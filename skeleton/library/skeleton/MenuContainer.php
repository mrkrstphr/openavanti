<?php

class MenuContainer
{
    protected $_menuItems = array();
    
    /**
     *
     *
     */
    public function addItem($item)
    {
        $menuItem = null;
        
        if(is_a($item, "MenuItem"))
        {
            $menuItem = $item;
        }
        else if(is_array($item))
        {
            $menuItem = new MenuItem($item['label'], $item['url']);
            
            if(isset($item['options']))
            {
                $menuItem->setOptions($item['options']);
            }
        }
        
        $this->_menuItems[] = $menuItem;
        
    } // addItem()
    
    
    /**
     *
     *
     */
    public function addItems($items)
    {
        foreach($items as $item)
        {
            $this->addItem($item);
        }
        
    } // addItems()
    
    
    /**
     *
     *
     */
    public function getItems()
    {
        return $this->_menuItems;
    
    } // getItems()

    
} // MenuContainer()

?>