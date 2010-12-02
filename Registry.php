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

namespace OpenAvanti;
 
/**
 * The Registry class is a simple way of storing and loading values within and across HTTP requests.
 * In reality, this class is just a wrapper to session variables. All Registry session variables
 * are stored in the oaRegistry array in the $_SESSION.
 *
 * @category    Registry
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/registry
 */
class Registry
{
    
    /**
     * Initializes the $_SESSION variable by setting it to an empty array. This is only done if
     * the variable does not exist to prevent clearing out session values.
     *
     */
    public static function initialize()
    {
        if(!isset($_SESSION['oaRegistry']) || !is_array($_SESSION['oaRegistry']))
        {
            $_SESSION['oaRegistry'] = array();
        }
        
    } // initialize()
    
    
    /**
     * Stores a key value pair in the registry
     *
     * @param string $key The name of the variable to store in the registry
     * @param mixed $value The value to store in the registry at $key
     */
    public static function store($key, $value)
    {
        self::initialize();
        
        $_SESSION['oaRegistry'][$key] = $value;
        
    } // store()
    
    
    /**
     * Retrieves a value from the registry by key
     *
     * @param string $key The key of the value to retrieve from the registry
     * @return mixed The value retrieved from the registry, or null if the key was not found
     */
    public static function retrieve($key)
    {
        self::initialize();
        
        if(isset($_SESSION['oaRegistry'][$key]))
        {
            return $_SESSION['oaRegistry'][$key];
        }
        
        return null;
        
    } // retrieve()
    
    
    /**
     *
     *
     */
    public static function dump()
    {
        return $_SESSION["oaRegistry"];
    }
    
} // Registry()

?>