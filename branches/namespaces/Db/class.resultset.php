<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @dependencies    
 * @copyright       Copyright (c) 2008, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.2.0-beta
 *
 */


    namespace OpenAvanti\Db;
 
    /**
     * Contains a set of database results, but is database indepenent, and allows the traversing
     * of the database records as well as access to the data.    
     *
     * @category    Database
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/resultset
     */
    class ResultSet implements Iterator, Countable
    {
        private $_dbConnection = null;
        private $_queryResource = null;
        
        private $_validRowSet = false;
        
        private $_rowCount = 0;
        
        private $_currentRow = 0;
        private $_dataSet = null;
        
        
        /**
         * Stores the supplied database and query resource for later processing. Counts the number
         * of rows in the query resource and stores for later use.   
         * 
         * @argument Database An instance of a database connection
         * @argument Resource A reference to the database result returned by a query
         */
        public function __construct(&$databaseConnection, &$queryResource)
        {
            $this->_dbConnection = &$databaseConnection;
            $this->_queryResource = &$queryResource;
            
            if(!is_null($this->_queryResource))
            {
                $this->_rowCount = $this->_dbConnection->countFromResult($this->_queryResource);
            }
            
            $this->_validRowSet = $this->count() != 0;
        
        
            if(!is_null($this->_queryResource))
            {
                $this->_dataSet = $this->_dbConnection->pullNextResult($this->_queryResource);
            }
            else
            {
                $this->_dataSet = null;
            }
        
        } // __construct()
    
    
        /**
         * Returns a copy of the current record, if any, or null if no record is stored
         *       
         * @returns StdClass The current data record, or null if none
         */
        public function getRecord()
        {
            return $this->current();
        
        } // getRecord()
        

        /**
         * Returns the number of rows returned by the query this result set originated from
         *       
         * @returns int The number of rows in the query resource resulting from the query        
         */
        public function Count()
        {           
            return $this->_rowCount;
            
        } // count()
        

        /**
         * Returns the data record for the current row, if any, or false if there is not a current 
         * row
         *       
         * @returns StdClass The current data record for the current row, or false if there is no data
         */
        public function current()
        {           
            if(!is_null($this->_dataSet))
            {
                return $this->_dataSet;
            }
            else
            {
                return false;
            }
        
        } // current()
        

        /**
         * Returns the key for the current data. This is defined as the current row of data in 
         * the query result.         
         *       
         * @returns int The current row loaded into the ResultSet from the query resource        
         */
        public function key()
        {
            return $this->_currentRow;
        
        } // key()
        

        /**
         * Attempts to advance the internal pointer of the query result to the next row of data.
         * On success, the data is loaded into this object. On failure, the data is cleared and
         * operations such as current will return false.                 
         *       
         * @returns void         
         */
        public function next()
        {
            // Clean up first to prevent memory problems:
            
            if(!is_null($this->_dataSet))
            {
                $this->_dataSet = null;
            }
            
            $this->_currentRow++;

            if(!is_null($this->_queryResource))
            {
                $this->_dataSet = $this->_dbConnection->pullNextResult($this->_queryResource);
            }
            else
            {
                $this->_dataSet = null;
            }

            $this->_validRowSet = !is_null($this->_dataSet) && $this->_dataSet !== false;
        
        } // next()
        

        /**
         * Returns the internal pointer of the query result to the first row of the data. 
         *       
         * @returns void         
         */
        public function rewind()
        {           
            $this->_dbConnection->resetResult($this->_queryResource);

            $this->_currentRow = -1;
            
            $this->next();
            
            $this->_validRowSet = $this->count() != 0;
        
        } // rewind()
        

        /**
         * Returns whether there is any data currently loaded in the ResultSet. If no data was 
         * returned by the query, or if the internal pointer is out of bounds (higher than the
         * number of results in the query), this method will return false.               
         *       
         * @returns bool True if there is data currently loaded in the result set, false otherwise       
         */
        public function valid()
        {           
            return $this->_validRowSet;
        
        } // valid()
        
        
        /**
         * Returns whether or not the column exists in the current row
         * 
         * @returns bool True if the column exists in the current row, false
         *      otherwise
         */
        public function __isset($column)
        {
            return isset($this->_dataSet->$column);
            
        } // __isset()
        
        
        /**
         * Gets the value of the specified column, if it exists, from the 
         * current database table row, if the current row is valid.
         * 
         * @returns mixed The value of the requested column, or null if the
         *      column is invalid or there is no current row
         */
        public function __get($column)
        {
            if(isset($this->_dataSet->$column))
            {
                return $this->_dataSet->$column;
            }
            
            return null;
            
        } // __get()
    
    } // ResultSet()

?>