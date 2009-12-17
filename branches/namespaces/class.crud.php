<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @dependencies    Database, StringFunctions
 * @copyright       Copyright (c) 2008, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.3.0-beta
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
        // the table name for this data element:
        protected $_tableName = null;
        
        // a reference to the database connection:
        protected $_database = null;
        // a reference to the result set for the last query:
        protected $_dataSet = null;
        // the data for the current object:
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
         * @argument mixed Optional; An array or object of data to load into the CRUD object
         * @argument string Optional; The name of the database profile to use 
         * @returns void
         */
        public function __construct($tableName, $data = null, $profileName = "")
        {
            if(!empty($profileName))
            {
                $this->_profileName = $profileName;
            }
            
            $this->_database = Database::getConnection($this->_profileName);

            $this->_tableName = $tableName;
        
            // Get the schema for this table:
            $this->_database->getTableDefinition($this->_tableName);
            
            // Prepare the fields for this table for CRUD->column access:
            $this->prepareColumns();

            // If data is supplied, load it, depending on data type:
            
            if(is_int($data))
            {
                $this->find($data);
            }
            else if(is_array($data) || is_object($data))
            {
                $this->load($data);
            }

        } // __construct()
        
        
        /**
         * Grabs all columns for this table and adds each as a key in the data array for
         * this object       
         * 
         * @returns void
         */                      
        protected function prepareColumns()
        {
            $columns = $this->_database->getTableColumns($this->_tableName);
            
            // Loop each column in the table and create a member variable for it:           
            foreach($columns as $column)
            {
                $this->_data[$column["field"]] = null;
            }
        
        } // prepareColumns()


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
        public function find($data = null) //$xId = null, $aClauses = array() )
        {
            $primaryKey = $this->_database->getTablePrimaryKey($this->_tableName);
                        
            $queryClauses = array();
            
            if(is_numeric($data))
            {
                if(count($primaryKey) > 1)
                {
                    throw new QueryFailedException("Primary key is compound but scalar provided.");
                }
                
                $primaryKeyColumn = reset($primaryKey);
                
                $columnType = $this->_database->getColumnType($this->_tableName, $primaryKeyColumn);
                    
                $queryClauses["where"] = "{$primaryKeyColumn} = " . 
                    $this->_database->formatData($columnType, $data); 
            }
            else if(!is_array($data) && !is_null($data))
            {
                $args = func_get_args();
                
                var_dump( $args );
            
                throw new QueryFailedException("Invalid argument provided to " . 
                    __METHOD__ . ": " . gettype($data));
            }
            else
            {
                $queryClauses = $data;
            }

            $tableAlias = StringFunctions::toSingular( $this->_tableName );

            $whereClause = isset($queryClauses["where"] ) ? $queryClauses["where"] : "";

            if(!empty($whereClause))
            {
                $whereClause = " WHERE {$whereClause} ";
            }

            $limitClause = isset($queryClauses["limit"]) ?
                " LIMIT " . intval($queryClauses["limit"]) : "";

            $offsetClause = isset($queryClauses["offset"]) ?
                " OFFSET " . intval($queryClauses["offset"]) : "";

            // Setup supplied joins:

            $joinClause = "";

            if(isset($queryClauses["join"]))
            {
                foreach($queryClauses["join"] as &$xJoin)
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

                    if(is_array($xJoin))
                    {
                        // Make sure the table value is provided:
                        if(!isset($xJoin["table"]))
                        {
                            throw new Exception("Join table not specified");
                        }

                        // Make sure the column is provided:
                        if(!isset($xJoin["on"]))
                        {
                            throw new Exception("Join column not specified");
                        }

                        $sJoinType = isset($xJoin["type"]) ?
                            $xJoin["type"] : Database::JoinTypeInner;

                        if(!isset(Database::$_joinTypes[$sJoinType]))
                        {
                            throw new Exception("Unknown join type specified: " . $xJoin["type"]);
                        }

                        $sJoinType = Database::$_joinTypes[$sJoinType];

                        if(isset($xJoin["through"]))
                        {
                            //throw new Exception( "through not yet implemented!" );

                            // If we are joining through another table, we should have already
                            // setup that join. Let's find it:

                            $aJoin = array();

                            foreach($queryClauses["join"] as $xJoinSub)
                            {
                                if(isset($xJoinSub["as"]))
                                {
                                    if($xJoin["through"] == $xJoinSub["as"])
                                    {
                                        $aJoin = $xJoinSub;
                                        break;
                                    }
                                }
                            }

                            if(empty($aJoin))
                            {
                                throw new Exception("Invalid through join specified: " .
                                    $xJoin["through"]);
                            }

                            // Find the relationship:
                            $aRelationship = $this->FindRelationship2($aJoin[ "table" ],
                                $xJoin["table"], $xJoin["on"]);

                            // If the relationship doesn't exist:
                            if(empty($aRelationship))
                            {
                                throw new Exception("Relationship not found: " .
                                    $this->_tableName . " -> " . $xJoin["table"] . "." .
                                    $xJoin["on"]);
                            }


                            // Start the join:
                            $joinClause .= "{$sJoinType} " . $xJoin["table"] . " ";

                            // Determine the alias (AS):
                            $sAs = "_" . $aRelationship["name"];

                            if(!empty($xJoin["as"]))
                            {
                                $sAs = $xJoin["as"];
                            }

                            $xJoin["as"] = $sAs; // Store this for later use!

                            // Add the alias:
                            $joinClause .= " AS " . $sAs . " ";

                            // Add the ON clause:
                            $joinClause .= " ON " . $aJoin["as"] . "." .
                                current($aRelationship["local"]) . " = " .
                                $sAs . "." . current($aRelationship["foreign"]) . " ";
                        }
                        else
                        {
                            // Find the relationship:
                            $aRelationship = $this->FindRelationship2($this->_tableName,
                                $xJoin["table"], $xJoin["on"]);

                            // If the relationship doesn't exist:
                            if(empty($aRelationship))
                            {
                                throw new Exception("Relationship not found: " .
                                    $this->_tableName . " -> " . $xJoin["table"] . "." .
                                    $xJoin["on"]);
                            }

                            // Start the join:
                            $joinClause .= "{$sJoinType} " . $xJoin["table"] . " ";

                            // Determine the alias (AS):
                            $sAs = "_" . $aRelationship["name"];

                            if(!empty($xJoin["as"]))
                            {
                                $sAs = $xJoin["as"];
                            }

                            $xJoin["as"] = $sAs; // Store this for later use!

                            // Add the alias:
                            $joinClause .= " AS " . $sAs . " ";

                            // Add the ON clause:
                            $joinClause .= " ON _" . $tableAlias . "." .
                                current($aRelationship["local"]) . " = " .
                                $sAs . "." . current($aRelationship["foreign"]) . " ";
                        }
                    }
                    else
                    {
                        $aRelationship = $this->FindRelationship($xJoin);

                        if(!count($aRelationship))
                        {
                            throw new Exception("Unknown join relationship specified: {$xJoin}");
                        }

                        $joinClause .= " INNER JOIN " . $aRelationship["table"] . " AS " .
                            "_" . $aRelationship["name"] . " ON ";

                        $sOn = "";

                        foreach($aRelationship["local"] as $iIndex => $sField)
                        {
                            $sOn .= (!empty($sOn) ? " AND " : "") .
                                "_" . StringFunctions::ToSingular($this->_tableName) .
                                "." . $sField . " = " . "_" . $aRelationship["name"] .
                                "." . $aRelationship["foreign"][$iIndex];
                        }

                        $joinClause .= " {$sOn} ";
                    }
                }
            }

            $selectColumns = "_" . StringFunctions::toSingular($this->_tableName) . ".*";

            $orderClause = isset( $queryClauses[ "order" ]) ?
                "ORDER BY " . $queryClauses[ "order" ] : "";

            if(isset($queryClauses["distinct"]) && $queryClauses["distinct"] === true)
            {
                $selectColumns = " DISTINCT {$selectColumns} ";
            }
            
            if(isset($queryClauses["count"]) && $queryClauses["count"] === true)
            {
                $selectColumns = "COUNT({$selectColumns})";
            }

            // Concatenate all the pieces of the query together:
            $sql = "SELECT
                {$selectColumns}
            FROM
                {$this->_tableName} AS _" .
                    StringFunctions::toSingular($this->_tableName) . "
            {$joinClause}
            {$whereClause}
            {$orderClause}
            {$limitClause}
            {$offsetClause}"; // FIXME PostgreSQL Specific Syntax

            // Execute and pray:
            if(!($this->_dataSet = $this->_database->query($sql)))
            {
                throw new Exception("Failed on Query: " .
                    $this->_database->getLastError());
            }

            // Loop the data and create member variables
            if($this->_dataSet->count() != 0)
            {
                $this->load($this->_dataSet->current());
            }

            if(isset($queryClauses["count"]) && $queryClauses["count"] == true)
            {
                return $this->_dataSet->current()->count;
            }
            
            return $this;

        } // find()


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
        public function findCount($clauses)
        {
            $result = $this->find($clauses + array("count" => true));
            
            return $result;

        } // findCount()
        
        
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
        protected function getDataByColumnValue($columnName, $columnValue, $orderBy = "")
        {
            $columns = $this->_database->getTableColumns($this->_tableName);
            
            $column = null;
            
            foreach($columns as $name => $tmpColumn)
            {
                if(strtolower(str_replace("_", "", $name)) == strtolower($columnName))
                {
                    $column = $tmpColumn;
                    break;
                }
            }
            
            if(is_null($column))
            {
                throw new Exception("Database column {$this->_tableName}.{$columnName} does not exist.");
            }
            
            $dataType = $column["type"];
            
            $selectClauses = array(
                "where" => $column["field"] . " = " . 
                    $this->_database->formatData($dataType, $columnValue)
            ); // FIXME (possible) PostgreSQL Specific Syntax
            
            if(!empty($orderBy))
            {
                $selectClauses["order"] = $orderBy;
            }
            
            $this->find($selectClauses);
            
            return $this;
            
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
        protected function destroyDataByColumnValue($columnName, $columnValue)
        {
            $columns = $this->_database->getTableColumns($this->_tableName);
            
            $column = null;
            
            foreach($columns as $name => $tmpColumn)
            {
                if(strtolower(str_replace("_", "", $name)) == strtolower($columnName))
                {
                    $column = $tmpColumn;
                    break;
                }
            }
            
            if(is_null($column))
            {
                throw new Exception("Database column {$this->_tableName}.{$columnName} does not exist.");
            }
            
            $dataType = $column["type"];
            
            $sql = "DELETE FROM 
                {$this->_tableName}
            WHERE
                " . $column["field"] . " = " . $this->_database->FormatData($dataType, $columnValue);
            // FIXME PostgreSQL Specific Syntax
            
            if(!$this->_database->Query($sql))
            {
                throw new QueryFailedException("Failed to delete data");
            }
            
            return true;
            
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
            $record = new StdClass();
            
            foreach($this->_data as $key => $value)
            {
                if(is_object($value))
                {
                    if($value->count() > 1)
                    {
                        $record->$key = array();
                        
                        foreach($value as $valueValue)
                        {
                            $oRecord->{$key}[] = $valueValue->GetRecord();
                        }
                    }
                    else
                    {
                        $record->$key = $value->GetRecord();
                    }
                }
                else
                {
                    $record->$key = $value;
                }
            }
            
            return $record;
        
        } // getRecord()
        
        
        /**
         *
         *
         *       
         */                     
        public function getAll()
        {
            $records = array();
            
            $this->rewind();
            
            foreach( $this->_dataSet as $data )
            {
                $records[] = $data;
            }
        
            return $records;
        
        } // getAll()
        
        
        /**
         *
         *
         *       
         */                     
        protected function findRelationship($relationshipName)
        {
            $foreignKeys = $this->_database->getTableForeignKeys($this->_tableName);
            
            foreach($foreignKeys as $foreignKey)
            {
                if($foreignKey["name"] == $relationshipName)
                {
                    return $foreignKey;
                }
            }
            
            return null;
        
        } // findRelationship()
        
        
        /**
         *
         *
         *       
         */                     
        protected function findRelationship2( $primaryTable, $relatedTable, $through )
        {
            $foreignKeys = $this->_database->GetTableForeignKeys($primaryTable);
            
            foreach($foreignKeys as $foreignKey)
            {
                if($foreignKey["table"] == $relatedTable &&
                    current($foreignKey["local"]) == $through)
                {
                    return $foreignKey;
                }
            }
            
            return null;
        
        } // findRelationship2()
        
        
        /**
         * Loads the specified data (either an array or object) into the CRUD object. This 
         * array/object to load can contained referenced data (through foreign keys) as either
         * an array or object.
         *               
         * @argument mixed The data to load into the CRUD object
         * @returns void
         */
        protected function load($record)
        {
            if(!is_object($record) && !is_array($record))
            {
                return;
            }
            
            $columns = $this->_database->getTableColumns($this->_tableName);
            $relationships = $this->_database->getTableForeignKeys($this->_tableName);

            foreach($record as $key => $value)
            {
                if(is_array($value) || is_object($value))
                {
                    if(isset($relationships[$key]))
                    {
                        $relationship = $relationships[$key];
                        $tableName = $relationships[$key]["table"];

                        if($relationship["type"] == "1-1" || $relationship["type"] == "m-1")
                        {                           
                            $this->_data[$key] = $this->instantiateClass($tableName, $value);
                        }
                        else if($relationships[$key]["type"] == "1-m")
                        {
                            if(!isset($this->_data[$key]))
                            {
                                $this->_data[$key] = array();
                            }

                            foreach($value as $relatedData)
                            {
                                $this->_data[$key][] = $this->instantiateClass($tableName, $relatedData);
                            }
                        }
                    }                   
                }
                else if(isset($columns[$key]))
                {
                    $this->_data[$key] = $value;
                }
                elseif(isset($this->{$key}))
                {
                    $this->{$key} = $value;
                }
            }

        } // load()
        
        
        /**
         *  Determines whether or not there is currently data in the CRUD object. Data is loaded into 
         *  CRUD through the Find() method, through specifying data into fields manually, or by 
         *  passing data to the constructor. If any of these cases are met, this method will 
         *  return true.                 
         *  
         * @returns boolean True if there is no data currently in CRUD, false otherwise
         */
        protected function isEmpty()
        {
            return $this->count() == 0;
            
        } // isEmpty()
        
        
        /**
         *  Gets the number of rows returned by the last Find() call. If Find() has not yet been 
         *  called, this method will return This method is invoked through the __call() method to 
         *  allow using the method name Count(), which is a reserved word in PHP.                    
         *  
         * @returns integer The number of results in the data set
         */
        public function count() 
        {
            if(!is_null($this->_dataSet))
            {
                return $this->_dataSet->count();
            }
            
            return 0;
        
        } // count()
            
        
        /**
         *
         *
         *
         */                             
        public function __isset($name)
        {
            return isset($this->_data[$name]) || isset($this->{$name});
            
        } // __isset()
        
        
        /**
         *
         *
         */                     
        public function __get($name)
        {
            if(array_key_exists($name, $this->_data))
            {
                return $this->_data[$name];
            }
        
            $definition = $this->_database->getTableDefinition($this->_tableName);
            
            $relationships = $definition["foreign_key"];            

            if(!isset($relationships[$name]))
            {
                throw new Exception("Relationship [{$name}] does not exist");
            }

            $relationship = $definition["foreign_key"][$name];
            
            // the relationship exists, attempt to load the data:
            
            if($relationship["type"] == "1-m")
            {               
                $whereClause = "";
                
                foreach($relationship["foreign"] as $index => $key)
                {
                    $related = $relationship["local"][$index];
                    
                    $whereClause .= empty($whereClause) ? "" : " AND ";
                    $whereClause .= " {$key} = " . intval($this->_data[$related]);
                    // FIXME postgresql specific syntax
                }
                            
                $this->_data[$name] = $this->instantiateClass($relationship["table"]);
                $this->_data[$name]->find(array( "where" => $whereClause));
            }
            else
            {
                $localColumn = current($relationship["local"]);
                
                if(isset($this->_data[$localColumn]))
                {
                    $this->_data[$name] = $this->instantiateClass($relationship["table"]);
                    $this->_data[$name]->find($this->_data[$localColumn]);  
                }
                else
                {
                    // Modified 2007-12-29 to prevent error:
                    // Notice: Indirect modification of overloaded property has no effect
                    // If we are dynamically creating a record, we need to return an empty object for 
                    // this relationship to load into
                    
                    $this->_data[$name] = $this->instantiateClass($relationship["table"]);
                }
            }
            
            return $this->_data[$name];
            
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
        public function __set($name, $value)
        {           
            $columns = $this->_database->getTableColumns($this->_tableName);

            if(isset($columns[$name]))
            {
                if(strpos($columns[$name][ "type" ], "bool" ) !== false)
                {
                    $this->_data[$name] = $value == "t" ? true : false;
                }
                else
                {
                    $this->_data[$name] = $value;
                }
            }
            else if(!is_null($this->findRelationship($name)))
            {
                $this->_data[$name] = $value;
            }
            else
            {
                throw new Exception("Unknown column [{$name}] referenced");
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
        public function __unset($name)
        {
            if(isset($this->_data[$name]))
            {
                unset($this->_data[$name]);
            }
        
        } // __unset()
        
        
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
        public function __call($name, $arguments)
        {
            switch(strtolower($name))
            {
                case "empty":
                    return $this->isEmpty();
                break;
            }
            
            if(is_callable(array($this->_database, $name)))
            {
                return call_user_func_array(array($this->_database, $name), $arguments);
            }
            
            if(strtolower(substr($name, 0, 6)) == "findby")
            {
                return $this->getDataByColumnValue(substr($name, 6), $arguments[0],
                    isset($arguments[1]) ? $arguments[1]  : null);
            }
            else if(strtolower(substr($name, 0, 9)) == "destroyby")
            {
                return $this->destroyDataByColumnValue(substr($name, 9), $arguments[0]);
            }
            
            throw new Exception( "Call to undefined method: {$name}" );
                
        } // __call()
        
        
        /**
         * Assists slightly in object cloning. If this table has a single primary key, the value
         * of this key will be whiped out when cloning.          
         *               
         * @returns void         
         */             
        public function __clone()
        {
            $primaryKey = $this->_database->getTablePrimaryKey($this->_tableName);
            
            if(count($primaryKey) == 1)
            {
                $primaryKey = reset($aprimaryKey);
                
                $this->{$primaryKey} = null;
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
            $primaryKeys = $this->_database->getTablePrimaryKey($this->_tableName);
            
            $insertQuery = false;
            
            // If we have a compound primary key, we must first determine if the record
            // already exists in the database. If it does, we're doing an update.
            
            // If we have a singular primary key, we can rely on whether the primary key
            // value of this object is null
            
            if(count($primaryKeys) == 1)
            {
                $primaryKey = reset($primaryKeys);
                
                if($this->_database->isPrimaryKeyReference($this->_tableName, $primaryKey))
                {
                    // See Task #56
                    $insertQuery = !$this->recordExists();
                }
                else if(empty($this->_data[$primaryKey]))
                {
                    $insertQuery = true;
                }
            }
            else
            {
                $insertQuery = !$this->recordExists();
            }
            
            if($insertQuery)
            {
                return $this->insert();
            }
            else
            {
                return $this->update();
            }
        
        } // save()
        
        
        /**
         *
         */
        public function saveAll()
        {           
            $foreignKeys = $this->_database->getTableForeignKeys($this->_tableName);
                    
            // Save all dependencies first

            foreach($foreignKeys as $relationship)
            {
                $relationshipName = $relationship["name"];
                
                if(isset($this->_data[$relationshipName]) && $relationship["dependency"])
                {
                    if(!$this->_data[$relationshipName]->SaveAll())
                    {
                        return false;
                    }
                    
                    // We only work with single keys here !!
                    $local = reset($relationship["local"]);
                    $foreign = reset($relationship["foreign"]);
                    
                    $this->_data[$local] = $this->_data[$relationshipName]->$foreign;
                }
            }

            // Save the primary record
            
            if(!self::save()) //!$this->Save() )
            {
                return false;
            }
            
            // Save all related data last

            foreach($foreignKeys as $relationship)
            {
                $relationshipName = $relationship["name"];
                
                if(isset($this->_data[$relationshipName]) && !$relationship["dependency"])
                {
                    // We only work with single keys here !!
                    $local = reset($relationship["local"]);
                    $foreign = reset($relationship["foreign"]);
                        
                    if($relationship["type"] == "1-m")
                    {
                        foreach($this->_data[$relationshipName] as $relationship)
                        {
                            $relationship->$foreign = $this->_data[$local];
                            
                            if(!$relationship->saveAll())
                            {
                                return false;
                            }
                        }
                    }
                    else if($relationship["type"] == "1-1")
                    {                       
                        $this->_data[$relationshipName]->$foreign = $this->_data[$local];
                        $this->_data[$relationshipName]->saveAll();
                    }
                }
            }
            
            return true;
        
        } // saveAll()
        
        
        /**
         * Generates an insert query based on the data stored within this class
         * 
         * @returns bool True if the query was successful, false otherwise
         */
        protected function insert()
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
                    !$this->_database->isPrimaryKeyReference($this->_tableName, reset($primaryKeys)))
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
                    $this->_database->formatData($column[ "type" ], $sValue);
            }
            
            $sql = "INSERT INTO {$this->_tableName} (
                {$columnsList}
            ) VALUES (
                {$valuesList}
            ) RETURNING *"; // FIXME PostgreSQL Specific Syntax
            
            $resultData = null;
            
            if(($resultData = $this->_database->query($sql)) === null)
            {
                throw new Exception($this->_database->getLastError());
            }
            
            if($resultData->valid())
            {                
                foreach($resultData->getRecord() as $key => $value)
                {
                    $this->_data[$key] = $value;
                }
            }
            
            return true;
            
        } // insert()
        
        
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
            
            if(($resultData = $this->_database->query($sql)) === null)
            {
                throw new Exception($this->_database->getLastError());
            }
            
            if($resultData->valid())
            {                
                foreach($resultData->getRecord() as $key => $value)
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
            $definition = $this->_database->getTableDefinition($this->_tableName);
            
            $primaryKeys = $definition["primary_key"];
                    
            $setClause = "";

            // loop each field in the table and specify it's data:
            foreach($definition["fields"] as $field)
            {
                // do not update certain fields:
                if(in_array($field["field"], array("created_date", "created_stamp", "created_on")))
                {
                    continue;
                }
                
                // automate updating update date fields:
                if(in_array($field["field"], array("updated_date", "updated_stamp", "updated_on")))
                {
                    // FIXME We shouldn't assume the developer would want times 
                    // in GMT
                    $this->_data[$field["field"]] = gmdate("Y-m-d H:i:s");
                }
                
                if(!isset($this->_data[$field["field"]]))
                {
                    continue;
                }
                
                // complete the query for this field:
                $setClause .= (!empty($setClause) ? ", " : "") . 
                    $field["field"] . " = " . 
                        $this->_database->formatData($field["type"], $this->_data[$field["field"]]) . " ";
            }
            
            // if we found no fields to update, return:
            if(empty($setClause))
            {
                return;
            }
            
            $whereClause = "";
            
            foreach($primaryKeys as $key)
            {
                $whereClause .= !empty($whereClause) ? " AND " : "";
                $whereClause .= "{$key} = " . intval($this->_data[$key]);
            }

            $sql = "UPDATE 
                {$this->_tableName}
            SET
                {$setClause}
            WHERE
                {$whereClause}
            RETURNING *";    // FIXME PostgreSQL Specific Syntax

            return $sql;
            
        } // updateQuery()
        
        
        /**
         *
         *
         *       
         */                     
        protected function recordExists()
        {
            $primaryKeys = $this->_database->getTablePrimaryKey($this->_tableName);
        
            $sql = "SELECT
                1
            FROM
                {$this->_tableName} ";
            
            $whereClause = "";
            
            foreach($primaryKeys as $primaryKey)
            {
                $columnType = $this->_database->getColumnType($this->_tableName, $primaryKey);
                
                $whereClause .= empty($whereClause) ? " WHERE " : " AND ";
                $whereClause .= $primaryKey . " = " . 
                    $this->_database->formatData($columnType, $this->_data[$primaryKey]) . " ";
            }
            
            $sql .= $whereClause; // FIXME PostgreSQL Specific Syntax
            
            if(!($resultSet = $this->_database->query($sql)))
            {
                throw new QueryFailedException($this->_database->getLastError());
            }
            
            return $resultSet->count() != 0;
        
        } // recordExists()
        
        
        /**
         * Destroys (deletes) the current data. This method will delete the primary record 
         * (assuming that the primary key for the data is set).
         *  
         * @returns void
         */
        public function destroy()
        {
            $primaryKeys = $this->_database->getTablePrimaryKey($this->_tableName);
            
            $sql = "DELETE FROM
                {$this->_tableName}
            WHERE ";
            
            $whereClause = "";
            
            foreach($primaryKeys as $key)
            {
                $columnType = $this->_database->getColumnType($this->_tableName, $key);
                
                $whereClause .= empty($whereClause) ? "" : " AND ";
                $whereClause .= "{$key} = " . $this->_database->formatData($columnType, $this->_data[$key]);
            }
            
            $sql .= $whereClause; // FIXME PostgreSQL Specific Syntax
            
            if(!$this->_database->query($sql))
            {
                throw new QueryFailedException($this->_database->getLastError() );
            }
        
        } // destroy()
        
        
        /**
         * Helper method for generating a where clause for a query string. Where clause is
         * built by supplied keys and associated data
         * 
         */                     
        protected function buildWhereClause($keys, $dataSet)
        {
            $where = "";
            
            // loop each primary key and build a where clause for the data: 
            foreach($keys as $key)
            {
                if(isset($dataSet->$key))
                {
                    $where .= !empty($where) ? " AND " : " WHERE ";
                    $where .= "{$key} = {$dataSet->$key}";
                }
            }
             // FIXME PostgreSQL Specific Syntax
            return $where;
            
        } // buildWhereClause()
    
    
        //
        // ITERATOR DEFINITION
        //
        

        /**
         *
         * 
         * 
         */              
        public function rewind() 
        {                           
            if(!is_null($this->_dataSet) && $this->_dataSet->count() != 0)
            {
                $this->cleanup();
                
                $this->_dataSet->rewind();
            
                if($this->_dataSet->valid())
                {
                    $this->load($this->_dataSet->current());
                }
            }
        
        } // rewind()
        

        /**
         *  Returns the current object from the DataSet generated from the last call to Find().
         *  This method is part of the PHP Iterator implementation.      
         *  
         * @returns CRUD Returns a CRUD object if there data, or null otherwise
         */
        public function current() 
        {           
            if($this->valid())
            {
                return $this;
            }
            
            return null;
        
        } // current()
        
        
        /**
         *
         * 
         * 
         */     
        public function key() 
        {           
            return $this->_dataSet->key();
            
        } // key()


        /**
         *
         * 
         * 
         */     
        public function next() 
        {           
            if(!is_null($this->_dataSet))
            {
                $this->cleanup();
            
                $this->_dataSet->next();
                
                if($this->valid())
                {
                    $definition = $this->_database->getTableDefinition($this->_tableName);
                
                    $data = $this->_dataSet->current();
                    
                    // Turn any boolean fields into true booleans, instead of chars:
                    
                    foreach($data as $key => $value)
                    {
                        if(strpos($definition[ "fields" ][$key][ "type" ], "bool" ) !== false)
                        {
                            $data->$key = $value == "t" ? true : false;
                        }
                    } 
                    
                    // FIXME PostgreSQL Specific Syntax (boolean handling)
                        
                    $this->load($data);
                }
            }
        
        } // next()


        /**
         *
         * 
         * 
         */     
        public function valid()  
        {           
            return $this->_dataSet->valid();
        
        } // valid()
        
        
        /**
         *
         *
         */                     
        protected function cleanup()
        {
            $relationships = $this->_database->getTableForeignKeys($this->_tableName);
            
            foreach($relationships as $relationship)
            {
                $relationshipName = $relationship["name"];
                
                if(isset($this->_data[$relationshipName]))
                {
                    unset($this->_data[$relationshipName]);
                }
            }
            
            $columns = $this->_database->getTableColumns($this->_tableName);
            
            // Loop each column in the table and create a member variable for it:           
            foreach($columns as $column)
            {
                $this->_data[$column["field"]] = null;
            }
                
        } // cleanup()
        
        
        
        /**
         * Returns the table name associated with this CRUD object
         *
         * @returns string The name of the table associated with this CRUD object
         */
        public function getTableName()
        {
            return $this->_tableName;
            
        } // getTableName() 
        
        
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
        public function asXmlString($includeReferences = false, $provideAll = false)
        {
            $xmlObj = $this->asXml($includeReferences, $provideAll);
            $xml = XMLFunctions::PrettyPrint($xmlObj->asXML());
            
            return $xml;
            
        } // AsXmlString()
        
        
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
        public function asXml($includeReferences = false, $provideAll = false)
        {
            $xml = null;
            
            if($provideAll)
            {
                $name = $this->_tableName;
                $elementName = StringFunctions::toSingular($this->_tableName);
                
                $xml = new SimpleXMLElement("<{$name}></{$name}>");
                
                foreach($this as $object)
                {
                    $element = $xml->addChild($elementName);
                    $this->addColumns($element, $object, $this->_tableName);
                    
                    if($includeReferences)
                    {
                        $this->addReferences($element, $object, $this->_tableName);
                    }
                }
            }
            else
            {
                $name = StringFunctions::toSingular($this->_tableName);
                
                $xml = new SimpleXMLElement("<{$name}></{$name}>");
                
                $this->addColumns($xml, $this, $this->_tableName);
            
                if($includeReferences)
                {
                    $this->addReferences($xml, $this, $this->_tableName);
                }
            }
        
            return $xml;
                
        } // asXml()
        
    
        /**
         * Add the database table columns for the specified table, from the specified object, to
         * the specfied SimpleXMLElement. Used internally by AsXML() 
         *          
         * @argument SimpleXMLElement 
         * @argument CRUD
         * @argument string           
         * @returns SimpleXMLElement The data requested as a SimpleXMLElement object
          */
        private function addColumns(&$element, &$object, $tableName)
        {
            $columns = $this->_database->getTableColumns($tableName);
            
            foreach($columns as $column)
            {
                $element->addChild($column["field"], $object->{$column["field"]});
            }
            
        } // addColumns()
        

        /**
         * Add the database table references for the specified table, from the specified object, to
         * the specfied SimpleXMLElement. Used internally by AsXML()   
         *          
         * @argument SimpleXMLElement 
         * @argument CRUD
         * @argument string           
         * @returns SimpleXMLElement The data requested as a SimpleXMLElement object
         */
        private function addReferences(&$element, &$object, $tableName)
        {
            $tableReferences = $this->_database->getTableForeignKeys($tableName);
                
            foreach($tableReferences as $reference)
            {
                $data = $this->{$reference[ "name" ]};
                                
                if(!empty($data) && !$data->Empty())
                {
                    if($reference["type"] == "1-m")
                    {
                        $childReferenceName = StringFunctions::toSingular($reference["name"]);
                        
                        $referenceObj = $element->addChild($reference["name"]);
                        
                        foreach($data as $dataElement)
                        {
                            $childReference = $referenceObj->addChild($childReferenceName);
                            
                            $this->addColumns($childReference, $dataElement, $reference["table"]);
                        }
                    }
                    else
                    {
                        $referenceObj = $element->addChild($reference["name"]);
                        $this->addColumns($referenceObj, $data, $reference["table"]);
                    }
                }
                
            }
        
        } // addReferences()

        
        /**
         * Returns the data currently stored in the CRUD object as a JSON (JavaScript object notation)
         * string. If bIncludeReferences is true, then each reference to the table is considered and 
         * added to the XML document.
         *
         * @argument bool Toggles whether references/relationships should be stored in the JSON string       
         * @returns string A JSON string representing the CRUD object
         */
        public function asJson($includeReferences = false)
        {
            $json = new JSONObject();
            
            $columns = $this->_database->getTableColumns($this->_tableName);
            
            foreach($columns as $column)
            {
                $json->addAttribute($column["field"], $this->_data[$column["field"]]);
            }
            
            if($includeReferences)
            {
                $tableReferences = $this->_database->getTableForeignKeys($this->_tableName);
                    
                foreach($tableReferences as $reference)
                {
                    $data = $this->{$reference["name"]};
                                    
                    if(!empty($data) && !$data->Empty())
                    {
                        $referenceColumns = $this->_database->getTableColumns($reference["table"]);
                            
                        if($reference["type"] == "1-m")
                        {                       
                            $references = array();
                            
                            $childReferenceName = StringFunctions::toSingular($reference["name"]);
                            
                            foreach($data as $dataElement)
                            {
                                $referenceJSON = new JSONObject();
                            
                                foreach($referenceColumns as $column)
                                {
                                    $referenceJSON->addAttribute($column["field"], $data->{$column["field"]});
                                }
                            
                                $references[] = $referenceJSON;
                            }
                            
                            $json->addAttribute($reference["name"], $references);                            
                        }
                        else
                        {
                            $referenceJSON = new JSONObject();
                            
                            foreach($referenceColumns as $column)
                            {
                                $referenceJSON->addAttribute($column["field"], $data->{$column["field"]});
                            }
                            
                            $json->addAttribute($reference["name"], $referenceJSON);
                        }
                    }
                    
                }
            }
            
            return $json->__toString();
            
        } // asJson()
        
        
        /**
         * Creates a readable, string representation of the object using print_r and returns that
         * string.       
         *
         * @returns string A readable, string representation of the object
         */
        public function __toString()
        {
            return print_r($this->_data, true);
            
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
        private function &instantiateClass($tableName, $data = null)
        {           
            $modelName = StringFunctions::toSingular($tableName);
            
            $object = null;
            
            if( class_exists($modelName, true) && is_subclass_of($modelName, "Model"))
            {
                $object = new $modelName($data);
            }
            else
            {
                $object = new CRUD($tableName, $data);
            }            
            
            return $object;
            
        } // instantiateClass()
        
    } // CRUD()

?>