<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @dependencies    Database, StringFunctions
 * @copyright       Copyright (c) 2007-2009, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.2.0-beta
 *
 */
 
    /**
     * Database abstraction layer implementing CRUD procedures
     *
     * @category    Database
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/crud
     */
    class CRUD implements Iterator
    {
        // specify the database profile to use:
        protected $_profileName = null;
        // specify the database schema to use:
        protected $_schemaName = null;
        //
        protected $_tableName = null;
        
        // 
        protected $_database = null;
        // 
        protected $_dataSet = null;
        // 
        protected $_data = array();
        
        
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
        public function __construct( $tableName, $data = null )
        {
            $this->_database = Database::getConnection( $this->_profileName );

            $this->_tableName = $tableName;
        
            // Get the schema for this table:
            $this->_database->getTableDefinition( $this->_tableName );
            
            // Prepare the fields for this table for CRUD->column access:
            $this->prepareColumns();

            // If data is supplied, load it, depending on data type:
            
            if(is_int($data))
            {
                $this->Find($data);
            }
            else if(is_array($data) || is_obj($data)))
            {
                $this->load( $data );
            }

        } // __construct()
        
        
        /**
         * Grabs all columns for this table and adds each as a key in the data array for
         * this object       
         * 
         * @returns void
         */                      
        protected function PrepareColumns()
        {
            $columns = $this->_database->GetTableColumns( $this->_tableName );
            
            // Loop each column in the table and create a member variable for it:           
            foreach( $columns as $column )
            {
                $this->_data[ $column[ "field" ] ] = null;
            }
        
        } // PrepareColumns()


        /**
         * This method attempts to load a record from the database based on the passed ID, or a
         * passed set of SQL query clauses. This method can be used retrieve one record from the
         * database, or a set of records that can be iterated through.
         *
         * @argument mixed The ID of the data being found
         * @argument array Additional databases clauses, including: join, where, order, offset and
         *       limit. All except for join are string that are directly appended to the query.
         *       Join is an array of referenced tables to inner join.
         * @returns CRUD returns a reference to itself to allow chaining
         */
        public function Find( $xId = null, $aClauses = array() )
        {
            $aPrimaryKey = $this->_database->GetTablePrimaryKey( $this->_tableName );

            if( !empty( $xId ) )
            {
                // If we have a primary key specified, make sure it the number of columns matches:
                if( count( $aPrimaryKey ) > 1 && ( !is_array( $xId ) ||
                    count( $xId ) != count( $aPrimaryKey ) ) )
                {
                    throw new QueryFailedException( "Invalid record key provided" );
                }
            }

            $sTableAlias = StringFunctions::ToSingular( $this->_tableName );


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
                    $sType = $this->_database->GetColumnType( $this->_tableName, $sField );

                    $sWhere .= !empty( $sWhere ) ? " AND " : " WHERE ";
                    $sWhere .= "_{$sTableAlias}.{$sField} = " .
                        $this->_database->FormatData( $sType, $sValue ) . " ";
                }
            }
            else if( !empty( $xId ) )
            {
                // we have a singular primary key -- put the data in the WHERE clause:
                $sKey = reset( $aPrimaryKey );
                $sType = $this->_database->GetColumnType( $this->_tableName, $sKey );

                $sWhere .= !empty( $sWhere ) ? " AND " : " WHERE ";
                $sWhere .= "_{$sTableAlias}.{$sKey} = " .
                    $this->_database->FormatData( $sType, $xId ) . " ";
            }

            $iLimit = isset( $aClauses[ "limit" ] ) ?
                " LIMIT " . intval( $aClauses[ "limit" ] ) : "";

            $iOffset = isset( $aClauses[ "offset" ] ) ?
                " OFFSET " . intval( $aClauses[ "offset" ] ) : "";


            // Setup supplied joins:

            $sJoins = "";

            if( isset( $aClauses[ "join" ] ) )
            {
                foreach( $aClauses[ "join" ] as &$xJoin )
                {
                    //
                    // xJoin may be either a relationship name, or it might be an array of
                    // join information:
                    //
                    // array(
                    //      table => table_name (required)
                    //      on => column_name (required)
                    //      as => table_alias (optional)
                    //      through => join_through (optional, through must be another join's "as")
                    // )
                    //


                    // If the join is an array:

                    if( is_array( $xJoin ) )
                    {
                        // Make sure the table value is provided:
                        if( !isset( $xJoin[ "table" ] ) )
                        {
                            throw new Exception( "Join table not specified" );
                        }

                        // Make sure the column is provided:
                        if( !isset( $xJoin[ "on" ] ) )
                        {
                            throw new Exception( "Join column not specified" );
                        }

                        $sJoinType = isset( $xJoin[ "type" ] ) ?
                            $xJoin[ "type" ] : Database::JoinTypeInner;

                        if( !isset( Database::$aJoinTypes[ $sJoinType ] ) )
                        {
                            throw new Exception( "Unknown join type specified: " . $xJoin[ "type" ] );
                        }

                        $sJoinType = Database::$aJoinTypes[ $sJoinType ];

                        if( isset( $xJoin[ "through" ] ) )
                        {
                            //throw new Exception( "through not yet implemented!" );

                            // If we are joining through another table, we should have already
                            // setup that join. Let's find it:

                            $aJoin = array();

                            foreach( $aClauses[ "join" ] as $xJoinSub )
                            {
                                if( isset( $xJoinSub[ "as" ] ) )
                                {
                                    if( $xJoin[ "through" ] == $xJoinSub[ "as" ] )
                                    {
                                        $aJoin = $xJoinSub;
                                        break;
                                    }
                                }
                            }

                            if( empty( $aJoin ) )
                            {
                                throw new Exception( "Invalid through join specified: " .
                                    $xJoin[ "through" ] );
                            }

                            // Find the relationship:
                            $aRelationship = $this->FindRelationship2( $aJoin[ "table" ],
                                $xJoin[ "table" ], $xJoin[ "on" ] );

                            // If the relationship doesn't exist:
                            if( empty( $aRelationship ) )
                            {
                                throw new Exception( "Relationship not found: " .
                                    $this->_tableName . " -> " . $xJoin[ "table" ] . "." .
                                    $xJoin[ "on" ] );
                            }


                            // Start the join:
                            $sJoins .= "{$sJoinType} " . $xJoin[ "table" ] . " ";

                            // Determine the alias (AS):
                            $sAs = "_" . $aRelationship[ "name" ];

                            if( !empty( $xJoin[ "as" ] ) )
                            {
                                $sAs = $xJoin[ "as" ];
                            }

                            $xJoin[ "as" ] = $sAs; // Store this for later use!

                            // Add the alias:
                            $sJoins .= " AS " . $sAs . " ";

                            // Add the ON clause:
                            $sJoins .= " ON " . $aJoin[ "as" ] . "." .
                                current( $aRelationship[ "local" ] ) . " = " .
                                $sAs . "." . current( $aRelationship[ "foreign" ] ) . " ";
                        }
                        else
                        {
                            // Find the relationship:
                            $aRelationship = $this->FindRelationship2( $this->_tableName,
                                $xJoin[ "table" ], $xJoin[ "on" ] );

                            // If the relationship doesn't exist:
                            if( empty( $aRelationship ) )
                            {
                                throw new Exception( "Relationship not found: " .
                                    $this->_tableName . " -> " . $xJoin[ "table" ] . "." .
                                    $xJoin[ "on" ] );
                            }


                            // Start the join:
                            $sJoins .= "{$sJoinType} " . $xJoin[ "table" ] . " ";

                            // Determine the alias (AS):
                            $sAs = "_" . $aRelationship[ "name" ];

                            if( !empty( $xJoin[ "as" ] ) )
                            {
                                $sAs = $xJoin[ "as" ];
                            }

                            $xJoin[ "as" ] = $sAs; // Store this for later use!

                            // Add the alias:
                            $sJoins .= " AS " . $sAs . " ";

                            // Add the ON clause:
                            $sJoins .= " ON _" . $sTableAlias . "." .
                                current( $aRelationship[ "local" ] ) . " = " .
                                $sAs . "." . current( $aRelationship[ "foreign" ] ) . " ";
                        }
                    }
                    else
                    {
                        $aRelationship = $this->FindRelationship( $xJoin );

                        if( !count( $aRelationship ) )
                        {
                            throw new Exception( "Unknown join relationship specified: {$xJoin}" );
                        }

                        $sJoins .= " INNER JOIN " . $aRelationship[ "table" ] . " AS " .
                            "_" . $aRelationship[ "name" ] . " ON ";

                        $sOn = "";

                        foreach( $aRelationship[ "local" ] as $iIndex => $sField )
                        {
                            $sOn .= ( !empty( $sOn ) ? " AND " : "" ) .
                                "_" . StringFunctions::ToSingular( $this->_tableName ) .
                                "." . $sField . " = " . "_" . $aRelationship[ "name" ] .
                                "." . $aRelationship[ "foreign" ][ $iIndex ];
                        }

                        $sJoins .= " {$sOn} ";
                    }
                }
            }

            $sFields = "_" . StringFunctions::ToSingular( $this->_tableName ) . ".*";

            // TODO test this functionality:
            
            if(isset($aClauses["count"]) && $aClauses["count"] === true)
            {
                $sFields = "COUNT({$sFields})";
            }

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
                {$this->_tableName} AS _" .
                    StringFunctions::ToSingular( $this->_tableName ) . "
            {$sJoins}
            {$sWhere}
            {$sOrder}
            {$iLimit}
            {$iOffset}"; // FIXME PostgreSQL Specific Syntax

            // Execute and pray:
            if( !( $this->_dataSet = $this->_database->Query( $sSQL ) ) )
            {
                throw new Exception( "Failed on Query. Error: " .
                    $this->_database->GetLastError() . "\n Query: {$sSQL}" );
            }

            // Loop the data and create member variables
            if( $this->_dataSet->Count() != 0 )
            {
                $this->_dataSet->Next();

                $this->Load( $this->_dataSet->Current() );
            }

            return( $this );

        } // Find()


        /**
         * This method returns the number of records that match a passed set of SQL query clauses.
         * This method is very similiar to Find(), except that it returns an integer value
         * representing the number of matching records.
         *
         * @deprecated Use find() with option "count" => true
         * @argument array Additional databases clauses, including: join and where. Where is a string
         *       that are directly appended to the query. Join is an array of referenced tables to
         *       inner join.
         * @returns int Returns the number of database records that match the passed clauses
         */
        public function FindCount( $aClauses = array() )
        {
            return( $this->FindCount( $aClauses + array( "count" => true ) ) );

        } // FindCount()
        
        
        /**
         * This method will retrieve records from the table based on column value using the supplied
         * column name (which may have had underscores removed and be cased differently) and
         * column value.
         * 
         * This method is invoked through __call() when the user uses the CRUD::FindBy[column]()
         * "virtual" method.                                     
         *
         * @argument string The name of the column we are pulling records by. This name may 
         *  underscores removed and be cased differently         
         * @argument string The value of the column in the first argument that determines which
         *  records will be selected
         * @argument string The order clause for the query       
         * @returns CRUD A reference to the current object to support chaining or secondary assignment
         * @throws Exception, QueryFailedException                                       
         */
        protected function getDataByColumnValue( $sColumn, $sValue, $sOrder = "" )
        {
            $aColumns = $this->_database->GetTableColumns( $this->_tableName );
            
            $aColumn = null;
            
            foreach( $aColumns as $sName => $aTmpColumn )
            {
                if( strtolower( str_replace( "_", "", $sName ) ) == strtolower( $sColumn ) )
                {
                    $aColumn = $aTmpColumn;
                    break;
                }
            }
            
            if( is_null( $aColumn ) )
            {
                throw new Exception( "Database column {$this->_tableName}.{$sColumn} does not exist." );
            }
            
            $sDataType = $aColumn[ "type" ];
            
            $aClauses = array(
                "where" => $aColumn[ "field" ] . " = " . 
                    $this->_database->FormatData( $sDataType, $sValue )
            ); // FIXME (possible) PostgreSQL Specific Syntax
            
            if( !empty( $sOrder ) )
            {
                $aClauses[ "order" ] = $sOrder;
            }
            
            $this->Find( null, $aClauses );
            
            return( $this );
            
        } // getDataByColumnValue()     
        
        
        /**
         * This method will delete records from the table based on column value using the supplied
         * column name (which may have had underscores removed and be cased differently) and
         * column value.
         * 
         * This method is invoked through __call() when the user uses the CRUD::destroyBy[column]()
         * "virtual" method.                                         
         *
         * @argument string The name of the column we are basing our delete from. This name may
         *  underscores removed and be cased differently         
         * @argument string The value of the column in the first argument that determines which
         *  records will be deleted.
         * @returns boolean True if successful/no error; throws an Exception otherwise
         * @throws Exception, QueryFailedException                                       
         */
        protected function destroyDataByColumnValue( $sColumn, $sValue )
        {
            $aColumns = $this->_database->GetTableColumns( $this->_tableName );
            
            $aColumn = null;
            
            foreach( $aColumns as $sName => $aTmpColumn )
            {
                if( strtolower( str_replace( "_", "", $sName ) ) == strtolower( $sColumn ) )
                {
                    $aColumn = $aTmpColumn;
                    break;
                }
            }
            
            if( is_null( $aColumn ) )
            {
                throw new Exception( "Database column {$this->_tableName}.{$sColumn} does not exist." );
            }
            
            $sDataType = $aColumn[ "type" ];
            
            $sSQL = "DELETE FROM 
                {$this->_tableName}
            WHERE
                " . $aColumn[ "field" ] . " = " . $this->_database->FormatData( $sDataType, $sValue ); // FIXME PostgreSQL Specific Syntax
            
            if( !$this->_database->Query( $sSQL ) )
            {
                throw new QueryFailedException( "Failed to delete data" );
            }
            
            return( true );
            
        } // destroyDataByColumnValue()                         
        
        
        /**
         *
         *       
         * @note GetRecord() will move the internal pointers of all 1-M iterators loaded
         * 
         *               
         */
        public function getRecord()
        {           
            $oRecord = new StdClass();
            
            foreach( $this->_data as $sKey => $xValue )
            {
                if( is_object( $xValue ) )
                {
                    if( $xValue->Count() > 1 )
                    {
                        $oRecord->$sKey = array();
                        
                        foreach( $xValue as $oValue )
                        {
                            $oRecord->{$sKey}[] = $oValue->GetRecord();
                        }
                    }
                    else
                    {
                        $oRecord->$sKey = $xValue->GetRecord();
                    }
                }
                else
                {
                    $oRecord->$sKey = $xValue;
                }
            }
            
            return( $oRecord );
        
        } // getRecord()
        
        
        /**
         *
         *
         *       
         */                     
        public function getAll()
        {
            $aRecords = array();
            
            $this->Rewind();
            
            foreach( $this->_dataSet as $oData )
            {
                $aRecords[] = $oData;
            }
        
            return( $aRecords );
        
        } // GetAll()
        
        
        /**
         *
         *
         *       
         */                     
        protected function FindRelationship( $sName )
        {
            $aForeignKeys = $this->_database->GetTableForeignKeys( $this->_tableName );
            
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
         *
         *
         *       
         */                     
        protected function FindRelationship2( $sPrimaryTable, $sRelatedTable, $sThroughColumn )
        {
            $aForeignKeys = $this->_database->GetTableForeignKeys( $sPrimaryTable );
            
            foreach( $aForeignKeys as $aForeignKey )
            {
                if( $aForeignKey[ "table" ] == $sRelatedTable &&
                    current( $aForeignKey[ "local" ] ) == $sThroughColumn )
                {
                    return( $aForeignKey );
                }
            }
            
            return( null );
        
        } // FindRelationship2()
        
        
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
            
            $aColumns = $this->_database->GetTableColumns( $this->_tableName );
                $aRelationships = $this->_database->GetTableForeignKeys( $this->_tableName );

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
                                $this->_data[ $sKey ] = $this->InstantiateClass( $sTable, $xValue );
                            }
                            else if( $aRelationships[ $sKey ][ "type" ] == "1-m" )
                            {
                                if( !isset( $this->_data[ $sKey ] ) )
                                {
                                    $this->_data[ $sKey ] = array();
                                }
                                
                                foreach( $xValue as $oRelatedData )
                                {
                                    $this->_data[ $sKey ][] = $this->InstantiateClass( 
                                        $sTable, $oRelatedData );
                                }
                            }
                        }                   
                }
                else if( isset( $aColumns[ $sKey ] ) )
                {
                        $this->_data[ $sKey ] = $xValue;
                }
                elseif( isset( $this->{$sKey} ) )
                {
                    $this->{$sKey} = $xValue;
                }
            }

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
            return( $this->Count() == 0 );
            
        } // IsEmpty()
        
        
        /**
         *  Gets the number of rows returned by the last Find() call. If Find() has not yet been 
         *  called, this method will return This method is invoked through the __call() method to 
         *  allow using the method name Count(), which is a reserved word in PHP.                    
         *  
         * @returns integer The number of results in the data set
         */
        public function Count() 
        {
            if( !is_null( $this->_dataSet ) )
            {
                return( $this->_dataSet->Count() );
            }
            
            return( 0 );
        
        } // GetCount()
            
        
        /**
         *
         *
         *
         */                             
        public function __isset( $sName )
        {
            return( array_key_exists( $sName, $this->_data ) || isset( $this->{$sName} ) );
            
        } // __isset()
        
        
        /**
         *
         *
         */                     
        public function __get( $sName )
        {           
            if( array_key_exists( $sName, $this->_data ) )
            {
                return( $this->_data[ $sName ] );
            }
        
            $aSchema = $this->_database->getTableDefinition( $this->_tableName );
            
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
                    $sWhere .= " {$sKey} = " . intval( $this->_data[ $sRelated ] );
                }
                            
                $this->_data[ $sName ] = $this->InstantiateClass( $aRelationship[ "table" ] );
                $this->_data[ $sName ]->Find( null, array( "where" => $sWhere ) );
            }
            else
            {
                $sLocalColumn = current( $aRelationship[ "local" ] );
                
                if( isset( $this->_data[ $sLocalColumn ] ) )
                {
                    $this->_data[ $sName ] = $this->InstantiateClass( $aRelationship[ "table" ] );
                    $this->_data[ $sName ]->Find( $this->_data[ $sLocalColumn ] );  
                }
                else
                {
                    // Modified 2007-12-29 to prevent error:
                    // Notice: Indirect modification of overloaded property has no effect
                    // If we are dynamically creating a record, we need to return an empty object for 
                    // this relationship to load into
                    
                    $this->_data[ $sName ] = $this->InstantiateClass( $aRelationship[ "table" ] );
                }
            }
            
            return( $this->_data[ $sName ] );
            
        } // __get()
        
        
        /** 
         * Attempts to set the value of a database column, or sets a relationship through the
         * CRUD->[column_name] syntax.
         * 
         * @argument string The name of the column to set
         * @argument string The value to set the column specified in the first argument
         * @returns void
         * @throws Exception
         */ 
        public function __set( $sName, $sValue )
        {           
            $aColumns = $this->_database->GetTableColumns( $this->_tableName );
        
            if( isset( $aColumns[ $sName ] ) )
            {
                $this->_data[ $sName ] = $sValue;
            }
            else if( !is_null( $this->FindRelationship( $sName ) ) )
            {
                $this->_data[ $sName ] = $sValue;
            }
            else
            {
                throw new Exception( "Unknown column [{$sName}] referenced" );
            }
        
        } // __set()
        
        
        /**
         * Unsets the value of a database column. This will effectively remove the column from
         * the known list of columns for this instance, causing a CRUD::Save() operation to not
         * update the value.
         * 
         * @argument string The name of the database column to unset
         * @returns void
         */ 
        public function __unset( $sName )
        {
            if( isset( $this->_data[ $sName ] ) )
            {
                unset( $this->_data[ $sName ] );
            }
        
        } // __set()
        
        
        /**
         * Supports several "virtual" or magic methods, such as data manipulation/retrieval through 
         *  getBy[column_name] and destroyBy[column_name], reserved word methods, such as empty(),
         *  and also provides access to public methods of the database, which fakes database
         *  class inheritance (which is needed to support multiple database drivers).
         *
         * @argument string The name of the argument to be called magically 
         * @argument array An array of arguments to pass to the magically called method
         * @returns mixed Depends sName, the first argument
         * @throws Exception
         */
        public function __call( $sName, $aArguments )
        {
            switch( strtolower( $sName ) )
            {
                case "empty":
                    return( $this->IsEmpty() );
                break;
            }
            
            if( is_callable( array( $this->_database, $sName ) ) )
            {
                return( call_user_func_array( array( $this->_database, $sName ), $aArguments ) );
            }
            
            if( strtolower( substr( $sName, 0, 6 ) ) == "findby" )
            {
                return( $this->GetDataByColumnValue( substr( $sName, 6 ), $aArguments[ 0 ],
                    isset( $aArguments[ 1 ] ) ? $aArguments[ 1 ] : null ) );
            }
            else if( strtolower( substr( $sName, 0, 9 ) ) == "destroyby" )
            {
                return( $this->DestroyDataByColumnValue( substr( $sName, 9 ), $aArguments[ 0 ] ) );
            }
            
            throw new Exception( "Call to undefined method: {$sName}" );
                
        } // __call()
        
        
        /**
         * Assists slightly in object cloning. If this table has a single primary key, the value
         * of this key will be whiped out when cloning.          
         *               
         * @returns void         
         */             
        public function __clone()
        {
            $aPrimaryKey = $this->_database->GetTablePrimaryKey( $this->_tableName );
            
            if( count( $aPrimaryKey ) == 1 )
            {
                $sPrimaryKey = reset( $aPrimaryKey );
                
                $this->{$sPrimaryKey} = null;
            }
        
        } // __clone()
        
        
        /**
         * Based on presence of primary key data, either creates a new record, or 
         * updates theexisting record
         *
         * @returns boolean True if the save was successful, false otherwise         
         */
        public function save()
        {           
            // grab a copy of the primary key:
            $aPrimaryKeys = $this->_database->GetTablePrimaryKey( $this->_tableName );
            
            $bInsert = false;
            
            // If we have a compound primary key, we must first determine if the record
            // already exists in the database. If it does, we're doing an update.
            
            // If we have a singular primary key, we can rely on whether the primary key
            // value of this object is null
            
            if( count( $aPrimaryKeys ) == 1 )
            {
                $sPrimaryKey = reset( $aPrimaryKeys );
                
                if( $this->_database->IsPrimaryKeyReference( $this->_tableName, $sPrimaryKey ) )
                {
                    // See Task #56
                    $bInsert = !$this->RecordExists();
                }
                else if( empty( $this->_data[ $sPrimaryKey ] ) )
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
        
        } // save()
        
        
        /**
         *
         */
        public function SaveAll()
        {           
            $aForeignKeys = $this->_database->GetTableForeignKeys( $this->_tableName );
                    
            // Save all dependencies first

            foreach( $aForeignKeys as $aRelationship )
            {
                $sRelationshipName = $aRelationship[ "name" ];
                
                if( isset( $this->_data[ $sRelationshipName ] ) && $aRelationship[ "dependency" ] )
                {
                    if( !$this->_data[ $sRelationshipName ]->SaveAll() )
                    {
                        return( false );
                    }
                    
                    // We only work with single keys here !!
                    $sLocal = reset( $aRelationship[ "local" ] );
                    $sForeign = reset( $aRelationship[ "foreign" ] );
                    
                    $this->_data[ $sLocal ] = $this->_data[ $sRelationshipName ]->$sForeign;
                }
            }

            // Save the primary record
            
            if( !self::Save() ) //!$this->Save() )
            {
                return( false );
            }
            
            // Save all related data last

            foreach( $aForeignKeys as $aRelationship )
            {
                $sRelationshipName = $aRelationship[ "name" ];
                
                if( isset( $this->_data[ $sRelationshipName ] ) && !$aRelationship[ "dependency" ] )
                {
                    // We only work with single keys here !!
                    $sLocal = reset( $aRelationship[ "local" ] );
                    $sForeign = reset( $aRelationship[ "foreign" ] );
                        
                    if( $aRelationship[ "type" ] == "1-m" )
                    {
                        foreach( $this->_data[ $sRelationshipName ] as $oRelationship )
                        {
                            $oRelationship->$sForeign = $this->_data[ $sLocal ];
                            
                            if( !$oRelationship->SaveAll() )
                            {
                                return( false );
                            }
                        }
                    }
                    else if( $aRelationship[ "type" ] == "1-1" )
                    {                       
                        $this->_data[ $sRelationshipName ]->$sForeign = $this->_data[ $sLocal ];
                        $this->_data[ $sRelationshipName ]->SaveAll();
                    }
                }
            }
            
            return( true );
        
        } // SaveAll()
        
        
        /**
         *
         */
        protected function Insert()
        {
            $columnsList = "";
            $valuesList = "";
            
            $primaryKeys = $this->_database->getTablePrimaryKey($this->_tableName);          
            $columns = $this->_database->getTableColumns($this->_tableName);
            
            // loop each column in the table and specify it's data:
            foreach($columns as $column)
            {
                // automate updating created date column:
                if(in_array($column["field"], array("created_date", "created_stamp", "created_on")))
                {
                    // dates are stored as GMT
                    $this->_data[$column["field"]] = gmdate("Y-m-d H:i:s");
                }
                 // FIXME (possible) PostgreSQL Specific Syntax (above; date formats)
                
                // If the primary key is singular, do not provide a value for it:               
                if(in_array($column["field"], $primaryKeys) && count($primaryKeys) == 1 && 
                    !$this->_database->IsPrimaryKeyReference($this->_tableName, reset($primaryKeys)))
                {
                    continue;
                }
                
                if(empty($this->_data[$column["field"]]))
                {
                    continue;
                }
                
                // Create a list of columns to insert into:
                $columnsList .= (!empty($columnsList) ? ", " : "") . 
                    $column[ "field" ];
                
                // Get the value for the column (if present):
                $sValue = isset($this->_data[$column["field"]]) ? 
                    $this->_data[$column["field"]] : "";
                
                // Create a list of values to insert into the above columns:
                $valuesList .= (!empty($valuesList) ? ", " : "") . 
                    $this->_database->FormatData($column[ "type" ], $sValue);
            }
            
            $sSQL = "INSERT INTO {$this->_tableName} (
                {$columnsList}
            ) VALUES (
                {$valuesList}
            ) RETURNING *"; // FIXME PostgreSQL Specific Syntax
            
            $resultData = null;
            
            if(($resultData = $this->_database->Query($sSQL)) !== false)
            {
                throw new Exception("Failed on Query: {$sSQL} - " . $this->_database->GetLastError());
            }
            
            // TODO test RETURNING functionality
            
            if(!is_null($resultData))
            {
                foreach($resultData as $key => $value)
                {
                    $this->_data[$key] = $value;
                }
                
                return( true );
            }
            else
            {
                // Note: an assumption is made that if the primary key is not singular, then there all
                // the data for the compound primary key should already be present -- meaning, we should 
                // not have a serial value on the table for a compound primary key.
                
                // If we have a singular primary key:
                if(count($primaryKeys) == 1)
                {               
                    // Get the current value of the serial for the primary key column:
                    $iKey = $this->_database->SerialCurrVal($this->_tableName, reset($primaryKeys));
                    
                    // Store the primary key:
                    $this->_data[ $primaryKeys[0] ] = $iKey;
                    
                    // return the primary key:
                    return true;
                }
                
                
                // If we have a compound primary key, return true:
                return true;
            }
            
        } // Insert()
        
        
        /**
         * Responsible for updating the currently stored data for primary table and
         * all foreign tables referenced
         * 
         * @returns boolean True if the update was successful, false otherwise                       
         */
        protected function update()
        {           
            // update the primary record:
            $sql = $this->updateQuery();
            
            $resultData = null;
            
            if(($data = $this->_database->query( $sql )) !== false)
            {
                throw new Exception( "Failed on Query: " . $this->_database->GetLastError() );
            }
            
            if(!is_null($resultData))
            {
                // TODO test new RETURNING functionality
                
                foreach($resultData as $key => $value)
                {
                    $this->_data[$key] = $value;
                }
            }
            
            return( true );
            
        } // update()
        
        
        /**
         * Called by the update() method to generate an update query for this table
         * 
         * @returns string The generated SQL query               
         */
        protected function updateQuery()
        {
            $definition = $this->_database->getTableDefinition( $this->_tableName );
            
            $primaryKeys = $definition[ "primary_key" ];
                    
            $setClause = "";

            // loop each field in the table and specify it's data:
            foreach( $definition[ "fields" ] as $field )
            {
                // do not update certain fields:
                if( in_array( $field[ "field" ], array( "created_date", "created_stamp", "created_on" ) ) )
                {
                    continue;
                }
                
                // automate updating update date fields:
                if( in_array( $field[ "field" ], array( "updated_date", "updated_stamp", "updated_on" ) ) )
                {
                    $this->_data[ $field[ "field" ] ] = gmdate( "Y-m-d H:i:s" );
                }
                
                if( !isset( $this->_data[ $field[ "field" ] ] ) )
                {
                    continue;
                }
                
                // complete the query for this field:
                $setClause .= ( !empty( $setClause ) ? ", " : "" ) . 
                    $field[ "field" ] . " = " . 
                        $this->_database->FormatData( $field[ "type" ], $this->_data[ $field[ "field" ] ] ) . " ";
            }
            
            // if we found no fields to update, return:
            if( empty( $setClause ) )
            {
                return;
            }
            
                        
            $whereClause = "";
            
            foreach( $primaryKeys as $key )
            {
                $whereClause .= !empty( $whereClause ) ? " AND " : "";
                $whereClause .= "{$key} = " . intval( $this->_data[ $key ] );
            }
            
            
            // TODO implement returning functionality
            
            $sSQL = "UPDATE 
                {$this->_tableName}
            SET
                {$setClause}
            WHERE
                {$whereClause}
            RETURNING *";    // FIXME PostgreSQL Specific Syntax
            

            return( $sSQL );
            
        } // updateQuery()
        
        
        /**
         *
         *
         *       
         */                     
        protected function RecordExists()
        {
            $aPrimaryKeys = $this->_database->GetTablePrimaryKey( $this->_tableName );
        
            $sSQL = "SELECT
                1
            FROM
                {$this->_tableName} ";
            
            $sWhere = "";
            
            foreach( $aPrimaryKeys as $sPrimaryKey )
            {
                $sType = $this->_database->GetColumnType( $this->_tableName, $sPrimaryKey );
                
                $sWhere .= empty( $sWhere ) ? " WHERE " : " AND ";
                $sWhere .= $sPrimaryKey . " = " . 
                    $this->_database->FormatData( $sType, $this->_data[ $sPrimaryKey ] ) . " ";
            }
            
            $sSQL .= $sWhere; // FIXME PostgreSQL Specific Syntax
            
            if( !( $oResultSet = $this->_database->Query( $sSQL ) ) )
            {
                throw new QueryFailedException( "Failed on Query: " . $this->_database->GetLastError() );
            }
            
            return( $oResultSet->Count() != 0 );
        
        } // RecordExists()
        
        
        /**
         * Destroys (deletes) the current data. This method will delete the primary record 
         * (assuming that the primary key for the data is set).
         *  
         * @returns void
         */
        public function Destroy()
        {
            $aPrimaryKeys = $this->_database->GetTablePrimaryKey( $this->_tableName );
            
            $sSQL = "DELETE FROM
                {$this->_tableName}
            WHERE ";
            
            $sWhere = "";
            
            foreach( $aPrimaryKeys as $sKey )
            {
                $sWhere .= empty( $sWhere ) ? "" : " AND ";
                $sWhere .= "{$sKey} = " . $this->_database->FormatData( 
                    $this->_database->GetColumnType( $this->_tableName, $sKey ), $this->_data[ $sKey ] );
            }
            
            $sSQL .= $sWhere; // FIXME PostgreSQL Specific Syntax
            
            if( !$this->_database->Query( $sSQL ) )
            {
                throw new QueryFailedException( "Failed on Query: " . $this->_database->GetLastError() );
            }
        
        } // Destroy()
        
        
        /**
         * Helper method for generating a where clause for a query string. Where clause is
         * built by supplied keys and associated data
         * 
         */                     
        protected function buildWhereClause( $keys, $dataSet )
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
             // FIXME PostgreSQL Specific Syntax
            return( $where );
            
        } // buildWhereClause()
    
    
        //
        // ITERATOR DEFINITION
        //
        

        /**
         *
         * See http://www.php.net/~helly/php/ext/spl/interfaceIterator.html
         * 
         */              
        public function Rewind() 
        {                           
            if( !is_null( $this->_dataSet ) && $this->_dataSet->Count() != 0 )
            {
                $this->Cleanup();
                
                $this->_dataSet->Rewind();
            
                if( $this->_dataSet->Valid() )
                {
                    $this->Load( $this->_dataSet->Current() );
                }
            }
        
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
            if( $this->Valid() )
            {
                return( $this );
            }
            
            return( null );
        
        } // Current()
        
        
        /**
         *
         * See http://www.php.net/~helly/php/ext/spl/interfaceIterator.html
         * 
         */     
        public function Key() 
        {           
            return( $this->_dataSet->Key() );
            
        } // Key()


        /**
         *
         * See http://www.php.net/~helly/php/ext/spl/interfaceIterator.html
         * 
         */     
        public function Next() 
        {           
            if( !is_null( $this->_dataSet ) )
            {
                $this->Cleanup();
            
                $this->_dataSet->Next();
                
                if( $this->Valid() )
                {
                    $aSchema = $this->_database->getTableDefinition( $this->_tableName );
                
                    $oData = $this->_dataSet->Current();
                    
                    // Turn any boolean fields into true booleans, instead of chars:
                    foreach( $oData as $sKey => $sValue )
                    {    
                        if( strpos( $aSchema[ "fields" ][ $sKey ][ "type" ], "bool" ) !== false )
                        {
                            $oData->$sKey = $sValue == "t" ? true : false;
                        }
                    } 
                    
                    // FIXME PostgreSQL Specific Syntax (boolean handling)
                        
                    $this->Load( $oData );
                }
            }
        
        } // Next()


        /**
         *
         * See http://www.php.net/~helly/php/ext/spl/interfaceIterator.html
         * 
         */     
        public function Valid()  
        {           
            return( $this->_dataSet->Valid() );
        
        } // Valid()
        
        
        /**
         *
         *
         */                     
        protected function Cleanup()
        {
            $aRelationships = $this->_database->GetTableForeignKeys( $this->_tableName );
            
            foreach( $aRelationships as $aRelationship )
            {
                $sRelationshipName = $aRelationship[ "name" ];
                
                if( array_key_exists( $sRelationshipName, $this->_data ) )
                {
                    unset( $this->_data[ $sRelationshipName ] );
                }
            }
            
            $aColumns = $this->_database->GetTableColumns( $this->_tableName );
            
            // Loop each column in the table and create a member variable for it:           
            foreach( $aColumns as $aColumn )
            {
                $this->_data[ $aColumn[ "field" ] ] = null;
            }
                
        } // Cleanup()
        
        
        
        /**
         * Returns the table name associated with this CRUD object
         *
         * @returns string The name of the table associated with this CRUD object
         */
        public function GetTableName()
        {
            return( $this->_tableName );
            
        } // GetTableName() 
        
        
        /**
         * Returns the data currently stored in the CRUD object a well formed XML document as a
         * string representation. This requires the DOM and SimpleXML extensions of PHP to be 
         * installed. If either extension is not installed, this method will throw an exception.
         *          
         * @argument bool Should this returned XML include references? Default false.
         * @argument bool Should this returned XML include all records returned by the last Find()
         *      call? If not, only the current record stored is returned. Default false.      
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
         *      call? If not, only the current record stored is returned. Default false.      
         * @returns SimpleXMLElement The data requested as a SimpleXMLElement object
         */
        public function AsXML( $bIncludeReferences = false, $bProvideAll = false )
        {
            $oXML = null;
            
            if( $bProvideAll )
            {
                $sName = $this->_tableName;
                $sElementName = StringFunctions::ToSingular( $this->_tableName );
                
                $oXML = new SimpleXMLElement( "<{$sName}></{$sName}>" );
                
                foreach( $this as $oObject )
                {
                    $oElement = $oXML->addChild( $sElementName );
                    $this->AddColumns( $oElement, $oObject, $this->_tableName );
                    
                    if( $bIncludeReferences )
                    {
                        $this->AddReferences( $oElement, $oObject, $this->_tableName );
                    }
                }
            }
            else
            {
                $sName = StringFunctions::ToSingular( $this->_tableName );
                
                $oXML = new SimpleXMLElement( "<{$sName}></{$sName}>" );
                
                $this->AddColumns( $oXML, $this, $this->_tableName );
                $this->AddReferences( $oXML, $this, $this->_tableName );
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
        private function AddColumns( &$oElement, &$oObject, $tableName )
        {
            $aColumns = $this->_database->GetTableColumns( $tableName );
            
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
        private function AddReferences( &$oElement, &$oObject, $tableName )
        {
            $aTableReferences = $this->_database->GetTableForeignKeys( $tableName );
                
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
            
            $aColumns = $this->_database->GetTableColumns( $this->_tableName );
            
            foreach( $aColumns as $aColumn )
            {
                $oJSON->AddAttribute( $aColumn[ "field" ], $this->_data[ $aColumn[ "field" ] ] );
            }
            
            if( $bIncludeReferences )
            {
                $aTableReferences = $this->_database->GetTableForeignKeys( $this->_tableName );
                    
                foreach( $aTableReferences as $aReference )
                {
                    $oData = $this->{$aReference[ "name" ]};
                                    
                    if( !empty( $oData ) && !$oData->Empty() )
                    {
                        $aReferenceColumns = $this->_database->GetTableColumns( $aReference[ "table" ] );
                            
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
            return( print_r( $this->_data, true ) );
            
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
        private function InstantiateClass( $tableName, $xData = null )
        {           
            $sModelName = StringFunctions::ToSingular( $tableName );
            
            $oObject = null;
            
            if( class_exists( $sModelName, true ) && is_subclass_of( $sModelName, "Model" ) )
            {
                $oObject = new $sModelName( $xData );
            }
            else
            {
                $oObject = new CRUD( $tableName, $xData );
            }            
            
            return( $oObject );
            
        } // InstantiateClass()
        
    } // CRUD()

?>
