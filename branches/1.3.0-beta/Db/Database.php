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


namespace OpenAvanti\Db;

use \Exception;


/**
 * Database interaction abstract class definition
 *
 * @category    Database
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/database
 */
class Database
{
    /**
     * Protected variables for storing database profiles and connections
     */ 
    protected static $_profiles = array();
    protected static $_defaultProfile = "";
    
    protected static $_connectionStore = array();


    /**
     * 
     */
    private function __construct()
    {
        
    } // __construct()



    /**
     * Adds a database profile to the list of known database profiles. These profiles contain
     * connection information for the database, including driver, host, name, user and password.                                                 
     * 
     * @param string $profileName A unique name for the profile used to get connections
     * @param string $dsn The DSN connection string
     * @param string $user Optional; The user to connect with
     * @param string $password Optional; The password for the user to connect with
     */ 
    final public static function addProfile($profileName, $dsn, $user = null, $password = null)
    {
        $profile = array(
            "dsn" => $dsn,
            "user" => $user,
            "password" => $password
        );
        
        self::ValidateProfile($profile);
        
        if(isset(self::$_profiles[$profileName]))
        {
            throw new Exception("Profile [{$profileName}] already in use.");
        }
        
        self::$_profiles[$profileName] = $profile;
        
    } // addProfile()
    
    
    /**
     * Sets the default database connection profile to the one specified in the first argument. 
     * The default profile is used to create or return a database connection by GetConnection() 
     * when no connection is specified to that method.
     * 
     * @param string $profileName The name of the profile to be used as the default database profile
     */ 
    final public static function setDefaultProfile($profileName)
    {
        if(!isset(self::$_profiles[$profileName]))
        {
            throw new DatabaseConnectionException("Unknown database profile: {$profileName}");
        }
    
        self::$_defaultProfile = self::$profileName;
    
    } // setDefaultProfile()
    
    
    /**
     * As the constructor of the Database class and all derived database driverss is protected,
     * the database class cannot be instantiated directly. Instead, the GetConnection() method
     * must be called, afterwhich a database driver object is returned. 

     *  A database profile array may be specified to control which database is connected to,
     * and with what driver. If no profile is passed to this method, it first checks to see
     * if there is a default database profile set up. If so, it uses that, if not, it then
     * checks to see if there is only one profile stored. If so, that profile is used. If none
     * of these conditions are met, an exception is thrown.                                          
     * 
     * @param string $profileName The name of the profile to get a connection for. If not supplied,
     *      and a profile is already loaded, that profile will be used. If no profile is 
     *      supplied and more than one profile has been loaded, null is returned. 
     * @param bool $unique Optional; Should this connection be unique, in other words, not 
     *      reused on subsequent calls for a connection to this profile?                   
     * @return Database A database object; the type depends on the database driver being used. 
     *      This object contains an active connection to the database.      
     */ 
    final public static function getConnection($profileName = null, $unique = false)
    {
        if(!empty($profileName))
        {
            if(!isset(self::$_profiles[$profileName]))
            {
                return null;
            }
        }
        else if(!empty(self::$_defaultProfile))
        {
            $profileName = self::$_defaultProfile;
        }
        else if(empty($profileName) && count(self::$_profiles) != 1)
        {
            return null;
        }
        else
        {
            $profileName = key(self::$_profiles);
        }
        
        $profile = self::$_profiles[$profileName];
        
        if($unique)
        {
            // Let's create a timestamped profile name to prevent reuse
            // of this connection:
            
            $profileName = md5(microtime());
        }
            
        if(!isset(self::$_connectionStore[$profileName]))
        {                
            $databaseDriver = "OpenAvanti\\Db\\Driver\\" . $profile["driverClass"];
            
            self::$_connectionStore[$profileName] = new $databaseDriver($profile);
        }
        
        return self::$_connectionStore[$profileName];
        
    } // getConnection()
    
    
    /**
     * Validates a database connection profile. Exceptions are thrown if the profile is not valid.
     *               
     * @param array $profile The profile array with database connection information to validate
     */
    private static function validateProfile(&$profile)
    {
        list($driver, $dsn) = explode(":", $profile["dsn"], 2);
        
        $driver = ucwords($driver);
        $driver = str_ireplace("sql", "Sql", $driver);
        
        if(!class_exists("OpenAvanti\\Db\\Driver\\{$driver}", true))
        {
            throw new Exception("Unknown database driver specified: " . $driver);
        }
        
        if(!is_subclass_of("OpenAvanti\\Db\\Driver\\{$driver}", "OpenAvanti\\Db\\Driver"))
        {
            throw new Exception("Database driver does not properly extend the Driver class.");
        }
        
        $profile["driverClass"] = $driver;
        
    } // validateProfile()
    
} // Database()

?>
