<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @copyright       Copyright (c) 2008, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.2.0-beta
 * 
 */
 
    namespace OpenAvanti\Util;
    
    /**
     * Simple object to aid in creating CSV documents
     *
     * @category    CSV
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/csv
     */
    class CSV
    {
        public $_headers = array(); 
        public $_data = array();
        
        
        /**
         * Adds a header to the list of CSV column headers. This method appends to the current list 
         * of headers by adding the single header.       
         *
         * @argument string The name of the header to add to the list of column headers      
         * @returns void         
         */                     
        public function addHeader( $header )
        {
            $this->_headers[] = $header;
            
        } // addHeader()
        
        
        /**
         * Adds an array of headers to the list of CSV column headers. This method appends to the 
         * current list of headers by adding the passed array of headers to the existing array of
         * headers already added.
         *                           
         * @argument array An array of headers to append to the current array of headers
         * @returns void
         */
        public function addHeaders( $headers )
        {
            if( is_array( $headers ) && !empty( $headers ) )
            {
                $this->_headers += $headers;
            }
            
        } // addHeaders()                           
        
        
        
        /**
         * Adds the supplied array of data to the CSV document. If the number of columns in the 
         * data does not match the number of columns in the headers (unless there are no headers),
         * this method will throw an exception.      
         * 
         * @argument array An array of CSV row data
         * @returns void
         */                     
        public function addData( $data )
        {
            if( !empty( $this->_headers ) && count( $data ) != count( $this->_headers ) )
            {
                throw new Exception( "Data column count does not match header column " . 
                    "count in CSV data" );
            }
            
            $this->_data[] = $data;
            
        } // addData()
        
        
        /**
         * This method takes the headers and data stored in this object and creates a CSV
         * document from that data.      
         *       
         * @returns string The headers and data supplied as a string formatted as a CSV document
         */              
        public function __toString()
        {
            $csvOutput = "";
            
            // If headers are supplied, add them to the CSV string:
            
            if( !empty( $this->_headers ) )
            {
                $csvOutput = implode( ",", $this->_headers ) . "\n";
            }
            
            // Loop each row and convert it to a row of CSV data and add it to the CSV string:
            
            foreach( $this->_data as $data )
            {
                $dataRow = "";
                
                foreach( $data as $dataElement )
                {
                    $dataElement = str_replace(array("\n", "\""), 
                        array(" ", "\"\""), $dataElement);
                    
                    $dataRow .= !empty($dataRow) ? "," : "";
                    $dataRow .= "\"{$dataElement}\"";
                }
                
                $csvOutput .= "{$dataRow}\n";
            }
            
            return $csvOutput;
        
        } // __toString()
    
    } // CSV()

?>