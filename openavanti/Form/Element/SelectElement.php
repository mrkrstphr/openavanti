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
 * @version         1.3.0-beta
 */


/**
 * FormField for a <select> element.
 *
 * @category    Forms
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/form
 */
class SelectElement extends LabeledFormElement
{
    /**
     * @var string Stores the default selected option 
     */
    protected $_default = "";
    
    /**
     * @var bool Should a blank option be added when no value is selected?
     */
    protected $_addBlankWhenEmpty = false;
    
    /**
     * @var string When $_addBlankWhenEmpty is true, controls the label of
     *      the blank option
     */
    protected $_autoBlankLabel = "";
    
    /**
     * Ensures that the options are stored within the attributes array
     *
     * @returns void
     */
    public function init()
    {
        if(!isset($this->_attributes['options']))
        {
            $this->_attributes['options'] = array();    
        }
        
    } // init()
    
    
    /**
     * Renders the <select> form element as HTML and returns the HTML
     * string. Options are specified by an 'options' array stored in the
     * attributes array when constructing.
     *
     * @return string The HTML of the rendered select element
     */
    public function render()
    {
        $html = "<select name=\"{$this->_name}\" id=\"{$this->_id}\" " .
            $this->generateAttributeString() . ">\n";
        
        if($this->_addBlankWhenEmpty == true)
        {
            $blank = array('' => $this->_autoBlankLabel);
            $this->_attributes['options'] = $blank + $this->_attributes['options'];
        }
        
        foreach($this->_attributes['options'] as $key => $value)
        {
            $selected = $key == $this->_value || $key == $this->_default ?
                " selected=\"selected\"" : "";
            
            $html .= "\t<option value=\"{$key}\"{$selected}>{$value}</option>\n";
        }
        
        
        $html .= "</select>";
        
        return $html;
        
    } // render()
    
    
    /**
     * Allows for adding a blank <option> to the <select> when no selection
     * is made. The benefit of this is to force the user to make a
     * selection, rather than overlooking the field and leaving the default.
     *
     * The label for this option can be specified using setAutoBlankLabel()
     *
     * @param bool $addBlank True if a blank option should be added, false
     *      otherwise. Default: true
     * @return SelectElement This object, to allow for chaning
     */
    public function addBlankWhenEmpty($addBlank = true)
    {
        $this->_addBlankWhenEmpty = $addBlank;
        
        return $this;
        
    } // addBlankWhenEmpty()
    
    
    /**
     * Controls the label of the blank <option> automatically added when
     * no <option> is selected. This behavior is controlled by the
     * addBlankWhenEmpty() method.
     *
     * @param string $label The label to use on the blank element
     * @return SelectElement This object, to allow for chaning
     */
    public function setAutoBlankLabel($label)
    {
        $this->_autoBlankLabel = $label;
        
        return $this;
        
    } // setAutoBlankLabel()
    
} // SelectElement()

?>
