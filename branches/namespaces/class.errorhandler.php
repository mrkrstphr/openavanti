<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @dependencies    
 * @copyright       Copyright (c) 2007-2009, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.2.0-beta
 *
 */

    /**
     * 
     *
     * @category    Database
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/errorhandler
     */
    abstract class ErrorHandler
    {
        const FILE_NOT_FOUND = "file_not_found";
        const CONTROLLER_NOT_FOUND = "controller_not_found";
        const LAYOUT_NOT_FOUND = "layout_not_found";
        const VIEW_NOT_FOUND = "view_not_found";
        
    }

?>
