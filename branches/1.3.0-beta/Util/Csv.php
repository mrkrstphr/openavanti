<?php
/**
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson <kwilson@shuttlebox.net>
 * @copyright       Copyright (c) 2007-2010, Kristopher Wilson
 * @license         http://www.openavanti.com/license MIT License
 * @link            http://www.openavanti.com
 * @version         SVN: $Id$
 */


namespace OpenAvanti\Util;

use \Exception;

 
/**
 * Simple object to aid in creating CSV documents
 *
 * @category    Csv
 * @author      Kristopher Wilson <kwilson@shuttlebox.net>
 * @link        http://www.openavanti.com/docs/csv
 */
class Csv
{
    public $headers = array(); 
    public $data = array();
    
    
    /**
     * Adds a header to the list of CSV column headers. This method appends to the current list 
     * of headers by adding the single header.       
     *
     * @param string $header The name of the header to add to the list of column headers      
     *
     * @return void         
     */                     
    public function addHeader($header)
    {
        $this->headers[] = $header;
        
    } // addHeader()
    
    
    /**
     * Adds an array of headers to the list of CSV column headers. This method appends to the 
     * current list of headers by adding the passed array of headers to the existing array of
     * headers already added.
     *                           
     * @param array $headers An array of headers to append to the current array of headers
     *
     * @return void
     */
    public function addHeaders($headers)
    {
        if(is_array($headers) && !empty($headers))
        {
            $this->headers += $headers;
        }
        
    } // addHeaders()                           
    
    
    
    /**
     * Adds the supplied array of data to the CSV document. If the number of columns in the 
     * data does not match the number of columns in the headers (unless there are no headers),
     * this method will throw an exception.      
     * 
     * @param array $data An array of CSV row data
     *
     * @return void
     */                     
    public function addData($data)
    {
        if(!empty($this->headers) && count($data) != count($this->headers))
        {
            throw new Exception("Data column count does not match header column " . 
                "count in CSV data");
        }
        
        $this->data[] = $data;
        
    } // addData()
    
    
    /**
     * This method takes the headers and data stored in this object and creates a CSV
     * document from that data.      
     *       
     * @return string The headers and data supplied as a string formatted as a CSV document
     */              
    public function __toString()
    {
        $csvOutput = "";
        
        // If headers are supplied, add them to the CSV string:
        
        if(!empty($this->headers))
        {
            $csvOutput = implode(",", $this->headers) . "\n";
        }
        
        // Loop each row and convert it to a row of CSV data and add it to the CSV string:
        
        foreach($this->data as $data)
        {
            $dataRow = "";
            
            foreach($data as $dataElement)
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

} // Csv()

?>
