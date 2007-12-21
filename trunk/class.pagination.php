<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author			Kristopher Wilson
 * @dependencies 	
 * @copyright		Copyright (c) 2008, Kristopher Wilson
 * @license			http://www.openavanti.com/license
 * @link				http://www.openavanti.com
 * @version			0.6.1-alpha
 *
 */
 
 
	/**
	 * A simple library to handle generating calculating pagination links
	 *
	 * @category	Pagination
	 * @author		Kristopher Wilson
	 * @link			http://www.openavanti.com/docs/pagination
	 */
	class Pagination
	{

		/**
		 * Generates an array of pagination data based on the supplied arguments.	 		  
		 * 
		 * @argument string The URL for each pagination link, containing a %s to denote where to 
		 * 		 place the page number for each page
		 * @argument integer The starting page number
		 * @argument integer The number of elements to show per page
		 * @argument integer The total number of elements to be paginated
		 * @argument integer The number of pagination links shown at a given time
		 * @returns array An array of pagination data. See the manual for more information	 		 
		 */
		public static function Generate( $sLink, $iStart, $iPerPage, $iTotalResults, $iLinksShown )
		{
			$aData = array();
            
			$iTotalPages = ceil( $iTotalResults / ( $iPerPage == 0 ? $iTotalResults : $iPerPage ) );
        
			// Return nothing if there's only one page:
			if( $iTotalPages <= 1 )
			{
				return( $aData );
			}

			// Calculations:
         $iStartPage = 1; $iEnd = $iTotalPages;
            
         if( $iTotalPages > $iLinksShown ) 
         {
				if( $iStart <= ceil( $iLinksShown / 2 ) ) 
            {
            	$iStartPage = 1;
            	$iEnd = $iLinksShown;
            } 
            else if( $iStart >= $iTotalPages - floor( $iLinksShown / 2 ) ) 
            {
            	$iStartPage = $iTotalPages - ( $iLinksShown - 1 );
            	$iEnd = $iTotalPages;
				} 
         	else 
         	{
         		$iStartPage = $iStart - floor( $iLinksShown / 2 );
            	$iEnd = $iStart + floor( $iLinksShown / 2 );
         	}
         }
													
			$iRecordsStart = ( ( $iStart - 1 ) * $iPerPage ) + 1;
			$iRecordsEnd = $iRecordsStart + $iPerPage - 1 > $iTotalResults ? 
			$iTotalResults : $iRecordsStart + $iPerPage - 1;
				
			$aData[ "start" ] = $iRecordsStart;
			$aData[ "end" ] = $iRecordsEnd;
			$aData[ "total" ] = $iTotalResults;
			
			$aData[ "links" ] = array();
			$aData[ "previous" ] = array();
			$aData[ "next" ] = array();
				
			// Previous page link:
			
			if( $iStart != 1 )
			{            
				$aData[ "previous" ] = array(
					"page" => $iStart - 1,
					"link" => sprintf( $sLink, $iStart - 1 )
				);
			}

			// Individual page link:
			
			// Use the variables we setup above to loop the links:
			for( $i = $iStartPage; $i <= $iEnd; $i++ ) 
			{
				// If this is our current page:
				if( $i == $iStart ) 
				{						  
					$aData[ "links" ][ $i ] = array(
						"page" => $i,
						"link" => ""
					);
				} 
				// Create a link to this page:
				else 
				{						  
					$aData[ "links" ][ $i ] = array(
						"page" => $i,
						"link" => sprintf( $sLink, $i )
					);
				}
			}
				
			// Next page link:
			if( $iStart != $iTotalPages )
			{
			
				$aData[ "next" ] = array(
					"page" => $iStart + 1,
					"link" => sprintf( $sLink, $iStart + 1 )
				);
			}

			return( $aData );

		} // Generate()


	}; // Pagination()

?> 
