<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @copyright       Copyright (c) 2007-2010, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         SVN: $Id$
 */


namespace OpenAvanti\Db;

use \Exception;


/**
 * Database interaction abstract class definition
 *
 * @category    Database
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/database
 */
abstract class Driver
{
    /** 
     * Class constants for query join types (used for CRUD operations)
     */                          
    const JoinTypeInner = "inner";
    const JoinTypeLeft = "left";
    
    /**
     * Defines the syntax for the join type constants
     */                 
    public static $_joinTypes = array( 
        self::JoinTypeInner => "INNER JOIN",
        self::JoinTypeLeft => "LEFT JOIN"
    );
    
    protected static $_schemas = array();
    
    protected static $_cacheDirectory = "";
    protected static $_cacheSchemas = false;
   
    
    protected $_dsnPrefix = "";
    protected $_databaseResource = null;

    protected $_databaseName = null;


    /**
     * The constructor sets up a new connection to the database. This method is
     * protected, and can only be called from within the class, normally through the
     * GetConnection() method. This helps support the singleton methodology.
     *
     * @param array $profile The database profile array containing connection information
     */
    public function __construct($profile)
    {
        // TODO This is PostgreSQL only stuff
        if(isset($profile["default_schema"]) && !empty($profile["default_schema"]))
            $this->_defaultSchema = trim($profile["default_schema"]);
        
        $this->_databaseResource = new \PDO($profile["dsn"], $profile["user"], $profile["password"]);
        
        list($dsnIdentifier, $information) = explode(":", $profile["dsn"], 2);
                
        $parts = explode(";", $information);
        
        foreach($parts as $part)
        {
            $nv = explode("=", $part, 2);
            
            if(count($nv) == 2 && $nv[0] == "dbname")
                $this->_databaseName = $nv[1];
        }
        
        if(!$this->_databaseResource)
            throw new DatabaseConnectionException("Failed to connect to database server: " .
                $profile["driver"] . ":" . $profile["host"] . "." . $profile["name"]);

    } // __construct()
    

    /**
     * Queries the database using the supplied SQL query.
     *
     * @param string $sql The query to execute
     * @return string|false A ResultSet object containing the results of the database query or
     *      false on failure
     */
    public function &query($sql, Array $params = array(), $selMode = \PDO::FETCH_OBJ)
    {
        $statement = $this->_databaseResource->prepare($sql);
        
        if(!$statement)
            return $statement;
        
        $statement->execute($params);

        $statement->setFetchMode($selMode);
        
        $results = $statement->fetchAll();
        
        $resultSet = new \OpenAvanti\Db\ResultSet($results);
        
        return $resultSet;
        
    } // query()

    
    /**
     * The Begin() method begins a database transaction which persists until either Commit() or
     * Rollback() is called, or the request ends. If Commit() is not called before the end of the
     * request, the database transaction will automatically roll back.
     *
     * @return boolean True if the operation was successful, false otherwise
     */
    public function begin()
    {
        return $this->_databaseResource->beginTransaction();
        
    } // begin()


    /**
     * The Commit() method commits a database transaction (assuming one was started with
     * Begin()). If Commit() is not called before the end of the request, the database
     * transaction will automatically roll back.
     *
     * @return boolean True if the operation was successful, false otherwise
     */
    public function commit()
    {
        return $this->_databaseResource->commit();

    } // commit()


    /**
     * The Rollback() method rolls back a database transaction (assuming one was started with
     * Begin()). The database transaction is automatically rolled back if Commit() is not called.
     *
     * @return boolean True if the operation was successful, false otherwise
     */
    public function rollback()
    {
        return $this->_databaseResource->rollback();
        
    } // rollback()


    /**
     * Returns the last database error, if any.
     *
     * @return string A string representation of the last error
     */
    public function getLastError()
    {
        return implode(": ", $this->_databaseResource->errorInfo());

    } // getLastError()
    
    
    /**
     * Returns the ID from the last insert operation, either by sequence value or through
     * the last insert id of an auto_increment column depending on the database driver.
     * The parameters will be ignored for systems using auto_increment columns.
     *
     * @param string $tableName Optional; The name of the database table that the record was
     *      inserted into
     * @param string $columnName Optional; The name of the table column being inserted into
     * @return int The ID of the last record inserted
     */
    abstract public function lastInsertId($tableName = null, $columnName = null);


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
     * @param string $directoryName The absolute path to the directory in the system to store and
     *      read cached database schema files                      
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
     * @param boolean $enabled Toggles whether or not to cache discovered database schemas       
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
     * This method returns all tables for the database the class is currently connected to.
     * 
     * @return array Returns an array of all tables in the form of table_name => table_name.
     */ 
    abstract public function getTables();
    
    
    /**
     * This method returns all databases on the database server. 
     *       
     * @return array An array of all databases on the database server in the formation of 
     *       database_name => database_name
     */ 
    abstract public function getDatabases();
    

