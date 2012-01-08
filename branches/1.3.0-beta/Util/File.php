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

namespace OpenAvanti\Util;
 
/**
 * A library for manipulating files, directories and paths
 *
 * @category    Files
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/filefunctions
 */
class File
{

    /**
     * Determines the file extension of the given file name. File extension is
     * determined to be any characters after the last period (.)
     * 
     * @argument string The name of the file
     * @returns string The extension of the supplied file
     */
    public static function getFileExtension( $fileName )
    {
        $fileExtension = substr($fileName, strrpos($fileName, ".") + 1);
        
        return $fileExtension;
    }
    
    
    /**
     * Determines the base name of a file by removing the directory structure before it, as
     * well as the extension of the file. Passing /path/to/file.ext will return "file"       
     * 
     * @argument string The name of the file
     * @returns string The base name of the file without path or extension
     */
    public static function getFileBaseName($fileName)
    {
        $lastSlashPos = strrpos($fileName, "/");

        if($lastSlashPos !== false)
        {
            $fileName = substr($fileName, $lastSlashPos + 1);
        }
        
        $lastPeriodPos = strrpos($fileName, ".");
        
        if($lastPeriodPos !== false)
        {
            $fileName = substr($fileName, 0, $lastPeriodPos);
        }
        
        
        return $fileName;
    
    } // getFileBaseName()
    
    
    /**
     * Determines the mime type of the given file. This method uses the FileInfo
     * extension of PHP and may not always be accurate in determining the mime type
     * of all files. FileInfo must be installed for this to work properly.
     * 
     * @argument string The name of the file
     * @returns string The mime type of the supplied file
     */
    public static function getMimeType($fileName)
    {
        if(!function_exists("finfo_open"))
        {
            return null;
        }
        
        $fileResource = new finfo(FILEINFO_MIME);

        $mimeType = $fileResource->file($fileName);
        
        // Strip off the charset, if one is appended. Return everything before a semicolon
        $mimeTypeParts = explode(";", $mimeType);
        $mimeType = $mimeTypeParts[0];
        
        return $mimeType;
    
    } // getMimeType()
    
    
    /**
     * This method takes the name of a file, and creates a new file name using microtime()
     * as the file name, and the original file's extension as the extension.
     *       
     * @argument string The base file name to use as an example
     * @returns string The file name created from microtime
     */
    public static function createFileNameFromTime($baseName)
    {
        $fileExtension = self::getFileExtension($baseName);
        
        $fileName = microtime(true) . "." . $fileExtension;
    
        return $fileName;
    
    } // createFileNameFromTime()
    
    
    /**
     * 
     *          
     * @argument string The name of the original file, used to get the file extension         
     * @argument string 
     * @returns string The name of the randomly generated file name
     */                                   
    public static function createRandomFileNameFromString($basename, $inputString)
    {
        $fileExtension = self::GetFileExtension($basename);
        
        $fileName = substr(md5(microtime(true) . $inputString), 18, 10 ) . 
            "." . $fileExtension;
    
        return $fileName;
        
    } // createRandomFileNameFromString()
    
    
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
    public static function handleUploadedFile($tmpFile, $uploadName, $directoryName)
    {
        $fileExtension = self::GetFileExtension($uploadName);
        
        $fileName = microtime(true) . "." . $fileExtension;
        
        if(!copy($tmpFile, $directoryName . "/" . $fileName))
        {
            return false;
        }
        
        return $fileName;
    
    } // handleUploadedFile()
    

    /**
     * Attempts to find the specified file name in the include path. Loops each path in the
     * include path, and, upon the first result, returns the absolute path to the file.
     *
     * @argument string The file name to attempt to find in the include path
     * @returns mixed Returns the absolute path to the file, if found, or false if not
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
    
    
    /**
     * Returns a human readable file size format in the form of #.## (bytes|KB|MB|GB)
     *
     * @argument integer The file size in bytes
     * @returns string A formated string of the file size
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
     * Creates a temporary directory in the systems temporary directory (determined by the 
     * sys_get_temp_dir()). This directory name is the first 8 characters of the the md5 hash 
     * of the current microtime().                           
     *       
     * @returns string The name of the newly created temporary directory
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
     * Removes the specified directory and all of it's contents, recursively. The need for
     * this method arises from the native php rmdir() not being able to delete a directory if
     * there are files in it. 
     *  
     * @argument string The name of the directory to remove (recursively)         
     * @returns boolean True if the directory was removed successfully, false otherwise
     */
    public static function RemoveRecursively( $sPath ) 
    {
        if( is_dir( $sPath ) && !is_link( $sPath ) && !in_array( $sPath, array( ".", ".." ) ) )
        {
            foreach( glob( "{$sPath}/{,.}*", GLOB_BRACE ) as $sCurrentFile ) 
            {
                if( in_array( basename( $sCurrentFile ), array( ".", ".." ) ) )
                {
                    continue;
                }
                
                if( !self::RemoveRecursively( $sCurrentFile ) )
                {
                    return( false );
                }
            }
          
            return( rmdir( $sPath ) );
        } 
        else 
        {
            return( unlink( $sPath ) );
        }
        
        return( true );
        
    } // RemoveRecursively()
    
    
    /**
     * Moves a specified directory to a new location, recursively. The need for this method
     * arises from the fact that the native php rename() function cannot move files or 
     * directories across partitions on Windows systems.
     *       
     * @argument string The directory to recursively move
     * @argument string The new directory to move the old directory to               
     * @returns boolean True if the directory and it's contents were all moved successfully, 
     *    false otherwise
     */
    public static function MoveRecursively( $sOldDirectory, $sNewDirectory )
    {
        foreach( glob( "{$sOldDirectory}/{,.}*", GLOB_BRACE ) as $sCurrentFile ) 
        {
            if( is_dir( $sCurrentFile ) && in_array( basename( $sCurrentFile ), array( ".", ".." ) ) )
            {
                continue;
            }
            
            if( is_dir( $sCurrentFile ) )
            {
                if( !file_exists( $sNewDirectory . "/" . basename( $sCurrentFile ) ) )
                {
                    mkdir( $sDestination . "/" . basename( $sCurrentFile ) );
                }
                
                self::MoveRecursively( $sCurrentFile, $sNewDirectory . "/" . basename( $sCurrentFile ) );
            }
            else
            {
                rename( $sCurrentFile, $sNewDirectory . "/" . basename( $sCurrentFile ) );
            }
        }
        
        return( true ); 
        
    } // MoveRecursively()

} // File()

?>
