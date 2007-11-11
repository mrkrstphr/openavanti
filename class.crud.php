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
 * @version			0.05
 *
 */
 
	/**
	 * Database abstraction layer implementing CRUD procedures
	 *
	 * @category	Database
	 * @author		Kristopher Wilson
	 * @link			http://www.openavanti.com/docs/database
	 */
	class CRUD extends Database implements Iterator
	//
	// Description:
	//		
	//
	{
		protected $sTableName = null;		
		protected $oDataSet = null;
		
		protected $bEmptySet = true;		
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////
		public function __construct( $sTableName, $oData = null )
		//
		// Description:
		//		Get the schema of the supplied table name, and, if an id is specified, load the 
		//		specified data into this object
		//
		{
			parent::__construct();
			
			parent::CacheSchemas( false );
			parent::SetCacheDirectory( BASE_PATH . "/cache/schemas" );

			$this->sTableName = $sTableName;
		
			$this->GetSchema( $this->sTableName );
			
			$this->PrepareFields();

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
		protected function PrepareFields()
		//
		// Description:
		//		Setup variables for each database field for this table
		//
		{
			$aFields = $this->GetTableFields( $this->sTableName );
			
			foreach( $aFields as $aField )
			{
				$this->{$aField[ "field" ]} = "";
			}
		
		} // PrepareFields()
		
		
      ///////////////////////////////////////////////////////////////////////////////////////////
      protected function LoadArray( $aArray )
      {
      	$aFields = $this->GetTableFields( $this->sTableName );

			echo "Loading...<br />";

         foreach( $aArray as $sKey => $xValue )
         {
         	if( is_object( $xValue ) )
            {
                
            }
            else if( is_array( $xValue ) )
            {
					if( isset( self::$aSchemas[ $this->sTableName ][ "foreign_key" ][ $sKey ] ) )
					{
						$this->$sKey = new $sKey( $xValue );
					}					
            }
            else
            {
               // problem is that the key of aFields is numeric
               if( isset( $aFields[ $sKey ] ) )
					{
						$this->$sKey = $xValue;
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
			$aPrimaryKey = $this->GetTablePrimaryKey( $this->sTableName );
			
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
					$sType = $this->GetFieldType( $this->sTableName, $sField );
					
					$sWhere .= !empty( $sWhere ) ? " AND " : " WHERE ";
					$sWhere .= "{$sField} = " . $this->FormatData( $sType, $sValue ) . " ";
				}
			}
			else if( !empty( $xId ) )
			{
				// we have a singular primary key -- put the data in the WHERE clause:
				$sKey = reset( $aPrimaryKey );
				$sType = $this->GetFieldType( $this->sTableName, $sKey );
				
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
			
			// Concatenate all the pieces of the query together:
			$sSQL = "SELECT {$sFields} FROM {$this->sTableName} AS _" . 
				StringFunctions::ToSingular( $this->sTableName ) . " {$sJoins} {$sWhere} {$sOrder}";		

			//echo "<b>Finished Query</b>: {$sSQL}<br /><br />";

			// Execute and pray:
			if( !( $this->oDataSet = $this->Query( $sSQL ) ) )
			{
				trigger_error( "Failed on Query. Error: " . 
					$this->getLastError() . "\n Query: {$sSQL}", E_USER_ERROR );
				exit;
			}
			
			// Loop the data and create member variables
			if( $this->oDataSet->Count() != 0 )
			{
				$this->Load( $this->oDataSet->Rewind() );
			}	
			
		} // Find()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		protected function FindRelationship( $sName = "", $sTable = "" )
		{
			if( empty( $sName ) && empty( $sTableName ) )
			{
				return( null );
			}
		
			$aForeignKeys = $this->GetTableForeignKeys( $this->sTableName );
			
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
					$this->$sKey = $sValue;
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
			
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public function __get( $sName )
		{
			$aRelationships = $this->GetTableForeignKeys( $this->sTableName );

			if( !isset( self::$aSchemas[ $this->sTableName ][ "foreign_key" ][ $sName ] ) )
			{
				throw new Exception( "Relationship [{$sName}] does not exist" );
			}

			$aRelationship = self::$aSchemas[ $this->sTableName ][ "foreign_key" ][ $sName ];
			
			
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
			$aPrimaryKeys = $this->GetTablePrimaryKey( $this->sTableName );
			
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
				return( $this->Insert() );
			}
			else
			{
				// if we do have data supplied in the primary key field, we need to update the data:
				return( $this->Update() );
			}
		
		} // Save()
				
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public function SaveAll()
		{
			$this->Save();
		
			$aForeignKeys = $this->GetTableForeignKeys( $this->sTableName );
		
			foreach( $aForeignKeys as $aRelationship )
			{
				$sRelationshipName = $aRelationship[ "name" ];
				
				if( isset( $this->$sRelationshipName ) )
				{
					// If the relationship type is 1 to Many, than iterate each
					// related data set and invoke SaveAll()
					
					if( $aRelationship[ "type" ] == "1-m" )
					{
						foreach( $this->$sRelationshipName as $oRelatedData )
						{
							$oRelatedData->SaveAll();
						}
					}
					else
					{
						$this->$sRelationshipName->SaveAll();
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
			$sFields = "";
			$sValues = "";
			
			$aPrimaryKeys = $this->GetTablePrimaryKey( $this->sTableName );			
			$aFields = $this->GetTableFields( $this->sTableName );
			
			// loop each field in the table and specify it's data:
			foreach( $aFields as $field )
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
			
			$sSQL = "INSERT INTO " . $this->sTableName . " (
				{$sFields}
			) VALUES (
				{$sValues}
			)";
			
			if( !$this->Query( $sSQL ) )
			{
				throw new Exception( "Failed on Query: " . $this->GetLastError() );
			}
			
			if( count( $aPrimaryKeys ) == 1 )
			{
				$iKey = $this->SerialCurrVal( $this->sTableName, reset( $aPrimaryKeys ) );
				
				return( $iKey );
			}
			
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
			// get the primary key of the primary table:
			//$primaryKey = reset( self::$aSchemas[ $this->sTableName ][ "primary_key" ] );
		
			// update the primary record:
			$sSQL = $this->UpdateQuery();
			
			if( !$this->Query( $sSQL ) )
			{
				trigger_error( "Failed on Query: {$sSQL}", E_USER_ERROR );
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
			$aSchema = $this->GetSchema( $this->sTableName );
			
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
			
			
			$sSQL = "UPDATE {$this->sTableName} SET {$sSet} WHERE {$sWhere}";	
			

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
				
				return( $this->Next() );
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
				$aRelationships = $this->GetTableForeignKeys( $this->sTableName );
				
				foreach( $aRelationships as $aRelationship )
				{
					$sRelationshipName = $aRelationship[ "name" ];
					
					if( isset( $this->$sRelationshipName ) )
					{
						unset( $this->$sRelationshipName );
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
