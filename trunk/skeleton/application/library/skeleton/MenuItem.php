<?php

class MenuItem
{
    public $label = null;
    public $url = null;
    public $options = array();
    
    public function __construct($label, $url, $options = array())
    {
        $this->label = $label;
        $this->url = $url;
        $this->options = $options;
    }
    
    public function setOptions($options)
    {
        $this->options = $options;
    
    } // setOptions()
    
    
    /**
     *
     *
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        
    } // setOption()
    
    
    /**
     *
     *
     */
    public function getOptions()
    {
        return $this->options;
    
    } // getOptions()
    
    
    /**
     *
     *
     */
    public function setLabel($label)
    {
        $this->label = $label;
        
        return $this;
    
    }
    
    
    /**
     *
     *
     */
    public function getLabel()
    {
        return $this->label;
    
    } // getLabel()
    
    
    /**
     *
     *
     */
    public function setUrl($url)
    {
        $this->url = $url;
        
        return $this;
    
    } // setUrl()
    
    
    /**
     *
     *
     */
    public function getUrl()
    {
        return $this->url;
    
    } // getUrl()


} // MenuItem()

?>