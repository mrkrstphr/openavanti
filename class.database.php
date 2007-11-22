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
 * @version			0.05
 *
 */

	//
	// TODO: Turn this into a singleton database class
	//


	/**
	 * Database Interaction Interface
	 *
	 * @category	Database
	 * @author		Kristopher Wilson
	 * @link			http://www.openavanti.com/docs/database
	 */
	interface Database
	{

		public function __construct();

		public function Query( $sSQL );

		public function Begin();
		public function Commit();
		public function Rollback();

		public function GetLastError();
        
		public function SetCacheDirectory( $sDirectoryName );
		public function CacheSchemas( $bEnable );

		public function GetConnection();

		public static function FormatData( $sType, $sValue );

		public function GetSchema( $sTableName );
		public function GetTableColumns( $sTableName );
		public function GetTablePrimaryKey( $sTableName );
		public function GetTableForeignKeys( $sTableName );

		public function GetColumnType( $sTableName, $sFieldName );
		
		public function TableExists( $sTableName );

    }; // Database()

?>
