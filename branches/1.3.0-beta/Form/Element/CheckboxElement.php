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

require_once __DIR__ . "/InputElement.php";

use \OpenAvanti\Form\Element;

/**
 * FormField for a checkbox <input /> element.
 *
 * @category    Forms
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/form
 */
class CheckboxElement extends InputElement
{
    
    /**
     * Sets the type attribute to text. 
     *
     * @returns void
     */
    public function init()
    {
        $this->_attributes["type"] = "checkbox";
        
    } // init()

} // CheckboxElement()

?>
