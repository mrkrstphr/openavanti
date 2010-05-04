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

/**
 * FormField for a password <input /> element.
 *
 * @category    Forms
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/form
 */
class PasswordElement extends InputElement
{
    
    /**
     * Sets the type attribute to password. 
     *
     * @returns void
     */
    public function init()
    {
        $this->_attributes["type"] = "password";
        
    } // init()


    /**
     * Override the parent setValue() method to prevent a value being set to 
     * a password field. This is done for security reasons to prevent others
     * from spying on a password value when the user is away from the computer.
     *
     * @param string $value The value to propogate (ignored)
     */
    public function setValue($value)
    {
        // set value on the password field is not allowed for security reasons

    } // setValue()

} // PasswordElement()

?>
