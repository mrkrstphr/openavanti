<?php
/**
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson <kwilson@shuttlebox.net>
 * @copyright       Copyright (c) 2007-2010, Kristopher Wilson
 * @package         openavanti 
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         SVN: $Id$
 */


namespace OpenAvanti\Db;
 
use \Exception;


/**
 * Database abstraction layer implementing CRUD procedures
 *
 * @category    Database
 * @author      Kristopher Wilson <kwilson@shuttlebox.net>
 * @package     openavanti
 * @link        http://www.openavanti.com/documentation/1.4.0/CRUD.php
 */
class CRUD implements \Iterator, \Countable
{
    /**
     * Specifies the database profile to use
     */
    protected $_profileName = null;
    
    
    /**
     * The table identifier for this data element
     */
    protected $_tableIdentifier = null;
    
    /**
     * A reference to the database connection
     */
    protected $_database = null;
    
    
    /**
     * A reference to the result set for the last query
     */
    protected $_dataSet = null;
    
    
    /**
     * Stores the data for the current object
     */
    protected $_data = array();
    
    
    /**
     * The constructor makes the necessary connection to the database (see Database::Construct) 
     * and attempts to load the schema of the specified table.
     *  
     * If the second argument of oData is supplied, the constructor will attempt to load that 
     * data into the class for later saving.
     * 
     * If there is a define defined called ENABLE_SCHEMA_CACHING, schema caching is turned on, 
     * allowing for faster subsequent page loads.       
     *       
     * @param string $identifier The name of the database table
     * @param mixed $data Optional; An array or object of data to load into the CRUD object
     * @param string $profileName Optional; The name of the database profile to use
     */
    public function __construct($identifier, $data = null, $profileName = "")
    {
        if(!empty($profileName))
        {
            $this->_profileName = $profileName;
        }
        
        $this->_database = Database::getConnection($this->_profileName);
        
        $this->_tableIdentifier = $identifier;
        
        // Get the schema for this table:
        $this->_database->getTableDefinition($identifier);
          
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
     */
    protected function prepareColumns()
    {
        $columns = $this->_database->getTableColumns($this->_tableIdentifier);
        
        // Loop each column in the table and create a member variable for it:           
        foreach($columns as $column)
        {
            $this->_data[$column["name"]] = null;
        }
    
    } // prepareColumns()


    /**
     * This method attempts to load a record from the database based on the passed ID, or a
     * passed set of SQL query clauses. This method can be used retrieve one record from the
     * database, or a set of records that can be iterated through.
     *
     * @param mixed $data An ID to load into the class or additional databases
     *      clauses, including: join, where, order, offset and limit. All
     *      except for join are string that are directly appended to the query.
     *      Join is an array of referenced tables to inner join.
     * @return CRUD returns a reference to itself to allow chaining
     */
    public function find($data = null)
    {
        $primaryKey = $this->_database->getTablePrimaryKey($this->_tableIdentifier);
        
        $queryClauses = array();
         
        if(is_numeric($data) || is_string($data))
        {
            if(count($primaryKey) > 1)
            {
                throw new QueryFailedException("Primary key is compound but scalar provided.");
            }
            
            $primaryKeyColumn = reset($primaryKey);
            
            $queryClauses["where"] = "{$primaryKeyColumn} = ?"; 
            $queryClauses["params"] = array($data);
        }
        else if(!is_array($data) && !is_null($data))
        {
            throw new QueryFailedException("Invalid argument provided to " . 
                __METHOD__ . ": " . gettype($data));
        }
        else
        {
            $queryClauses = $data;
        }
        
        if(isset($data["as"]))
        {
            $tableAlias = $data["as"];
        }
        else
        {
            // FIXME: This is borked -- why?
            $singularIdentifier = \OpenAvanti\StringFunctions::toSingular($this->_tableIdentifier);
            $tableAlias = $this->_database->getIdentifier($singularIdentifier, "_", false);
        }
        
        $whereClause = isset($queryClauses["where"]) ? $queryClauses["where"] : "";
        
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

                    $sJoinType = (isset($xJoin["type"]) ? $xJoin["type"] : "INNER") . " JOIN";

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
                                (!empty($this->_schemaName) ? $this->_schemaName . '.' : '') . 
                                $this->_tableName . " -> " . $xJoin["table"] . "." .
                                $xJoin["on"]);
                        }
                        