    /**
     * Collects information about the schema for the specified table, including information on 
     * columns (name, datatype), primary keys and foreign keys (relationships to other tables).
     * 
     * This method stores its information the static variable $aSchemas so that if the data is 
     * required again, the database does not have to be consoluted.
     * 
     * If schema caching is on, this method can pull data from a schema cache. 
     * 
     * @param string $identifier The identifier for the table
     * @return array An array of schema information for the specified table     
     */     
    abstract public function getTableDefinition($identifier);
    
    
    /**
     * Returns an array of columns that belong to the specified table.
     * 
     * This method stores its information the static variable $aSchemas so that if the data is 
     * required again, the database does not have to be consoluted.
     * 
     * If schema caching is on, this method can pull data from a schema cache. 
     *
     * @param string $identifier The identifier for the table
     * @return array An array of columns that belong to the specified table
     */
    abstract public function getTableColumns($identifier);

    
    /**
     * Returns an array of columns that belong to the primary key for the specified table.
     * 
     * This method stores its information the static variable $aSchemas so that if the data is 
     * required again, the database does not have to be consoluted.
     * 
     * If schema caching is on, this method can pull data from a schema cache. 
     * 
     * @param string $identifier The identifier for the table
     * @return array An array of columns that belong to the primary key for the specified table
     */
    abstract public function getTablePrimaryKey($identifier);
    

    /**
     * Returns an array of relationships (foreign keys) for the specified table.
     * 
     * This method stores its information the static variable $aSchemas so that if the data is 
     * required again, the database does not have to be consoluted.
     * 
     * If schema caching is on, this method can pull data from a schema cache.
     * 
     * @param string $identifier The identifier for the table
     * @return array An array of relationships for the specified table
     */
    abstract public function getTableForeignKeys($identifier);
    
    
    /**
     * This method determines if the specified tables primary key (or a single column from
     * a compound primary key) references another table.         
     *
     * @param string $identifier The identifier for the table
     * @param string $columnName The column that is, or is part of, the primary key for the table                 
     * @return boolean True if the primary key references another table, false otherwise                
     */
    public function isPrimaryKeyReference($identifier, $columnName)
    {
        $foreignKeys = $this->getTableForeignKeys($identifier);
        
        foreach($foreignKeys as $foreignKey)
            if($foreignKey["dependency"] && reset($foreignKey["local"]) == $columnName)
                return true;
        
        return false;
        
    } // isPrimaryKeyReference()
    
    
    /**
     * Returns the data type of the specified column in the specified table. 
     * 
     * @param string $identifier The identifier for the table
     * @param string $columName The name of the column that is desired to know the type of 
     * @return string The data type of the column, if one is found, or null.
     */
    public function getColumnType($identifier, $columnName)
    {
        $columns = $this->getTableColumns($identifier);
        
        foreach($columns as $column)
            if($columnName == $column["name"])
                return $column[ "type" ];
        
        return null;
    
    } // getColumnType()
    

    /**
     * Determines whether the specified table exists in the current database.
     * 
     * @param string $identifier The identifier for the table
     * @return bool True or false, depending on whether the table exists.                   
     */     
    abstract public function tableExists($identifier);
    

    /**
     * Returns the name of the column at the specified position from the specified table. 
     * This method is primarily interally as, in the PostgreSQL catalog, table references, 
     * indexes, etc, are stored by column number in the catalog tables. 
     *
     * @param string $identifier The identifier for the table
     * @param int $columnNumber The column number from the table (from the PostgreSQL catalog) 
     * @return string The name of the column, if one is found, or null
     */
    public function getColumnByNumber($identifier, $columnNumber)
    {
        $tableIdentifier = $this->getIdentifier($identifier, "_", false);

        $this->getTableColumns($identifier);

        foreach(self::$_schemas[$tableIdentifier]["columns"] as $column)
            if($column["number"] == $columnNumber)
                return $column;
    
        return null;
    
    } // getColumnByNumber()
    
    
    /**
     * Returns the version of the database server.
     *
     * @return string The database server version reported by the database server
     */
    public function getVersion()
    {
        return $this->_databaseResource->getAttribute(constant("PDO::ATTR_SERVER_VERSION"));
        
    } // getVersion()

} // Driver()

?>
