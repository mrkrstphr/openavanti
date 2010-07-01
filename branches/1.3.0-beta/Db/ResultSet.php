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

/**
 * Extends the ArrayIterator class to provide some extra functionality. This class 
 * encapsulates the results of a database query. All rows returned by the query are
 * stored within this class and can be iterated through. 
 *
 * @category    Database
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/resultset
 */
class ResultSet extends \ArrayIterator #implements \Iterator, \Countable
{

    /**
     * Provides access to members of the array iterator. The purpose of putting this
     * here rather than relying on the ArrayIterator is to allow access to a value
     * in the iterator without actually iterating, which is useful for a query 
     * that only returns one row of data.
     *
     * @param string $column The column to pull the value for
     * @return string The value of the column if it exists in the result
     */
    public function __get($column)
    {
        if(isset(current($this)->$column))
        {
            return current($this)->$column;
        }

        return null;
    
    } // __get()
    
    
    /**
     *
     *
     */
    public function __isset($column)
    {
        return isset(current($this)->$column);
        
    } // __isset()


    /**
     * Returns a copy of the current record, if any, or null if no record is stored
     *       
     * @returns StdClass The current data record, or null if none
     */
    public function getRecord()
    {
        return current($this);
    
    } // getRecord()

} // ResultSet()

?>
