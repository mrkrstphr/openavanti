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
 * @link				http://www.openavanti.com
 * @version			0.6.4-alpha
 *
 */
 
 
	/**
	 * A default controller class to be extended
	 *
	 * @category	Controller
	 * @author		Kristopher Wilson
	 * @link			http://www.openavanti.com/docs/controller
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
		
		
		public function Is404Error()
		{
			return( $this->b404Error );
			
		} // Is404Error()
						
		
		/**
		 * Determines whether or not the current HTTP request came via AJAX.	 		 		 		 		 		 
		 * 
		 * @returns boolean True of the request is via AJAX, false otherwise 
		 */
		public function IsAjaxRequest()
		{
			return( Dispatcher::IsAjaxRequest() );
		
		} // IsAjaxRequest()
		
		
		public function RedirectTo( $sURL, $bPermanentRedirect = true )
		{
			header( "Location: {$sURL}", true, $bPermanentRedirect ? 301 : null );
		
		} // RedirectTo()
		
		
		public function SetView( $sView )
		{
			$this->sView = $sView;
		
		} // SetView()
		
		
		public function SetData( $sName, $sValue )
		{
			$this->aData[ $sName ] = $sValue;
			
		} // SetData()
	
	} // Controller()

?>
