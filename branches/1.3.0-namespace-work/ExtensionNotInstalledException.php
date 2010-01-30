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
 * This exception extends the PHP Exception class and should be thrown when a required PHP 
 * extension is not installed.
 * 
 * See http://www.php.net/Exception for more information.
 */         
class ExtensionNotInstalledException extends Exception
{

} // DatabaseConnectionException()

?>