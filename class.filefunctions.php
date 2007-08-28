<?php

    class FileFunctions
    {

        //////////////////////////////////////////////////////////////////////////////////////////////
        public static function getFileExtension( $filename )
        {
            $ext = substr( $filename, strrpos( $filename, "." ) + 1 );
                                                
            return( $ext );
                                            
        } // getFileExtension()
                                                                                    
                                                                              
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
        
        } // fileExistsInPath()

    };

?>
