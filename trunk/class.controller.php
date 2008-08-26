<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author			Kristopher Wilson
 * @dependencies 	FileFunctions
 * @copyright		Copyright (c) 2008, Kristopher Wilson
 * @license			http://www.openavanti.com/license
 * @link			http://www.openavanti.com
 * @version			0.6.7-beta
 *
 */
 
 
	/**
	 * A default controller class to be extended
	 *
	 * @category	Controller
	 * @author		Kristopher Wilson
	 * @link		http://www.openavanti.com/docs/controller
	 */
	class Controller
	{		
		public $aData = array();
		public $sView = "";
		
		public $b404Error = false;
		
		/**
		 * Constructor. Currently does not do anything.		 		 		 		 		 		 		 
		 * 
		 * @returns void 
		 */
		public function __construct()
		{
		
		} // __construct()
				
		
		/**
		 * Every controller must have an index() method defined for default requests to the 
		 * controller that do not define a method. Since it is a requirement for this method to 
		 * exist, it is defined in the parent controller class.	 		 		 		 		 		 		 
		 * 
		 * @returns void 
		 */
		public function index()
		{
			$this->b404Error = true;
				
		} // index()
		
		
		/**
		 *
		 *
		 *
		 */		 		 		 		
		public function Is404Error()
		{
			return( $this->b404Error );
			
		} // Is404Error()
		
		
		/**
		 *
		 *
		 *
		 */	
		public function Set404Error( $bIs404Error = true )
		{
			$this->b404Error = $bIs404Error;
			
		} // Set404Error()
		
		
		/**
		 * Determines whether or not the current HTTP request came via AJAX.	 		 		 		 		 		 
		 * 
		 * @returns boolean True of the request is via AJAX, false otherwise 
		 */
		public function IsAjaxRequest()
		{
			return( Dispatcher::IsAjaxRequest() );
		
		} // IsAjaxRequest()
		
		
		/**
		 *
		 *
		 */
		public function SetHTTPStatus( $iCode )
		{
			if( !headers_sent() )
			{
				header( " ", true, $iCode );
			}
			
		} // SetHTTPStatus()
		
		
		/**
		 *
		 *
		 */		 		 		
		public function AjaxError( $sError, $iResponseCode = 400 )
		{
			$this->SetHTTPStatus( $iResponseCode );
			
			echo $sError;
			
		} // AjaxError()
		
		
		/**
		 *
		 *
		 *
		 */	
		public function RedirectTo( $sURL, $bPermanentRedirect = true )
		{
			header( "Location: {$sURL}", true, $bPermanentRedirect ? 301 : null );
		
		} // RedirectTo()
		
		
		/**
		 *
		 *
		 *
		 */	
		public function SetView( $sView )
		{
			$this->sView = $sView;
		
		} // SetView()
		
		
		/**
		 *
		 *
		 *
		 */	
		public function SetData( $sName, $sValue )
		{
			$this->aData[ $sName ] = $sValue;
			
		} // SetData()
		
		
		/**
		 *
		 *
		 *
		 */	
		public function SetFlash( $sMessage )
		{
			$_SESSION[ "flash" ] = $sMessage;
			
		} // SetFlash()
		
		
		/**
		 *
		 *
		 *
		 */	
		public function GetFlash()
		{
			$sFlash = isset( $_SESSION[ "flash" ] ) ? $_SESSION[ "flash" ] : "";
			
			unset( $_SESSION[ "flash" ] );
			
			return( $sFlash );
			
		} // GetFlash()
	
	} // Controller()

?>
