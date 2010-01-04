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
     * FormField for a file <input /> element.
     *
     * @category    Forms
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/form
     */
    class FileElement extends InputElement
    {
        
        /**
         * Sets the type attribute to file. 
         *
         * @returns void
         */
        public function init()
        {
            $this->_attributes["type"] = "file";
            
        } // init()

    } // FileElement()

?>
