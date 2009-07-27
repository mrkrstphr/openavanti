<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @dependencies    Database
 * @copyright       Copyright (c) 2007-2009, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.0
 *
 */


    /**
     * Database Interaction Class (MySQL)
     *
     * @category    Database
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/mysqldatabase
     */
    class MySQLDatabase extends Database
    {
        private $hDatabase = null;
        
        protected static $aSchemas = array();
        
        private static $sCacheDirectory = "";
        private static $bCacheSchemas = false;
        
        protected $aProfile = null;

        /*
         * The constructor sets up a new connection to the MySQL database. This method is
         * protected, and can only be called from within the class, normally through the 
         * GetConnection() method. This helps support the singleton methodology.
         * 
         * @argument array The database profile array containing connection information                              
         */
        protected function __construct( $aProfile )
        {
            $this->aProfile = $aProfile;
            
            $this->hDatabase = @mysql_connect( $aProfile[ "host" ], $aProfile[ "user" ], 
                $aProfile[ "password" ] );
                        
            if( !$this->hDatabase )
            {
                throw new DatabaseConnectionException( "Failed to connect to MySQL server: " . 
                    $aProfile[ "host" ] . "." . $aProfile[ "name" ] );
            }
            
            if( !@mysql_select_db( $aProfile[ "name" ], $this->hDatabase ) )
            {
                throw new DatabaseConnectionException( "Failed to connect to MySQL database: " . 
                    $aProfile[ "host" ] . "." . $aProfile[ "name" ] );
            }
            
        } // __construct()
        

        /*
         * Queries the MySQL database using the supplied SQL query.
         * 
         * @argument string The MySQL query to execute
         * @returns string A ResultSet object containing the results of the database query                   
         */
        public function Query( $sSQL )
        {
            $rResult = @mysql_query( $sSQL, $this->hDatabase );
        
            if( !$rResult )
            {
                return( null );
            }
        
            return( new ResultSet( $this, $rResult ) );
        
        } // Query()
        
        
        public function LastInsertID()
        {
            return( @mysql_insert_id( $this->hDatabase ) );
            
        } // LastInsertID()
        
        
        public function PullNextResult( &$rResult )
        {
            if( !is_null( $rResult ) )
            {
                return( mysql_fetch_object( $rResult ) );
            }
            else
            {
                return( null );
            }
            
        } // PullNextResult()
        
        
        public function CountFromResult( &$rResult )
        {
            if( $rResult )
            {
                return( mysql_num_rows( $rResult ) );
            }
            else
            {
                return( 0 );
            }
            
        } // CountFromResult()
        
        
        public function ResetResult( &$rResult )
        {
            return( @mysql_data_seek( $rResult, 0 ) );
            
        } // ResetResult()
        

        /*
         * The Begin() method begins a database transaction which persists until either Commit() or 
         * Rollback() is called, or the request ends. If Commit() is not called before the end of the 
         * request, the database transaction will automatically roll back.
         * 
         * @returns void                 
         */
        public function Begin()
        {
            if( !$this->Query( "START TRANSACTION" ) )
            {
                throw new QueryFailedException( $this->GetLastError() );
            }
            
            return( true );

        } // Begin()
        

        /*
         * The Commit() method commits a database transaction (assuming one was started with 
         * Begin()). If Commit() is not called before the end of the request, the database 
         * transaction will automatically roll back.
         * 
         * @returns void         
         */
        public function Commit()
        {
            if( !$this->Query( "COMMIT" ) )
            {
                throw new QueryFailedException( $this->GetLastError() );
            }
            
            return( true );
            
        } // Commit()
        

        /*
         * The Rollback() method rolls back a database transaction (assuming one was started with 
         * Begin()). The database transaction is automatically rolled back if Commit() is not called.
         *       
         * @returns void         
         */
        public function Rollback()
        {
            if( !$this->Query( "ROLLBACK" ) )
            {
                throw new QueryFailedException( $this->GetLastError() );
            }
            
            return( true );
            
        } // Rollback()
        

        /*
         * Returns the last PostgreSQL database error, if any.
         * 
         * @returns string A string representation of the last PostgreSQL error              
         */
        public function GetLastError()
        //
        // Description:
        //      Returns the last database error, if any
        //
        {
            return( mysql_error( $this->hDatabase ) );
        
        } // GetLastError()
        

        /*
         * Returns the native PHP database resource
         * 
         * @returns resource The native PHP database resource                
         */
        public function GetResource()
        {
            return( $this->hDatabase );
        
        } // GetResource()
        

        /*
         * Returns a database-safe formatted representation of the supplied data, based on the 
         * supplied data type.
         * 
         * 1. If the supplied data is empty and does not equal zero, this method returns NULL.
         * 2. If the data type is of text, varchar, timestamp, or bool, this method returns that 
         *       value surrounded in single quotes. 
         * 
         * @argument string The data type of the supplied value
         * @argument string The value to be formatted into a database-safe representation
         * @returns string A string of the formatted value supplied                          
         */
        public function FormatData( $sType, $sValue )
        {
            $aQuoted_Types = array( "/text/", "/character varying/", "/date/", 
                "/timestamp/", "/bool/" );
                
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


        /*
         * This method returns all databases on the database server. 
         *       
         * @returns array An array of all databases on the database server in the formation of 
         *       database_name => database_name
         */      
        public function GetDatabases()
        {
        
        } // GetDatabases()
        
        
        /*
         * This method returns all tables for the database the class is currently connected to.
         *       
         * @returns array Returns an array of all tables in the form of table_name => table_name.
         */ 
        public function GetTables()
        {               
        
        } // GetTables()
        

        /*
         *
         */
        public function GetSchema( $sTableName )
        //
        // Description:
        //      Collects all fields/columns in the specified database table, as well as data type
        //      and key information.
        //
        {       
            $sCacheFile = self::$sCacheDirectory . "/" . md5( $sTableName );
            
            if( self::$bCacheSchemas && !isset( self::$aSchemas[ $sTableName ] ) && Cache::Exists( $sCacheFile ) )
            {
                $oCache = new Cache( $sCacheFile );
                self::$aSchemas[ $sTableName ] = unserialize( $oCache );    
            }
            else
            {
                $this->GetTableColumns( $sTableName );
                $this->GetTablePrimaryKey( $sTableName );
                $this->GetTableForeignKeys( $sTableName );
            
                if( self::$bCacheSchemas )
                {
                    $oCache = new Cache();
                    $oCache->Save( $sCacheFile, serialize( self::$aSchemas[ $sTableName ] ), true );
                }
            }
            
            return( self::$aSchemas[ $sTableName ] );
        
        } // GetSchema()
        

        /*
         *
         */
        public function GetTableColumns( $sTableName )
        {
            if( isset( self::$aSchemas[ $sTableName ][ "fields" ] ) )
            {
                return( self::$aSchemas[ $sTableName ][ "fields" ] );
            }
            
            $aFields = array();


            $oColumns = $this->Query( "SHOW COLUMNS FROM {$sTableName}" );

            foreach( $oColumns as $iCount => $oColumn )
            {
                //echo '<pre>' . print_r( $oColumn, true ) . '</pre>';
                
                $aFields[ $oColumn->Field ] = array(
                    "number" => $iCount,
                    "field" => $oColumn->Field, 
                    "type" => $oColumn->Type,
                    "not-null" => $oField->Null == "NO",
                    "default" => $oColumn->Default
                );
            }

            self::$aSchemas[ $sTableName ][ "fields" ] = $aFields;
 
            return( $aFields );
            
        } // GetTableColumns()
        

        /*
         *
         */
        public function GetTablePrimaryKey( $sTableName )
        {

            if( isset( self::$aSchemas[ $sTableName ][ "primary_key" ] ) )
            {
                return( self::$aSchemas[ $sTableName ][ "primary_key" ] );
            }
        
            $aLocalTable = $this->GetTableColumns( $sTableName );
            
            self::$aSchemas[ $sTableName ][ "primary_key" ] = array();
                    
            $sSQL = "SHOW KEYS FROM {$sTableName} WHERE Key_name = 'PRIMARY'";       
            
            if( !( $oPrimaryKeys = $this->Query( $sSQL ) ) )
            {
                throw new QueryFailedException( $this->GetLastError() );
            }

            foreach( $oPrimaryKeys as $oKey )
            {
                self::$aSchemas[ $sTableName ][ "primary_key" ][] = $oKey->Column_name;
            }
    
            return( self::$aSchemas[ $sTableName ][ "primary_key" ] );
            
        } // GetTablePrimaryKey()
        

        /*
         *
         */
        public function GetTableForeignKeys( $sTableName )
        {
            if( isset( self::$aSchemas[ $sTableName ][ "foreign_key" ] ) )
            {
                return( self::$aSchemas[ $sTableName ][ "foreign_key" ] );
            }
        
            //
            // This method needs to be cleaned up and consolidated
            //
            
            
            self::$aSchemas[ $sTableName ][ "foreign_key" ] = array();
        

            $sSQL = "SELECT 
                *
            FROM 
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE 
                CONSTRAINT_SCHEMA = '" . $this->aProfile[ "name" ] . "' 
            AND 
                CONSTRAINT_NAME != 'PRIMARY'
            AND
                TABLE_NAME = '{$sTableName}'";
//echo $sSQL . "<br /><br />";
            if( !( $oForeignKeys = $this->Query( $sSQL ) ) !== false )
            {
                throw new QueryFailedException( $this->GetLastError() );
            }
            
            foreach( $oForeignKeys as $oForeignKey )
            {
                //echo '<pre>' . print_r( $oForeignKey, true ) . '</pre>';

                // we currently do not handle references to multiple fields:

                $localField = $oForeignKey->COLUMN_NAME;

                $sName = substr( $localField, strlen( $localField ) - 3 ) == "_id" ? 
                    substr( $localField, 0, strlen( $localField ) - 3 ) : $localField;
                
                $sName = StringFunctions::ToSingular( $sName );
                
                self::$aSchemas[ $sTableName ][ "foreign_key" ][ $sName ] = array(
                    "table" => $oForeignKey->REFERENCED_TABLE_NAME,
                    "name" => $sName,
                    "local" => array( $oForeignKey->COLUMN_NAME ),
                    "foreign" => array( $oForeignKey->REFERENCED_COLUMN_NAME ),
                    "type" => "m-1",
                    "dependency" => true
                );
                
                //echo '<pre>' . print_r( self::$aSchemas[ $sTableName ][ "foreign_key" ][ $sName ], true ) . '</pre>';
            }
            
            
            // find tables that reference us:
                    
            $sSQL = "SELECT 
                *
            FROM 
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE 
                CONSTRAINT_SCHEMA = '" . $this->aProfile[ "name" ] . "' 
            AND 
                CONSTRAINT_NAME != 'PRIMARY'
            AND
                REFERENCED_TABLE_NAME = '{$sTableName}'";
//echo $sSQL . "<br /><br />";
            if( !( $oForeignKeys = $this->Query( $sSQL ) ) )
            {
                throw new QueryFailedException( $this->GetLastError() );
            }

            foreach( $oForeignKeys as $oForeignKey )
            {
                //echo '<pre>' . print_r( $oForeignKey, true ) . '</pre>';
                $localField = $oForeignKey->COLUMN_NAME;
                $foreignField = $oForeignKey->REFERENCED_COLUMN_NAME;
                
                // if foreign_table.local_field == foreign_table.primary_key AND
                // if local_table.foreign_key == local_table.primary_key THEN
                //      Relationship = 1-1
                // end
                
                $aTmpForeignPrimaryKey = &self::$aSchemas[ $oForeignKey->typname ][ "primary_key" ];
                $aTmpLocalPrimaryKey = &self::$aSchemas[ $sTableName ][ "primary_key" ];
                
                $bForeignFieldIsPrimary = count( $aTmpForeignPrimaryKey ) == 1 &&
                    reset( $aTmpForeignPrimaryKey ) == $foreignField;
                $bLocalFieldIsPrimary = count( $aTmpLocalPrimaryKey ) &&
                    reset( $aTmpLocalPrimaryKey ) == $localField;
                $bForeignIsSingular = count( $aForeignFields ) == 1;
                
                $sType = "1-m";
                
                if( $bForeignFieldIsPrimary && $bLocalFieldIsPrimary && $bForeignIsSingular )
                {
                    $sType = "1-1";
                }

                self::$aSchemas[ $sTableName ][ "foreign_key" ][ $oForeignKey->TABLE_NAME ] = array(
                    "table" => $oForeignKey->TABLE_NAME,
                    "name" => $oForeignKey->TABLE_NAME,
                    "local" => array( $oForeignKey->REFERENCED_COLUMN_NAME ),
                    "foreign" => array( $oForeignKey->COLUMN_NAME ),
                    "type" => $sType,
                    "dependency" => false
                );
                
                //echo '<pre>' . print_r( self::$aSchemas[ $sTableName ][ "foreign_key" ][ $oForeignKey->TABLE_NAME ], true ) . '</pre>';
            }


            return( self::$aSchemas[ $sTableName ][ "foreign_key" ] );

        } // GetTableForeignKeys()
        

        /*
         *
         */
        public function IsPrimaryKeyReference( $sTableName, $sColumnName )
        {
        
        } // IsPrimaryKeyReference()
        

        /*
         *
         */
        public function GetColumnType( $sTableName, $sFieldName )
        {
        
        } // GetColumnType()
        

        /*
         * Determines whether the specified table exists in the current database.
         * 
         * This method first determines whether or not the table exists in the schemas array. If not, 
         * it attempts to find the table in the PostgreSQL catalog. 
         * 
         * @argument string The name of the table to determine existence
         * @returns boolean True or false, depending on whether the table exists             
         */
        public function TableExists( $sTableName )
        {
        
        } // TableExists()
        

        /*
         *
         */
        protected function GetColumnByNumber( $sTableName, $iColumnNumber )
        {
        
        } // GetColumnByNumber()
        
        
        /**
         * 
         * 
         */
        public function GetVersion()
        {
            
        } // GetVersion()

    } // MySQLDatabase()

?>
