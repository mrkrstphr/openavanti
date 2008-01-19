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
 * @version			0.6.4-alpha
 *
 */

	/**
	 * The request object stores information about the web request and how it was routed, as well
     * as stores data setup by the controller, including view file and loaded data.
	 *
	 * @category	Database
	 * @author		Kristopher Wilson
	 * @link		http://www.openavanti.com/docs/request
     */
    class Request
	{
		public $sURI = null;
		public $sRewrittenURI = null;
		
		public $sControllerName = null;
		public $oController = null;
		
		public $sAction = null;
		
		public $aArguments = array();
		
		public $aLoadedData = array();
		
		public $sView = null;
		
	}; // Request()

?>
