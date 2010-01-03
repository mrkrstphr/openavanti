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
    abstract class InputElement extends LabeledFormElement
    {
        /**
         * Generate an input element for the form. Note that the supplied attributes are not 
         * validated to be valid attributes for the element. Each element provided is added to the 
         * XHTML tag.         
         * 
         * @param array An array of attributes for the HTML element
         * @param bool Controls whether or not to return the HTML, otherwise echo it, default false
         * @return void/string If bReturn is true, returns a string with the XHTML, otherwise void
         */
        public function render()
        {
            $html = "<input name=\"{$this->_name}\" id=\"{$this->_name}\" " .
                $this->generateAttributeString() . " value=\"{$this->_value}\" />";
            
            return $html;
            
        } // render()
        
        
    } // InputElement()

?>