                        // Start the join:
                        $joinClause .= "{$sJoinType} " . $xJoin["table"] . " ";
                        
                        if(!empty($xJoin["as"]))
                        {
                            $as = $xJoin["as"];
                        }
                        else
                        {
                            // Determine the alias (AS):
                            //$sAs = "_" . $aRelationship["name"];
                            
                            $as = $this->_database->getIdentifier($aRelationship["schema"], 
                                \OpenAvanti\StringFunctions::toSingular($aRelationship["name"]), "_", false);
                        }

                        $xJoin["as"] = $as; // Store this for later use!

                        // Add the alias:
                        $joinClause .= " AS " . $as . " ";

                        // Add the ON clause:
                        $joinClause .= " ON " . $aJoin["as"] . "." .
                            current($aRelationship["local"]) . " = " .
                            $as . "." . current($aRelationship["foreign"]) . " ";
                    }
                    else
                    {
                        // Find the relationship:
                        $aRelationship = $this->findRelationship2($this->_tableIdentifier,
                            $xJoin["table"], $xJoin["on"]);

                        // If the relationship doesn't exist:
                        if(empty($aRelationship))
                        {
                            throw new Exception("Relationship not found: " .
                                $this->_tableIdentifier . " -> " . $xJoin["table"] . "." .
                                $xJoin["on"]);
                        }

                        // Start the join:
                        $joinClause .= "{$sJoinType} " . $xJoin["table"] . " ";

                        if(!empty($xJoin["as"]))
                        {
                            $as = $xJoin["as"];
                        }
                        else
                        {
                            // Determine the alias (AS):
                            //$sAs = "_" . $aRelationship["name"];
                            
                            $as = $this->_database->getIdentifier($aRelationship["schema"], 
                                \OpenAvanti\StringFunctions::toSingular($aRelationship["name"]), "_", false);
                        }

                        if(!empty($xJoin["as"]))
                        {
                            $as = $xJoin["as"];
                        }

                        $xJoin["as"] = $as; // Store this for later use!

                        // Add the alias:
                        $joinClause .= " AS " . $as . " ";

                        // Add the ON clause:
                        $joinClause .= " ON _{$tableAlias}." .
                            current($aRelationship["local"]) . " = " .
                            $as . "." . current($aRelationship["foreign"]) . " ";
                    }
                }
                else
                {
                    $aRelationship = $this->FindRelationship($xJoin);

                    if(!count($aRelationship))
                    {
                        throw new Exception("Unknown join relationship specified: {$xJoin}");
                    }
                    
                    $as = $this->_database->getIdentifier($aRelationship["schema"], 
                        \OpenAvanti\StringFunctions::toSingular($aRelationship["name"]), "_", false);
                    
                    $joinClause .= " INNER JOIN " . $aRelationship["table"] . " AS " .
                        "{$as} ON ";

                    $sOn = "";

                    foreach($aRelationship["local"] as $iIndex => $sField)
                    {
                        $sOn .= (!empty($sOn) ? " AND " : "") .
                            "_{$tableAlias}." . $sField .
                            " = {$as}." . $aRelationship["foreign"][$iIndex];
                    }

                    $joinClause .= " {$sOn} ";
                }
            }
        }


        $orderClause = isset($queryClauses["order"]) ?
            "ORDER BY " . $queryClauses["order"] : "";

        $tableIdentifier = $this->_database->getIdentifier($this->_tableIdentifier);
        
        if(isset($queryClauses["columns"]))
            $selectColumns = $queryClauses["columns"];
        else
            $selectColumns = "_" . $tableAlias . ".*";
        
        if(isset($queryClauses["distinct"]) && $queryClauses["distinct"] === true)
            $selectColumns = " DISTINCT {$selectColumns} ";
        
        if(isset($queryClauses["count"]) && $queryClauses["count"] === true)
            $selectColumns = "COUNT(*) AS count";
       
        if(!isset($queryClauses["params"]) || !is_array($queryClauses["params"]))
            $queryClauses["params"] = array();
        
        // Concatenate all the pieces of the query together:
        $sql = "SELECT {$selectColumns} FROM {$tableIdentifier} AS _{$tableAlias} " . 
            "{$joinClause} {$whereClause} {$orderClause} {$limitClause} " . 
            "{$offsetClause}";
        
        if(!($this->_dataSet = $this->_database->query($sql, $queryClauses["params"])))
            throw new Exception("Failed on Query: " . $this->_database->getLastError());

        // Loop the data and create member variables
        if($this->_dataSet->count() != 0)
            $this->load($this->_dataSet->current());

        if(isset($queryClauses["count"]) && $queryClauses["count"] == true)
            return $this->_dataSet->current()->count;
        
        return $this;

    } // find()
    
    
    /**
     * This method will retrieve records from the table based on column value using the supplied
     * column name (which may have had underscores removed and be cased differently) and
     * column value.
     * 
     * This method is invoked through __call() when the user uses the CRUD::FindBy[column]()
     * "virtual" method.                                     
     *
     * @throws Exception, QueryFailedException
     * 
     * @param string $columnName The name of the column we are pulling records by. This name may 
     *      underscores removed and be cased differently         
     * @param string $columnValue The value of the column in the first argument that determines
     *      which records will be selected
     * @param string $orderBy Optional; The order clause for the query. Default: null
     * 
     * @return CRUD A reference to the current object to support chaining or secondary assignment                                     
     */
    protected function getDataByColumnValue($columnName, $columnValue, $orderBy = "")
    {
        $columns = $this->_database->getTableColumns($this->_tableIdentifier);
        
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
            throw new Exception("Database column " . 
                "{$this->_tableIdentifier}.{$columnName} does not exist.");
        
        $selectClauses = array(
            "where" => $column["field"] . " = ?", 
            "params" => array($columnValue)
        );
        
        if(!empty($orderBy))
            $selectClauses["order"] = $orderBy;
        
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
     * @throws Exception, QueryFailedException
     * 
     * @param string The name of the column we are basing our delete from. This name may
     *  underscores removed and be cased differently         
     * @param string The value of the column in the first argument that determines which
     *  records will be deleted.
     * @return boolean True if successful/no error; throws an Exception otherwise                                     
     */
    protected function destroyDataByColumnValue($columnName, $columnValue)
    {
        $columns = $this->_database->getTableColumns($this->_tableIdentifier);
        
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
            throw new Exception("Database column " . 
                "{$this->_tableIdentifier}.{$columnName} does not exist.");
        }
        
        $dataType = $column["type"];
        
        $tableIdentifier = $this->_database->getIdentifier($this->_tableIdentifier);
        
        $sql = "DELETE FROM {$tableIdentifier} WHERE " . $column["field"] . " = ?";
        
        if(!$this->_database->Query($sql, array($columnValue)))
        {
            throw new QueryFailedException("Failed to delete data");
        }
        
        return true;
        
    } // destroyDataByColumnValue() 
    

    /**
     * Loops all data stored within this CRUD class and returns a standard class copy
     * of the data as a StdClass.
     * 
     * NOTE:  GetRecord() will move the internal pointers of all 1-M iterators loaded
     *
     * @return StdClass A pure class copy of the data stored within the CRUD class          
     */
    public function getRecord()
    {           
        $record = new \StdClass();
        
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
     * Returns an array containing all data stored in the current data set from 
     * the last query
     *
     * @return array An array of data from the dataset      
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
     * Attempts to find a relationship to the current database table by the relationship name
     * provided.
     *
     * @param string $sName The name of the relationship to find
     * @return array An array containing information about the relationship, if found
     */
    protected function findRelationship($relationshipName)
    {
        $foreignKeys = $this->_database->getTableForeignKeys($this->_tableIdentifier);
        
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
     * Attempts to find a relationship between two specified tables based on the column they
     * are related through.
     *
     * @param string $sPrimaryTableName The name of the main table of the relationship
     * @param string $sRelatedTable The name of the related table of the relationship
     * @param string $sThroughColumn The name of the column of the primary table that the 
     *      the related table is related through.
     * @return array An array containing information about the relationship, if found
     */
    protected function findRelationship2($tableIdentifier, $relatedTable, $through)
    {
        list($schemaName, $tableName) = $this->_database->parseIdentifier($tableIdentifier);
       
        list($relatedSchemaName, $relatedTableName) = $this->_database->parseIdentifier($relatedTable, $schemaName);

        $foreignKeys = $this->_database->GetTableForeignKeys($tableIdentifier);
        
        foreach($foreignKeys as $foreignKey)
        {
            if($foreignKey["table"] == $relatedTableName && $foreignKey["schema"] == $relatedSchemaName &&
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
     * @param mixed $record The data to load into the CRUD object
     */
    protected function load($record)
    {
        if(!is_object($record) && !is_array($record))
        {
            return;
        }
        
        $columns = $this->_database->getTableColumns($this->_tableIdentifier);
        $relationships = $this->_database->getTableForeignKeys($this->_tableIdentifier);

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
            else if(is_scalar($value))
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
     * @return boolean True if there is no data currently in CRUD, false otherwise
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
     * @return integer The number of results in the data set
     */
    public function count() 
    {
        if(!is_null($this->_dataSet))
            return count($this->_dataSet);
        
        return 0;
    
    } // count()
        
    
    /**
     * Determines if the specified attribute is defined in the data array.
     *
     * @param string $sName The name of the attribute we are searching for
     * @return bool true if the attribute is set, false otherwise
     */
    public function __isset($name)
    {
        return isset($this->_data[$name]) || isset($this->{$name});
        
    } // __isset()
    
    
    /**
     * Retrieves the specified attribute based on its name and presence in the data array
     *
     * @param string $sName The name of the attribute to retrieve
     * @return mixed The value of the attribute if found
     */
    public function __get($name)
    {
        if(array_key_exists($name, $this->_data))
        {
            return $this->_data[$name];
        }
    
        $definition = $this->_database->getTableDefinition($this->_tableIdentifier);
        
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
     * @throws Exception
     * 
     * @param string $name The name of the column to set
     * @param string $value The value to set the column specified in the first argument
     */ 
    public function __set($name, $value)
    {           
        $columns = $this->_database->getTableColumns($this->_tableIdentifier);

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
        else if(is_scalar($value))
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
     * @param string $name The name of the database column to unset
     * @return void
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
     * getBy[column_name] and destroyBy[column_name], reserved word methods, such as empty(),
     * and also provides access to public methods of the database, which fakes database
     * class inheritance (which is needed to support multiple database drivers).
     *
     * @throws Exception
     * 
     * @param string $name The name of the argument to be called magically 
     * @param array $arguments An array of arguments to pass to the magically called method
     * @return mixed Depends $name, the first argument
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
     */             
    public function __clone()
    {
        $primaryKey = $this->_database->getTablePrimaryKey($this->_tableIdentifier);
        
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
     * @return boolean True if the save was successful, false otherwise         
     */
    public function save()
    {
        // grab a copy of the primary key:
        $primaryKeys = $this->_database->getTablePrimaryKey($this->_tableIdentifier);
        
        $insertQuery = false;
        
        // If we have a compound primary key, we must first determine if the record
        // already exists in the database. If it does, we're doing an update.
        
        // If we have a singular primary key, we can rely on whether the primary key
        // value of this object is null
        
        if(count($primaryKeys) == 1)
        {
            $primaryKey = reset($primaryKeys);
            
            if($this->_database->isPrimaryKeyReference($this->_tableIdentifier, $primaryKey))
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
     * saveAll() is misleading and really means SaveDeep. This method will attempt to save
     * all of the data stored within this CRUD class, and calls SaveAll() on all dependent
     * CRUD data that is currently loaded into the class. 
     *
     * @return bool True if the operation was successful, false if it failed
     */
    public function saveAll()
    {           
        $foreignKeys = $this->_database->getTableForeignKeys($this->_tableIdentifier);
                
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
         * Builds an insert query based on the data stored within this CRUD class and 
         * executes it against the database.
         *
         * @return bool True if the operation was successful, false on failure
         */
        protected function insert()
        {
            $columnsList = "";
            $valuesList = "";
            $params = array();

            $primaryKeys = $this->_database->getTablePrimaryKey($this->_tableIdentifier);          
            $columns = $this->_database->getTableColumns($this->_tableIdentifier);
            
            // loop each column in the table and specify it's data:
            foreach($columns as $column)
            {
                // automate updating created date column:
                if(in_array($column["name"], array("created_date", "created_stamp", "created_on")))
                {
                    // dates are stored as GMT
                    $this->_data[$column["name"]] = gmdate("Y-m-d H:i:s");
                }
                // FIXME (possible) PostgreSQL Specific Syntax (above; date formats)
                // FIXME Assumption that the developer wants dates in GMT
                
                // If the primary key is singular, do not provide a value for it:               
                if(in_array($column["name"], $primaryKeys) && count($primaryKeys) == 1 && 
                    !$this->_database->isPrimaryKeyReference($this->_tableIdentifier, reset($primaryKeys)))
                {
                    continue;
                }
                
                if(empty($this->_data[$column["name"]]))
                {
                    continue;
                }
                
                // Create a list of columns to insert into:
                $columnsList .= (!empty($columnsList) ? ", " : "") . 
                    $column[ "name" ];
                
                // Get the value for the column (if present):
                $value = isset($this->_data[$column["name"]]) ? 
                    $this->_data[$column["name"]] : "";
                
                // Create a list of values to insert into the above columns:
                $params[] = $value;

                $valuesList .= (!empty($valuesList) ? ", " : "") . "?";
            }
            
            if(empty($columnsList) || empty($valuesList))
            {
                return false;
            }
            
            $tableIdentifier = $this->_database->getIdentifier($this->_tableIdentifier);
            
            $sql = "INSERT INTO {$tableIdentifier} ({$columnsList}) VALUES ({$valuesList})";
            
            $result = null;
            
            if($this->_database->query($sql, $params) === false)
                throw new Exception($this->_database->getLastError());
            
            // For singular primary keys, let's grab the autoincrement or serial value
            
            if(count($primaryKeys) == 1)
            {
                $pk = current($primaryKeys);
                
                $this->_data[$pk] = $this->_database->lastInsertId($this->_tableIdentifier, $pk);
            }
            
            return true;
            
        } // insert()
        
        
        /**
         * Responsible for updating the currently stored data for primary table and
         * all foreign tables referenced
         * 
         * @return boolean True if the update was successful, false otherwise                       
         */
        protected function update()
        {           
            // update the primary record:
            $sql = $this->updateQuery();
            
            if(!$this->_database->query($sql['sql'], $sql['params']))
                throw new Exception($this->_database->getLastError());
            
            return true;
            
        } // update()
        
        
        /**
         * Called by the update() method to generate an update query for this table
         * 
         * @return string The generated SQL query               
         */
        protected function updateQuery()
        {
            $definition = $this->_database->getTableDefinition($this->_tableIdentifier);
            
            $primaryKeys = $definition["primary_key"];
                    
            $setClause = "";

            $params = array();

            // loop each field in the table and specify it's data:
            foreach($definition["columns"] as $field)
            {
                // do not update certain fields:
                if(in_array($field["name"], array("created_date", "created_stamp", "created_on")))
                {
                    continue;
                }
                
                // automate updating update date fields:
                if(in_array($field["name"], array("updated_date", "updated_stamp", "updated_on")))
                {
                    // FIXME We shouldn't assume the developer would want times 
                    // in GMT
                    $this->_data[$field["name"]] = gmdate("Y-m-d H:i:s");
                }
                
                if(!isset($this->_data[$field["name"]]))
                {
                    continue;
                }
                
                $params[] = $this->_data[$field["name"]];

                // complete the query for this field:
                $setClause .= (!empty($setClause) ? ", " : "") . $field["name"] . " = ?"; 
            }
            
            // if we found no fields to update, return:
            if(empty($setClause))
            {
                return;
            }
            
            $whereClause = "";

            foreach($primaryKeys as $key)
            {
                $params[] = $this->_data[$key];
                $whereClause .= !empty($whereClause) ? " AND " : "";
                $whereClause .= "{$key} = ?";
            }

            $tableIdentifier = $this->_database->getIdentifier($this->_tableIdentifier);

            $sql = "UPDATE {$tableIdentifier} SET {$setClause} WHERE {$whereClause}";

            return array('sql' => $sql, 'params' => $params);
            
        } // updateQuery()
        
        
        /**
         * Determines if the data currently stored within this CRUD class already exists 
         * in the database based on primary key data. This method is used to determine whether
         * to perform an INSERT or UPDATE operation on a table with compound primary keys
         *
         * @return bool True if the record exists, false if it does not
         */ 
        protected function recordExists()
        {
            $primaryKeys = $this->_database->getTablePrimaryKey($this->_tableIdentifier);
            $tableIdentifier = $this->_database->getIdentifier($this->_tableIdentifier);
            
            $sql = "SELECT 1 FROM {$tableIdentifier} ";
            
            $whereClause = "";
            $queryParams = array();

            foreach($primaryKeys as $primaryKey)
            {
                $queryParams[] = $this->_data[$primaryKey];
                $whereClause .= (empty($whereClause) ? " WHERE " : " AND ") . $primaryKey . " = ?"; 
            }
            
            $sql .= $whereClause;
            
            if(!($resultSet = $this->_database->query($sql, $queryParams)))
                throw new QueryFailedException($this->_database->getLastError());
            
            return $resultSet->count() != 0;
        
        } // recordExists()
        
        
        /**
         * Destroys (deletes) the current data. This method will delete the primary record 
         * (assuming that the primary key for the data is set).
         */
        public function destroy()
        {
            $primaryKeys = $this->_database->getTablePrimaryKey($this->_tableIdentifier);
            
            $tableIdentifier = $this->_database->getIdentifier($this->_tableIdentifier);
            
            $sql = "DELETE FROM {$tableIdentifier} WHERE ";
            
            $whereClause = "";
            $queryParams = array();

            foreach($primaryKeys as $key)
            {
                $queryParams[] = $this->_data[$key];
                $whereClause .= (empty($whereClause) ? "" : " AND ") . "{$key} = ?";
            }
            
            $sql .= $whereClause;
            
            if(!$this->_database->query($sql, $queryParams))
                throw new QueryFailedException($this->_database->getLastError() );
        
        } // destroy()
        
        
        /**
         * Helper method for generating a WHERE clause for a query string. WHERE clause is
         * built by supplied keys and associated data
         * 
         * @param array $keys The keys of the dataset
         * @param object $dataSet The dataset containing data for the WHERE clause
         * @return string The WHERE clause
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
         * Returns to the first element in the dataset
         *
         * @see http://www.php.net/manual/en/spl.iterators.php
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
         * Returns the current object from the DataSet generated from the last call to Find().
         * This method is part of the PHP Iterator implementation
         *
         * @see http://www.php.net/manual/en/spl.iterators.php
         * @return CRUD Returns a CRUD object if there data, or null otherwise
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
         * Gets the key of the current element in the dataset
         * 
         * @see http://www.php.net/manual/en/spl.iterators.php
         * @return mixed The key of the current element in the dataset
         */   
        public function key() 
        {           
            return $this->_dataSet->key();
            
        } // key()


        /**
         * Move to the next element in the dataset
         *
         * @see http://www.php.net/manual/en/spl.iterators.php
         */   
        public function next() 
        {           
            if(!is_null($this->_dataSet))
            {
                $this->cleanup();
            
                $this->_dataSet->next();
                
                if($this->valid())
                {
                    $definition = $this->_database->getTableDefinition($this->_tableIdentifier);
                
                    $data = $this->_dataSet->current();
                    
                    // Turn any boolean fields into true booleans, instead of chars:
                    
                    foreach($data as $key => $value)
                    {
                        
                        if(isset($definition["columns"][$key]) &&
                            strpos($definition["columns"][$key]["type"], "bool") !== false)
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
         * Checks if the iterator is valid
         * 
         * @see http://www.php.net/manual/en/spl.iterators.php
         * @return bool True if the iterator is valid, false otherwise
         */      
        public function valid()  
        {           
            return $this->_dataSet->valid();
        
        } // valid()
        
        
        /**
         * Removes all data and related data from the data array to prepare for the next load.
         * Prevents stale data from persisting when null values are encountered in the column
         * of the next data set.
         */
        protected function cleanup()
        {
            $relationships = $this->_database->getTableForeignKeys($this->_tableIdentifier);
            
            foreach($relationships as $relationship)
            {
                $relationshipName = $relationship["name"];
                
                if(isset($this->_data[$relationshipName]))
                {
                    unset($this->_data[$relationshipName]);
                }
            }
            
            $columns = $this->_database->getTableColumns($this->_tableIdentifier);
            
            // Loop each column in the table and create a member variable for it:           
            foreach($columns as $column)
            {
                $this->_data[$column["name"]] = null;
            }
                
        } // cleanup()
        
        
        /**
         * Returns the table name associated with this CRUD object
         *
         * @return string The name of the table associated with this CRUD object
         */
        public function getTableName()
        {
            return $this->_tableIdentifier;
            
        } // getTableName() 
        
        
        /**
         * Returns the data currently stored in the CRUD object a well formed XML document as a
         * string representation. This requires the DOM and SimpleXML extensions of PHP to be 
         * installed. If either extension is not installed, this method will throw an exception.
         *          
         * @param bool $includeReferences Optional; Should this returned XML include 
         *      references? Default false.
         * @param bool $provideAll Optional; Should this returned XML include all records 
         *      returned by the last Find() call? If not, only the current record stored is 
         *      returned. Default false.      
         * @return string A well formed XML document as a string representation
         */
        public function asXmlString($includeReferences = false, $provideAll = false)
        {
            $xmlObj = $this->asXml($includeReferences, $provideAll);
            $xml = \OpenAvanti\XMLFunctions::PrettyPrint($xmlObj->asXML());
            
            return $xml;
            
        } // AsXmlString()
        
        
        /**
         * Returns the data currently stored in the CRUD object a well formed XML document as a 
         * SimpleXMLElement object. This method requires the SimpleXML extension of PHP to be
         * installed. If the SimpleXML extension is not installed, this method will throw an 
         * exception.
         *          
         * @param bool $includeReferences Optional; Should this returned XML include 
         *      references? Default false.
         * @param bool $provideAll Optional; Should this returned XML include all records 
         *      returned by the last Find() call? If not, only the current record stored is 
         *      returned. Default false.      
         * @return SimpleXMLElement The data requested as a SimpleXMLElement object
         */
        public function asXml($includeReferences = false, $provideAll = false)
        {
            $xml = null;
            
            if($provideAll)
            {
                $name = $this->_tableName;
                $elementName = \OpenAvanti\StringFunctions::toSingular($this->_tableName);
                
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
                $name = \OpenAvanti\StringFunctions::toSingular($this->_tableName);
                
                $xml = new \SimpleXMLElement("<{$name}></{$name}>");
                
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
         * @param SimpleXMLElement $element
         * @param CRUD $object
         * @param string $tableName    
         * @return SimpleXMLElement The data requested as a SimpleXMLElement object
         */
        private function addColumns(&$element, &$object, $tableName)
        {
            // FIXME: ???
            $columns = $this->_database->getTableColumns($this->_schemaName, $tableName);
            
            foreach($columns as $column)
            {
                $element->addChild($column["field"], $object->{$column["field"]});
            }
            
        } // addColumns()
        

        /**
         * Add the database table columns for the specified table, from the specified object, to
         * the specfied SimpleXMLElement. Used internally by AsXML() 
         *          
         * @param SimpleXMLElement $element
         * @param CRUD $object
         * @param string $tableName    
         * @return SimpleXMLElement The data requested as a SimpleXMLElement object
         */
        private function addReferences(&$element, &$object, $tableName)
        {
            // FIXME: ???
            $tableReferences = $this->_database->getTableForeignKeys($this->_schemaName, $tableName);
                
            foreach($tableReferences as $reference)
            {
                $data = $this->{$reference[ "name" ]};
                                
                if(!empty($data) && !$data->Empty())
                {
                    if($reference["type"] == "1-m")
                    {
                        $childReferenceName = \OpenAvanti\StringFunctions::toSingular($reference["name"]);
                        
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
         * Returns the data currently stored in the CRUD object as a JSON (JavaScript object
         * notation) string. If bIncludeReferences is true, then each reference to the table is
         * considered and added to the XML document.
         *
         * @param bool $includeReferences Optional; Toggles whether references/relationships should
         *      be stored in the JSON string. Default: false
         * @return string A JSON string representing the CRUD object
         */
        public function asJson($includeReferences = false)
        {
            $json = new JSONObject();
            
            $columns = $this->_database->getTableColumns($this->_tableIdentifier);
            
            foreach($columns as $column)
            {
                $json->addAttribute($column["field"], $this->_data[$column["field"]]);
            }
            
            if($includeReferences)
            {
                $tableReferences = $this->_database->getTableForeignKeys($this->_tableIdentifier);
                    
                foreach($tableReferences as $reference)
                {
                    $data = $this->{$reference["name"]};
                                    
                    if(!empty($data) && !$data->Empty())
                    {
                        // FIXME: ???
                        $referenceColumns = $this->_database->getTableColumns($this->_schemaName, $reference["table"]);
                            
                        if($reference["type"] == "1-m")
                        {                       
                            $references = array();
                            
                            // FIXME: ???
                            $childReferenceName = \OpenAvanti\StringFunctions::toSingular($this->_schemaName, $reference["name"]);
                            
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
         * @return string A readable, string representation of the object
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
         * @return object The generated object, either CRUD or a subclass of CRUD
         */
        private function &instantiateClass($tableName, $data = null)
        {
            // FIXME This code is not optimal. Consider the impact of multiple schemas. For 
            // instance: if the developer has two schemas to interact with and both schemas
            // have a table called users, what do they name their models? Two classes called
            // User cannot exist. The best solution would be to use namespaces, but those
            // don't exist until PHP 5.3, which is not available on all systems yet (Gentoo).
            // For now this code will persist, but users that fall under this scenario will
            // find the automatic instantiation of Model classes unpredictable and unusable.
            
            $modelName = \OpenAvanti\StringFunctions::toSingular($tableName);
            
            $pieces = explode("_", $modelName);
           
            foreach($pieces as &$piece)
                $piece = ucwords($piece);
            
            $modelName = implode("", $pieces);

            $object = null;
            
            if(class_exists($modelName, true) && is_subclass_of($modelName, "OpenAvanti\\Db\\Model"))
                $object = new $modelName($data);
            else
                $object = new CRUD($tableName, $data);
            
            return $object;
            
        } // instantiateClass()
        
    } // CRUD()

?>
