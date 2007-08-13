<?php

    ///////////////////////////////////////////////////////////////////////////////////////////////
    class Query
    //
    // Description:
    //
    //
    {
        private $oDB = null;
        private $rResult = null;
        private $oRecord = null;

        ///////////////////////////////////////////////////////////////////////////////////////////
        public function __construct( $oDB )
        //
        // Description:
        //      Constructor()
        //
        {
            $this->oDB = &$oDB;
        
        } // __construct()

        
        ///////////////////////////////////////////////////////////////////////////////////////////
        public function Execute( $sSQL )
        //
        // Description:
        //      Executes the supplied query and stores the result in rResult
        //
        {
            $this->rResult = pg_query( $sSQL );

				if( !$this->rResult )
				{
					return( false );
				}

            return( true );
        
        } // Ex()


        ///////////////////////////////////////////////////////////////////////////////////////////
        public function RowCount()
        //
        // Description:
        //      Returns the number of rows in the current query result (if any)
        //
        {
            if( $this->rResult )
            {
                return( pg_num_rows( $this->rResult ) );
            }
            else
            {
                return( 0 );
            }
        
        } // RowCount()


        ///////////////////////////////////////////////////////////////////////////////////////////
        public function Fetch()
        //
        // Description:
        //      Returns the next database record, if any
        //
        {
            if( $this->rResult )
            {
                $this->oRecord = pg_fetch_object( $this->rResult );
            }
            else
            {
                $this->oRecord = null;
            }

            return( $this->oRecord != null );

        } // Fetch()


		////////////////////////////////////////////////////////////////////////////////////////////
		public function Seek( $iRow )
		//
		// Description:
		//
		//
		{
			return( pg_result_seek( $this->rResult, $iRow ) );
		
		} // Seek()

		
		////////////////////////////////////////////////////////////////////////////////////////////
		public function Reset()
		//
		// Description:
		//
		//
		{
			return( $this->Seek( 0 ) );
		
		} // Reset()


        ///////////////////////////////////////////////////////////////////////////////////////////
        public function Value( $sField )
        //
        // Description:
        //      Returns the value of sField in the current database record (if one exists)
        //
        {
            if( isset( $this->oRecord->$sField ) )
            {
                return( $this->oRecord->$sField );
            }
            else
            {
                return( null );
            }

        } // Value()


        ///////////////////////////////////////////////////////////////////////////////////////////
        public function GetRecord()
        //
        // Description:
        //      Returns the current 
        //
        {
            return( $this->oRecord );

        } // GetRecord()


        ///////////////////////////////////////////////////////////////////////////////////////////
        public function Begin()
        //
        // Description:
        //      Begins a new database transaction
        //
        {
            @pg_query( "BEGIN" ) or
                trigger_error( "Failed to begin transaction", E_USER_ERROR );

        } // Begin()


        ///////////////////////////////////////////////////////////////////////////////////////////
        public function Commit()
        //
        // Description:
        //      Commits the current database transaction
        //
        {
            @pg_query( "COMMIT" ) or
                trigger_error( "Failed to commit transaction", E_USER_ERROR );

        } // Commit()


        ///////////////////////////////////////////////////////////////////////////////////////////
        public function Rollback()
        //
        // Description:
        //      Rolls back/aborts the database transaction
        //
        {
            @pg_query( "ROLLBACK" ) or
                trigger_error( "Failed to rollback transaction", E_USER_ERROR );

        } // Rollback()


			///////////////////////////////////////////////////////////////////////////////////////////
			public function NextVal( $sSequence )
			{
				$sSQL = "SELECT
					NEXTVAL( '{$sSequence}' )
				AS
					next_val";
                
            $rResult = @pg_query( $sSQL ) or
                trigger_error( "Failed to query sequence value: " . $this->getLastError(), 
					 	E_USER_ERROR );
                
         	$oRecord = pg_fetch_object( $rResult );
         	
         	if( $oRecord )
         	{
         		return( $oRecord->next_val );
         	}
         	
         	return( null );
			}
			
			
			///////////////////////////////////////////////////////////////////////////////////////////
			public function CurrVal( $sSequence )
			{
				$sSQL = "SELECT
					CURRVAL( '{$sSequence}' )
				AS
					current_value";
                
            $rResult = @pg_query( $sSQL ) or
                trigger_error( "Failed to query sequence value: " . $this->getLastError(), 
					 	E_USER_ERROR );
                
         	$oRecord = pg_fetch_object( $rResult );
         	
         	if( $oRecord )
         	{
         		return( $oRecord->current_value );
         	}
         	
         	return( null );
			}
			
			
			///////////////////////////////////////////////////////////////////////////////////////////
			public function SerialCurrVal( $sTable, $sColumn )
			{
				$sSQL = "SELECT
					CURRVAL(
						PG_GET_SERIAL_SEQUENCE(
							'{$sTable}', 
							'{$sColumn}'
						)
					)
				AS
					current_value";
                
            $rResult = @pg_query( $sSQL ) or
                trigger_error( "Failed to query sequence value: " . $this->getLastError(), 
					 	E_USER_ERROR );
                
         	$oRecord = pg_fetch_object( $rResult );
         	
         	if( $oRecord )
         	{
         		return( $oRecord->current_value );
         	}
         	
         	return( null );
			}


			///////////////////////////////////////////////////////////////////////////////////////////
			public function SerialNextVal( $sTable, $sColumn )
			{
				$sSQL = "SELECT
					NEXTVAL(
						PG_GET_SERIAL_SEQUENCE(
							'{$sTable}', 
							'{$sColumn}'
						)
					)
				AS
					next_value";
                
            $rResult = @pg_query( $sSQL ) or
                trigger_error( "Failed to query sequence value: " . $this->getLastError(), 
					 	E_USER_ERROR );
                
         	$oRecord = pg_fetch_object( $rResult );
         	
         	if( $oRecord )
         	{
         		return( $oRecord->next_value );
         	}
         	
         	return( null );
			}


        ///////////////////////////////////////////////////////////////////////////////////////////
        public function GetLastError()
        //
        // Description:
        //      Returns the last database error, if any
        //
        {
            return( pg_last_error() );

        } // GetLastError()

    }; // class Query()

?>
