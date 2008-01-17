<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author			Kristopher Wilson
 * @dependencies 	FileFunctions
 * @copyright		Copyright (c) 2008, Kristopher Wilson
 * @license			http://www.openavanti.com/license
 * @link				http://www.openavanti.com
 * @version			0.6.1-alpha
 *
 */
 
 
	/**
	 * A class to handle content cached content
	 *
	 * @category	Controller
	 * @author		Kristopher Wilson
	 * @link			http://www.openavanti.com/docs/cache
	 */
	class Cache implements Throwable
	{
		private static $bCacheDatabaseSchemas = false;
		private static $sDatabaseSchemaCacheDir = "";
		
		private static $sCacheDirectory = "";
		
		public static function SetCacheDirectory( $sCacheDirectory )
		{
			self::$sCacheDirectory = $sCacheDirectory;
			
		} // SetCacheDirectory()
		
		
		public static function CacheExists( $sCacheFile )
		{
			$sCacheFile = self::$sCacheDirectory . "/" . $sCacheFile;
			
			return( !empty( self::$sCacheDirectory ) && file_exists( $sCacheFile ) );
			
		} // CacheExists()
		
		
		public static function CacheFile( $sCacheFile, $sContents )
		{
			$sCacheFile = self::$sCacheDirectory . "/" . $sCacheFile;
			
			file_put_contents( $sCacheFile, $sContents );
		
		} // CacheFile()
		
		public static function LoadCacheFile( $sFile )
		{
			$sCacheFile = self::$sCacheDirectory . "/" . $sFile;
			
			if( self::CacheExists( $sFile ) )
			{
				return( file_get_contents( $sCacheFile ) );
			}
			else
			{
				throw new FileNotFoundException( "File not found: {$sFile}" );
			}
		
		} // LoadCacheFile()
		
		
		public static function CacheDatabaseSchemas( $bCache )
		{
			self::$bCacheDatabaseSchemas = $bCache;
			
		} // CacheDatabaseSchemas()
		
		
		
		public static function DatabaseSchemaCacheDir( $sCacheDir )
		{
			self::$bCacheDatabaseSchemas = $sCacheDir;
		
		} // DatabaseSchemaCacheDir()
		
		
		
		public static function CacheSchema( $sFileName, $sSchema )
		{
			if( !self::$bCacheDatabaseSchemas )
			{
				return;
			}
			
			if( !is_dir( self::$sDatabaseSchemaCacheDir ) )
			{
				throw new FileNotFoundException( "Path not found: " . self::$sDatabaseSchemaCacheDir );
			}
			
			file_put_contents( self::$sDatabaseSchemaCacheDir . "/" . $sFileName, $sSchema );
			
		} // CacheSchema()
	
	
	}; // Cache()

?>
