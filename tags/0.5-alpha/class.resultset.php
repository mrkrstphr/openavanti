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
 * @link				http://www.openavanti.com
 * @version			0.05a
 *
 */
 
 
	/**
	 * Contains a set of database results, but is database indepenent, and allows the traversing
	 * of the database records as well as access to the data.	 
	 *
	 * @category	Database
	 * @author		Kristopher Wilson
	 * @link			http://www.openavanti.com/docs/resultset
	 */
	class ResultSet implements Iterator
	{
		private $oDatabase = null;
		private $rResult = null;
      private $oRecord = null;
		
		
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
		
		} // __construct()
	
	
		/**
		 * Returns a copy of the current record	 
		 * 		 
		 * @returns StdClass The ccurrent database result, or null if none
		 */
		public function GetRecord()
		{
			return( $this->oRecord );
		
		} // GetRecord()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public function Count()
		{
         if( $this->rResult )
         {
             return( pg_num_rows( $this->rResult ) );
         }
         else
         {
             return( 0 );
         }
            
		} // Count()
	
	
		////////////////////////////////////////////////////////////////////////////////////////////
 		public function Current()
		{
			return( $this->oRecord );
		
		} // Current()
	
	
		////////////////////////////////////////////////////////////////////////////////////////////
 		public function Key()
		{
			return( null );
		
		} // Key()
	
	
		////////////////////////////////////////////////////////////////////////////////////////////
 		public function Next()
		{
         if( $this->rResult )
         {
             $this->oRecord = pg_fetch_object( $this->rResult );
         }
         else
         {
             $this->oRecord = null;
         }

         return( $this->oRecord );
		
		} // Next()
	
	
		////////////////////////////////////////////////////////////////////////////////////////////
 		public function Rewind()
		{
			pg_result_seek( $this->rResult, 0 );
			
			$oRecord = $this->Next();
			
			return( $oRecord );
		
		} // Rewind()
	
	
		////////////////////////////////////////////////////////////////////////////////////////////
 		public function Valid()
		{
			return( $this->oRecord );
		
		} // Valid()
		
	
	} // ResultSet()

?>
