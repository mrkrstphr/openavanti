<?php

	////////////////////////////////////////////////////////////////////////////////////////////////
	class Crud extends database
	//
	// Description:
	//		
	//
	{
		protected $tableName = null;
		
		protected $emptySet = true;
		
		protected $oSelect = null;
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		public function __construct( $sTableName ) //, $iID = null, $sWhere = null )
		//
		// Description:
		//		Get the schema of the supplied table name, and, if an id is specified, load the 
		//		specified data into this object
		//
		{
			parent::__construct();

			$this->tableName = $sTableName;
			
			$this->GetSchema( $this->tableName );
		
		} // __construct()
		
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		public function Find( $xId = null, $aClauses = array() )
		//
		// Description:
		//
		//
		{			
			$aPrimaryKey = &$this->aSchemas[ $this->tableName ][ "primary_key" ];
			
			if( !empty( $xId ) )
			{
				if( count( $aPrimaryKey ) > 1 && ( !is_array( $xId ) || count( $xId ) != count( $aPrimaryKey ) ) )
				{
					trigger_error( "Invalid Key Provided", E_USER_ERROR );
					exit;
				}
			}
			
			if( empty( $xId ) && !isset( $aClauses[ "where" ] ) )
			{
				trigger_error( "Invalid Key Provided", E_USER_ERROR );
				exit;
			}
			
			
			$sWhere = isset( $aClauses[ "where" ] ) ? $aClauses[ "where" ] : "";
			
			
			$this->oSelect = $this->spawnQuery();
					
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
					$sType = $this->GetFieldType( $this->tableName, $sField );
					
					$sWhere .= !empty( $sWhere ) ? " AND " : " WHERE ";
					$sWhere .= "{$sField} = " . $this->FormatData( $sType, $sValue ) . " ";
				}
			}
			else if( !empty( $xId ) )
			{
				// we have a singular primary key -- put the data in the WHERE clause:
				$sKey = reset( $aPrimaryKey );
				$sType = $this->GetFieldType( $this->tableName, $sKey );
				
				$sWhere .= !empty( $sWhere ) ? " AND " : " WHERE ";
				$sWhere .= "{$sKey} = " . $this->FormatData( $sType, $xId ) . " ";
			}
			
			
			// Setup supplied joins:
			
			$sJoins = "";
			
			if( isset( $aClauses[ "join" ] ) )
			{
				foreach( $aClauses[ "join" ] as $sJoin )
				{
					$aRelationship = $this->FindRelationship( $sJoin );
					
					if( !$aRelationship )
					{
						new Exception( "Unknown join relationship specified: {$sJoin}" );
					}
					
					$sJoins .= " INNER JOIN " . $aRelationship[ "table" ] . " AS " . 
						"_" . $aRelationship[ "name" ] . " ON ";
					
					$sOn = "";
					
					foreach( $aRelationship[ "local" ] as $iIndex => $sField )
					{
						$sOn .= ( !empty( $sOn ) ? " AND " : "" ) . 
							"_" . StringFunctions::ToSingular( $this->tableName ) . 
							"." . $sField . " = " . "_" . $aRelationship[ "name" ] . 
							"." . $aRelationship[ "foreign" ][ $iIndex ];
					}
					
					$sJoins .= " {$sOn} ";
				}
			}
			
			$sFields = "_" . StringFunctions::ToSingular( $this->tableName ) . ".*";
			
			// Concatenate all the pieces of the query together:
			$sSQL = "SELECT {$sFields} FROM {$this->tableName} AS _" . 
				StringFunctions::ToSingular( $this->tableName ) . " {$sJoins} {$sWhere}";		

			//echo "<b>Finished Query</b>: {$sSQL}<br /><br />";

			// Execute and pray:
			if( !$this->oSelect->execute( $sSQL ) )
			{
				trigger_error( "Failed on Query. Error: " . 
					$this->oSelect->getLastError() . "\n Query: {$sSQL}", E_USER_ERROR );
				exit;
			}
			
			// Loop the data and create member variables
			if( $this->oSelect->fetch() )
			{
				$this->Load( $this->oSelect->GetRecord() );
			}	
			
		} // Find()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		protected function FindRelationship( $sName = "", $sTable = "" )
		{
			if( empty( $sName ) && empty( $sTableName ) )
			{
				return( null );
			}
		
			
			foreach( $this->aSchemas[ $this->tableName ][ "foreign_key" ] as $oForeignKey )
			{
				if( $oForeignKey[ "name" ] == $sName || $oForeignKey[ "table" ] == $sTable )
				{
					return( $oForeignKey );
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
		
			if( !is_null( $oRecord ) )
			{
				// Loop each field
				foreach( $oRecord as $sKey => $sValue )
				{
					// otherwise this data is part of the primary table, 
					// create a member variable:
					$this->$sKey = $sValue;
				}
				
				$this->emptySet = false;
			}
	
		} // Load()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		protected function IsEmpty()
		{
			return( $this->emptySet );
			
		} // IsEmpty()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		protected function GetCount() 
		{
		
			if( !is_null( $this->oSelect ) )
			{
				return( $this->oSelect->RowCount() );
			}
			
			return( 0 );
		
		} // GetCount()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public function Reset()
		{
			// this method is untested ---
			
			
			if( !is_null( $this->oSelect ) && $this->oSelect->Fetch() )
			{
				$this->oSelect->Reset();
				$this->Load( $this->oSelect->GetRecord() );
			}
			else
			{
				$this->emptySet = true;
				return( false );
			}
			
			return( true );
			
		
		} // Reset()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public function Next()
		{
			if( !is_null( $this->oSelect ) && $this->oSelect->Fetch() )
			{
				$this->Load( $this->oSelect->GetRecord() );
			}
			else
			{
				$this->emptySet = true;
				return( false );
			}
			
			return( true );
			
		} // Next()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public function __get( $sName )
		{			
			// first, determine if a relationship by this name exists		
			$aRelationship = array();
	
			foreach( $this->aSchemas[ $this->tableName ][ "foreign_key" ] as $aTmpRelationship )
			{			
				if( strtolower( $sName ) == strtolower( $aTmpRelationship[ "name" ] ) )
				{
					$aRelationship = $aTmpRelationship;
					break;
				}
			}
			
			if( !$aRelationship )
			{
				trigger_error( "", E_USER_ERROR );
				exit;
			}	
			
			// the relationship exists, attempt to load the data:
			
			if( $aRelationship[ "type" ] == "1-m" )
			{				
				$sWhere = "";
				
				foreach( $aRelationship[ "foreign" ] as $iIndex => $sKey )
				{
					$sRelated = $aRelationship[ "local" ][ $iIndex ];
					
					$sWhere .= empty( $sWhere ) ? "" : " AND ";
					$sWhere .= " {$sKey} = {$this->$sRelated} ";
				}
				
				$this->$sName = new crud( $aRelationship[ "table" ] );
				$this->$sName->Find( null, array(
					"where" => $sWhere 
				) );
			}
			else
			{
				$sLocalColumn = current( $aRelationship[ "local" ] );
				
				$this->$sName = new crud( $aRelationship[ "table" ] );		
				$this->$sName->Find( $this->$sLocalColumn );
			}
			
			return( $this->$sName );
			
		} // __get()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
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
			$aPrimaryKeys = $this->aSchemas[ $this->tableName ][ "primary_key" ];
			
			$bInsert = false;
			
			foreach( $aPrimaryKeys as $sPrimaryKey )
			{
				if( empty( $this->$sPrimaryKey ) )
				{
					$bInsert = true;
					break;
				}
			}
			
			// Fundamental flaw in this logic: if we are in a relationship table, which has
			// two primary keys, such as item_id and quantity, then both of those keys will
			// more than likely be present, even though the data might not be in the database.
			
			// In other words, when a compound key is comprised entirely of foreign keys,
			// this logic will not work.
			
			// Recommend possibly keeping track of whether or not we pulled the data from the
			// database (using Find()), or if it was programmer supplied.
			
			// Recommendation above is flawed as objects loaded from post may already exist in
			// the database but were not loaded from the database
			
			if( $bInsert )
			{
				// if there is no data in the primary key field of this object, we need to insert
				// a new record:
				$this->insert();
			}
			else
			{
				// if we do have data supplied in the primary key field, we need to update the data:
				$this->update();
			}
		
		} // Save()
				
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public function SaveAll()
		{
			$this->Save();
		
			foreach( $this as &$xVar )
			{
				if( ( is_object( $xVar ) && is_subclass_of( $xVar, "crud" ) ) || 
					( is_object( $xVar ) && strtolower( get_class( $xVar ) ) == "crud" ) )
				{
					$xVar->SaveAll();
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
			$oQuery = $this->SpawnQuery();

			$sFields = "";
			$sValues = "";
			
			$aPrimaryKeys = $this->aSchemas[ $this->tableName ][ "primary_key" ];			
			
			// loop each field in the table and specify it's data:
			foreach( $this->aSchemas[ $this->tableName ][ "fields" ] as $field )
			{
				// automate updating update date fields:
				if( in_array( $field[ "field" ], array( "created_date", "created_stamp", "created_on" ) ) )
				{
					$this->$field[ "field" ] = gmdate( "Y-m-d H:i:s" );
				}
				
				if( in_array( $field[ "field" ], $aPrimaryKeys ) )
				{
					continue;
				}				
				
				$sFields .= ( !empty( $sFields ) ? ", " : "" ) . 
					$field[ "field" ];
					
				$sValue = isset( $this->$field[ "field" ] ) ? 
					$this->$field[ "field" ] : "";
					
				$sValues .= ( !empty( $sValues ) ? ", " : "" ) . 
					$this->FormatData( $field[ "type" ], $sValue );
			}
			
			$sSQL = "INSERT INTO " . $this->tableName . " (
				{$sFields}
			) VALUES (
				{$sValues}
			)";
			
			if( !$oQuery->Execute( $sSQL ) )
			{
				throw new Exception( "Failed on Query: " . $oQuery->GetLastError() );
			}
		
		} // insert()
		
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		protected function Update()
		//
		// Description:
		//		Responsible for updating the currently stored data for primary table and all foreign
		//		tables referenced.
		//
		{	
			// get the primary key of the primary table:
			$primaryKey = reset( $this->aSchemas[ $this->tableName ][ "primary_key" ] );
		
			// update the primary record:
			$sSQL = $this->UpdateQuery();
			
			$oQuery = $this->SpawnQuery();
			
			if( !$oQuery->Execute( $sSQL ) )
			{
				trigger_error( "Failed on Query: {$sSQL}", E_USER_ERROR );
				exit;
			}
				
			//echo "<br />{$sSQL}<br /><br />";
			
		} // update()
		
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		protected function UpdateQuery()
		//
		// Description:
		//		Called by update() method
		//
		{
			$aPrimaryKeys = $this->aSchemas[ $this->tableName ][ "primary_key" ];
					
			$sSet = "";

			// loop each field in the table and specify it's data:
			foreach( $this->aSchemas[ $this->tableName ][ "fields" ] as $field )
			{
				// do not update certain fields:
				if( in_array( $field[ "field" ], array( "created_date", "created_stamp", "created_on" ) ) )
				{
					continue;
				}
				
				// automate updating update date fields:
				if( in_array( $field[ "field" ], array( "updated_date", "updated_stamp", "updated_on" ) ) )
				{
					$this->$field[ "field" ] = gmdate( "Y-m-d H:i:s" );
				}
				
				// complete the query for this field:
				$sSet .= ( !empty( $sSet ) ? ", " : "" ) . 
					$field[ "field" ] . " = " . 
						$this->FormatData( $field[ "type" ], 
							$this->$field[ "field" ] ) . " ";
			}
			
			// if we found no fields to update, return:
			if( empty( $sSet ) )
			{
				return;
			}
			
						
			$sWhere = "";
			
			foreach( $aPrimaryKeys as $sKey )
			{
				$sWhere .= !empty( $sWhere ) ? ", " : "";
				$sWhere .= "{$sKey} = {$this->$sKey} "; 
			}
			
			
			$sSQL = "UPDATE {$this->tableName} SET {$sSet} WHERE {$sWhere}";	
			

			return( $sSQL );
			
		} // updateQuery()
		
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		public function Destroy()
		//
		// Description:
		//		Destroys (deletes) the current data. This method will delete the primary record
		//		(assuming that the primary key for the data is set). If cascade is true, this method
		//		will also delete all data that is related through a 1-1 or 1-Many relationship
		//
		{
			/*
			$query = $this->spawnQuery();
			
			$query->begin();
			
			// for now, assume we only have one column in the primary key:
			$primaryKey = reset( $this->aSchemas[ $this->tableName ][ "primary_key" ] );
			
			if( !isset( $this->$primaryKey ) )
			{
				// if we don't have a primary key defined, we cannot delete
				return( false );
			}
			
						
			if( $cascade )
			{
				// if cascade is true, delete data from 1-1 and 1-M relationships:
				// (generally not a good idea to shut cascade off)
				
				// loop each foreign table:	
				foreach( $this->aSchemas[ $this->tableName ][ "foreign_key" ] as $foreignKey )
				{
					// quick access to the pertinant data:
					$name = $foreignKey[ "name" ];
					$table = $foreignKey[ "table" ];
						
					if( !isset( $this->$name ) )
					{
						// if no data is stored for this relationship, skip
						continue;
					}
						
					if( $foreignKey[ "type" ] == "1-m" )
					{
						// if the relationship is 1-to-many, we could have many pieces of data,
						// so we must loop the relationship data:
										
						foreach( $this->$name as $dataSet )
						{
							// build a where clause of the related table:
							$where = $this->buildWhereClause( 
								$this->aSchemas[ $table ][ "primary_key" ],
								$dataSet 
							);
								
							// put the query together and execute it:
							$sql = "DELETE FROM {$table} {$where}";
							
							if( !$query->execute( $sql ) )
							{
								trigger_error( "Query Failed: " . $query->getLastError(), E_USER_ERROR );
								$query->rollback();
							}
						}
					}
					else if( $foreignKey[ "type" ] == "1-1" )
					{						
						// if the relationship is 1-to-1, then we only have one piece of data
						// to delete, and it is not stored as an array
						
						// put the query together and execute it:
						$where = $this->buildWhereClause( 
							$this->aSchemas[ $table ][ "primary_key" ],
							$dataSet 
						);
							
						// put the query together and execute it:
						$sql = "DELETE FROM {$table} {$where}";
						
						if( !$query->execute( $sql ) )
						{
							trigger_error( "Query Failed: " . $query->getLastError(), E_USER_ERROR );
							$query->rollback();
						}
						
					} // if( relationship-type )
				
				} // foreach( relationship )
			
			} // if( cascade )


			// delete the primary record last:
			
			$sql = "DELETE FROM 
				{$this->tableName}
			WHERE 
				{$primaryKey} = " . $this->$primaryKey;
			
			if( !$query->execute( $sql ) )
			{
				trigger_error( "Query Failed: " . $query->getLastError(), E_USER_ERROR );
				$query->rollback();
			}
			
			$query->rollback();
			*/
		
		} // Destroy()
	
	
		////////////////////////////////////////////////////////////////////////////////////////////
		public function DestroyAll()
		{
			
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
	
	
	} // crud()

?>
