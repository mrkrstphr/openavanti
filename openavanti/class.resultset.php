<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @dependencies    Database
 * @copyright       Copyright (c) 2007-2009, Kristopher Wilson
 * @package         openavanti
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 */
 
 
    /**
     * Contains a set of database results, but is database indepenent, and allows the traversing
     * of the database records as well as access to the data.    
     *
     * @category    Database
     * @author      Kristopher Wilson
     * @package     openavanti
     * @link        http://www.openavanti.com/documentation/docs/1.0.3/ResultSet
     */
    class ResultSet implements Iterator, Countable
    {
        /**
         * The database connection resource for this resultset
         */
        private $oDatabase = null;
        
        /**
         * The query resource for this resultset
         */
        private $rQueryResource = null;
        
        /**
         * Stores whether or not the iterator is currently valid
         */
        private $bValid = false;
        
        /**
         * Stores the number of rows in the resultset
         */
        private $iNumRows = 0;
        
        /**
         * Stores the current row in the iterator
         */
        private $iCurrentRow = -1;
        
        /**
         * Stores the data for the iterator
         */
        private $aData = array();
        
        
        /**
         * Stores the supplied database and query resource for later processing. Counts the number
         * of rows in the query resource and stores for later use.   
         * 
         * @param Database $oDatabase An instance of a database connection
         * @param Resource $rQueryResource A reference to the database result returned by a query
         */
        public function __construct( &$oDatabase, &$rQueryResource )
        {
            $this->oDatabase = &$oDatabase;
            $this->rQueryResource = &$rQueryResource;
            
            if( !is_null( $this->rQueryResource ) )
            {
                $this->iNumRows = $this->oDatabase->CountFromResult( $this->rQueryResource );
            }
            
            $this->bValid = $this->Count() != 0;
        
        } // __construct()
    
    
        /**
         * Returns a copy of the current record, if any, or null if no record is stored
         *       
         * @return StdClass The current data record, or null if none
         */
        public function GetRecord()
        {
            return( $this->Current() );
        
        } // GetRecord()
        

        /**
         * Returns the number of rows returned by the query this result set originated from
         *       
         * @return int The number of rows in the query resource resulting from the query        
         */
        public function Count()
        {           
            return( $this->iNumRows );
            
        } // Count()
        

        /**
         * Returns the data record for the current row, if any, or false if there is not a current 
         * row
         *       
         * @return StdClass The current data record for the current row, or false if there is no
         *      data
         */
        public function Current()
        {           
            if( isset( $this->aData[ $this->iCurrentRow ] ) )
            {
                return( $this->aData[ $this->iCurrentRow ] );
            }
            else
            {
                return( false );
            }
        
        } // Current()
        

        /**
         * Returns the key for the current data. This is defined as the current row of data in 
         * the query result.         
         *       
         * @return int The current row loaded into the ResultSet from the query resource        
         */
        public function Key()
        {
            return( $this->iCurrentRow );
        
        } // Key()
        

        /**
         * Attempts to advance the internal pointer of the query result to the next row of data.
         * On success, the data is loaded into this object. On failure, the data is cleared and
         * operations such as current will return false.    
         */
        public function Next()
        {
            // Clean up first to prevent memory problems:
            
            if( isset( $this->aData[ $this->iCurrentRow ] ) )
            {
                unset( $this->aData[ $this->iCurrentRow ] );
            }
            
            
            $this->iCurrentRow++;

            if( !is_null( $this->rQueryResource ) )
            {
                $this->aData[ $this->iCurrentRow ] = 
                    $this->oDatabase->PullNextResult( $this->rQueryResource );
            }
            else
            {
                $this->aData[ $this->iCurrentRow ] = null;
            }

            $this->bValid = !is_null( $this->aData[ $this->iCurrentRow ] ) &&
                $this->aData[ $this->iCurrentRow ] !== false;
        
        } // Next()
        

        /**
         * Returns the internal pointer of the query result to the first row of the data.        
         */
        public function Rewind()
        {           
            $this->oDatabase->ResetResult( $this->rQueryResource );

            $this->iCurrentRow = -1;
            
            $this->Next();  
            
            $this->bValid = $this->Count() != 0;
        
        } // Rewind()
        

        /**
         * Returns whether there is any data currently loaded in the ResultSet. If no data was 
         * returned by the query, or if the internal pointer is out of bounds (higher than the
         * number of results in the query), this method will return false.               
         *       
         * @return bool True if there is data currently loaded in the result set, false otherwise       
         */
        public function Valid()
        {           
            return( $this->bValid );
        
        } // Valid()
        
    } // ResultSet()

?>
