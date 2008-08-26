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
 * @link			http://www.openavanti.com
 * @version			0.6.7-beta
 *
 */
 
	/**
	 * A library for manipulating files, directories and paths
	 *
	 * @category	Files
	 * @author		Kristopher Wilson
	 * @link		http://www.openavanti.com/docs/filefunctions
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
			
			$rFinfo = new finfo( FILEINFO_MIME );

			$sMimeType = $rFinfo->file( $sFileName );
			
			// Strip off the charset, if one is appended. Return everything before a semicolon
			$aMimeType = explode( ";", $sMimeType );
			$sMimeType = $aMimeType[ 0 ];
			
			return( $sMimeType );
		
		} // GetMimeType()
		
		
		/**
		 *
		 * @argument string The base file name to use as an example
		 * @returns string The file name created from microtime
		 */
		public static function CreateFileNameFromTime( $sBase )
		{
			$sExt = self::GetFileExtension( $sBase );
			
			$sFileName = microtime( true ) . "." . $sExt;
		
			return( $sFileName );
		
		} // CreateFileNameFromTime()
		
		
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
		
		
		/*
		 * Returns a human readable file size format in the form of #.## (bytes|KB|MB|GB)
		 *
		 * @argument integer The file size in bytes
		 * @returns integer A formated string of the file size
		 */
		public static function HumanReadableSize( $iSizeInBytes )
		{
			if( $iSizeInBytes >= 1073741824 )
			{
				$iSizeInBytes = round( $iSizeInBytes / 1073741824 * 100 ) / 100 . " GB";
			}
			elseif( $iSizeInBytes >= 1048576 )
			{
				$iSizeInBytes = round( $iSizeInBytes / 1048576 * 100 ) / 100 . " MB";
			}
			elseif( $iSizeInBytes >= 1024 )
			{
				$iSizeInBytes = round( $iSizeInBytes / 1024 * 100 ) / 100 . " KB";
			}
			else
			{
				$iSizeInBytes = $iSizeInBytes . " bytes";
			}
			
			return( $iSizeInBytes );
		
		} // HumanReadableSize()
		
		
		/**
		 *
		 *
		 */		 		 		
		public static function CreateTemporaryDirectory()
		{
			$sTempDirectory = sys_get_temp_dir();

			$sNewDirectory = "";

			do
			{
				$sNewDirectory = $sTempDirectory . "/" . substr( md5( microtime() ), 0, 8 );
				
			} while( !mkdir( $sNewDirectory ) );

			return( $sNewDirectory );

		} // CreateTemporaryDirectory()
		
		
		/**
		 *
		 *
		 */
		public static function RemoveRecursively( $sFile ) 
		{
			if( is_dir( $sFile ) && !is_link( $sFile ) && !in_array( $sFile, array( ".", ".." ) ) )
			{
				foreach( glob( "{$sFile}/{,.}*", GLOB_BRACE ) as $sCurrentFile ) 
				{
					if( in_array( basename( $sCurrentFile ), array( ".", ".." ) ) )
					{
						continue;
					}
					
		      	if( !FileFunctions::RemoveRecursively( $sCurrentFile ) )
					{
		         	return( false );
		         }
		      }
		      
				return( rmdir( $sFile ) );
			} 
			else 
			{
				return( unlink( $sFile ) );
			}
			
			return( true );
			
		} // RemoveRecursively()
		
		
		/**
		 *
		 *
		 */
		public static function MoveRecursively( $sPath, $sDestination )
		{
			foreach( glob( "{$sPath}/{,.}*", GLOB_BRACE ) as $sCurrentFile ) 
			{
				if( is_dir( $sCurrentFile ) && in_array( basename( $sCurrentFile ), array( ".", ".." ) ) )
				{
					continue;
				}
				
				if( is_dir( $sCurrentFile ) )
				{
					if( !file_exists( $sDestination . "/" . basename( $sCurrentFile ) ) )
					{
						mkdir( $sDestination . "/" . basename( $sCurrentFile ) );
					}
					
					FileFunctions::MoveRecursively( $sCurrentFile, $sDestination . "/" . basename( $sCurrentFile ) );
				}
				else
				{
					rename( $sCurrentFile, $sDestination . "/" . basename( $sCurrentFile ) );
				}
			}
			
			return( true ); 
			
		} // MoveRecursively()

    }; // FileFunctions()

?>
