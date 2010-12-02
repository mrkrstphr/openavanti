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

namespace OpenAvanti\Form\Element;

require_once __DIR__ . "/Labeled.php";

/**
 * An abstract implementation of LabeledFormElement that encapsulates any
 * <input /> element. This class is meant to be inherited by the various
 * different types of <input /> elements. This implementation takes care of
 * the default rendering of any <input /> element.
 *
 * @category    Forms
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/form
 */
abstract class Input extends Labeled
{
    /**
     * Renders the form element as HTML and returns the HTML string
     *
     * @return string The HTML of the rendered form element
     */
    public function render()
    {
        $html = "<input name=\"{$this->_name}\" id=\"{$this->_id}\" " .
            $this->generateAttributeString() . " value=\"{$this->_value}\" />";
        
        return $html;
    }
    
}

