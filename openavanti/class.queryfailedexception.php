<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @copyright       Copyright (c) 2007-2009, Kristopher Wilson
 * @package         openavanti
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 */
 

    /**
     * This exception extends the PHP Exception class and should be thrown when a query fails so 
     * that a developer can properly handle that exception.
     * 
     * @author      Kristopher Wilson
     * @package     openavanti
     * @see         http://www.php.net/Exception
     * @link        http://www.openavanti.com/documentation/docs/1.0.3/QueryFailedException
     */           
    class QueryFailedException extends Exception
    {
    
    } // QueryFailedException()
    
?>
