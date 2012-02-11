<?php
/**
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5.3+
 *
 * @author          Kristopher Wilson
 * @copyright       Copyright (c) 2007-2012, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.3.0-beta
 */

namespace OpenAvanti\Db; 

/**
 * 
 * @category    Database
 * @author      Kristopher Wilson
 */
class Row extends \ArrayIterator
{
    /**
     *
     */
    protected $_data;
    
    /**
     *
     */
    public function __isset($var)
    {
        return isset($this->_data[$var]);
    }
    
    /**
     *
     */
    public function __set($var, $value)
    {
        $this->_data[$var] = $value;
    }
    
    /**
     *
     */
    public function __get($var)
    {
        if (isset($this->_data[$var])) {
            return $this->_data[$var];
        }
        
        $altVar = \OpenAvanti\Util\String::fromCamelCase($var);
        
        if (isset($this->_data[$altVar])) {
            return $this->_data[$altVar];
        }
        
        throw new \Exception('Undefined property ' . $var);
    }
    
    /**
     *
     */
    public function getArrayCopy()
    {
        return (array)$this->_data;
    }
}
