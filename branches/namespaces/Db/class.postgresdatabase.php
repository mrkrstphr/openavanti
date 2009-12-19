<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @dependencies    Database, ResultSet, StringFunctions
 * @copyright       Copyright (c) 2008, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.2.0-beta
 *
 */


    namespace OpenAvanti\Db;

    /**
     * Database Interaction Class (PostgreSQL)
     *
     * @category    Database
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/postgresdatabase
     */
    class PostgresDatabase extends Database
    {
        private $_databaseResource = null;
        
        protected static $_schemas = array();
        
        protected $_defaultSchema = "";
        
        private static $_cacheDirectory = "";
        private static $_cacheSchemas = false;
        

        /**
         * The constructor sets up a new connection to the PostgreSQL database. This method is
         * protected, and can only be called from within the class, normally through the 
         * GetConnection() method. This helps support the singleton methodology.
         * 
         * @param array The database profile array containing connection information                              
         */
        protected function __construct($profile)
        {
            $connectionString = "";
            
            if(isset($profile["host"]))
            {
                $connectionString .= " host=" . $profile["host"] . " ";
            }
      
            $connectionString .= " dbname=" . $profile["name"] . " ";
        
            if(isset($profile["user"]))
            {
                $connectionString .= " user=" . $profile["user"] . " ";
            }
            
            if(isset($profile["password"]))
            {
                $connectionString .= " password=" . $profile["password"] . " ";
            }
            
            if(isset($profile["default_schema"]) && !empty($profile["default_schema"]))
            {
                $this->_defaultSchema = trim($profile["default_schema"]);
            }
            
            $this->_databaseResource = @pg_connect($connectionString);
            
            if(!$this->_databaseResource)
            {
                throw new DatabaseConnectionException("Failed to connect to Postgres server: " . 
                    $profile["host"] . "." . $profile["name"]);
            }
            
        } // __construct()
        

        /**
         * Queries the PostgreSQL database using the supplied SQL query.
         * 
         * @param string The PostgreSQL query to execute
         * @return string A ResultSet object containing the results of the database query                   
         */
        public function &query($sql)
        {
            $resultResource = @pg_query($this->_databaseResource, $sql);
            
            if(!$resultResource)
            {
                return null;
            }

            $resultSet = new ResultSet($this, $resultResource);
            
            return $resultSet;
        
        } // query()
        
        
        /**
         * Pulls the next record from specified database resource and returns it as an object.
         *              
         * @param resource The database connection resource to pull the next record from
         * @return object The next record from the database, or null if there are no more records
         */      
        public function pullNextResult(&$resultResource)
        {
            if(!is_null($resultResource))
            {                
                return pg_fetch_object($resultResource);
            }
            else
            {
                return null;
            }
            
        } // pullNextResult()
        
        
        /**
         * Returns the number of results from the last query performed on the specified database
         * resource object.      
         *              
         * @param resource The database connection resource
         * @return int The number of rows in the specified database resource
         */ 
        public function countFromResult(&$resultResource)
        {
            if($resultResource)
            {
                return pg_num_rows($resultResource);
            }
            else
            {
                return 0;
            }
            
        } // countFromResult()
        
        
        /**
         * Attempts to return the internal pointer of the specified database resource to the
         * first row. 
         * 
         * @param resource The database connection resource to pull the next record from
         * @return bool True if the operation was successful, false otherwise                                   
         */
        public function resetResult(&$resultResource)
        {
            return @pg_result_seek($resultResource, 0);
        
        } // resetResult()
        

        /**
         * The Begin() method begins a database transaction which persists until either Commit() or 
         * Rollback() is called, or the request ends. If Commit() is not called before the end of the 
         * request, the database transaction will automatically roll back.
         * 
         * @return void                 
         */
        public function begin()
        {
            $resultResource = @pg_query($this->_databaseResource, "BEGIN") or
                trigger_error("Failed to begin transaction", E_USER_ERROR);

            return $resultResource ? true : false;

        } // begin()
        

        /**
         * The Commit() method commits a database transaction (assuming one was started with 
         * Begin()). If Commit() is not called before the end of the request, the database 
         * transaction will automatically roll back.
         * 
         * @return void         
         */
        public function commit()
        {
            $resultResource = @pg_query($this->_databaseResource, "COMMIT") or
                trigger_error("Failed to commit transaction", E_USER_ERROR);
        
            return $resultResource ? true : false;
        
        } // commit()
        

        /**
         * The Rollback() method rolls back a database transaction (assuming one was started with 
         * Begin()). The database transaction is automatically rolled back if Commit() is not called.
         *       
         * @return void         
         */
        public function rollback()
        {
            $resultResource = @pg_query($this->_databaseResource, "ROLLBACK") or
                trigger_error("Failed to rollback transaction", E_USER_ERROR);
        
            return $resultResource ? true : false;
        
        } // rollback()
        

        /**
         * Advances the value of the supplied sequence and returns the new value.
         * 
         * @param string The name of the database sequence to advance and get the current value of
         * @return integer An integer representation of the next value of the sequence
         */
        public function nextVal($sequenceName)
        {
            // TODO: Schema Support
            $sql = "SELECT
                NEXTVAL('{$sequenceName}')
            AS
                next_val";
            
            $resultResource = @pg_query($this->_databaseResource, $sql) or
                trigger_error("Failed to query sequence value: " . $this->getLastError(), 
                    E_USER_ERROR);
            
            $record = pg_fetch_object($resultResource);
        
            if($record)
            {
                return $record->next_val;
            }
        
            return null;
        
        } // nextVal()
        

        /**
         * Gets the current value of the specified sequence.
         * 
         * This method does not alter the current value of the sequence.
         * 
         * This method will only work if the value of the sequence has already been altered during 
         * the current database transaction; meaning that you must call NextVal() or SerialNextVal() 
         * prior to using this method.
         *  
         * @param string The name of the database sequence to get the current value of
         * @return integer An integer representation of the current value of the sequence.
         */
        public function currVal($sequenceName)
        {
            // TODO: Schema Support
            $sql = "SELECT
                CURRVAL('{$sequenceName}')
            AS
                current_value";
            
            $resultResource = @pg_query($this->_databaseResource, $sql) or
                trigger_error("Failed to query sequence value: " . $this->getLastError(), 
                        E_USER_ERROR);
                
            $record = pg_fetch_object($resultResource);
            
            if($record)
            {
                return $record->current_value;
            }
            
            return null;
        
        } // currVal()
        

        /**
         * Gets the current value of the specified sequence by the name of the table and the name of 
         * the database column. This will only work if a sequence is defined as the default value of 
         * a table column.
         * 
         * This method does not alter the current value of the sequence.
         * 
         * This method will only work if the value of the sequence has already been altered during 
         * the current database transaction; meaning that you must call NextVal() or SerialNextVal() 
         * prior to using this method.
         * 
         * @param string The name of the database table that holds the column with the sequence as 
         *       a default value
         * @param string The name of the database table column with the sequence as a default value
         * @return integer An integer representation of the current value of the sequence
         */
        public function serialCurrVal($tableName, $columnName)
        {
            // TODO: Schema Support
            $sql = "SELECT
                CURRVAL(
                    PG_GET_SERIAL_SEQUENCE(
                        '{$tableName}', 
                        '{$columnName}'
                    )
                )
            AS
                current_value";
            
            $resultResource = @pg_query($this->_databaseResource, $sql) or
                trigger_error("Failed to query sequence value: " . $this->getLastError(), 
                E_USER_ERROR);
                
            $record = pg_fetch_object($resultResource);
            
            if( $record )
            {
                return $record->current_value;
            }
            
            return null;
        
        } // serialCurrVal()
        

        /**
         * Advances the value of the supplied sequence and returns the new value by the name of the 
         * table and the name of the column. This will only work if a sequence is defined as the 
         * default value of a table column.
         * 
         * @param string The name of the database table that holds the column with the sequence as 
         *       a default value
         * @param string The name of the database table column with the sequence as a default value
         * @return integer An integer representation of the next value of the sequence                  
         */
        public function serialNextVal($tableName, $columnName)
        {
            // TODO: Schema Support
            $sql = "SELECT
                NEXTVAL(
                    PG_GET_SERIAL_SEQUENCE(
                        '{$tableName}', 
                        '{$columnName}'
                    )
                )
            AS
                next_value";
            
          $resultResource = @pg_query($this->_databaseResource, $sql) or
             trigger_error("Failed to query sequence value: " . $this->getLastError(), 
                    E_USER_ERROR);
                
            $record = pg_fetch_object($resultResource);
            
            if($record)
            {
                return $record->next_value;
            }
            
            return null;
        
        } // serialNextVal()
        

        /**
         * Returns the last PostgreSQL database error, if any.
         * 
         * @return string A string representation of the last PostgreSQL error              
         */
        public function getLastError()
        {
            return pg_last_error();
        
        } // getLastError()
        
        
        /**
         * 
         * 
         */
        public function getDefaultSchema()
        {
            return $this->_defaultSchema;
            
        } // getDefaultSchema()


        /**
         * The SetCacheDirectory() method stores which directory should be used to load and store 
         * database schema cache files. If the directory does not exist, an exception will be thrown.
         * 
         * Setting the cache directory is useless unless schema caching is turned on using 
         * CacheSchemas().
         * 
         * Schema caching is primarily used by the CRUD object, which analyzes database schemas to 
         * automate database operations. 
         * 
         * @param The absolute path to the directory in the system to store and read cached 
         *       database schema files
         * @return void                         
         */
        public function setCacheDirectory($directoryName)
        {
            self::$_cacheDirectory = $directoryName;
        
        } // setCacheDirectory()
        

        /**
         * The CacheSchemas() method toggles whether or not database schemas discovered through the 
         * GetSchema(), GetTableColumns(), GetTableForeignKeys() and GetTablePrimaryKey() methods 
         * should be cached, and also whether or not those methods will pull their information from a 
         * cache, if available.
         * 
         * Attempting to cache schemas without properly setting the cache directory using 
         * SetCacheDirectory(). If caching is attempted without setting the directory, an exception 
         * will be thrown.
         * 
         * Schema caching is primarily used by the CRUD object, which analyzes database schemas to 
         * automate database operations. 
         * 
         * @param boolean Toggles whether or not to cache discovered database schemas
         * @return void         
         */
        public function cacheSchemas($enabled)
        {
            self::$_cacheSchemas = $enabled;

        } // cacheSchemas()
        

        /**
         * Returns the native PHP database resource
         * 
         * @return resource The native PHP database resource                
         */
        public function &getResource()
        {
            return $this->_databaseResource;
        
        } // getResource()
        

        /**
         * Returns a database-safe formatted representation of the supplied data, based on the 
         * supplied data type.
         * 
         * 1. If the supplied data is empty and does not equal zero, this method returns NULL.
         * 2. If the data type is of text, varchar, timestamp, or bool, this method returns that 
         *       value surrounded in single quotes.
         * 
         * @param string The data type of the supplied value
         * @param string The value to be formatted into a database-safe representation
         * @return string A string of the formatted value supplied                          
         */
        public function formatData($dataType, $value)
        {
            $quotedTypes = array("/text/", "/character varying/", "/date/", 
                "/timestamp/", "/time without time zone/");
            
            if(strlen($value) == 0)
            {
                return "NULL";
            }
            
            if(preg_replace($quotedTypes, "", $dataType) != $dataType)
            {
                return "'" . addslashes($value) . "'";
            }
            else if(strpos($dataType, "bool") !== false)
            {
                if($value === true || strtolower($value) == "true" || 
                    strtolower($value) == "t")
                {
                    return "true";
                }
                else
                {
                    return "false";
                }
            }
            
            return $value;
        
        } // formatData()


        /**
         * This method returns all databases on the database server. 
         *       
         * @return array An array of all databases on the database server in the formation of 
         *       database_name => database_name
         */      
        public function getDatabases()
        {
            $sql = "SELECT
                datname
            FROM
                pg_database
            ORDER BY
                datname";
                
            if(!($databasesObj = $this->query($sql)))
            {
                throw new QueryFailedException($this->getLastError());
            }
            
            $databases = array();
            
            foreach($databasesObj as $database)
            {
                $databases[$database->datname] = $database->datname;
            }
            
            return $databases;
        
        } // getDatabases()
        
        
        /**
         * This method returns all tables for the database the class is currently connected to.
         * 
         * @param string Optional; The name of the schema to pull tables for
         * @return array Returns an array of all tables in the form of table_name => table_name.
         */ 
        public function getTables($schemaName = null)
        {
            $tables = array();

            $sql = "SELECT 
                pt.tablename, 
                pp.typrelid 
            FROM 
                pg_tables AS pt 
            INNER JOIN 
                pg_type AS pp ON pp.typname = pt.tablename 
            WHERE
                pt.tablename NOT LIKE 'pg_%' 
            AND
                pt.tablename NOT LIKE 'sql_%'";
                
            if(!empty($schemaName))
            {
                $sql .= " AND pt.schemaname = '" . addslashes($schemaName) . "'";
            }
            
            if(!($tablesObj = $this->query($sql)))
            {
                throw new QueryFailedException($this->getLastError());
            }

            foreach($tablesObj as $table) 
            {
                $tables[ $table->typrelid ] = $table->tablename;
            }

            return $tables;
        
        } // getTables()
        

        /**
         * Collects information about the schema for the specified table, including information on 
         * columns (name, datatype), primary keys and foreign keys (relationships to other tables).
         * 
         * This method stores its information the static variable $aSchemas so that if the data is 
         * required again, the database does not have to be consoluted.
         * 
         * If schema caching is on, this method can pull data from a schema cache. 
         * 
         * @param string The name of the schema that contains the table
         * @param string The name of the table for the requested schema
         * @return array An array of schema information for the specified table     
         */  
        public function getTableDefinition($schemaName, $tableName)
        {
            // If no schemaName is specified, we assume the schema is public. Since we're
            // attempting to get the columns of a specific table, if no schemaName is
            // specified, we assume the schema is public. Otherwise, if two tables with the
            // same name exist in two different schemas, it's almost random which one this
            // method will return. So let's dictate that it returns the one in the public
            // schema (assuming their is one). It is not possible to have tables in
            // PostgreSQL without them being in some schema.
            
            if(empty($schemaName))
            {
                $schemaName = 'public';
            }
            
            $tableIdentifier = $schemaName . "_". $tableName;
            
            $cacheFile = self::$_cacheDirectory . "/" . md5($tableIdentifier);
            
            if(self::$_cacheSchemas && !isset(self::$_schemas[$tableIdentifier]) && Cache::Exists($cacheFile))
            {
                $cache = new Cache($cacheFile);
                self::$_schemas[$tableIdentifier] = unserialize($cache);    
            }
            else
            {            
                $this->getTableColumns($schemaName, $tableName);
                $this->getTablePrimaryKey($schemaName, $tableName);
                $this->getTableForeignKeys($schemaName, $tableName);
            
                if(self::$_cacheSchemas)
                {
                    $cache = new Cache();
                    $cache->save($cacheFile, serialize(self::$_schemas[$tableIdentifier]), true);
                }
            }
            
            return self::$_schemas[$tableIdentifier];
        
        } // getTableDefinition()
        

        /**
         * Returns an array of columns that belong to the specified table.
         * 
         * This method stores its information the static variable $aSchemas so that if the data is 
         * required again, the database does not have to be consoluted.
         * 
         * If schema caching is on, this method can pull data from a schema cache. 
         *
         * @param string The name of the schema that contains the table
         * @param string The name of the table for the requested columns
         * @return array An array of columns that belong to the specified table
         */
        public function getTableColumns($schemaName, $tableName)
        {
            // If no schemaName is specified, we assume the schema is public. Since we're
            // attempting to get the columns of a specific table, if no schemaName is
            // specified, we assume the schema is public. Otherwise, if two tables with the
            // same name exist in two different schemas, it's almost random which one this
            // method will return. So let's dictate that it returns the one in the public
            // schema (assuming their is one). It is not possible to have tables in
            // PostgreSQL without them being in some schema.
            
            if(empty($schemaName))
            {
                $schemaName = 'public';
            }
            
            $tableIdentifier = $schemaName . "_". $tableName;
            
            if(isset(self::$_schemas[$tableIdentifier]["columns"]))
            {
                return self::$_schemas[$tableIdentifier]["columns"];
            }
            
            $columnsReturn = array();

            $sql = "SELECT 
                pa.attname, 
                pa.attnum,
                pat.typname,
                pa.atttypmod,
                pa.attnotnull,
                pg_get_expr( pad.adbin, pa.attrelid, true ) AS default_value,
                format_type( pa.atttypid, pa.atttypmod ) AS data_type
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
            INNER JOIN 
                pg_namespace AS pn 
            ON 
                pn.oid = pt.typnamespace
            LEFT JOIN
                pg_attrdef AS pad
            ON
                pad.adrelid = pa.attrelid
            AND
                pad.adnum = pa.attnum
            WHERE  
                pt.typname = '{$tableName}' 
            AND 
                pa.attnum > 0
            AND
                pn.nspname = '{$schemaName}'
            ORDER BY 
                pa.attnum";

            if(!($columns = $this->query($sql)))
            {
                throw new QueryFailedException($this->getLastError());
            }
            
            foreach($columns as $columnCount => $column)
            {           
                // When dropping a column with PostgreSQL, you get a lovely .pg.dropped. column
                // in the PostgreSQL catalog
                
                if(strpos($column->attname, ".pg.dropped.") !== false)
                {
                    continue;
                }
                
                $columnsReturn[$column->attname] = array(
                    "number" => $column->attnum,
                    "name" => $column->attname, 
                    "type" => $column->data_type,
                    "not-null" => $column->attnotnull == "t",
                    "default" => $column->default_value
                );
                 
                if($column->typname == "_varchar")
                {
                    $columnsReturn[$column->attname]["size"] = $column->atttypmod - 4;
                }
            }
            
            self::$_schemas[$tableIdentifier]["columns"] = $columnsReturn;
 
            return $columnsReturn;
            
        } // getTableColumns()
        

        /**
         * Returns an array of columns that belong to the primary key for the specified table.
         * 
         * This method stores its information the static variable $aSchemas so that if the data is 
         * required again, the database does not have to be consoluted.
         * 
         * If schema caching is on, this method can pull data from a schema cache. 
         * 
         * @param string The name of the schema that contains the table
         * @param string The name of the table for the requested primary key
         * @return array An array of columns that belong to the primary key for the specified table
         */
        public function getTablePrimaryKey($schemaName, $tableName)
        {
            // If no schemaName is specified, we assume the schema is public. Since we're
            // attempting to get the columns of a specific table, if no schemaName is
            // specified, we assume the schema is public. Otherwise, if two tables with the
            // same name exist in two different schemas, it's almost random which one this
            // method will return. So let's dictate that it returns the one in the public
            // schema (assuming their is one). It is not possible to have tables in
            // PostgreSQL without them being in some schema.
            
            if(empty($schemaName))
            {
                $schemaName = 'public';
            }
            
            $tableIdentifier = $schemaName . "_". $tableName;
            
            if(isset(self::$_schemas[$tableIdentifier]["primary_key"]))
            {
                return self::$_schemas[$tableIdentifier]["primary_key"];
            }
        
            $localTable = $this->getTableColumns($schemaName, $tableName);
            
            self::$_schemas[$tableIdentifier]["primary_key"] = array();
             
            $sql = "SELECT 
                pi.indkey
            FROM 
                pg_index AS pi 
            INNER JOIN
                pg_type AS pt
            INNER JOIN
                pg_namespace AS pn
            ON
                pn.oid = pt.typnamespace
            ON 
                pt.typrelid = pi.indrelid 
            WHERE 
                pt.typname = '{$tableName}'
            AND
                pn.nspname = '{$schemaName}'
            AND 
                pi.indisprimary = true";            
            
            if(!($primaryKeys = $this->query($sql)))
            {
                throw new QueryFailedException($this->getLastError());
            }

            if($primaryKeys->Count() != 0)
            {             
                $primaryKey = $primaryKeys->Current();
                
                $indedColumns = explode(" ", $primaryKey->indkey);
                
                foreach($indedColumns as $columnNumber)
                {
                    $column = $this->getColumnByNumber($schemaName, $tableName, $columnNumber);
                    
                    self::$_schemas[$tableIdentifier]["primary_key"][] = 
                        $column["name"];
                }
            }
    
            return self::$_schemas[$tableIdentifier]["primary_key"];
        
        } // getTablePrimaryKey()
        

        /**
         * Returns an array of relationships (foreign keys) for the specified table.
         * 
         * This method stores its information the static variable $aSchemas so that if the data is 
         * required again, the database does not have to be consoluted.
         * 
         * If schema caching is on, this method can pull data from a schema cache.
         * 
         * @param string The name of the schema that contains the table
         * @param string The name of the table for the requested relationships
         * @return array An array of relationships for the specified table
         */
        public function getTableForeignKeys($schemaName, $tableName)
        {
            // If no schemaName is specified, we assume the schema is public. Since we're
            // attempting to get the columns of a specific table, if no schemaName is
            // specified, we assume the schema is public. Otherwise, if two tables with the
            // same name exist in two different schemas, it's almost random which one this
            // method will return. So let's dictate that it returns the one in the public
            // schema (assuming their is one). It is not possible to have tables in
            // PostgreSQL without them being in some schema.
            
            if(empty($schemaName))
            {
                $schemaName = 'public';
            }
            
            $tableIdentifier = $schemaName . "_". $tableName;
            
            if(isset(self::$_schemas[$tableIdentifier]["foreign_key"]))
            {
                return self::$_schemas[$tableIdentifier]["foreign_key"];
            }
            
            $localTable = $this->getTableColumns($schemaName, $tableName);
            
            self::$_schemas[$tableIdentifier]["foreign_key"] = array();
        
            $sql = "SELECT
                rpn.nspname,
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
                pg_namespace AS pn
            ON
                pn.oid = pt.typnamespace
            INNER JOIN
                pg_type AS rpt
            ON
                rpt.typrelid = confrelid
            INNER JOIN
                pg_namespace AS rpn
            ON
                rpn.oid = rpt.typnamespace 
            WHERE
                pt.typname = '{$tableName}'
            AND
                pn.nspname = '{$schemaName}'
            AND
                contype = 'f'
            AND
                confrelid IS NOT NULL";

            if(!($foreignKeys = $this->query($sql)))
            {
                throw new QueryFailedException($this->getLastError());
            }
            
            $count = 0;
            
            foreach($foreignKeys as $foreignKey)
            {
                $foreignSchema = $foreignKey->nspname;
                
                $localFields = explode(",", 
                    str_replace(array("{", "}"), "", $foreignKey->conkey));
            
                $foreignFields = explode(",", 
                    str_replace(array("{", "}"), "", $foreignKey->confkey));

                $fields = $this->getTableColumns($foreignSchema, $foreignKey->typname);
                
                foreach($foreignFields as $index => $fieldNumber)
                {
                    $foreignColumn = $this->getColumnByNumber($foreignSchema, $foreignKey->typname, $fieldNumber);
                    $foreignFields[$index] = $foreignColumn["name"];
                }
                
                foreach($localFields as $index => $fieldNumber)
                {
                    $localColumn = $this->getColumnByNumber($schemaName, $tableName, $fieldNumber);
                    $localFields[$index] = $localColumn["name"];
                }
            
                // we currently do not handle references to multiple fields:

                $localField = current($localFields);

                $name = substr($localField, strlen($localField) - 3) == "_id" ? 
                    substr($localField, 0, strlen($localField) - 3) : $localField;
                
                $name = StringFunctions::toSingular($name);
                
                self::$_schemas[$tableIdentifier]["foreign_key"][$name] = array(
                    "schema" => $foreignSchema,
                    "table" => $foreignKey->typname,
                    "name" => $name,
                    "local" => $localFields,
                    "foreign" => $foreignFields,
                    "type" => "m-1",
                    "dependency" => true
                );
            
                $count++;
            }
            
            // find tables that reference us:
            
            $sql = "SELECT
                pnr.nspname,
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
                pg_namespace AS pn
            ON
                pn.oid = pt.typnamespace
            INNER JOIN
                pg_type AS ptr
            ON
                ptr.typrelid = pc.conrelid
            INNER JOIN
                pg_namespace AS pnr
            ON
                pnr.oid = ptr.typnamespace
            WHERE
                pt.typname = '{$tableName}'
            AND
                pn.nspname = '{$schemaName}'
            AND
                contype = 'f'
            AND
                confrelid IS NOT NULL";

            if(!($foreignKeys = $this->query($sql)))
            {
                throw new QueryFailedException($this->getLastError());
            }

            foreach($foreignKeys as $foreignKey)
            {
                $foreignSchema = $foreignKey->nspname;
                
                $localFields = explode(",", 
                    str_replace(array("{", "}"), "", $foreignKey->confkey));
            
                $foreignFields = explode( ",", 
                    str_replace(array("{", "}"), "", $foreignKey->conkey));
                
                $this->getTableDefinition($foreignSchema, $foreignKey->typname);
                
                $fields = $this->getTableColumns($foreignSchema, $foreignKey->typname);
                
                foreach($foreignFields as $index => $fieldNumber)
                {
                    $field = $this->getColumnByNumber($foreignSchema, $foreignKey->typname, $fieldNumber);
                    $foreignFields[$index] = $field["name"];
                }
                
                foreach($localFields as $index => $fieldNumber)
                {
                    $field = $this->getColumnByNumber($schemaName, $tableName, $fieldNumber);
                    $localFields[$index] = $field["name"];
                }

                $localField = reset($localFields);
                $foreignField = reset($foreignFields);
                
                // if foreign_table.local_field == foreign_table.primary_key AND
                // if local_table.foreign_key == local_table.primary_key THEN
                //      Relationship = 1-1
                // end
                
                $tmpForeignPrimaryKey = &self::$_schemas[$foreignKey->typname]["primary_key"];
                $tmpLocalPrimaryKey = &self::$_schemas[$tableIdentifier]["primary_key"];
                
                $foreignFieldIsPrimary = count($tmpForeignPrimaryKey) == 1 &&
                    reset($tmpForeignPrimaryKey) == $foreignField;
                $localFieldIsPrimary = count($tmpLocalPrimaryKey) &&
                    reset($tmpLocalPrimaryKey) == $localField;
                $foreignIsSingular = count($foreignFields) == 1;
                
                $type = "1-m";
                
                if($foreignFieldIsPrimary && $localFieldIsPrimary && $foreignIsSingular)
                {
                    $type = "1-1";
                }

                self::$_schemas[$tableIdentifier]["foreign_key"][$foreignKey->typname] = array(
                    "schema" => $foreignSchema,
                    "table" => $foreignKey->typname,
                    "name" => $foreignKey->typname,
                    "local" => $localFields,
                    "foreign" => $foreignFields,
                    "type" => $type,
                    "dependency" => false
                );
                
                $count++;
            }
            
            return self::$_schemas[$tableIdentifier][ "foreign_key"];
        
        } // getTableForeignKeys()
        

        /**
         * This method determines if the specified tables primary key (or a single column from
         * a compound primary key) references another table.         
         *
         * @param string The name of the schema that contains the table
         * @param string The name of the table that the key exists on
         * @param string The column that is, or is part of, the primary key for the table                 
         * @return boolean True if the primary key references another table, false otherwise                
         */
        public function isPrimaryKeyReference($schemaName, $tableName, $columnName)
        {
            $foreignKeys = $this->getTableForeignKeys($schemaName, $tableName);
                        
            foreach($foreignKeys as $foreignKey)
            {
                if($foreignKey["dependency"] && reset($foreignKey["local"]) == $columnName)
                {
                    return true;
                }
            }
            
            return false;
        
        } // isPrimaryKeyReference()
        

        /**
         * Returns the data type of the specified column in the specified table. 
         * 
         * @param string The name of the schema that contains the table
         * @param string The name of the table that the desired column belongs to 
         * @param string The name of the column that is desired to know the type of 
         * @return string The data type of the column, if one is found, or null.
         */
        public function getColumnType($schemaName, $tableName, $columnName)
        {
            $columns = $this->getTableColumns($schemaName, $tableName);
            
            foreach($columns as $column)
            {
                if($columnName == $column["name"])
                {
                    return $column[ "type" ];
                }
            }
            
            return null;
        
        } // getColumnType()
        

        /**
         * Determines whether the specified table exists in the current database.
         * 
         * This method first determines whether or not the table exists in the schemas array. If not, 
         * it attempts to find the table in the PostgreSQL catalog. 
         * 
         * @param string The name of the schema that contains the table
         * @param string The name of the table to determine existence
         * @return boolean True or false, depending on whether the table exists             
         */
        public function tableExists($schemaName, $tableName)
        {
            // If no schemaName is specified, we assume the schema is public. Since we're
            // attempting to get the columns of a specific table, if no schemaName is
            // specified, we assume the schema is public. Otherwise, if two tables with the
            // same name exist in two different schemas, it's almost random which one this
            // method will return. So let's dictate that it returns the one in the public
            // schema (assuming their is one). It is not possible to have tables in
            // PostgreSQL without them being in some schema.
            
            if(empty($schemaName))
            {
                $schemaName = 'public';
            }
            
            $tableIdentifier = $schemaName . "_". $tableName;
            
            if(isset(self::$_schemas[$tableIdentifier]))
            {
                return true;
            }
            
            $sql = "SELECT 
                1 
            FROM 
                pg_tables AS pt 
            INNER JOIN 
                pg_type AS pp
            ON
                pp.typname = pt.tablename 
            WHERE
                LOWER(tablename) = '" . strtolower(addslashes($tableName)) . "'";
                
            if(!empty($schemaName))
            {
                $sql .= " AND pt.schemaname = '" . addslashes($schemaName) . "'";
            }
                            
            if(!($resultSet = $this->query($sql)))
            {
                throw new QueryFailedException($this->getLastError());
            }
            
            return $resultSet->count() > 0;
        
        } // tableExists()
        

        /**
         * Returns the name of the column at the specified position from the specified table. 
         * This method is primarily interally as, in the PostgreSQL catalog, table references, 
         * indexes, etc, are stored by column number in the catalog tables. 
         *
         * @param string The name of the schema that the table belongs to
         * @param string The name of the table that the column belongs to 
         * @param int The column number from the table (from the PostgreSQL catalog) 
         * @return string The name of the column, if one is found, or null
         */
        protected function getColumnByNumber($schemaName, $tableName, $columnNumber)
        {
            // If no schemaName is specified, we assume the schema is public. Since we're
            // attempting to get the columns of a specific table, if no schemaName is
            // specified, we assume the schema is public. Otherwise, if two tables with the
            // same name exist in two different schemas, it's almost random which one this
            // method will return. So let's dictate that it returns the one in the public
            // schema (assuming their is one). It is not possible to have tables in
            // PostgreSQL without them being in some schema.
            
            if(empty($schemaName))
            {
                $schemaName = 'public';
            }
            
            $tableIdentifier = $schemaName . "_". $tableName;
            
            foreach(self::$_schemas[$tableIdentifier]["columns"] as $column)
            {
                if($column["number"] == $columnNumber)
                {
                    return $column;
                }
            }
        
            return null;
        
        } // getColumnByNumber()
        
        
        /**
         * Quotes a database element identifier
         *
         * @param string The element identifier to quote
         * @return string The quoted identifier
         */
        public function quoteIdentifier($identifier)
        {
            return "\"{$identifier}\"";
        
        } // quoteIdentifier()
        
        
        /**
         * Returns a database element identifier based on schema name and table
         * name. Accepts a separator, "." by default, and also, by default,
         * quotes the identifier using quoteIdentifier().
         *
         * @param string The name of the schema for the identifier (can
         *      be null)
         * @param string The name of the table for the identifier
         * @param string The separator to place between schema and table.
         *      Default: .
         * @param bool Should the identifier be quoted. Default: true
         * @return string The identifier
         */
        public function getIdentifier($schemaName, $tableName, $separator = ".", $quote = true)
        {
            $identifier = "";
            
            if(!empty($schemaName))
            {
                if($quote === true)
                {
                    $identifier = $this->quoteIdentifier($schemaName) . $separator;
                }
                else
                {
                    $identifier = $schemaName . $separator;
                }
            }
            
            if($quote === true)
            {
                $identifier .= $this->quoteIdentifier($tableName);
            }
            else
            {
                $identifier .= $tableName;
            }
            
            return $identifier;
            
        } // getIdentifier()


        /**
         * Returns the version of the database server.
         *
         * @return string The database server version reported by the database server
         */
        public function getVersion()
        {
            $versions = pg_version( $this->_databaseResource );
            
            return $versions[ "server" ];

        } // getVersion()

    } // PostgresDatabase()

?>