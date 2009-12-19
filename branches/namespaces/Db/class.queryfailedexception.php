<?php
// $Id$

/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @dependencies    
 * @copyright       Copyright (c) 2008, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.2.0-beta
 *
 */
 
    namespace OpenAvanti\Db;

    /**
     * This exception extends the PHP Exception class and should be thrown when a query fails so 
     * that a developer can properly handle that exception.
     * 
     * See http://www.php.net/Exception for more information.
     */         
    class QueryFailedException extends Exception
    {
    
    } // QueryFailedException()
    
?>