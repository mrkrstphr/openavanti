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
 * Database Interaction Class (MySQL)
 *
 * @category    Database
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/mysqldatabase
 */
class MySql extends Driver
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
        $sql = "SHOW DATABASES";
            
        if(!($databasesObj = $this->query($sql)))
        {
            throw new QueryFailedException($this->getLastError());
        }
        
        $databases = array();
        
        foreach($databasesObj as $database)
        {
            $databases[$database->Database] = $database->Database;
        }
        
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

        $sql = "SHOW TABLES";
        
        if(!($tablesObj = $this->query($sql)))
        {
            throw new QueryFailedException($this->getLastError());
        }

        foreach($tablesObj as $table) 
        {
            $table = current((array)$table);
            
            $tables[$table] = $table;
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
        {
            return self::$_schemas[$identifier]["columns"];
        }

        $columnsReturn = array();

        if(!($columns = $this->query("SHOW COLUMNS FROM {$identifier}")))
        {
            throw new QueryFailedException($this->getLastError());
        }

        foreach($columns as $columnCount => $column)
        {
            $columnsReturn[$column->Field] = array(
                "number" => $columnCount,
                "name" => $column->Field,
                "type" => $column->Type,
                "not-null" => $column->Null == "NO",
                "default" => $column->Default
            );
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
        {
            return self::$_schemas[$identifier]["primary_key"];
        }
        
        $localTable = $this->getTableColumns($identifier);
        
        self::$_schemas[$identifier]["primary_key"] = array();
        
        $sql = "SHOW KEYS FROM {$identifier} WHERE Key_name = 'PRIMARY'";       
        
        if(!($primaryKeys = $this->query($sql)))
        {
            throw new QueryFailedException($this->getLastError());
        }
        
        foreach($primaryKeys as $key)
        {
            self::$_schemas[$identifier]["primary_key"][] = $key->Column_name;
        }
        
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
        if(isset(self::$_schemas[$identifier]["foreign_key"]))
            return self::$_schemas[$identifier]["foreign_key"];
        
        //
        // This method needs to be cleaned up and consolidated
        //
        
        self::$_schemas[$identifier]["foreign_key"] = array();
        
        $sql = "SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE " . 
            "WHERE CONSTRAINT_SCHEMA = '{$this->_databaseName}' " . 
            "AND REFERENCED_TABLE_NAME IS NOT NULL " . 
            "AND TABLE_NAME = '{$identifier}'";
        
        if(!($foreignKeys = $this->query($sql)))
            throw new QueryFailedException($this->getLastError());
        
        foreach($foreignKeys as $foreignKey)
        {
            // we currently do not handle references to multiple fields:
            
            $localField = $foreignKey->COLUMN_NAME;
            
            $relName = substr($localField, strlen($localField) - 3) == "_id" ? 
                substr($localField, 0, strlen($localField) - 3) : $localField;
            
            $relName = \OpenAvanti\Util\String::toSingular($relName);
            
            self::$_schemas[$identifier]["foreign_key"][$relName] = array(
                "table" => $foreignKey->REFERENCED_TABLE_NAME,
                "name" => $relName,
                "local" => array($foreignKey->COLUMN_NAME),
                "foreign" => array($foreignKey->REFERENCED_COLUMN_NAME),
                "type" => "m-1",
                "dependency" => true
            );
        }
        
        
        // find tables that reference us:
        
        $sql = "SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE " . 
            "WHERE CONSTRAINT_SCHEMA = '{$this->_databaseName}' " . 
            "AND CONSTRAINT_NAME != 'PRIMARY' AND REFERENCED_TABLE_NAME = '{$identifier}'";
        
        if(!( $foreignKeys = $this->query($sql)))
            throw new QueryFailedException($this->getLastError());

        foreach($foreignKeys as $foreignKey)
        {
            $localField = $foreignKey->COLUMN_NAME;
            $foreignField = $foreignKey->REFERENCED_COLUMN_NAME;
            
            // if foreign_table.local_field == foreign_table.primary_key AND
            // if local_table.foreign_key == local_table.primary_key THEN
            //      Relationship = 1-1
            // end
            
            $tmpForeignPrimaryKey = &self::$_schemas[$foreignKey->TABLE_NAME]["primary_key"];
            $tmpLocalPrimaryKey = &self::$_schemas[$identifier][ "primary_key" ];
            
            $foreignFieldIsPrimary = count($tmpForeignPrimaryKey) == 1 &&
                reset($tmpForeignPrimaryKey) == $foreignField;
            $localFieldIsPrimary = count($tmpLocalPrimaryKey) &&
                reset($tmpLocalPrimaryKey) == $localField;
            $foreignIsSingular = true; // count( $aForeignFields ) == 1; // TODO: FIX
            
            $type = "1-m";
            
            if($foreignFieldIsPrimary && $localFieldIsPrimary && $foreignIsSingular)
            {
                $type = "1-1";
            }
            
            self::$_schemas[$identifier]["foreign_key"][$foreignKey->TABLE_NAME] = array(
                "table" => $foreignKey->TABLE_NAME,
                "name" => $foreignKey->TABLE_NAME,
                "local" => array($foreignKey->REFERENCED_COLUMN_NAME),
                "foreign" => array($foreignKey->COLUMN_NAME),
                "type" => $type,
                "dependency" => false
            );
        }
        
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
            $identifier = $this->quoteIdentifier($identifier);
        
        return $identifier;
        
    } // getIdentifier()
    
} // MySql()

?>
