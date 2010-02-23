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
    public function lastInsertId($tableName = null, $primaryKey = null)
    {
        return $this->_databaseResource->lastInsertId();
    
    } // lastInsertId()


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
        }

        // get tables that reference us -- this is painful. Is there a better way?
        
        foreach($this->getTables() as $table)
        {
            if($table == $identifier)
                continue;
            
            $sql = "PRAGMA foreign_key_list(" . $this->quoteIdentifier($table) . ")";
            
            if(($keys = $this->query($sql)) === false)
                throw new \OpenAvanti\Db\QueryFailedException($this->getLastError());
            
            foreach($keys as $key)
            {
                if($key->table == $identifier)
                {
                    self::$_schemas[$identifier]["foreign_key"][$table] = array(
                        "table" => $table,
                        "name" => $table,
                        "local" => array($key->to),
                        "foreign" => array($key->from),
                        "type" => "1-m",
                        "dependency" => false
                    );
                }
            }
        }
        
        self::$_schemas[$identifier]["fks_loaded"] = true;
        
        return self::$_schemas[$identifier]["foreign_key"];
        
    } // getTableForeignKeys()
    

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
    
} // Sqlite()

?>
