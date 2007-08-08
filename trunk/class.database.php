<?php

    ///////////////////////////////////////////////////////////////////////////////////////////////
    class Database
    //
    // Description:
    //      Implements the database interface for PostgreSQL. 
    //
    {
        private $hDatabase = null;
        
        protected $aSchemas = array();

        ///////////////////////////////////////////////////////////////////////////////////////////
        public function __construct()
        //
        // Description:
        //      Constructor, establishes connection to the Postgres database
        //
        {
        		$sString = "host=" . DATABASE_HOST . " dbname=" . DATABASE_NAME . " " . 
					"user=" . DATABASE_USER;
				  
				if( trim( DATABASE_PASSWORD ) != "" )
				{
					$sString .= " password={$sPassword}";
				}
        		
            $this->hDatabase = pg_connect( $sString )
                or trigger_error( "Failed to connect to Postgres server", E_USER_ERROR );

        } // Database()


        ///////////////////////////////////////////////////////////////////////////////////////////
        public function SpawnQuery()
        //
        // Description:
        //      Generates a query object for this database
        //
        {
            return( new Query( $this ) );

        } // SpawnQuery()


        ///////////////////////////////////////////////////////////////////////////////////////////
        public function GetResource()
        //
        // Description:
        //      Returns the database resource
        //
        {
            return( $this->hDatabase );

        } // GetResource()


        ///////////////////////////////////////////////////////////////////////////////////////////
        public function FormatData( $sType, $sValue )
        {
            $aQuoted_Types = array( "/text/", "/varchar/", "/date/", "/timestamp/", "/bool/" );

            if( empty( $sValue ) )
            {
                return( "NULL" );
            }

            if( preg_replace( $aQuoted_Types, "", $sType ) != $sType )
            {
                return( "'" . addslashes( $sValue ) . "'" );
            }

            return( $sValue );

        } // FormatData()


        ///////////////////////////////////////////////////////////////////////////////////////////
        public function GetSchema( $sTableName )
        //
        // Description:
        //      Collects all fields/columns in the specified database table, as well as data type
        //      and key information.
        //
        {
            
            $this->GetTableFields( $sTableName );
            $this->GetTablePrimaryKey( $sTableName );
            $this->GetTableForeignKeys( $sTableName );
            
            return( $this->aSchemas[ $sTableName ] );

        } // GetFields()


		////////////////////////////////////////////////////////////////////////////////////////////
		public function GetTableFields( $sTableName )
		{
			if( isset( $aSchemas[ $sTableName ][ "fields" ] ) )
			{
				return( $aSchemas[ $sTableName ][ "fields" ] );
			}
		
         $oQ = $this->SpawnQuery();


         $aFields = array();


			$sSQL = "SELECT 
				pt.typrelid,
				pa.attname, 
				pa.attnum,
				pat.typname,
				pa.atttypmod
			FROM 
				pg_attribute AS pa 
			INNER JOIN 
				pg_type AS pt 
			ON 
				pt.typrelid = pa.attrelid 
			INNER JOIN  
				pg_type AS pat 
			ON 
				pat.typelem = pa.atttypid 
			WHERE  
				pt.typname = '" . $sTableName . "' 
			AND 
				pa.attnum > 0 
			ORDER BY 
				pa.attnum";
				
			if( !$oQ->Execute( $sSQL ) )
			{
				trigger_error( "Failed on Query. Error: " . $oQ->GetLastError() . ". SQL: {$sSQL}", E_USER_ERROR );
				exit;
			}
            
            
			while( $oQ->Fetch() )
			{
				$sField = $oQ->Value( "attname" );

				$aReturn[ $oQ->Value( "attnum" ) ] = array(
					"field" => $sField, 
					"type" => $oQ->Value( "typname" )
				);
				 
				if( $oQ->Value( "typname" ) == "_varchar" )
				{
					$aReturn[ $oQ->Value( "attnum" ) ][ "size" ] =
					$oQ->Value( "atttypmod" ) - 4;
				}
			}

			$this->aSchemas[ $sTableName ][ "fields" ] = $aReturn;
 
			return( $aReturn );
            
		} // GetTableFields()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public function GetTablePrimaryKey( $sTableName )
		{
			if( isset( $this->aSchemas[ $sTableName ][ "primary_key" ] ) )
			{
				return( $this->aSchemas[ $sTableName ][ "primary_key" ] );
			}
			
		
			$aLocalTable = $this->GetTableFields( $sTableName );
			
			$this->aSchemas[ $sTableName ][ "primary_key" ] = array();
		
			$oQ = $this->SpawnQuery();
			
         $sSQL = "SELECT 
				pi.indkey,
				pi.indnatts
			FROM 
				pg_index AS pi 
			INNER JOIN
				pg_type AS pt 
			ON 
				pt.typrelid = pi.indrelid 
			INNER JOIN 
				pg_attribute AS pa 
			ON 
				pa.attrelid = pt.typrelid 
			AND 
				pa.attnum = pi.indnatts 
			WHERE 
				pt.typname = '" . $sTableName . "' 
			AND 
				pi.indisprimary = true";

			if( !$oQ->Execute( $sSQL ) )
			{
				trigger_error( "SQL Error", E_USER_ERROR );
				exit;
			}

			if( $oQ->Fetch() )
			{
				$aIndexFields = explode( " ", $oQ->Value( "indkey" ) );
				
				foreach( $aIndexFields as $iField )
				{
					$this->aSchemas[ $sTableName ][ "primary_key" ][] = 
						$this->aSchemas[ $sTableName ][ "fields" ][ $iField ][ "field" ];
				}
			}
		
			return( $this->aSchemas[ $sTableName ][ "primary_key" ] );
		
		} // GetTablePrimaryKey()
		
		

		////////////////////////////////////////////////////////////////////////////////////////////
		public function GetTableForeignKeys( $sTableName )
		{
			if( isset( $this->aSchemas[ $sTableName ][ "foreign_key" ] ) )
			{
				return( $this->aSchemas[ $sTableName ][ "foreign_key" ] );
			}
			
			$aLocalTable = $this->GetTableFields( $sTableName );
			
			$aReferences = array();
			
			$oQ = $this->SpawnQuery();
			$oQ2 = $this->SpawnQuery();
		
         $sSQL = "SELECT 
				pc.confrelid,
				pc.conkey,
				pc.confkey
			FROM 
				pg_constraint AS pc 
			INNER JOIN 
				pg_type AS pt 
			ON 
				pt.typrelid = pc.conrelid 
			WHERE
				pt.typname = '{$sTableName}'
			AND
				contype = 'f'
			AND
				confrelid IS NOT NULL";
				
				
         if( !$oQ->Execute( $sSQL ) )
			{
				trigger_error( "Failed on Query: " . $sSQL, E_USER_ERROR );
				exit;
			}
            
         $iCount = 0;

         while( $oQ->Fetch() )
         {
         	$aLocalFields = $aArray = explode( ",", 
					str_replace( array( "{", "}" ), "", $oQ->Value( "conkey" ) ) );

         	$aForeignFields = $aArray = explode( ",", 
					str_replace( array( "{", "}" ), "", $oQ->Value( "confkey" ) ) );
         	
         	// get the table name of the reference:
         	
         	$sSQL = "SELECT
         		pt.typname
         	FROM
         		pg_type AS pt
         	WHERE
         		pt.typrelid = " . $oQ->Value( "confrelid" );
         		
         	if( !$oQ2->Execute( $sSQL ) )
         	{
					trigger_error( "Failed on Query: " . $sSQL, E_USER_ERROR );
					exit;
         	}
         	
         	if( !$oQ2->Fetch() )
         	{
         		continue;
         	}
         	
         	$aFields = $this->GetTableFields( $oQ2->Value( "typname" ) );
         	
         	foreach( $aForeignFields as $iIndex => $iField )
         	{
         		$aForeignFields[ $iIndex ] = $aFields[ $iField ][ "field" ];
         	}
         	
         	foreach( $aLocalFields as $iIndex => $iField )
         	{
         		$aLocalFields[ $iIndex ] = $aLocalTable[ $iField ][ "field" ];
         	}
         	
				// we currently do not handle references to multiple fields:

				$localField = current( $aLocalFields );

         	$name = substr( $localField, strlen( $localField ) - 3 ) == "_id" ? 
         		substr( $localField, 0, strlen( $localField ) - 3 ) : $localField;
         	
         	
         	$aReferences[ $iCount ] = array(
         		"table" => $oQ2->Value( "typname" ),
         		"name" => $name,
         		"local" => $aLocalFields,
         		"foreign" => $aForeignFields,
         		"type" => "m-1" // todo -- determine 1-1 relationships
         	);
         	
         	$iCount++;
			}
			
			$this->aSchemas[ $sTableName ][ "foreign_key" ] = $aReferences;
			
			
			
			
			// find tables that reference us:
			
			$aReferences = array();
			
			$oQ = $this->SpawnQuery();
			$oQ2 = $this->SpawnQuery();
		
         $sSQL = "SELECT 
         	ptr.typname,
				pc.conrelid,
				pc.conkey,
				pc.confkey
			FROM 
				pg_constraint AS pc 
			INNER JOIN 
				pg_type AS pt 
			ON 
				pt.typrelid = pc.confrelid 
			INNER JOIN
				pg_type AS ptr
			ON
				ptr.typrelid = pc.conrelid
							
			WHERE
				pt.typname = '{$sTableName}'
			AND
				contype = 'f'
			AND
				confrelid IS NOT NULL";
				
				
         if( !$oQ->Execute( $sSQL ) )
			{
				trigger_error( "Failed on Query: " . $sSQL, E_USER_ERROR );
				exit;
			}

         while( $oQ->Fetch() )
         {
         	$aLocalFields = $aArray = explode( ",", 
					str_replace( array( "{", "}" ), "", $oQ->Value( "confkey" ) ) );

         	$aForeignFields = $aArray = explode( ",", 
					str_replace( array( "{", "}" ), "", $oQ->Value( "conkey" ) ) );
         	
         	// get the table name of the reference:
         	
         	$sSQL = "SELECT
         		pt.typname
         	FROM
         		pg_type AS pt
         	WHERE
         		pt.typrelid = " . $oQ->Value( "conrelid" );
         		
         	if( !$oQ2->Execute( $sSQL ) )
         	{
         		trigger_error( "Failed on Query: " . $sSQL, E_USER_ERROR );
         		exit;
         	}
         	
         	if( !$oQ2->Fetch() )
         	{
         		trigger_error( "Failed to find table: " . $oQ->FieldVal( "conrelid" ), E_USER_ERROR );
         		exit;
         		//continue;
         	}
         	
         	$this->GetSchema( $oQ2->Value( "typname" ) );
         	
         	$aFields = $this->GetTableFields( $oQ2->Value( "typname" ) );
         	
         	foreach( $aForeignFields as $iIndex => $iField )
         	{
         		$aForeignFields[ $iIndex ] = $aFields[ $iField ][ "field" ];
         	}
         	
         	foreach( $aLocalFields as $iIndex => $iField )
         	{
         		$aLocalFields[ $iIndex ] = $this->aSchemas[ $sTableName ][ "fields" ][ $iField ][ "field" ];
         	}

				$localField = reset( $aLocalFields );
				$foreignField = reset( $aForeignFields );
				
				// if foreign_table.local_field == foreign_table.primary_key AND
				// if local_table.foreign_key == local_table.primary_key THEN
				//		Relationship = 1-1
				// end
				
				$aTmpForeignPrimaryKey = &$this->aSchemas[ $oQ2->Value( "typname" ) ][ "primary_key" ];
				$aTmpLocalPrimaryKey = &$this->aSchemas[ $sTableName ][ "primary_key" ];
				
				$bForeignFieldIsPrimary = count( $aTmpForeignPrimaryKey ) == 1 &&
					reset( $aTmpForeignPrimaryKey ) == $foreignField;
				$bLocalFieldIsPrimary = count( $aTmpLocalPrimaryKey ) &&
					reset( $aTmpLocalPrimaryKey ) == $localField;
				$bForeignIsSingular = count( $aForeignFields ) == 1;
				
				$sType = "1-m";
				
				if( $bForeignFieldIsPrimary && $bLocalFieldIsPrimary && $bForeignIsSingular )
				{
					$sType = "1-1";
				}


         	$aReferences[ $iCount ] = array(
         		"table" => $oQ2->Value( "typname" ),
         		"name" => $oQ2->Value( "typname" ),
					"local" => $aLocalFields,
         		"foreign" => $aForeignFields,
         		"type" => $sType
         	);
         	
         	$iCount++;
			}
			
			$this->aSchemas[ $sTableName ][ "foreign_key" ] += $aReferences;
			
			
			
			return( $aReferences );
		
		} // GetTableForeignKeys();


		////////////////////////////////////////////////////////////////////////////////////////////
		public function GetFieldType( $sTable, $sField )
		{
		
			$this->GetTableFields( $sTable );
			
			foreach( $this->aSchemas[ $sTable ][ "fields" ] as $aField )
			{
				if( $sField == $aField[ "field" ] )
				{
					return( $aField[ "type" ] );
				}
			}
			
			return( null );
		
		} // GetFieldType()

    }; // class Database()

?>
