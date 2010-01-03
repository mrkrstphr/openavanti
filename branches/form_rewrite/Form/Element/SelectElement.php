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
     * 
     *
     * @category    Forms
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/form
     */
    class SelectElement extends LabeledFormElement
    {
        protected $_default = "";
        protected $_addBlankWhenEmpty = false;
        protected $_autoBlankLabel = "";
        
        /**
         *
         *
         */
        public function init()
        {
            if(!isset($this->_attributes['options']))
            {
                $this->_attributes['options'] = array();    
            }
            
        } // init()
        
        
        /**
         * Generate a select element for the form. Note that the supplied attributes are not 
         * validated to be valid attributes for the element. Each element provided is added to the 
         * XHTML tag.
         * 
         * The options are specified by aAttributes[ options ] as an array of key => values to
         * display in the select         
         *               
         * The default (selected) attribute is controlled by aAttributes[ default ], which should
         * match a valid key in aAttributes[ options ]                         
         * 
         * @param array An array of attributes for the HTML element
         * @param bool Controls whether or not to return the HTML, otherwise echo it, default false
         * @return void/string If bReturn is true, returns a string with the XHTML, otherwise void
         */
        public function render()
        {
            $html = "<select name=\"{$this->_name}\" id=\"{$this->_name}\" " .
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
         *
         *
         */
        public function addBlankWhenEmpty($addBlank = true)
        {
            $this->_addBlankWhenEmpty = $addBlank;
            
        } // addBlankWhenEmpty()
        
        
        /**
         *
         *
         */
        public function setAutoBlankLabel($label)
        {
            $this->_autoBlankLabel = $label;
            
        } // setAutoBlankLabel()
        
    } // SelectElement()

?>
