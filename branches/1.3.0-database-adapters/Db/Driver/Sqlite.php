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


namespace OpenAvanti\Db\Driver;

use OpenAvanti\Db\Driver;

/**
 * Database Interaction Class (sqlite)
 *
 * @category    Database
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/postgresdatabase
 */
class Sqlite extends Driver
{
    private static $_cacheDirectory = "";
    private static $_cacheSchemas = false;
   
    protected static $_schemas = array();

    protected $_dsnPrefix = "sqlite";
    
    
    /**
     *
     *
     */
    public function lastInsertId($tableName = null, $primaryKey = null)
    {
        return $this->_databaseResource->lastInsertId();
    }
    
    
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
     * @returns void                         
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
     * @returns void         
     */
    public function cacheSchemas($enabled)
    {
        self::$_cacheSchemas = $enabled;

    } // cacheSchemas()
    

    /**
     * Returns the native PHP database resource
     * 
     * @returns resource The native PHP database resource                
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
     * @returns string A string of the formatted value supplied                          
     */
    public function FormatData( $sType, $sValue )
    {
        // TODO this is for PgSql - update for Sqlite
        if( $sType == "tinyint(1)" && in_array( strtolower( $sValue ),
            array( "true", "t" ) ) )
        {
            return( 1 );
        }
        else if( $sType == "tinyint(1)" && in_array( strtolower( $sValue ),
            array( "false", "f" ) ) )
        {
            return( 0 );
        }

        $aQuoted_Types = array( "/text/", "/tinytext/", "/mediumtext/", "/longtext/",
            "/char/", "/varchar/", "/date/", "/timestamp/", "/datetime/", "/time/",
            "/binary/", "/varbinary/",
            "/blob/", "/tinyblob/", "/mediumblob/", "/longblob/" );

       if( strlen( $sValue ) == 0 )
       {
           return( "NULL" );
       }

       if( preg_replace( $aQuoted_Types, "", $sType ) != $sType )
       {
           return( "'" . addslashes( $sValue ) . "'" );
       }

       return( $sValue );

    } // FormatData()


    /**
     * This method returns all databases on the database server. 
     *       
     * @returns array An array of all databases on the database server in the formation of 
     *       database_name => database_name
     */      
    public function getDatabases()
    {
        $sql = "PRAGMA database_list";
            
        if(!($databasesObj = $this->query($sql)))
            throw new QueryFailedException($this->getLastError());
        
        $databases = array();
        
        foreach($databasesObj as $database)
            $databases[$database->name] = $database->name;
        
        return $databases;
        
    } // getDatabases()
    

