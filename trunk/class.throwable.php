<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author			Kristopher Wilson
 * @dependencies 	
 * @copyright		Copyright (c) 2008, Kristopher Wilson
 * @license			http://www.openavanti.com/license
 * @link			http://www.openavanti.com
 * @version			0.6.7-beta
 *
 */
 

	/**
	 *
	 *
	 */	 	 	
	class FileNotFoundException extends Exception{};
	
	
	/**
	 *
	 *
	 */
	class ExtensionNotInstalledException extends Exception{};
	
	
	/**
	 *
	 *
	 */
	class QueryFailedException extends Exception{};
	
	
	/**
	 *
	 *
	 */
	class DatabaseConnectionException extends Exception{};

	/**
	 *
	 *
	 */
	interface Throwable
	{
		// nothing. implementing Throwable is simply a means of loading the exception classes
		
		// something may be added here eventually
	
	}; // Throwable()

?>
