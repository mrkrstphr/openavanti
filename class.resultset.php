<?php

	////////////////////////////////////////////////////////////////////////////////////////////////
	class ResultSet implements Iterator
	{
		private $oDatabase = null;
		private $rResult = null;
      private $oRecord = null;
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public function __construct( &$oDatabase, &$rResult )
		{
			$this->oDatabase = &$oDatabase;
			$this->rResult = &$rResult;
		
		} // __construct()
	
		
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