    /**
     * This method returns all tables for the database the class is currently connected to.
     * 
     * @param string Optional; The name of the schema to pull tables for
     * @returns array Returns an array of all tables in the form of table_name => table_name.
     */ 
    public function getTables()
    {
        $tables = array();
        
        $sql = "SELECT name FROM sqlite_master WHERE type='table'" . 
            "UNION ALL SELECT name FROM sqlite_temp_master WHERE type='table' ORDER BY name";
        
        if(!($tablesObj = $this->query($sql)))
        {
            throw new \OpenAvanti\Db\QueryFailedException($this->getLastError());
        }

        foreach($tablesObj as $table) 
        {
            $tables[$table->name] = $table->name;
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
     * @param string The identifier for the table
     * @returns array An array of schema information for the specified table     
     */  
    public function getTableDefinition($identifier)
    {
        $cacheFile = self::$_cacheDirectory . "/" . md5($identifier);
        
        if(self::$_cacheSchemas && !isset(self::$_schemas[$identifier]) && Cache::Exists($cacheFile))
        {
            $cache = new Cache($cacheFile);
            self::$_schemas[$identifier] = unserialize($cache);    
        }
        else
        {            
            $this->getTableColumns($identifier);
            $this->getTablePrimaryKey($identifier);
            $this->getTableForeignKeys($identifier);
            
            if(self::$_cacheSchemas)
            {
                $cache = new Cache();
                $cache->save($cacheFile, serialize(self::$_schemas[$identifier]), true);
            }
        }
        
        return self::$_schemas[$identifier];
        
    } // getTableDefinition()
    
    
    /**
     * Returns an array of columns that belong to the specified table.
     * 
     * This method stores its information the static variable $aSchemas so that if the data is 
     * required again, the database does not have to be consoluted.
     * 
     * If schema caching is on, this method can pull data from a schema cache. 
     *
     * @param string The identifier for the table
     * @returns array An array of columns that belong to the specified table
     */
    public function getTableColumns($identifier)
    {
        if(isset(self::$_schemas[$identifier]["columns"]))
            return self::$_schemas[$identifier]["columns"];
        
        $columnsReturn = array();
        
        if(!($columns = $this->query("PRAGMA table_info('{$identifier}')")))
            throw new \OpenAvanti\Db\QueryFailedException($this->getLastError());
        
        foreach($columns as $columnCount => $column)
        {
            $columnsReturn[$column->name] = array(
                "number" => $columnCount + 1,
                "name" => $column->name,
                "type" => $column->type,
                "not-null" => $column->notnull == 1,
                "default" => $column->dflt_value
            );
            
            if(substr($column->type, 0, 7) == "varchar")
            {
                $columnsReturn[$column->name]["size"] =
                    preg_replace("/varchar\((.*)\)/i", "$1", $column->type);
            }
        }
        
        self::$_schemas[$identifier]["columns"] = $columnsReturn;
        
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
     * @param string The identifier for the table
     * @returns array An array of columns that belong to the primary key for the specified table
     */
    public function getTablePrimaryKey($identifier)
    {
        if(isset(self::$_schemas[$identifier]["primary_key"]))
            return self::$_schemas[$identifier]["primary_key"];

        if(!($columns = $this->query("PRAGMA table_info('{$identifier}')")))
            throw new \OpenAvanti\Db\QueryFailedException($this->getLastError());
        
        foreach($columns as $columnCount => $column)
            if($column->pk == 1)
                self::$_schemas[$identifier]["primary_key"][] = $column->name;
        
        return self::$_schemas[$identifier]["primary_key"];
        
    } // getTablePrimaryKey()
    
    
    /**
     * Returns an array of relationships (foreign keys) for the specified table.
     * 
     * This method stores its information the static variable $aSchemas so that if the data is 
     * required again, the database does not have to be consoluted.
     * 
     * If schema caching is on, this method can pull data from a schema cache.
     * 
     * @param string The identifier for the table
     * @returns array An array of relationships for the specified table
     */
    public function getTableForeignKeys($identifier)
    {
        if(isset(self::$_schemas[$identifier]["fks_loaded"]) &&
            self::$_schemas[$identifier]["fks_loaded"] === true)
        {
            return self::$_schemas[$identifier]["foreign_key"];
        }
        
        if(!isset(self::$_schemas[$identifier]["foreign_key"]))
            self::$_schemas[$identifier]["foreign_key"] = array();
        
        $sql = "PRAGMA foreign_key_list(" . $this->quoteIdentifier($identifier) . ")";
        
        if(($keys = $this->query($sql)) === false)
            throw new \OpenAvanti\Db\QueryFailedException($this->getLastError());
            
        foreach($keys as $key)
        {
            $name = substr($key->from, strlen($key->from) - 3) == "_id" ? 
                substr($key->from, 0, strlen($key->from) - 3) : $key->from;
                
            self::$_schemas[$identifier]["foreign_key"][$name] = array(
                "table" => $key->table,
                "name" => $name,
                "local" => array($key->from),
                "foreign" => array($key->to),
                "type" => "m-1",
                "dependency" => true
            );
            
            self::$_schemas[$key->table]["foreign_key"][$name] = array(
                "table" => $identifier,
                "name" => $identifier,
                "local" => array($key->to),
                "foreign" => array($key->from),
                "type" => "1-m",
                "dependency" => false
            );
        }
        
        self::$_schemas[$identifier]["fks_loaded"] = true;
        
        return self::$_schemas[$identifier]["foreign_key"];
        
    } // getTableForeignKeys()
    
    
    /**
     * This method determines if the specified tables primary key (or a single column from
     * a compound primary key) references another table.         
     *
     * @param string The identifier for the table
     * @param string The column that is, or is part of, the primary key for the table                 
     * @returns boolean True if the primary key references another table, false otherwise                
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
     * @param string The identifier for the table
     * @param string The name of the column that is desired to know the type of 
     * @returns string The data type of the column, if one is found, or null.
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
     * This method first determines whether or not the table exists in the schemas array. If not, 
     * it attempts to find the table in the PostgreSQL catalog. 
     * 
     * @param string The identifier for the table
     * @returns boolean True or false, depending on whether the table exists             
     */
    public function tableExists($identifier)
    {
        $tables = $this->getTables();
        
        foreach($tables as $table)
            if($table == $identifier)
                return true;
            
        return false;
        
    } // tableExists()
    

    /**
     * Returns the name of the column at the specified position from the specified table. 
     * This method is primarily interally as, in the PostgreSQL catalog, table references, 
     * indexes, etc, are stored by column number in the catalog tables. 
     *
     * @param string The identifier for the table
     * @param int The column number from the table (from the PostgreSQL catalog) 
     * @returns string The name of the column, if one is found, or null
     */
    public function getColumnByNumber($identifier, $columnNumber)
    {
        $this->getTableColumns($identifier);
        
        foreach(self::$_schemas[$identifier]["columns"] as $column)
            if($column["number"] == $columnNumber)
                return $column;
        
        return null;
        
    } // getColumnByNumber()
    
    
    /**
     * Quotes a database element identifier
     *
     * @param string The element identifier to quote
     * @returns string The quoted identifier
     */
    public function quoteIdentifier($identifier)
    {
        return "`{$identifier}`";
    
    } // quoteIdentifier()
    
    
    /**
     * Returns a database element identifier based on schema name and table
     * name. Accepts a separator, "." by default, and also, by default,
     * quotes the identifier using quoteIdentifier().
     *
     * @param string The identifier for the table
     * @param string The separator to place between schema and table.
     *      Default: .
     * @param bool Should the identifier be quoted. Default: true
     * @returns string The identifier
     */
    public function getIdentifier($identifier, $separator = ".", $quote = true)
    {
        if($quote === true)
        {
            $identifier = $this->quoteIdentifier($identifier);
        }
        
        return $identifier;
        
    } // getIdentifier()
    
    
    /**
     * Returns the version of the database server.
     *
     * @returns string The database server version reported by the database server
     */
    public function getVersion()
    {
        return $this->_databaseResource->getAttribute(constant("PDO::ATTR_SERVER_VERSION"));
        
    } // getVersion()
    
} // Sqlite()

?>
