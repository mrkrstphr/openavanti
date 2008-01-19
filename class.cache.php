<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author			Kristopher Wilson
 * @dependencies 	Caching
 * @copyright		Copyright (c) 2008, Kristopher Wilson
 * @license			http://www.openavanti.com/license
 * @link				http://www.openavanti.com
 * @version			0.6.4-alpha
 *
 */
 
 
	/**
	 * A class to handle content cached content
	 *
	 * @category	Controller
	 * @author		Kristopher Wilson
	 * @link			http://www.openavanti.com/docs/cache
	 */
	class Cache implements Throwable
	{
		private $sFileName = null;
		private $iLastModified = null;
		private $iCreated = null;
		
		public function __construct()
		{
		
		} // __construct()
		
		
		public function __get( $sVar )
		{
			if( isset( $this->$sVar ) )
			{
				return( $this->$sVar );
			}
			
			throw new Exception( "Cache::{$sVar} does not exist" );
		
		} // __get()		
		
		
		public function Exists( $sCacheFileName )
		{
		
		} // Exists()
		
		
		public function Open( $sCacheFileName )
		{
		
		} // Open()
		
		
		public function Create( $sCacheFileName, $sCacheContents )
		{
		
		} // Create()		
	
	
		public function Close()
		{
		
		} // Close()
	
	}; // Cache()

?>
