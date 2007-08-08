<?php

	////////////////////////////////////////////////////////////////////////////////////////////////
	class Crud extends database
	//
	// Description:
	//		
	//
	{
		protected $tableName = null;
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		public function __construct( $tableName, $id = null, $where = null )
		//
		// Description:
		//		Get the schema of the supplied table name, and, if an id is specified, load the 
		//		specified data into this object
		//
		{
			parent::__construct();

			$this->tableName = $tableName;
			
			$this->getSchema( $this->tableName );
			
			if( !is_null( $id ) || !is_null( $where ) )
			{
				$this->load( intval( $id ), $where );
			}
		
		} // __construct()
		
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		protected function load( $id = '', $where = null )
		//
		// Description:
		//
		//
		{
			$query = $this->spawnQuery();
			
			// main data:
			
			// note - currently assumes we only have one field in the primary key
			
			$primaryKey = $this->aSchemas[ $this->tableName ][ "primary_key" ][ 0 ];

			if( !is_array( $where ) )
			{
				$where = array();
			}

			if( !empty( $id ) && !isset( $where[ $primaryKey ] ) )
			{
				$where[ "{$this->tableName}.{$primaryKey}" ] = $id;
			}
			
			$select = " SELECT ";
			
			// build the fields for the primary table:
			$fields = array();
			
			foreach( $this->aSchemas[ $this->tableName ][ "fields" ] as $field )
			{
				$fields[] = $this->tableName . "." . $field[ "field" ] . " ";
			}
			
			
			// from clause:
			$from = " FROM {$this->tableName} ";
			
			
			// build the join clause (appends the fields):
			
			$join = array();
			
			$nameLookup = array();
			
			foreach( $this->aSchemas[ $this->tableName ][ "foreign_key" ] as $relationship )
			{
				if( $relationship[ "type" ] == "m-1" || $relationship[ "type" ] == "1-1" )
				{
					// LEFT JOIN [relationship_table] AS [relationship_name]
					// ON [relationship_table.field ] = [primary_table.field]
					
					$join[] = "LEFT JOIN " . $relationship[ "table" ] . " AS " . $relationship[ "name" ] . " \n" . 
						"ON " . $relationship[ "name" ] . "." . current( $relationship[ "foreign" ] ) . " = " . 
						$this->tableName . "." . current( $relationship[ "local" ] );
					
					$nameLookup[ $relationship[ "name" ] ] = $relationship[ "table" ];
						
					foreach( $this->aSchemas[ $relationship[ "table" ] ][ "fields" ] as $field )
					{
						// SELECT [relationship_name].[field] AS [relationship_name].[field]
						$fields[] = $relationship[ "name" ] . "." . $field[ "field" ] . " AS " . 
							"\"" . $relationship[ "name" ] . "." . $field[ "field" ] . "\" " ;
					}
				}
			}
			
			// build the where clause:
			
			$tmpWhere = '';

			foreach( $where as $key => $value )
			{
			
				// holy moly, clean this up:
			
				// figure out the field type:
				$field = explode( ".", $key );
				$tmpTable = ''; $tmpField = '';
				
				if( count( $field ) == 1 )
				{
					$tmpTable = $this->tableName;
					$tmpField = $key;
				}
				elseif( count( $field ) == 2 )
				{
					$tmpTable = $field[0];
					$tmpField = $field[1];
				}
				
				if( !isset( $this->aSchemas[ $tmpTable ] ) )
				{
					$tmpTable = $nameLookup[ $tmpTable ];
				}
				
				$type = $this->GetFieldType( $tmpTable, $tmpField );
				
				$tmpWhere .= !empty( $tmpWhere ) ? " AND " : " WHERE ";
				$tmpWhere .= "{$key} = " . $this->FormatData( $type, $value ) . " ";
			}

			$where = $tmpWhere;

			$limit = " LIMIT 1 ";
			
			
			// concatenate all the pieces of the query together:
			
			$sql = "{$select} " . implode( ", \n", $fields ) . " {$from} " . 
				implode( "\n", $join ) . " {$where} {$limit}";
			
			$this->aSchemas[ $this->tableName ][ "sql" ] = str_replace( "\n", "", $sql );

			// execute and pray:
			if( !$query->execute( $sql ) )
			{
				trigger_error( "Failed on Query. Error: " . 
					$query->getLastError() . "\n Query: {$sql}", E_USER_ERROR );
				exit;
			}
			
			// loop the data and create member variables
			if( $query->fetch() )
			{
				// grab a copy of the record:
				$record = $query->getRecord();
				
				// loop each field
				foreach( $record as $key => $value )
				{
					if( strpos( $key, "." ) )
					{
						// if the key contains a period, it is a tablename.field name combination,
						// and data must be loaded into the appropriate member class.

						$data = explode( ".", $key );
						
						if( !isset( $this->$data[0] ) )
						{
							// if we haven't defined this class yet, create it:
							$this->$data[0] = new StdClass();
						}

						// save the data:
						$this->$data[0]->$data[1] = $value;
					}
					else
					{
						// otherwise this data is part of the primary table, 
						// create a member variable:
						$this->$key = $value;
					}
				}
			}
			

			// 1-M relationships:
			foreach( $this->aSchemas[ $this->tableName ][ "foreign_key" ] as $foreignKey )
			{
				if( $foreignKey[ "table" ] == $this->tableName )
				{
					continue;
				}
				
				if( $foreignKey[ "type" ] == "m-1" || $foreignKey[ "type" ] == "1-1" )
				{
					continue;
				}
				
				if( $foreignKey[ "type" ] == "1-m" || $foreignKey[ "type" ] == '' ) // blank is temporary
				{
					// if the relationship is 1-M, we create an array to hold the many data records
					// that might refer to this table:
					
					$this->$foreignKey[ "table" ] = array();
				}
				
				// setup the query to get the data:
				
				$sql = "SELECT
					*
				FROM
					" . $foreignKey[ "table" ] . "
				WHERE ";
				
				$where = '';
				
				// loop each foreign key and create the where clauses for the relationships:
				foreach( $foreignKey[ "foreign" ] as $key => $foreignField )
				{
					if( !empty( $this->$foreignKey[ "local" ][ $key ] ) )
					{
						// where = foreignTable.foreignField = valueOf( thisTable.localField )
					
						$where .= !empty( $where ) ? " AND " : "";
						$where .= $foreignField . " = " . $this->$foreignKey[ "local" ][ $key ] . " " ;
					}
				}
				
				
				if( empty( $where ) )
				{
					// if we somehow do not have a where, do not execute the query:
					continue;
				}
				
				$sql .= $where;
				
				
				// execute the query:
				$query = $this->spawnQuery();
				
				if( !$query->execute( $sql ) )
				{
					trigger_error( "Failed on Query: {$sql}", E_USER_ERROR );
					exit;
				}
				
				
				$i = 0;
				
				// loop each result:
				while( $query->fetch() )
				{
					// get a copy of the data
					$record = $query->getRecord();
					
					$table = &$this->$foreignKey[ "table" ];
				
					// create a class for this data object in the array:
					$table[ $i ] = new StdClass();
					$table = &$table[ $i ];
					
					// loop each value and store it in the member variable:
					foreach( $record as $key => $value )
					{
						$table->$key = $value; 
					}
					
					$i++;
				}

			} // foreach( foreign_key )
			
			
			
		} // load()
		
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		public function save()
		//
		// Description:
		//		Based on presence of primary key data, either creates a new record, or updates the
		//		existing record
		//
		{
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
			
		} // update()
		
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		public function updateQuery( $table, $id )
		//
		// Description:
		//		Called by update() method for primary table and all foreign tables to facilitate
		//		updating the data.
		//
		{
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
