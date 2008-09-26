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
	 * Contains a set of database results, but is database indepenent, and allows the traversing
	 * of the database records as well as access to the data.	 
	 *
	 * @category	Database
	 * @author		Kristopher Wilson
	 * @link		http://www.openavanti.com/docs/resultset
	 */
	class ResultSet implements Iterator, Countable
	{
		private $oDatabase = null;
		private $rResult = null;
		private $oRecord = null;
      
      	private $bValid = false;
		
		private $iNumRows = 0;
		
		private $iCurrentRow = -1;
		private $aData = array();
		
		
		private $bDebug = false;
		
		
		/**
		 * Constructor. Prepares the result set for traversing	 
		 * 
		 * @argument Database An instance of a database connect
		 * @argument Resource A reference to the database result returned by a query
		 */
		public function __construct( &$oDatabase, &$rResult )
		{
			$this->oDatabase = &$oDatabase;
			$this->rResult = &$rResult;
			
			if( !is_null( $this->rResult ) )
			{
				$this->iNumRows = $this->oDatabase->CountFromResult( $this->rResult );
			}
			
			$this->bValid = $this->Count() != 0;
		
		} // __construct()
	
	
		/**
		 * Returns a copy of the current record	 
		 * 		 
		 * @returns StdClass The ccurrent database result, or null if none
		 */
		public function GetRecord()
		{
			return( $this->Current() );
		
		} // GetRecord()
		

		/**
		 *
		 */
		public function Count()
		{
			$this->Debug( "Count: {$this->iNumRows}" );
			
			return( $this->iNumRows );
            
		} // Count()
		

		/**
		 *
		 */
 		public function Current()
		{
			$this->Debug( "Current: {$this->iCurrentRow}" );
			
			if( isset( $this->aData[ $this->iCurrentRow ] ) )
			{
				return( $this->aData[ $this->iCurrentRow ] );
			}
			else
			{
				return( false );
			}
		
		} // Current()
		

		/**
		 *
		 */
 		public function Key()
		{
			return( $this->iCurrentRow );
		
		} // Key()
		

		/**
		 *
		 */
 		public function Next()
		{
			$this->iCurrentRow++;
			
			$this->Debug( "Next: {$this->iCurrentRow}" );

			//if( !isset( $this->aData[ $this->iCurrentRow ] ) )
			//{
				if( !is_null( $this->rResult ) )
	         	{
	         		$this->Debug( "Fetching row [{$this->iCurrentRow}]" );
					$this->aData[ $this->iCurrentRow ] = 
						$this->oDatabase->PullNextResult( $this->rResult );
					
					if( !$this->aData[ $this->iCurrentRow ] )
					{
	         			$this->Debug( "There is no row [{$this->iCurrentRow}]" );
					}
				}
	         	else
	         	{
					$this->aData[ $this->iCurrentRow ] = null;
	        	}
         	//}
         
			$this->Debug( "<pre>" . print_r( $this->aData, true ) . "</pre>" );
         
			$this->bValid = !is_null( $this->aData[ $this->iCurrentRow ] ) &&
				$this->aData[ $this->iCurrentRow ] !== false;
				
			if( $this->bValid )
			{
				$this->Debug( "Next [{$this->iCurrentRow}] is Valid!" );
			}
		
		} // Next()
		

		/**
		 *
		 */
 		public function Rewind()
		{
			$this->Debug( "Rewind: {$this->iCurrentRow} | 0" );
			
			$this->oDatabase->ResetResult( $this->rResult );
			
			//if( !isset( $this->aData[ 0 ] ) )
			//{
				$this->iCurrentRow = -1;
			/*	$this->Next();			
			}
			else
			{
				$this->iCurrentRow = 0;
			}*/
			
			
			$this->Next();	
			
			$this->bValid = $this->Count() != 0;
			
			//$this->Next();
		
		} // Rewind()
		

		/**
		 *
		 */
 		public function Valid()
		{
			$this->Debug( "Valid: {$this->iCurrentRow}" );
			
			if( $this->bValid )
			{
				$this->Debug( "<pre>" . print_r( $this->aData, true ) . "</pre>" );
			}
			
			return( $this->bValid );
		
		} // Valid()
		
		
		/**
		 *
		 */		 		
		public function Debug( $sMessage )
		{
			if( $this->bDebug )
			{
				echo $sMessage . "<br />";
			}
		}
		
	} // ResultSet()

?>
