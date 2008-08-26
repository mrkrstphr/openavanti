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
 * @version			0.6.4-alpha
 *
 */
 
	/**
	 * Database abstraction layer implementing CRUD procedures
	 *
	 * @category	Database
	 * @author		Kristopher Wilson
	 * @link			http://www.openavanti.com/docs/crud
	 */
	class CRUD implements Iterator, Throwable
	//
	// Description:
	//		
	//
	{
		protected $oDatabase = null;
		protected $sTableName = null;		
		protected $oDataSet = null;
		
		protected $bEmptySet = true; // This could possibily be removed now that we are an iterator
		
		protected $aData = array();
		
		protected $bDirty = true; // I don't know if this is used
		
		
		/**
		 *  The constructor makes the necessary connection to the database (see Database::Construct) 
		 *  and attempts to load the schema of the specified table.
		 *  
		 *  If the second argument of oData is supplied, the constructor will attempt to load that 
		 *  data into the class for later saving.
		 * 
		 *  If there is a define defined called ENABLE_SCHEMA_CACHING, schema caching is turned on, 
		 *  allowing for faster subsequent page loads. 	 	 
		 * 		 
		 * @argument string The name of the database table
		 * @argument mixed An array or object of data to load into the CRUD object		 
		 * @returns void
		 */
		public function __construct( $sTableName, $oData = null )
		{
			// relies on there only being one database profile or a default profile set:
			$this->oDatabase = Database::GetConnection();

			$this->sTableName = $sTableName;
		
			// Get the schema for this table:
			$this->oDatabase->GetSchema( $this->sTableName );
			
			// Prepare the fields for this table for CRUD->column access:
			$this->PrepareColumns();

			// If data is supplied, load it, depending on data type:
			
			if( !is_null( $oData ) )
			{
				$this->Load( $oData );
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
		
		
		
		/**
		 * This method attempts to load a record from the database based on the passed ID, or a 
		 * passed set of SQL query clauses. This method can be used retrieve one record from the 
		 * database, or a set of records that can be iterated through.
		 * 		 		 
		 * @argument mixed The ID of the data being found
		 * @argument array Additional databases clauses, including: join, where, order, offset and 
		 * 		 limit. All except for join are string that are directly appended to the query. 
		 * 		 Join is an array of referenced tables to inner join.
		 * @returns CRUD returns a reference to itself to allow chaining
		 */
		public function Find( $xId = null, $aClauses = array() )
		{
			$aPrimaryKey = $this->oDatabase->GetTablePrimaryKey( $this->sTableName );
			
			if( !empty( $xId ) )
			{
				// If we have a primary key specified, make sure it the number of columns matches:
				if( count( $aPrimaryKey ) > 1 && ( !is_array( $xId ) || 
					count( $xId ) != count( $aPrimaryKey ) ) )
				{
					throw new QueryFailedException( "Invalid record key provided" );
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
			
			return( $this );
			
		} // Find()
		
		
		/**
		 * This method returns the number of records that match a passed set of SQL query clauses. 
		 * This method is very similiar to Find(), except that it returns an integer value 
		 * representing the number of matching records.
		 * 
		 * @argument array Additional databases clauses, including: join and where. Where is a string 
		 * 		 that are directly appended to the query. Join is an array of referenced tables to 
		 * 		 inner join.
		 * @returns int Returns the number of database records that match the passed clauses
		 */
		public function FindCount( $aClauses = array() )
		{
			$aPrimaryKey = $this->oDatabase->GetTablePrimaryKey( $this->sTableName );
						
			
			$sWhere = isset( $aClauses[ "where" ] ) ? $aClauses[ "where" ] : "";
			
			if( !empty( $sWhere ) )
			{
				$sWhere = " WHERE {$sWhere} ";
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
				throw new QueryFailedException( $this->oDatbase->GetLastError() );
			}
						
			return( $this->oDataSet->Rewind()->count );
			
		} // FindCount()
		
		
		/////////////////////////////////////////////////////////
		public function GetRecord()
		{			
			$oRecord = new StdClass();
			
			foreach( $this->aData as $sKey => $xValue )
			{
				if( is_object( $xValue ) )
				{
					$oRecord->$sKey = $xValue->GetRecord();
				}
				else
				{
					$oRecord->$sKey = $xValue;
				}
			}
			
			return( $oRecord );
		
		} // GetRecord()
		
		
		public function GetAll()
		{
			$aRecords = array();
			
			$this->Rewind();
			
			foreach( $this->oDataSet as $oData )
			{
				$aRecords[] = $oData;
			}
		
			return( $aRecords );
		
		} // GetAll()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		protected function FindRelationship( $sName )
		{
			$aForeignKeys = $this->oDatabase->GetTableForeignKeys( $this->sTableName );
			
			foreach( $aForeignKeys as $aForeignKey )
			{
				if( $aForeignKey[ "name" ] == $sName )
				{
					return( $aForeignKey );
				}
			}
			
			return( null );
		
		} // FindRelationship()
		
		
		/**
		 * Loads the specified data (either an array or object) into the CRUD object. This 
		 * array/object to load can contained referenced data (through foreign keys) as either
		 * an array or object.
		 * 		 		 
		 * @argument mixed The data to load into the CRUD object
		 * @returns void
		 */
		protected function Load( $oRecord )
		{
			if( !is_object( $oRecord ) && !is_array( $oRecord ) )
			{
				return;
			}
			
      	$aColumns = $this->oDatabase->GetTableColumns( $this->sTableName );
			$aRelationships = $this->oDatabase->GetTableForeignKeys( $this->sTableName );

         foreach( $oRecord as $sKey => $xValue )
         {
         	if( is_array( $xValue ) || is_object( $xValue ) )
            {
					if( isset( $aRelationships[ $sKey ] ) )
					{
						$aRelationship = $aRelationships[ $sKey ];
						$sTable = $aRelationships[ $sKey ][ "table" ];
						
						if( $aRelationship[ "type" ] == "1-1" || $aRelationship[ "type" ] == "m-1" )
						{							
							$this->aData[ $sKey ] = $this->InstantiateClass( $sTable, $xValue );
						}
						else if( $aRelationships[ $sKey ][ "type" ] == "1-m" )
						{
							if( !isset( $this->aData[ $sKey ] ) )
							{
								$this->aData[ $sKey ] = array();
							}
							
							foreach( $xValue as $oRelatedData )
							{								
								$this->aData[ $sKey ] = $this->InstantiateClass( $sTable, $oRelatedData );
							}
						}
					}					
            }
            else if( isset( $aColumns[ $sKey ] ) )
            {
					$this->aData[ $sKey ] = $xValue;
         	}
			}
			
			$this->bEmptySet = false; // is this still used?

		} // Load()
		
		
		
		
		/**
		 *  Determines whether or not there is currently data in the CRUD object. Data is loaded into 
		 *  CRUD through the Find() method, through specifying data into fields manually, or by 
		 *  passing data to the constructor. If any of these cases are met, this method will 
		 *  return true.	 		 	 
		 * 	
		 * @returns boolean True if there is no data currently in CRUD, false otherwise
		 */
		protected function IsEmpty()
		{
			return( $this->bEmptySet );
			
		} // IsEmpty()
		
		
		/**
		 *  Gets the number of rows returned by the last Find() call. If Find() has not yet been 
		 *  called, this method will return This method is invoked through the __call() method to 
		 *  allow using the method name Count(), which is a reserved word in PHP. 		 		 	 
		 * 	
		 * @returns integer The number of results in the data set
		 */
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
							
				$this->aData[ $sName ] = $this->InstantiateClass( $aRelationship[ "table" ] );
				$this->aData[ $sName ]->Find( null, array( "where" => $sWhere ) );
			}
			else
			{
				$sLocalColumn = current( $aRelationship[ "local" ] );
				
				if( isset( $this->aData[ $sLocalColumn ] ) )
				{
					$this->aData[ $sName ] = $this->InstantiateClass( $aRelationship[ "table" ] );
					$this->aData[ $sName ]->Find( $this->aData[ $sLocalColumn ] );	
				}
				else
				{
					// Modified 2007-12-29 to prevent error:
					// Notice: Indirect modification of overloaded property has no effect
					// If we are dynamically creating a record, we need to return an empty object for 
					// this relationship to load into
					
					$this->aData[ $sName ] = $this->InstantiateClass( $aRelationship[ "table" ] );
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
				
				if( $this->oDatabase->IsPrimaryKeyReference( $this->sTableName, $sPrimaryKey ) )
				{
					// See Task #56
					$bInsert = !$this->RecordExists();
				}
				else if( empty( $this->aData[ $sPrimaryKey ] ) )
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
					
			// Save all dependencies first

			foreach( $aForeignKeys as $aRelationship )
			{
				$sRelationshipName = $aRelationship[ "name" ];
				
				if( isset( $this->aData[ $sRelationshipName ] ) && $aRelationship[ "dependency" ] )
				{
					$this->aData[ $sRelationshipName ]->SaveAll();
					
					// We only work with single keys here !!
					$sLocal = reset( $aRelationship[ "local" ] );
					$sForeign = reset( $aRelationship[ "foreign" ] );
					
					$this->aData[ $sLocal ] = $this->aData[ $sRelationshipName ]->$sForeign;
				}
			}

			// Save the primary record
			
			$this->Save();
			
			// Save all related data last

			foreach( $aForeignKeys as $aRelationship )
			{
				$sRelationshipName = $aRelationship[ "name" ];
				
				if( isset( $this->aData[ $sRelationshipName ] ) && !$aRelationship[ "dependency" ] )
				{
					// We only work with single keys here !!
					$sLocal = reset( $aRelationship[ "local" ] );
					$sForeign = reset( $aRelationship[ "foreign" ] );
						
					if( $aRelationship[ "type" ] == "1-m" )
					{
						foreach( $this->aData[ $sRelationshipName ] as $oRelationship )
						{
							$oRelationship->$sForeign = $this->aData[ $sLocal ];
							$oRelationship->SaveAll();
						}
					}
					else if( $aRelationship[ "type" ] == "1-1" )
					{						
						$this->aData[ $sRelationshipName ]->$sForeign = $this->aData[ $sLocal ];
						$this->aData[ $sRelationshipName ]->SaveAll();
					}
				}
			}
		
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
				if( in_array( $aColumn[ "field" ], $aPrimaryKeys ) && count( $aPrimaryKeys ) == 1 && 
					!$this->oDatabase->IsPrimaryKeyReference( $this->sTableName, reset( $aPrimaryKeys ) ) )
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
				throw new Exception( "Failed on Query: {$sSQL} - " . $this->oDatabase->GetLastError() );
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
			
		} // UpdateQuery()
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		protected function RecordExists()
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
				throw new QueryFailedException( "Failed on Query: " . $this->oDatabase->GetLastError() );
			}
			
			return( $oResultSet->Count() != 0 );
		
		} // RecordExists()
		
		
		/**
		 *	 Destroys (deletes) the current data. This method will delete the primary record 
		 *	 (assuming that the primary key for the data is set).
		 * 	
		 * @returns void
		 */
		public function Destroy()
		{
			$aPrimaryKeys = $this->oDatabase->GetTablePrimaryKey( $this->sTableName );
			
			$sSQL = "DELETE FROM
				{$this->sTableName}
			WHERE ";
			
			$sWhere = "";
			
			foreach( $aPrimaryKeys as $sKey )
			{
				$sWhere .= empty( $sWhere ) ? "" : " AND ";
				$sWhere .= "{$sKey} = " . $this->oDatabase->FormatData( 
					$this->oDatabase->GetColumnType( $this->sTableName, $sKey ), $this->aData[ $sKey ] );
			}
			
			$sSQL .= $sWhere;
			
			if( !$this->oDatabase->Query( $sSQL ) )
			{
				throw new QueryFailedException( "Failed on Query: " . $this->oDatabase->GetLastError() );
			}
		
		} // Destroy()
		
		
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
    	

		/**
		 *  Returns the current object from the DataSet generated from the last call to Find().
		 *  This method is part of the PHP Iterator implementation, see
		 *  http://www.php.net/~helly/php/ext/spl/interfaceIterator.html for reference.		 
		 * 	
		 * @returns CRUD Returns a CRUD object if there data, or null otherwise
		 */
		public function Current() 
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

	
		//////////////////////////////////////////////////////////////////////////////////////////////
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
		
		
		/**
       * Returns the table name associated with this CRUD object
       *
       * @returns string The name of the table associated with this CRUD object
       */
		public function GetTableName()
		{
			return( $this->sTableName );
			
		} // GetTableName() 
		
		
		/**
       * Returns the data currently stored in the CRUD object a well formed XML document as a
       *	string representation. This requires the DOM and SimpleXML extensions of PHP to be 
		 *	installed. If either extension is not installed, this method will throw an exception.
       * 	        
       * @argument bool Should this returned XML include references? Default false.
       * @argument bool Should this returned XML include all records returned by the last Find()
       * 	call? If not, only the current record stored is returned. Default false.      
       * @returns string A well formed XML document as a string representation
       */
		public function AsXMLString( $bIncludeReferences = false, $bProvideAll = false )
		{
			$oXML = $this->asXML( $bIncludeReferences, $bProvideAll );			
			$sXML = XMLFunctions::PrettyPrint( $oXML->asXML() );
			
			return( $sXML );
			
		} // AsXMLString()
		
		
		/**
       * Returns the data currently stored in the CRUD object a well formed XML document as a 
       * SimpleXMLElement object. This method requires the SimpleXML extension of PHP to be
       * installed. If the SimpleXML extension is not installed, this method will throw an 
       * exception.
       * 	        
       * @argument bool Should this returned XML include references? Default false.
       * @argument bool Should this returned XML include all records returned by the last Find()
       * 	call? If not, only the current record stored is returned. Default false.      
       * @returns SimpleXMLElement The data requested as a SimpleXMLElement object
       */
		public function AsXML( $bIncludeReferences = false, $bProvideAll = false )
		{
			$oXML = null;
			
			if( $bProvideAll )
			{
				$sName = $this->sTableName;
				$sElementName = StringFunctions::ToSingular( $this->sTableName );
				
				$oXML = new SimpleXMLElement( "<{$sName}></{$sName}>" );
				
				foreach( $this as $oObject )
				{
					$oElement = $oXML->addChild( $sElementName );
					$this->AddColumns( $oElement, $oObject, $this->sTableName );
					
					if( $bIncludeReferences )
					{
						$this->AddReferences( $oElement, $oObject, $this->sTableName );
					}
				}
			}
			else
			{
				$sName = StringFunctions::ToSingular( $this->sTableName );
				
				$oXML = new SimpleXMLElement( "<{$sName}></{$sName}>" );
				
				$this->AddColumns( $oXML, $this, $this->sTableName );
				$this->AddReferences( $oXML, $this, $this->sTableName );
			}
		
			return( $oXML );
				
		} // AsXML()
		
	
		/**
       * Add the database table columns for the specified table, from the specified object, to
       * the specfied SimpleXMLElement. Used internally by AsXML() 
       * 	        
       * @argument SimpleXMLElement 
       * @argument CRUD
       * @argument string		      
       * @returns SimpleXMLElement The data requested as a SimpleXMLElement object
       */
		private function AddColumns( &$oElement, &$oObject, $sTableName )
		{
			$aColumns = $this->oDatabase->GetTableColumns( $sTableName );
			
			foreach( $aColumns as $aColumn )
			{
				$oElement->addChild( $aColumn[ "field" ], $oObject->{$aColumn[ "field" ]} );
			}
			
		} // AddColumns()
		

		/**
       * Add the database table references for the specified table, from the specified object, to
       * the specfied SimpleXMLElement. Used internally by AsXML()   
       * 	        
       * @argument SimpleXMLElement 
       * @argument CRUD
       * @argument string		      
       * @returns SimpleXMLElement The data requested as a SimpleXMLElement object
       */
		private function AddReferences( &$oElement, &$oObject, $sTableName )
		{
			$aTableReferences = $this->oDatabase->GetTableForeignKeys( $sTableName );
				
			foreach( $aTableReferences as $aReference )
			{
				$oData = $this->{$aReference[ "name" ]};
								
				if( !empty( $oData ) && !$oData->Empty() )
				{
					if( $aReference[ "type" ] == "1-m" )
					{
						$sChildReferenceName = StringFunctions::ToSingular( $aReference[ "name" ] );
						
						$oReference = $oElement->addChild( $aReference[ "name" ] );
						
						foreach( $oData as $oDataElement )
						{
							$oChildReference = $oReference->addChild( $sChildReferenceName );
							
							$this->AddColumns( $oChildReference, $oDataElement, $aReference[ "table" ] );
						}
					}
					else
					{
						$oReference = $oElement->addChild( $aReference[ "name" ] );
						$this->AddColumns( $oReference, $oData, $aReference[ "table" ] );
					}
				}
				
			}
		
		} // AddReferences()

		
		/**
       * Returns the data currently stored in the CRUD object as a JSON (JavaScript object notation)
       * string. If bIncludeReferences is true, then each reference to the table is considered and 
       * added to the XML document.
       *
       * @argument bool Toggles whether references/relationships should be stored in the JSON string       
       * @returns string A JSON string representing the CRUD object
       */
		public function AsJSON( $bIncludeReferences = false)
		{
			$oJSON = new JSONObject();
			
			$aColumns = $this->oDatabase->GetTableColumns( $this->sTableName );
			
			foreach( $aColumns as $aColumn )
			{
				$oJSON->AddAttribute( $aColumn[ "field" ], $this->aData[ $aColumn[ "field" ] ] );
			}
			
			if( $bIncludeReferences )
			{
				$aTableReferences = $this->oDatabase->GetTableForeignKeys( $this->sTableName );
					
				foreach( $aTableReferences as $aReference )
				{
					$oData = $this->{$aReference[ "name" ]};
									
					if( !empty( $oData ) && !$oData->Empty() )
					{
						$aReferenceColumns = $this->oDatabase->GetTableColumns( $aReference[ "table" ] );
							
						if( $aReference[ "type" ] == "1-m" )
						{						
							$aReferences = array();
							
							$sChildReferenceName = StringFunctions::ToSingular( $aReference[ "name" ] );
							
							//$oReference = $oElement->addChild( $aReference[ "name" ] );
							
							foreach( $oData as $oDataElement )
							{
								$oReferenceJSON = new JSONObject();
							
								foreach( $aReferenceColumns as $aColumn )
								{
									$oReferenceJSON->AddAttribute( $aColumn[ "field" ], $oData->{$aColumn[ "field" ]} );
								}
							
								$aReferences[] = $oReferenceJSON;
							}
							
							
							$oJSON->AddAttribute( $aReference[ "name" ], $aReferences );							
						}
						else
						{
							$oReferenceJSON = new JSONObject();
							
							foreach( $aReferenceColumns as $aColumn )
							{
								$oReferenceJSON->AddAttribute( $aColumn[ "field" ], $oData->{$aColumn[ "field" ]} );
							}
							
							$oJSON->AddAttribute( $aReference[ "name" ], $oReferenceJSON );
						}
					}
					
				}
			}
			
			return( $oJSON->__toString() );
			
		} // AsJSON()
		
		
		/**
       * Creates a readable, string representation of the object using print_r and returns that
       * string.       
       *
       * @returns string A readable, string representation of the object
       */
		public function __toString()
		{
			return( print_r( $this->aData, true ) );
			
		} // __toString()
		
		
		/**
       * The purpose of this method is to instantiate a class based on a table name. This is used 
       * several times throughout the CRUD class. If we determine that a Model class exists for the 
       * specified table name, then we instiantiate an object of that class. Otherwise, we 
       * instantiate an object of CRUD for that table name.
       *
       * To determine if a Model exists, we look for a class name that matches the English singular
       * version of the table name. If we find such a class, and if this class is a subclass of
       * the Model class (which itself is a subclass of CRUD), we assume this is the Model class
       * we should use and instantiate it.
       *
       * @returns object The generated object, either CRUD or a subclass of CRUD
       */
		private function InstantiateClass( $sTableName, $xData = null )
		{			
			$sModelName = StringFunctions::ToSingular( $sTableName );
			
			$oObject = null;
			
			if( class_exists( $sModelName, true ) && is_subclass_of( $sModelName, "Model" ) )
			{
				$oObject = new $sModelName( $xData );
			}
			else
			{
				$oObject = new CRUD( $sTableName, $xData );
			}			 
			
			return( $oObject );
			
		} // InstantiateClass()
		
	}; // CRUD()

?>