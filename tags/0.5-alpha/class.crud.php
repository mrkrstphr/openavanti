<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author			Kristopher Wilson
 * @dependencies 	Database, StringFunctions
 * @copyright		Copyright (c) 2008, Kristopher Wilson
 * @license			http://www.openavanti.com/license
 * @link				http://www.openavanti.com
 * @version			0.05a
 *
 */
 
	/**
	 * Database abstraction layer implementing CRUD procedures
	 *
	 * @category	Database
	 * @author		Kristopher Wilson
	 * @link			http://www.openavanti.com/docs/database
	 */
	class CRUD implements Iterator
	//
	// Description:
	//		
	//
	{
		protected $oDatabase = null;
		protected $sTableName = null;		
		protected $oDataSet = null;
		
		protected $bEmptySet = true; // This could possibily be removed now that we are an iterator
		
		protected $aData = array(); // because object member variables cannot be unset
		
		protected $bDirty = true; // I don't know if this is used
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		public function __construct( $sTableName, $oData = null )
		//
		// Description:
		//		Get the schema of the supplied table name
		//
		{			
			$sDatabaseClass = DATABASE_DRIVER . "Database";
			
			$this->oDatabase = new $sDatabaseClass();
			
			// Enable/disable schema caching:
			$bCache = defined( "ENABLE_SCHEMA_CACHING" ) ? 
				ENABLE_SCHEMA_CACHING : false;
			
			$this->oDatabase->CacheSchemas( $bCache );
			$this->oDatabase->SetCacheDirectory( BASE_PATH . "/cache/schemas" );

			$this->sTableName = $sTableName;
		
			// Get the schema for this table:
			$this->oDatabase->GetSchema( $this->sTableName );
			
			// Prepare the fields for this table for CRUD->column access:
			$this->PrepareColumns();

			// If data is supplied, load it, depending on data type:
			
         if( is_object( $oData ) )
         {
         	$this->LoadObject( $oData );
         }
         else if( is_array( $oData ) )
         {
            $this->LoadArray( $oData );
         }

		} // __construct()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		protected function PrepareColumns()
		//
		// Description:
		//		Setup variables for each database column for this table
		//
		{
			$aColumns = $this->oDatabase->GetTableColumns( $this->sTableName );
			
			// Loop each column in the table and create a member variable for it:			
			foreach( $aColumns as $aColumn )
			{
				$this->aData[ $aColumn[ "field" ] ] = null;
			}
		
		} // PrepareColumns()
		
		
      ///////////////////////////////////////////////////////////////////////////////////////////
      protected function LoadArray( $aArray )
      {
      	$aColumns = $this->oDatabase->GetTableColumns( $this->sTableName );

			$aRelationships = $this->oDatabase->GetTableForeignKeys( $this->sTableName );

         foreach( $aArray as $sKey => $xValue )
         {
         	if( is_object( $xValue ) )
            {
                
            }
            else if( is_array( $xValue ) )
            {
					if( isset( $aRelationships[ $sKey ] ) )
					{
						$this->aData[ $sKey ] = new CRUD( $aRelationships[ $sKey ][ "table" ], $xValue );
					}					
            }
            else
            {
               // problem is that the key of aFields is numeric
               if( isset( $aColumns[ $sKey ] ) )
					{
						$this->aData[ $sKey ] = $xValue;
					}
         	}
			}

		} // LoadArray()

		
		/////////////////////////////////////////////////////////////////////////////////////////////
		public function Find( $xId = null, $aClauses = array() )
		//
		// Description:
		//
		//
		{
			$aPrimaryKey = $this->oDatabase->GetTablePrimaryKey( $this->sTableName );
			
			if( !empty( $xId ) )
			{
				// If we have a primary key specified, make sure it the number of columns matches:
				if( count( $aPrimaryKey ) > 1 && ( !is_array( $xId ) || 
					count( $xId ) != count( $aPrimaryKey ) ) )
				{
					trigger_error( "Invalid Key Provided", E_USER_ERROR );
					exit;
				}
			}
			
			$sTableAlias = StringFunctions::ToSingular( $this->sTableName );
			
			
			$sWhere = isset( $aClauses[ "where" ] ) ? $aClauses[ "where" ] : "";
			
					
			// Handle our provided key:	
			
			if( !empty( $sWhere ) )
			{
				$sWhere = " WHERE {$sWhere} ";
			}

			if( is_array( $xId ) && count( $aPrimaryKey ) > 0 )
			{
				// our primary key value is an array -- put the data in the WHERE clause:
				
				foreach( $xId as $sField => $sValue )
				{					
					$sType = $this->oDatabase->GetColumnType( $this->sTableName, $sField );
					
					$sWhere .= !empty( $sWhere ) ? " AND " : " WHERE ";
					$sWhere .= "_{$sTableAlias}.{$sField} = " . 
						$this->oDatabase->FormatData( $sType, $sValue ) . " ";
				}
			}
			else if( !empty( $xId ) )
			{
				// we have a singular primary key -- put the data in the WHERE clause:
				$sKey = reset( $aPrimaryKey );
				$sType = $this->oDatabase->GetColumnType( $this->sTableName, $sKey );
				
				$sWhere .= !empty( $sWhere ) ? " AND " : " WHERE ";
				$sWhere .= "_{$sTableAlias}.{$sKey} = " . 
					$this->oDatabase->FormatData( $sType, $xId ) . " ";
			}
			
			$iLimit = isset( $aClauses[ "limit" ] ) ? 
				" LIMIT " . intval( $aClauses[ "limit" ] ) : "";
			
			$iOffset = isset( $aClauses[ "offset" ] ) ? 
				" OFFSET " . intval( $aClauses[ "offset" ] ) : "";
			
			
			// Setup supplied joins:
			
			$sJoins = "";
			
			if( isset( $aClauses[ "join" ] ) )
			{
				foreach( $aClauses[ "join" ] as $sJoin )
				{
					$aRelationship = $this->FindRelationship( $sJoin );
					
					if( !count( $aRelationship ) )
					{
						throw new Exception( "Unknown join relationship specified: {$sJoin}" );
					}
					
					$sJoins .= " INNER JOIN " . $aRelationship[ "table" ] . " AS " . 
						"_" . $aRelationship[ "name" ] . " ON ";
					
					$sOn = "";
					
					foreach( $aRelationship[ "local" ] as $iIndex => $sField )
					{
						$sOn .= ( !empty( $sOn ) ? " AND " : "" ) . 
							"_" . StringFunctions::ToSingular( $this->sTableName ) . 
							"." . $sField . " = " . "_" . $aRelationship[ "name" ] . 
							"." . $aRelationship[ "foreign" ][ $iIndex ];
					}
					
					$sJoins .= " {$sOn} ";
				}
			}
			
			$sFields = "_" . StringFunctions::ToSingular( $this->sTableName ) . ".*";
			
			$sOrder = isset( $aClauses[ "order" ] ) ? 
				"ORDER BY " . $aClauses[ "order" ] : "";
				
			if( isset( $aClauses[ "distinct" ] ) && $aClauses[ "distinct" ] === true )
			{
				$sFields = " DISTINCT {$sFields} ";
			}
			
			// Concatenate all the pieces of the query together:
			$sSQL = "SELECT 
				{$sFields} 
			FROM 
				{$this->sTableName} AS _" . 
					StringFunctions::ToSingular( $this->sTableName ) . " 
			{$sJoins} 
			{$sWhere} 
			{$sOrder}
			{$iLimit}
			{$iOffset}";

			// Execute and pray:
			if( !( $this->oDataSet = $this->oDatabase->Query( $sSQL ) ) )
			{
				throw new Exception( "Failed on Query. Error: " . 
					$this->oDatabase->GetLastError() . "\n Query: {$sSQL}" );
			}
			
			// Loop the data and create member variables
			if( $this->oDataSet->Count() != 0 )
			{
				$this->Load( $this->oDataSet->Rewind() );
			}	
			
			$this->bDirty = false;
			
		} // Find()
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		public function FindCount( $xId = null, $aClauses = array() )
		//
		// Description:
		//
		//
		{
			$aPrimaryKey = $this->oDatabase->GetTablePrimaryKey( $this->sTableName );
			
			if( !empty( $xId ) )
			{
				// If we have a primary key specified, make sure it the number of columsn matches:
				if( count( $aPrimaryKey ) > 1 && ( !is_array( $xId ) || 
					count( $xId ) != count( $aPrimaryKey ) ) )
				{
					trigger_error( "Invalid Key Provided", E_USER_ERROR );
					exit;
				}
			}
			
			if( empty( $xId ) && !isset( $aClauses[ "where" ] ) )
			{
				//trigger_error( "Invalid Key Provided", E_USER_ERROR );
				//exit;
			}
			
			
			$sWhere = isset( $aClauses[ "where" ] ) ? $aClauses[ "where" ] : "";
			
					
			// Handle our provided key:	
			
			if( !empty( $sWhere ) )
			{
				$sWhere = " WHERE {$sWhere} ";
			}

			if( is_array( $xId ) && count( $aPrimaryKey ) > 0 )
			{
				// our primary key value is an array -- put the data in the WHERE clause:
				
				foreach( $xId as $sField => $sValue )
				{					
					$sType = $this->oDatabase->GetColumnType( $this->sTableName, $sField );
					
					$sWhere .= !empty( $sWhere ) ? " AND " : " WHERE ";
					$sWhere .= "{$sField} = " . $this->oDatabase->FormatData( $sType, $sValue ) . " ";
				}
			}
			else if( !empty( $xId ) )
			{
				// we have a singular primary key -- put the data in the WHERE clause:
				$sKey = reset( $aPrimaryKey );
				$sType = $this->oDatabase->GetColumnType( $this->sTableName, $sKey );
				
				$sWhere .= !empty( $sWhere ) ? " AND " : " WHERE ";
				$sWhere .= "{$sKey} = " . $this->oDatabase->FormatData( $sType, $xId ) . " ";
			}
			
			
			// Setup supplied joins:
			
			$sJoins = "";
			
			if( isset( $aClauses[ "join" ] ) )
			{
				foreach( $aClauses[ "join" ] as $sJoin )
				{
					$aRelationship = $this->FindRelationship( $sJoin );
					
					if( !count( $aRelationship ) )
					{
						throw new Exception( "Unknown join relationship specified: {$sJoin}" );
					}
					
					$sJoins .= " INNER JOIN " . $aRelationship[ "table" ] . " AS " . 
						"_" . $aRelationship[ "name" ] . " ON ";
					
					$sOn = "";
					
					foreach( $aRelationship[ "local" ] as $iIndex => $sField )
					{
						$sOn .= ( !empty( $sOn ) ? " AND " : "" ) . 
							"_" . StringFunctions::ToSingular( $this->sTableName ) . 
							"." . $sField . " = " . "_" . $aRelationship[ "name" ] . 
							"." . $aRelationship[ "foreign" ][ $iIndex ];
					}
					
					$sJoins .= " {$sOn} ";
				}
			}
			
			// Concatenate all the pieces of the query together:
			$sSQL = "SELECT 
				COUNT( * ) AS count 
			FROM 
				{$this->sTableName} AS _" . 
					StringFunctions::ToSingular( $this->sTableName ) . " 
			{$sJoins} 
			{$sWhere}";		


			// Execute and pray:
			if( !( $this->oDataSet = $this->oDatabase->Query( $sSQL ) ) )
			{
				trigger_error( "Failed on Query. Error: " . 
					$this->oDatabase->GetLastError() . "\n Query: {$sSQL}", E_USER_ERROR );
				exit;
			}
			
			
			$oData = $this->oDataSet->Rewind();
						
			return( $oData->count );
			
		} // FindCount()
		
		
		/////////////////////////////////////////////////////////
		public function GetRecord()
		{
			// ********************************************************************
			// ********************************************************************
			// ********************************************************************
			// *****************             FIX THIS              ****************
			// ********************************************************************
			// ********************************************************************
			// ********************************************************************
			
			return( $this->oDataSet->GetRecord() ); // not good...
			// need to present data in aData, as it may have changed, but we need to return
			// aData (an array) as an object... hmm...
		
		} // GetRecord()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		protected function FindRelationship( $sName )
		{
			$aForeignKeys = $this->oDatabase->GetTableForeignKeys( $this->sTableName );
			
			foreach( $aForeignKeys as $aForeignKey )
			{
				if( $aForeignKey[ "name" ] == $sName ) // || $aForeignKey[ "table" ] == $sTable )
				{
					return( $aForeignKey );
				}
			}
			
			return( null );
		
		} // FindRelationship()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		protected function Load( $oRecord = null )
		//
		// Description:
		//	
		{
		
			if( !is_null( $oRecord ) && is_object( $oRecord ) )
			{
				// Loop each field
				foreach( $oRecord as $sKey => $sValue )
				{
					// otherwise this data is part of the primary table, 
					// create a member variable:
					$this->aData[ $sKey ] = $sValue;
				}
				
				$this->bEmptySet = false;
			}
	
		} // Load()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		protected function IsEmpty()
		{
			return( $this->bEmptySet );
			
		} // IsEmpty()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		protected function GetCount() 
		{
		
			if( !is_null( $this->oDataSet ) )
			{
				return( $this->oDataSet->Count() );
			}
			
			return( 0 );
		
		} // GetCount()
			
		
		public function __isset( $sName )
		{
			return( array_key_exists( $sName, $this->aData ) );
			
		} // __isset()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public function __get( $sName )
		{			
			if( array_key_exists( $sName, $this->aData ) )
			{
				return( $this->aData[ $sName ] );
			}
		
			$aSchema = $this->oDatabase->GetSchema( $this->sTableName );
			
			$aRelationships = $aSchema[ "foreign_key" ];			

			if( !isset( $aRelationships[ $sName ] ) )
			{
				throw new Exception( "Relationship [{$sName}] does not exist" );
			}


			$aRelationship = $aSchema[ "foreign_key" ][ $sName ];
			
			// the relationship exists, attempt to load the data:
			
			if( $aRelationship[ "type" ] == "1-m" )
			{				
				$sWhere = "";
				
				foreach( $aRelationship[ "foreign" ] as $iIndex => $sKey )
				{
					$sRelated = $aRelationship[ "local" ][ $iIndex ];
					
					$sWhere .= empty( $sWhere ) ? "" : " AND ";
					$sWhere .= " {$sKey} = " . intval( $this->aData[ $sRelated ] );
				}
				
				$this->aData[ $sName ] = new CRUD( $aRelationship[ "table" ] );
				$this->aData[ $sName ]->Find( null, array(
					"where" => $sWhere 
				) );
			}
			else
			{
				$sLocalColumn = current( $aRelationship[ "local" ] );
								
				if( isset( $this->aData[ $sLocalColumn ] ) )
				{
					$this->aData[ $sName ] = new CRUD( $aRelationship[ "table" ] );		
					$this->aData[ $sName ]->Find( $this->aData[ $sLocalColumn ] );
				}
				else
				{
					$this->aData[ $sName ] = null;
				}
			}
			
			return( $this->aData[ $sName ] );
			
		} // __get()
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////
		public function __set( $sName, $sValue )
		{
		
			$aColumns = $this->oDatabase->GetTableColumns( $this->sTableName );
		
			if( isset( $aColumns[ $sName ] ) )
			{
				$this->aData[ $sName ] = $sValue;
			}
			else if( !is_null( $this->FindRelationship( $sName ) ) )
			{
				$this->aData[ $sName ] = $sValue;
			}
			else
			{
				throw new Exception( "Unknown column [{$sName}] referenced" );
			}
		
		} // __set()
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////
		public function __call( $sName, $aArguments )
		{
			switch( strtolower( $sName ) )
			{
				case "empty":
					return( $this->IsEmpty() );
				break;
				
				case "count":
					return( $this->GetCount() );
				break;
				
				default:
					throw new Exception( "Call to undefined method: {$sName}" );
				break;
			}
				
		} // __call()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public function Save()
		//
		// Description:
		//		Based on presence of primary key data, either creates a new record, or updates the
		//		existing record
		//
		{
			// grab a copy of the primary key:
			$aPrimaryKeys = $this->oDatabase->GetTablePrimaryKey( $this->sTableName );
			
			$bInsert = false;
			
			// If we have a compound primary key, we must first determine if the record
			// already exists in the database. If it does, we're doing an update.
			
			// If we have a singular primary key, we can rely on whether the primary key
			// value of this object is null
			
			
			if( count( $aPrimaryKeys ) == 1 )
			{
				$sPrimaryKey = reset( $aPrimaryKeys );
				
				if( empty( $this->aData[ $sPrimaryKey ] ) )
				{
					$bInsert = true;
				}
			}
			else
			{
				$bInsert = !$this->RecordExists();
			}


			
			if( $bInsert )
			{
				return( $this->Insert() );
			}
			else
			{
				return( $this->Update() );
			}
		
		} // Save()
				
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public function SaveAll()
		{		
			$aForeignKeys = $this->oDatabase->GetTableForeignKeys( $this->sTableName );
		
			foreach( $aForeignKeys as $aRelationship )
			{
				//echo '<div class="printr"><pre>' . print_r( $aRelationship , true ) . '</pre></div>';
				$sRelationshipName = $aRelationship[ "name" ];
				
				if( isset( $this->aData[ $sRelationshipName ] ) )
				{
					// If the relationship type is 1 to Many, than iterate each
					// related data set and invoke SaveAll()
					
					if( $aRelationship[ "type" ] == "1-m" )
					{
						foreach( $this->aData[ $sRelationshipName ] as $oRelatedData )
						{
							$oRelatedData->SaveAll();
						}
					}
					else
					{
						$this->aData[ $sRelationshipName ]->SaveAll();
					
						// If the relationship is many to one, then we have to set the foreign key
						// value for this record
						if( $aRelationship[ "type" ] == "m-1" )
						{
							// do we need to handle multiple columns?
							
							$this->aData[ $aRelationship[ "local" ][ 0 ] ] = 
								$this->aData[ $sRelationshipName ]->{$aRelationship[ "foreign" ][ 0 ]};
						}
					}
				}
			}
			
			$this->Save();
		
		} // SaveAll()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		protected function Insert()
		//
		// Description:
		//
		//
		{
			$sColumns = "";
			$sValues = "";
			
			$aPrimaryKeys = $this->oDatabase->GetTablePrimaryKey( $this->sTableName );			
			$aColumns = $this->oDatabase->GetTableColumns( $this->sTableName );
			
			// loop each column in the table and specify it's data:
			foreach( $aColumns as $aColumn )
			{
				// automate updating created date column:
				if( in_array( $aColumn[ "field" ], array( "created_date", "created_stamp", "created_on" ) ) )
				{
					// dates are stored as GMT
					$this->aData[ $aColumn[ "field" ] ] = gmdate( "Y-m-d H:i:s" );
				}
				
				// If the primary key is singular, do not provide a value for it:				
				if( in_array( $aColumn[ "field" ], $aPrimaryKeys ) && count( $aPrimaryKeys ) == 1 )
				{
					continue;
				}				
				
				// Create a list of columns to insert into:
				$sColumns .= ( !empty( $sColumns ) ? ", " : "" ) . 
					$aColumn[ "field" ];
				
				// Get the value for the column (if present):
				$sValue = isset( $this->aData[ $aColumn[ "field" ] ] ) ? 
					$this->aData[ $aColumn[ "field" ] ] : "";
				
				// Create a list of values to insert into the above columns:
				$sValues .= ( !empty( $sValues ) ? ", " : "" ) . 
					$this->oDatabase->FormatData( $aColumn[ "type" ], $sValue );
			}
			
			$sSQL = "INSERT INTO {$this->sTableName} (
				{$sColumns}
			) VALUES (
				{$sValues}
			)";
			
			if( !$this->oDatabase->Query( $sSQL ) )
			{
				throw new Exception( "Failed on Query: {$sSQL} \n" . $this->oDatabase->GetLastError() );
			}
			
			// Note: an assumption is made that if the primary key is not singular, then there all
			// the data for the compound primary key should already be present -- meaning, we should 
			// not have a serial value on the table for a compound primary key.
			
			// If we have a singular primary key:
			if( count( $aPrimaryKeys ) == 1 )
			{				
				// Get the current value of the serial for the primary key column:
				$iKey = $this->oDatabase->SerialCurrVal( $this->sTableName, reset( $aPrimaryKeys ) );
				
				// Store the primary key:
				$this->aData[ $aPrimaryKeys[0] ] = $iKey;
				
				// return the primary key:
				return( $iKey );
			}
			
			
			// If we have a compound primary key, return true:
			return( true );
			
		} // Insert()
		
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		protected function Update()
		//
		// Description:
		//		Responsible for updating the currently stored data for primary table and all foreign
		//		tables referenced.
		//
		{			
			// update the primary record:
			$sSQL = $this->UpdateQuery();
			
			if( !$this->oDatabase->Query( $sSQL ) )
			{
				throw new Exception( "Failed on Query: {$sSQL} <br />" . $this->oDatabase->GetLastError() );
				exit;
			}
			
		} // Update()
		
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		protected function UpdateQuery()
		//
		// Description:
		//		Called by update() method
		//
		{
			$aSchema = $this->oDatabase->GetSchema( $this->sTableName );
			
			$aPrimaryKeys = $aSchema[ "primary_key" ];
					
			$sSet = "";

			// loop each field in the table and specify it's data:
			foreach( $aSchema[ "fields" ] as $field )
			{
				// do not update certain fields:
				if( in_array( $field[ "field" ], array( "created_date", "created_stamp", "created_on" ) ) )
				{
					continue;
				}
				
				// automate updating update date fields:
				if( in_array( $field[ "field" ], array( "updated_date", "updated_stamp", "updated_on" ) ) )
				{
					$this->aData[ $field[ "field" ] ] = gmdate( "Y-m-d H:i:s" );
				}
				
				// complete the query for this field:
				$sSet .= ( !empty( $sSet ) ? ", " : "" ) . 
					$field[ "field" ] . " = " . 
						$this->oDatabase->FormatData( $field[ "type" ], $this->aData[ $field[ "field" ] ] ) . " ";
			}
			
			// if we found no fields to update, return:
			if( empty( $sSet ) )
			{
				return;
			}
			
						
			$sWhere = "";
			
			foreach( $aPrimaryKeys as $sKey )
			{
				$sWhere .= !empty( $sWhere ) ? " AND " : "";
				$sWhere .= "{$sKey} = " . intval( $this->aData[ $sKey ] );
			}
			
			$sSQL = "UPDATE {$this->sTableName} SET {$sSet} WHERE {$sWhere}";	
			

			return( $sSQL );
			
		} // updateQuery()
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		private function RecordExists()
		{
			$aPrimaryKeys = $this->oDatabase->GetTablePrimaryKey( $this->sTableName );
		
			$sSQL = "SELECT
				1
			FROM
				{$this->sTableName} ";
			
			$sWhere = "";
			
			foreach( $aPrimaryKeys as $sPrimaryKey )
			{
				$sType = $this->oDatabase->GetColumnType( $this->sTableName, $sPrimaryKey );
				
				$sWhere .= empty( $sWhere ) ? " WHERE " : " AND ";
				$sWhere .= $sPrimaryKey . " = " . 
					$this->oDatabase->FormatData( $sType, $this->aData[ $sPrimaryKey ] ) . " ";
			}
			
			$sSQL .= $sWhere;
			
			if( !( $oResultSet = $this->oDatabase->Query( $sSQL ) ) )
			{
				throw new Exception( "Failed on Query: {$sSQL}\n" . $this->oDatabase->GetLastError() );
			}
			
			return( $oResultSet->Count() != 0 );
		
		} // RecordExists()
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		public function Destroy()
		//
		// Description:
		//		Destroys (deletes) the current data. This method will delete the primary record
		//		(assuming that the primary key for the data is set). If cascade is true, this method
		//		will also delete all data that is related through a 1-1 or 1-Many relationship
		//
		{
			throw new Exception( "Destroy() is not yet implemented." );
		
		} // Destroy()
	
	
		////////////////////////////////////////////////////////////////////////////////////////////
		public function DestroyAll()
		{
			throw new Exception( "DestroyAll() is not yet implemented." );
			
			// destroy dependent data:
			
			
			// destroy this:
			
			$this->destory();
		
			
		
		} // DestroyAll()
		
		//////////////////////////////////////////////////////////////////////////////////////////////
		protected function buildWhereClause( $keys, $dataSet )
		//
		// Description:
		//		Helper method for generating a where clause for a query string. Where clause is
		//		built by supplied keys and associated data
		//
		{
			$where = "";
			
			// loop each primary key and build a where clause for the data:	
			foreach( $keys as $key )
			{
				if( isset( $dataSet->$key ) )
				{
					$where .= !empty( $where ) ? " AND " : " WHERE ";
					$where .= "{$key} = {$dataSet->$key}";
				}
			}
			
			return( $where );
			
		} // buildWhereClause()
	
	
		//
		// ITERATOR DEFINITION
		//

		////////////////////////////////////////////////////////////////////////////////////////////
	   public function Rewind()  
	   //
	   // Description:
	   //		See http://www.php.net/~helly/php/ext/spl/interfaceIterator.html
	   //
		{		
			if( !is_null( $this->oDataSet ) )
			{
				$this->oDataSet->Rewind();
				
				return( $this->oDataSet->Rewind() );
			}
			
			return( null );
	
	   } // Rewind()
    	

		////////////////////////////////////////////////////////////////////////////////////////////
		public function Current() 
		//
		// Description:
		//		See http://www.php.net/~helly/php/ext/spl/interfaceIterator.html
		// 
		{
			if( !$this->bEmptySet )
			{
				return( $this );
			}
			
			return( null );
		
		} // Current()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public function Key() 
		//
		// Description:
		//		See http://www.php.net/~helly/php/ext/spl/interfaceIterator.html
		//
		{			
			// It might be a good idea to return the primary key of the current record here,
			// but what if that primary key is compound? Return an array? Hmm...
			
			return( null );
			
		} // Key()
	
	
		////////////////////////////////////////////////////////////////////////////////////////////
		public function Next() 
		//
		// Description:
		//		See http://www.php.net/~helly/php/ext/spl/interfaceIterator.html
		// 
		{
			$this->bEmptySet = true;
				
			if( !is_null( $this->oDataSet ) && $this->oDataSet->Count() != 0 )
			{
				$aRelationships = $this->oDatabase->GetTableForeignKeys( $this->sTableName );
				
				foreach( $aRelationships as $aRelationship )
				{
					$sRelationshipName = $aRelationship[ "name" ];
					
					if( array_key_exists( $sRelationshipName, $this->aData ) )
					{
						unset( $this->aData[ $sRelationshipName ] );
					}
				}
			
				$this->Load( $this->oDataSet->Next() );
			}
			else
			{
				return( null );
			}
			
			return( $this );
		
		} // Next()

	
		////////////////////////////////////////////////////////////////////////////////////////////////
		public function Valid()  
		//
		// Description:
		//		See http://www.php.net/~helly/php/ext/spl/interfaceIterator.html
		//
		{ 			
			if( !$this->bEmptySet )
			{
				return( $this );
			}
			
			return( null );
		
		} // Valid()

	}; // CRUD()

?>
