<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author			Kristopher Wilson
 * @dependencies 	
 * @copyright		Copyright (c) 2008, Kristopher Wilson
 * @license			http://www.openavanti.com/license
 * @link				http://www.openavanti.com
 * @version			0.05a
 *
 */


	/**
	 * Database Interaction Interface
	 *
	 * @category	Database
	 * @author		Kristopher Wilson
	 * @link			http://www.openavanti.com/docs/database
	 */
	abstract class Database implements Throwable
	{
		protected static $aProfiles = array();
		protected static $aDefaultProfile = array();
		
		protected static $aConnections = array();
		
		
		/**
		 * Adds a database profile to the list of known database profiles. These profiles contain
		 * connection information for the database, including driver, host, name, user and password.		  		 		 		 		 		 
		 * 
		 * @argument array The profile array with database connection information		 
		 * @returns void 
		 */	
		final public static function AddProfile( $aProfile )
		{
			self::ValidateProfile( $aProfile );
			
			if( !isset( $aProfile[ "host" ] ) )
			{
				$aProfile[ "host" ] = "localhost";
			}
			
			self::$aProfiles[ $aProfile[ "driver" ] . "_" . $aProfile[ "name" ] ] = $aProfile;
			
		} // AddProfile()
		
		
		/**
		 * Sets the default database connection profile to the one specified in the first argument. 
		 * The default profile is used to create or return a database connection by GetConnection() 
		 * when no connection is specified to that method.
		 * 
		 * @argument string The name of the profile to be used as the default database profile
		 * @returns void 
		 */	
		final public static function SetDefaultProfile( $sProfile )
		{
			if( !isset( self::$aProfiles[ $sProfile ] ) )
			{
				throw new DatabaseConnectionException( "Unknown database profile: {$sProfile}" );
			}
		
			self::$aDefaultProfile = self::$aProfiles[ $sProfile ];
		
		} // SetDefaultProfile()
		
		
		/**
		 * As the constructor of the Database class and all derived database drivers is protected,
		 * the database class cannot be instantiated directly. Instead, the GetConnection() method
		 * must be called, afterwhich a database driver object is returned. 

		 *	A database profile array may be specified to control which database is connected to,
		 * and with what driver. If no profile is passed to this method, it first checks to see
		 * if there is a default database profile set up. If so, it uses that, if not, it then
		 * checks to see if there is only one profile stored. If so, that profile is used. If none
		 * of these conditions are met, an exception is thrown. 		 		 		 		 		 
		 * 
		 * @argument array The profile array with database connection information. If not supplied,
		 * 		 and a profile is already loaded, that profile will be used. If no profile is 
		 * 		 supplied and more than one profile has been loaded, an exception is thrown.		 		 	 
		 * @returns Database A database object; the type depends on the database driver being used. 
		 * 		 This object contains an active connection to the database.		 
		 */	
		final public static function GetConnection( $aProfile = array() )
		{
			if( !empty( $aProfile ) )
			{
				self::ValidateProfile( $aProfile );
				
				self::AddProfile( $aProfile );
			}
			else if( !empty( self::$aDefaultProfile ) )
			{
				$aProfile = self::$aDefaultProfile;
			}
			else if( empty( $aProfile ) && count( self::$aProfiles ) != 1 )
			{
				throw new Exception( "No profile specified for database connection" );
			}
			else
			{
				$aProfile = current( self::$aProfiles );
			}
			
			$sProfile = $aProfile[ "driver" ] . "_" . $aProfile[ "name" ];
			
			if( !isset( self::$aConnections[ $sProfile ] ) )
			{
				$sDatabaseDriver = $aProfile[ "driver" ] . "Database";
				
				self::$aConnections[ $sProfile ] = new $sDatabaseDriver( $aProfile );
			}
			
			
			return( self::$aConnections[ $sProfile ] );			
			
		} // GetConnection()
		
		
		/**
		 * Validates a database connection profile:
		 * 	1. Must have a driver specified
		 * 		a. Driver must reference a valid class [DriverName]Database
		 * 		b. [DriverName]Database must be a subclass of Database
		 * 	2. Must contain a database name.		 		 		 		 	 		 		 		 		 
		 * 
		 * Exceptions are thrown when any of the above criteria are not met describing the
		 * nature of the failed validation		 
		 *		 		 
		 * @argument array The profile array with database connection information to validate	 		 	 
		 * @returns Void	 
		 */
		private static function ValidateProfile( $aProfile )
		{
			if( !isset( $aProfile[ "driver" ] ) )
			{
				throw new Exception( "No database driver specified in database profile" );
			}
			
			if( !isset( $aProfile[ "name" ] ) )
			{
				throw new Exception( "No database name specified in database profile" );
			}
			
			$sDriver = $aProfile[ "driver" ];
			
			if( !class_exists( "{$sDriver}Database", true ) )
			{
				throw new Exception( "Unknown database driver specified: " . $aProfile[ "driver" ] );
			}
			
			if( !is_subclass_of( "{$sDriver}Database", "Database" ) )
			{
				throw new Exception( "Database driver does not properly extend the Database class." );
			}
			
		} // ValidateProfile()
		

		abstract public function Query( $sSQL );

		abstract public function Begin();
		abstract public function Commit();
		abstract public function Rollback();

		abstract public function GetLastError();
        
		abstract public function SetCacheDirectory( $sDirectoryName );
		abstract public function CacheSchemas( $bEnable );

		abstract public function GetResource();

		abstract public static function FormatData( $sType, $sValue );

		abstract public function GetSchema( $sTableName );
		abstract public function GetTableColumns( $sTableName );
		abstract public function GetTablePrimaryKey( $sTableName );
		abstract public function GetTableForeignKeys( $sTableName );

		abstract public function GetColumnType( $sTableName, $sFieldName );
		
		abstract public function TableExists( $sTableName );

    }; // Database()

?>
