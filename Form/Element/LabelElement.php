<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @copyright       Copyright (c) 2007-2010, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         SVN: $Id$
 */

namespace OpenAvanti;

/**
 * 
 *
 * @category    Forms
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/form
 */
class LabelElement extends FormElement
{
    protected $_label = null;
    
    
    /**
     *
     *
     */
    public function __construct($name, $label)
    {
        parent::__construct($name, array());
        
        $this->_label = $label;
        
    } // __construct()
    
    
    /**
     *
     *
     */
    public function render()
    {
        $label = "";
        
        if(class_exists("OpenAvanti\Validation") && isset($this->_name) && 
            Validation::fieldHasErrors($this->_name))
        {
            $this->_attributes["class"] = isset($this->_attributes["class"]) ? 
                $this->_attributes["class"] . " error" : "error";
        }
        
        $atts = $this->generateAttributeString();
        $label = "<label for=\"{$this->_name}\" {$atts}>{$this->_label}</label>";
        
        return $label;
        
    } // render()
    
} // LabelElement()

?>
