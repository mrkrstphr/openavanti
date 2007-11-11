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
 
	/**
	 * A library for manipulating files, directories and paths
	 *
	 * @category	Files
	 * @author		Kristopher Wilson
	 * @link			http://www.openavanti.com/docs/files
	 */
	class FileFunctions
   {

		//////////////////////////////////////////////////////////////////////////////////////////////
		public static function GetFileExtension( $sFilename )
		{
			$sExt = substr( $sFilename, strrpos( $sFilename, "." ) + 1 );
		                                       
		   return( $sExt );
		                                   
		} // GetFileExtension()
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////
		public static function GetMimeType( $sFileName )
		{
			$rFinfo = finfo_open( FILEINFO_MIME );

    		$sMimeType = finfo_file( $rFinfo, $sFileName );

			finfo_close( $rFinfo );
			
			return( $sMimeType );
		
		} // GetMimeType()
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////
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
                                                                                    
                                                                              
      //////////////////////////////////////////////////////////////////////////////////////////////
      public static function FileExistsInPath( $sFile )
      {
			$sFile = strtolower( $sFile );
			$aPaths = explode( PATH_SEPARATOR, get_include_path() );
			
			foreach( $aPaths as $sPath )
			{
				if( file_exists( "{$sPath}/{$sFile}" ) )
			   {
			   	return( "{$sPath}/{$sFile}" );
			   }
			}
			
			return( false );
        
      } // FileExistsInPath()


	}; // FileFunctions()

?>
