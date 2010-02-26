<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @copyright       Copyright (c) 2007-2010, Kristopher Wilson
 * @package         openavanti
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         SVN: $Id$
 */


namespace OpenAvanti;

use \Exception;

 
/**
 * A class to handle manipulation of cache files (or any file, really).
 *
 * @category    Cache
 * @author      Kristopher Wilson
 * @package     openavanti
 * @link        http://www.openavanti.com/documentation/1.4.0/Cache
 */
class Cache
{
    /**
     * The name of the cache file
     */
    private $_fileName = null;

    /**
     * The date the cache file was created
     */
    private $_createdStamp = null;

    /**
     * The date the cache file was last modified
     */
    private $_modifiedStamp = null;
    

    /**
     * The contents of the cache file
     */
    private $_cacheFile = null;
    
    
    /**
     * The constructor for the cache class. Loads the supplied cache file, if one was specified.
     * 
     * @param string $cacheFileName Optional; The absolute path to the cache file to load.
     *     Default: null
     */
    public function __construct($cacheFileName = null)
    {
        if(!is_null($cacheFileName))
        {
            $this->open($cacheFileName);
        }
        
    } // __construct()  
    

    /**
     * Simply returns whether or not the supplied file path exists. There is no difference 
     * between calling this method and calling file_exists().
     * 
     * @param string $cacheFileName The absolute path to the cache file we're checking the 
     *      existence of
     * @return boolean True if the file exists, false if not
     */
    public static function exists($cacheFileName)
    {
        return(file_exists($cacheFileName));
        
    } // exists()
    
    
    /**
     * Attempts to open a cache file. If the file does not exist, a FileNotFoundException is
     * thrown. If the file does exist, it's contents are loaded, as well as the created and
     * modified time for the file. This method returns the contents of the cache file.                       
     * 
     * @throws FileNotFoundException
     *
     * @param string $cacheFileName The name of the cache file to load       
     * @return string The contents of the cache file  
     */
    public function open($cacheFileName)
    {
        if(!file_exists($cacheFileName))
        {
            throw new FileNotFoundException("Cache file {$cacheFileName} does not exist");
        }
        
        $this->_fileName = $cacheFileName;
        
        $this->_cacheFile = file_get_contents($cacheFileName);
        $this->_createdStamp = filectime($cacheFileName);
        $this->_modifiedStamp = filemtime($cacheFileName);
        
        return( $this->_cacheFile );
    
    } // open()
    
    
    /**
     * Attempts to save a cache file with the specified contents. If the directory part of
     * the supplied file name does not exist, a FileNotFoundException is thrown. If this method
     * fails to write to the supplied file, an Exception is thrown.
     * 
     * On a sucessful save, this method loads information about the cache file and stores
     * the cache contents. 
     * 
     * @throws FileNotFoundException
     * @throws Exception
     *
     * @param string $cacheFile The name of the file to save the cache contents to
     * @param string $cacheContents The content to be cached in the supplied file             
     */
    public function save($cacheFile, $cacheContents)
    {
        $directoryName = dirname($cacheFile);
        
        if(!file_exists($directoryName))
        {
            throw new FileNotFoundException("Directory path {$directoryName} does not exist");
        }
        
        if(@file_put_contents($cacheFile, $cacheContents) === false)
        {
            throw new Exception("Failed to write to file {$cacheFile}");
        }
    
        $this->_fileName = $cacheFile;
        
        $this->_cacheFile = $cacheContents;
        $this->_createdStamp = filectime($cacheFile);
        $this->_modifiedStamp = filemtime($cacheFile);
        
    } // save()       


    /**
     * This method actually does not close anything as we do not keep an active connection
     * to the file. Instead, this method simply clears all file variables and stored contents.
     */
    public function close()
    {
        $this->_fileName = null;
        
        $this->_cacheFile = null;
        $this->_createdStamp = null;
        $this->_modifiedStamp = null;
        
    } // close()


    /**
     * Returns the created time for the current cache file.
     * 
     * @return integer The timestamp for when the current file was created
     */
    public function getCreatedTime()
    {
        return $this->_createdStamp;
    
    } // getCreatedTime()
    
    
    /**
     * Returns the last created time for the current cache file.
     * 
     * @return integer The timestamp for when the current file was last modified
     */
    public function getModifiedTime()
    {
        return $this->_modifiedStamp;
    
    } // getModifiedTime()
    
    
    /**
     * The __toString() method returns the contents of the cache file
     * 
     * @return string The contents of the cache file
     */
    public function __toString()
    {
        return $this->_cacheFile;
        
    } // __toString()
    
} // Cache()

?>
