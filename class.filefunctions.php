<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author			Kristopher Wilson
 * @dependencies 	FileInfo
 * @copyright		Copyright (c) 2008, Kristopher Wilson
 * @license			http://www.openavanti.com/license
 * @link				http://www.openavanti.com
 * @version			0.05a
 *
 */
 
	/**
	 * A library for manipulating files, directories and paths
	 *
	 * @category	Files
	 * @author		Kristopher Wilson
	 * @link			http://www.openavanti.com/docs/filefunctions
	 */
	class FileFunctions
 	{

		/**
		 * Determines the file extension of the given file name. File extension is
		 * determined to be any characters after the last period (.)
		 * 
		 * @argument string The name of the file
		 * @returns string The extension of the supplied file
		 */
		public static function GetFileExtension( $sFilename )
		{
			$sExt = substr( $sFilename, strrpos( $sFilename, "." ) + 1 );
		                                       
			return( $sExt );

		} // GetFileExtension()
		
		
		/**
		 * Determines the base name of a file by removing the directory structure before it, as
		 * well as the extension of the file. Passing /path/to/file.ext will return "file"		 
		 * 
		 * @argument string The name of the file
		 * @returns string The base name of the file without path or extension
		 */
		public static function GetFileBaseName( $sFileName )
		{
			$iLastSlash = strrpos( $sFileName, "/" );

			if( $iLastSlash !== false )
			{
				$sFileName = substr( $sFileName, $iLastSlash + 1 );
			}
			
			$iLastPeriod = strrpos( $sFileName, "." );
			
			if( $iLastPeriod !== false )
			{
				$sFileName = substr( $sFileName, 0, $iLastPeriod );
			}
			
			
			return( $sFileName );
		
		} // GetFileBaseName()
		
		
		/**
		 * Determines the mime type of the given file. This method uses the FileInfo
		 * extension of PHP and may not always be accurate in determining the mime type
		 * of all files. FileInfo must be installed for this to work properly.
		 * 
		 * @argument string The name of the file
		 * @returns string The mime type of the supplied file
		 */
		public static function GetMimeType( $sFileName )
		{
			if( !function_exists( "finfo_open" ) )
			{
				return( null );
			}
			
			$rFinfo = finfo_open( FILEINFO_MIME );

			$sMimeType = finfo_file( $rFinfo, $sFileName );

			finfo_close( $rFinfo );
			
			return( $sMimeType );
		
		} // GetMimeType()
		
		
		/**
		 * This method takes information about an uploaded file through _FILES and gives the 
		 * file a unique name using microtime() and the extension of the original file name, as 
		 * well as moves that file from the temporary path to a specified folder.
		 * 
		 * @argument string The absolute path to the temp file uploaded via _FILES
		 * @argument string The name of the uploaded file to determine the file extension
		 * @argument string The path to the directory to store the new file
		 * @returns string The unique name of the file without the path.
		 */
		public static function HandleUploadedFile( $sTmpFile, $sUploadName, $sDirectory )
		{
			$sExt = self::GetFileExtension( $sUploadName );
			
			$sFileName = microtime( true ) . "." . $sExt;
			
			if( !copy( $sTmpFile, $sDirectory . "/" . $sFileName ) )
			{
				return( false );
			}
			
			return( $sFileName );
		
		} // HandleUploadedFile()
                                                                                    

		/*
		 * Attempts to find the specified file name in the include path. Loops each path in the
		 * include path, and, upon the first result, returns the absolute path to the file.
		 *
		 * @argument string The file name to attempt to find in the include path
		 * @returns string/bool Returns the absolute path to the file, if found, or false if not
		 */
		public static function FileExistsInPath( $sFileName )
		{
			$sFileName = strtolower( $sFileName );
		 	$aPaths = explode( PATH_SEPARATOR, get_include_path() );
		
		 	foreach( $aPaths as $sPath )
		 	{				
		   	if( file_exists( "{$sPath}/{$sFileName}" ) )
		    	{
		      	return( "{$sPath}/{$sFileName}" );
		    	}
		  	}
		
		 	return( false );
		
		} // FileExistsInPath()


    }; // FileFunctions()

?>
