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
     * This exception extends the PHP Exception class and should be thrown when an attempted 
     * connection to a database fails.
     * 
     * See http://www.php.net/Exception for more information.
     */         
    class DatabaseConnectionException extends Exception
    {
    
    } // DatabaseConnectionException()
    
?>