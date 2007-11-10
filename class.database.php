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
 * @version			0.05
 *
 */

	//
	// TODO: Turn this into a singleton database class
	//


	/**
	 * Database Interaction Class (PostgreSQL)
	 *
	 * @category	Database
	 * @author		Kristopher Wilson
	 * @link			http://www.openavanti.com/docs/database
	 */
	class Database
	{
		private $hDatabase = null;
        
      protected static $aSchemas = array();
        
      private static $sCacheDirectory = "";
      private static $bCacheSchemas = false;

		
      //////////////////////////////////////////////////////////////////////////////////////////////
		public function __construct()
      {
			$sString = "host=" . DATABASE_HOST . " dbname=" . DATABASE_NAME . " " . 
				"user=" . DATABASE_USER;
			  
			if( trim( DATABASE_PASSWORD ) != "" )
			{
				$sString .= " password={$sPassword}";
			}
			
			$this->hDatabase = @pg_connect( $sString )
			    or trigger_error( "Failed to connect to Postgres server", E_USER_ERROR );

		} // __construct()


		///////////////////////////////////////////////////////////////////////////////////////////
		public function Query( $sSQL )
		{
		   $rResult = @pg_query( $sSQL );
		
			if( !$rResult )
			{
				return( null );
			}
		
			return( new ResultSet( $rResult ) );
		
		} // Query()


		///////////////////////////////////////////////////////////////////////////////////////////
		public function Begin()
		{
			$rResult = @pg_query( "BEGIN" ) or
				trigger_error( "Failed to begin transaction", E_USER_ERROR );

			return( $rResult ? true : false );

		} // Begin()


		///////////////////////////////////////////////////////////////////////////////////////////
		public function Commit()
		{
			$rResult = @pg_query( "COMMIT" ) or
				trigger_error( "Failed to commit transaction", E_USER_ERROR );
		
			return( $rResult ? true : false );
		
		} // Commit()
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
		public function Rollback()
		{
			$rResult = @pg_query( "ROLLBACK" ) or
				trigger_error( "Failed to rollback transaction", E_USER_ERROR );
		
			return( $rResult ? true : false );
		
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
		
		} // NextVal()
			
			
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
		
		} // CurrVal()
			
			
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
		
		} // SerialCurrVal()

     
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
		
		} // SerialNextVal()


		///////////////////////////////////////////////////////////////////////////////////////////
		public function GetLastError()
		//
		// Description:
		//      Returns the last database error, if any
		//
		{
			return( pg_last_error() );
		
		} // GetLastError()
        


		////////////////////////////////////////////////////////////////////////////////////////////
		public function SetCacheDirectory( $sDirectory )
		{
			self::$sCacheDirectory = $sDirectory;
		
		} // SetCacheDirectory()

		
		////////////////////////////////////////////////////////////////////////////////////////////
		public function CacheSchemas( $bEnable )
		{
			self::$bCacheSchemas = $bEnable;

		} // CacheSchemas()


		///////////////////////////////////////////////////////////////////////////////////////////
		public function GetConnection()
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
			if( isset( self::$aSchemas[ $sTableName ] ) )
			{
				return( self::$aSchemas[ $sTableName ] );
			}
		
			$sCacheFile = self::$sCacheDirectory . "/" . md5( $sTableName );
		
		   if( self::$bCacheSchemas && file_exists( $sCacheFile ) )
			{
				self::$aSchemas[ $sTableName ] = unserialize( file_get_contents( $sCacheFile ) );	
			}
			else
			{
		   	$this->GetTableFields( $sTableName );
		   	$this->GetTablePrimaryKey( $sTableName );
		   	$this->GetTableForeignKeys( $sTableName );
		   	
		   	if( self::$bCacheSchemas )
		   	{
		   		file_put_contents( $sCacheFile, serialize( self::$aSchemas[ $sTableName ] ) );
		   	}
		   }
		   
		   return( self::$aSchemas[ $sTableName ] );
		
		} // GetFields()


		////////////////////////////////////////////////////////////////////////////////////////////
		public function GetTableFields( $sTableName )
		{
			if( isset( self::$aSchemas[ $sTableName ][ "fields" ] ) )
			{
				return( self::$aSchemas[ $sTableName ][ "fields" ] );
			}

         $aFields = array();

			$sSQL = "SELECT 
				pa.attname, 
				pa.attnum,
				pat.typname,
				pa.atttypmod,
				pa.attnotnull,
				pad.adsrc
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
			LEFT JOIN
				pg_attrdef AS pad
			ON
				pad.adrelid = pa.attrelid
			AND
				pad.adnum = pa.attnum
			WHERE  
				pt.typname = '" . $sTableName . "' 
			AND 
				pa.attnum > 0 
			ORDER BY 
				pa.attnum";
				
			if( !( $oFields = $this->Query( $sSQL ) ) )
			{
				trigger_error( "Failed on Query. Error: " . $this->GetLastError() . ". SQL: {$sSQL}", E_USER_ERROR );
				exit;
			}
            
            
			foreach( $oFields as $oField )
			{
				$aFields[ $oField->attname ] = array(
					"number" => $oField->attnum,
					"field" => $oField->attname, 
					"type" => $oField->typname,
					"not-null" => $oField->attnotnull == "t",
					"default" => $oField->adsrc
				);
				 
				if( $oField->typname == "_varchar" )
				{
					$aFields[ $oField->attname ][ "size" ] = $oField->atttypmod - 4;
				}
			}
			
			self::$aSchemas[ $sTableName ][ "fields" ] = $aFields;
 
			return( $aFields );
            
		} // GetTableFields()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public function GetTablePrimaryKey( $sTableName )
		{
			if( isset( self::$aSchemas[ $sTableName ][ "primary_key" ] ) )
			{
				return( self::$aSchemas[ $sTableName ][ "primary_key" ] );
			}
		
			$aLocalTable = $this->GetTableFields( $sTableName );
			
			self::$aSchemas[ $sTableName ][ "primary_key" ] = array();
					
			$sSQL = "SELECT 
				pi.indkey
			FROM 
				pg_index AS pi 
			INNER JOIN
				pg_type AS pt 
			ON 
				pt.typrelid = pi.indrelid 
			WHERE 
				pt.typname = '" . $sTableName . "' 
			AND 
				pi.indisprimary = true";			
			
			if( !( $oPrimaryKeys = $this->Query( $sSQL ) ) )
			{
				trigger_error( "SQL Error", E_USER_ERROR );
				exit;
			}

			if( $oPrimaryKeys->Count() != 0 )
			{
				$oPrimaryKey = $oPrimaryKeys->Rewind();
				
				$aIndexFields = explode( " ", $oPrimaryKey->indkey );
				
				foreach( $aIndexFields as $iField )
				{
					$aField = $this->GetFieldByNumber( $sTableName, $iField );
					
					self::$aSchemas[ $sTableName ][ "primary_key" ][] = 
						$aField[ "field" ];
				}
			}
	
			return( self::$aSchemas[ $sTableName ][ "primary_key" ] );
		
		} // GetTablePrimaryKey()
			
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public function GetTableForeignKeys( $sTableName )
		{
			if( isset( self::$aSchemas[ $sTableName ][ "foreign_key" ] ) )
			{
				return( self::$aSchemas[ $sTableName ][ "foreign_key" ] );
			}
		
			//
			// This method needs to be cleaned up and consolidated
			//
			
			$aLocalTable = $this->GetTableFields( $sTableName );
			
			$aReferences = array();
		
			$sSQL = "SELECT 
				rpt.typname,
				pc.confrelid,
				pc.conkey,
				pc.confkey
			FROM 
				pg_constraint AS pc 
			INNER JOIN 
				pg_type AS pt 
			ON 
				pt.typrelid = pc.conrelid 
			INNER JOIN
				pg_type AS rpt
			ON
				rpt.typrelid = confrelid
			WHERE
				pt.typname = '{$sTableName}'
			AND
				contype = 'f'
			AND
				confrelid IS NOT NULL";
				
			if( !( $oForeignKeys = $this->Query( $sSQL ) ) )
			{
				trigger_error( "Failed on Query: " . $sSQL, E_USER_ERROR );
				exit;
			}
            
			$iCount = 0;
			
			foreach( $oForeignKeys as $oForeignKey )
			{
				$aLocalFields = $aArray = explode( ",", 
					str_replace( array( "{", "}" ), "", $oForeignKey->conkey ) );
			
				$aForeignFields = $aArray = explode( ",", 
					str_replace( array( "{", "}" ), "", $oForeignKey->confkey ) );
			
		         	
         	$aFields = $this->GetTableFields( $oForeignKey->typname );
         	
         	foreach( $aForeignFields as $iIndex => $iField )
         	{
         		$aField = $this->GetFieldByNumber( $oForeignKey->typname, $iField );
         		$aForeignFields[ $iIndex ] = $aField[ "field" ];
         	}
         	
         	foreach( $aLocalFields as $iIndex => $iField )
         	{
         		$aField = $this->GetFieldByNumber( $sTableName, $iField );
         		$aLocalFields[ $iIndex ] = $aField[ "field" ];
         	}
         	
				// we currently do not handle references to multiple fields:

				$localField = current( $aLocalFields );

         	$sName = substr( $localField, strlen( $localField ) - 3 ) == "_id" ? 
         		substr( $localField, 0, strlen( $localField ) - 3 ) : $localField;
         	
         	$sName = StringFunctions::ToSingular( $sName );
         	
         	$aReferences[ $sName ] = array(
         		"table" => $oForeignKey->typname,
         		"name" => $sName,
         		"local" => $aLocalFields,
         		"foreign" => $aForeignFields,
         		"type" => "m-1"
         	);
      	
      		$iCount++;
			}
			
			self::$aSchemas[ $sTableName ][ "foreign_key" ] = $aReferences;
			
			
			// find tables that reference us:
					
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
				
				
			if( !( $oForeignKeys = $this->Query( $sSQL ) ) )
			{
				trigger_error( "Failed on Query: " . $sSQL, E_USER_ERROR );
				exit;
			}

	      foreach( $oForeignKeys as $oForeignKey )
	      {
	      	$aLocalFields = $aArray = explode( ",", 
					str_replace( array( "{", "}" ), "", $oForeignKey->confkey ) );
	
	         $aForeignFields = $aArray = explode( ",", 
					str_replace( array( "{", "}" ), "", $oForeignKey->conkey ) );
	         	
         	
	         $this->GetSchema( $oForeignKey->typname );
	         	
	         $aFields = $this->GetTableFields( $oForeignKey->typname );
	         	
	         foreach( $aForeignFields as $iIndex => $iField )
	         {
	         	$aField = $this->GetFieldByNumber( $oForeignKey->typname, $iField );
	         	$aForeignFields[ $iIndex ] = $aField[ "field" ];
	         }
	         	
	         foreach( $aLocalFields as $iIndex => $iField )
	         {
	         	$aField = $this->GetFieldByNumber( $sTableName, $iField );
	         	$aLocalFields[ $iIndex ] = $aField[ "field" ];
	         }

				$localField = reset( $aLocalFields );
				$foreignField = reset( $aForeignFields );
				
				// if foreign_table.local_field == foreign_table.primary_key AND
				// if local_table.foreign_key == local_table.primary_key THEN
				//		Relationship = 1-1
				// end
				
				$aTmpForeignPrimaryKey = &self::$aSchemas[ $oForeignKey->typname ][ "primary_key" ];
				$aTmpLocalPrimaryKey = &self::$aSchemas[ $sTableName ][ "primary_key" ];
				
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


	         $aReferences[ $oForeignKey->typname ] = array(
	         	"table" => $oForeignKey->typname,
	         	"name" => $oForeignKey->typname,
					"local" => $aLocalFields,
	         	"foreign" => $aForeignFields,
	         	"type" => $sType
	         );
	         	
         	$iCount++;
			}
			
			self::$aSchemas[ $sTableName ][ "foreign_key" ] += $aReferences;
			
			return( $aReferences );
		
		} // GetTableForeignKeys();


		////////////////////////////////////////////////////////////////////////////////////////////
		public function GetFieldType( $sTable, $sField )
		{
			$aFields = $this->GetTableFields( $sTable );
			
			foreach( $aFields as $aField )
			{
				if( $sField == $aField[ "field" ] )
				{
					return( $aField[ "type" ] );
				}
			}
			
			return( null );
		
		} // GetFieldType()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public function TableExists( $sTable )
		{
			if( isset( self::$aSchemas[ $sTable ] ) )
			{
				return( true );
			}
			
			$sSQL = "SELECT
				1
			FROM
				pg_tables
			WHERE
				LOWER( tablename ) = '" . strtolower( addslashes( $sTable ) ) . "'";
							
			if( !( $oResultSet = $this->Query( $sSQL ) ) )
			{
				trigger_error( "Failed on Query: {$sSQL}", E_USER_ERROR );
				exit;
			}
			
			return( $oResultSet->Count() );
		
		} // TableExists()


		////////////////////////////////////////////////////////////////////////////////////////////
		protected function GetFieldByNumber( $sTableName, $iFieldNumber )
		{
			foreach( self::$aSchemas[ $sTableName ][ "fields" ] as $aField )
			{
				if( $aField[ "number" ] == $iFieldNumber )
				{
					return( $aField );
				}
			}
		
			return( null );
		
		} // GetFieldByNumber()

    }; // Database()

?>
