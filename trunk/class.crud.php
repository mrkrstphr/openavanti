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
		public function Find( $xId )
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
			
			if( empty( $xId ) )
			{
				trigger_error( "Invalid Key Provided", E_USER_ERROR );
				exit;
			}
			
			$oQuery = $this->spawnQuery();
					
			// Handle our provided key:	
			$sWhere = "";		

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
			else
			{
				// we have a singular primary key -- put the data in the WHERE clause:
				$sKey = reset( $aPrimaryKey );
				$sType = $this->GetFieldType( $this->tableName, $sKey );
				
				$sWhere .= !empty( $sWhere ) ? " AND " : " WHERE ";
				$sWhere .= "{$sKey} = " . $this->FormatData( $sType, $xId ) . " ";
			}
			
			
			// Build the fields for the primary table:
						
			$sFields = "";
			
			foreach( $this->aSchemas[ $this->tableName ][ "fields" ] as $aField )
			{
				$sFields .= !empty( $sFields ) ? ", \n" : "";
				$sFields .= $aField[ "field" ];
			}			
			
			// Concatenate all the pieces of the query together:
			$sSQL = "SELECT {$sFields} FROM {$this->tableName} {$sWhere}";		

			// Execute and pray:
			if( !$oQuery->execute( $sSQL ) )
			{
				trigger_error( "Failed on Query. Error: " . 
					$query->getLastError() . "\n Query: {$sSQL}", E_USER_ERROR );
				exit;
			}
			
			// Loop the data and create member variables
			if( $oQuery->fetch() )
			{
				// Grab a copy of the record:
				$oRecord = $oQuery->getRecord();
				
				// Loop each field
				foreach( $oRecord as $sKey => $sValue )
				{
					// otherwise this data is part of the primary table, 
					// create a member variable:
					$this->$sKey = $sValue;
				}
				
				$this->emptySet = false;
			}	
			
		} // Find()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public function IsEmpty()
		{
			return( $this->emptySet );
			
		} // IsEmpty()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public function __get( $sName )
		{			
			// first, determine if a relationship by this name exists
		
			//echo "Looking for: {$sName}<br /><br />";
		
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
				$this->$sName = array();
			
				
			}
			else
			{
				$sLocalColumn = current( $aRelationship[ "local" ] );
				
				$this->$sName = new crud( $aRelationship[ "table" ] );		
				$this->$sName->Find( $this->$sLocalColumn );
			}
			
			return( $this->$sName );
			
		} // __get()
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		public function save()
		//
		// Description:
		//		Based on presence of primary key data, either creates a new record, or updates the
		//		existing record
		//
		{
			/*
			// grab a copy of the primary key:
			$primaryKey = reset( $this->aSchemas[ $this->tableName ][ "primary_key" ] );
			
			
			if( empty( $this->$primaryKey ) )
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
			
			*/
		
		} // save()
				
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		protected function insert()
		//
		// Description:
		//
		//
		{
		
		} // insert()
		
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		protected function update()
		//
		// Description:
		//		Responsible for updating the currently stored data for primary table and all foreign
		//		tables referenced.
		//
		{
			/*
			// get the primary key of the primary table:
			$primaryKey = reset( $this->aSchemas[ $this->tableName ][ "primary_key" ] );
		
			// update the primary record:
			$this->updateQuery( $this->tableName, $this->$primaryKey );
			

			// loop each foreign table:	
			foreach( $this->aSchemas[ $this->tableName ][ "foreign_key" ] as $foreignKey )
			{
				if( $foreignKey[ "type" ] != "1-1" && $foreignKey[ "type" ] != "1-m" )
				{
					continue;
				}
			
				// temporary -- for now, if we reference the same table, don't update -- TODO	
				if( $foreignKey[ "table" ] == $this->tableName )
				{
					continue;
				}
				
				
				// loop each copy of a foreign record of this table type (may only be one, or may
				// have multiple (ie, line items):
				
				foreach( $this->$foreignKey[ "name" ] as $data )
				{
				
					echo '<pre>' . print_r( $data, true ) . '</pre>';
					
					$primaryKey = reset( $this->aSchemas[ $foreignKey[ "table" ] ][ "primary_key" ] ); 
					
					if( isset( $data->$primaryKey ) )
					{
						$table = $foreignKey[ "table" ];
						
						//echo $table . "<br />";
						
						$this->updateQuery( $foreignKey[ "table" ], 
							$data->$primaryKey );
					}
					else
					{
					//	$this->insertQuery( );
					}
				
				} // foreach( instanceOf( foreign_table )

			} // foreach( foreign_table )
			*/			
			
		} // update()
		
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		public function updateQuery( $table, $id )
		//
		// Description:
		//		Called by update() method for primary table and all foreign tables to facilitate
		//		updating the data.
		//
		{
			/*
			// grab a copy of this table's foreign key -- TODO : remove duplication:
			$primaryKey = reset( $this->aSchemas[ $table ][ "primary_key" ] );
			
			// start the update query:
			$sql = "UPDATE " . $table . " SET ";
			

			$set = "";
			
			
			// loop each field in the table and specify it's data:
			foreach( $this->aSchemas[ $table ][ "fields" ] as $field )
			{
				// do not update certain fields:
				if( in_array( $field[ "field" ], array( "created_date", "created_stamp", "created_on", $primaryKey ) ) )
				{
					continue;
				}
				
				// automate updating update date fields:
				if( in_array( $field[ "field" ], array( "updated_date", "updated_stamp", "updated_on" ) ) )
				{
					$this->$field[ "field" ] = gmdate( "Y-m-d H:i:s" );
				}
				
				// complete the query for this field:
				$set .= ( !empty( $set ) ? ", " : "" ) . 
					$field[ "field" ] . " = " . 
						$this->FormatData( $field[ "type" ], 
							$this->$field[ "field" ] ) . " ";
			}
			
			// if we found no fields to update, return:
			if( empty( $set ) )
			{
				return;
			}
			
			// combine the query data:
			$sql .= "{$set} WHERE {$primaryKey} = {$this->$primaryKey} "; 
			
			
			// for now:
			echo "{$sql}<br /><br />";
			*/
			
		} // updateQuery()
		
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		public function destroy( $cascade = true )
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
		
		} // destroy()
	
		
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
