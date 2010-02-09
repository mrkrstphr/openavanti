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
 * FormField for a <textarea> element.
 *
 * @category    Forms
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/form
 */
class TextAreaElement extends LabeledFormElement
{
    
    /**
     * Renders the <textarea> form element as HTML and returns the HTML
     * string.
     * 
     * @return string The HTML of the rendered textarea element
     */
    public function render()
    {
        $html = "<textarea name=\"{$this->_name}\" id=\"{$this->_id}\" " .
            $this->generateAttributeString() . ">" . $this->_value . "</textarea>";
        
        return $html;
        
    } // render()
    
} // TextAreaElement()

?>
