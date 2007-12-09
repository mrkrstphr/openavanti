<?php

    /////////////////////////////////////////////////////////////////////////////////////
    class Pagination
    //
    // Description:
    //      Generates page links for easy pagination
    //
    {
        private $iTotalResults;
        private $iStart;
        private $iResultsPerPage;
        private $iTotalPages;


        /////////////////////////////////////////////////////////////////////////////////
        public function __construct( $iTotalResults, $iStart, $iResultsPerPage )
        //
        // Description:
        //      Accepts the number of results, the starting page, and the number
        //      of results per page and calculates the total number of pages
        //      required
        //
        {
            $this->iTotalResults = $iTotalResults;
            $this->iStart = $iStart;
            $this->iResultsPerPage = $iResultsPerPage;
            
            $this->iTotalPages = ceil( $iTotalResults /
            	( $iResultsPerPage == 0 ? $iTotalResults : $iResultsPerPage ) );

        } // __construct()


        /////////////////////////////////////////////////////////////////////////////////
        public function GeneratePagination( $iLinksShown = 5, $sCustomOnClick = "", $sCustomLink = "" )
        //
        // Description:
        //      iLinksShown determines how many links are shown, and also the page jumps
        //      on Previous # and Next #. bForcePrevNext will force the output to show
        //      Previous and Next text (without links) even when there are no pages
        //      to jump to
        //
        {
            $sPageLinks = "";

            // Return nothing if there's only one page:
            if( $this->iTotalPages <= 1 )
            {
                return( $sPageLinks );
            }

				// Calculations:

            $iStart = 1; $iEnd = $this->iTotalPages;
            
            if( $this->iTotalPages > $iLinksShown ) 
            {
                if( $this->iStart <= ceil( $iLinksShown / 2 ) ) 
                {
                    $iStart = 1;
                    $iEnd = $iLinksShown;
                } 
                else if( $this->iStart >= $this->iTotalPages - floor( $iLinksShown / 2 ) ) 
                {
                    $iStart = $this->iTotalPages - ( $iLinksShown - 1 );
                    $iEnd = $this->iTotalPages;
                } 
                else 
                {
                    $iStart = $this->iStart - floor( $iLinksShown / 2 );
                    $iEnd = $this->iStart + floor( $iLinksShown / 2 );
                }
            }

            
            $sOnClick = $sCustomOnClick;
            $sLink = $sCustomLink;


				// Results Summary:

				$sString = '<table>
					<tr>
						<td class="summary">
							Displaying %s - %s of %s
						</td>
						<td class="links">';
															

				$iRecordsStart = ( ( $this->iStart - 1 ) * $this->iResultsPerPage ) + 1;
				$iRecordsEnd = $iRecordsStart + $this->iResultsPerPage - 1 > $this->iTotalResults ? 
					$this->iTotalResults : $iRecordsStart + $this->iResultsPerPage - 1;
				$sPageLinks .= sprintf( $sString, $iRecordsStart, $iRecordsEnd, $this->iTotalResults );
				
				
				// Previous page link:
				
            if( $this->iStart != 1 )
            {
                $sString = '<a href="' . $sLink . '" onclick="' . $sOnClick . '">
                	&lt;&lt; Previous</a> &nbsp;';

                $sPageLinks .= sprintf( $sString, ( $this->iStart - 1 ), $this->iResultsPerPage );
            }
            
            $sPageLinks .= " Page: ";

				// Individual page link:

				// Use the variables we setup above to loop the links:
				for( $i = $iStart; $i <= $iEnd; $i++ ) 
				{
					 // If this is our current page:
					 if( $i == $this->iStart ) 
					 {
						  $sPageLinks .= '<span class="pagingOn">' . $i . '</span> ';
					 } 
					 // Create a link to this page:
					 else 
					 {
						  $sString = '<a href="' . $sLink . '" onclick="' . $sOnClick . '">' . $i . '</a> ';

						  $sPageLinks .= sprintf( $sString, $i, $this->iResultsPerPage );
					 }
				}
				
            // Next page link:
            if( $this->iStart != $this->iTotalPages )
            {
                $sString = '&nbsp; <a href="' . $sLink . '" onclick="' . $sOnClick . '">
                	Next &gt;&gt;</a>';

                $sPageLinks .= sprintf( $sString, ( $this->iStart + 1 ), $this->iResultsPerPage );
            }
            
            
            $sPageLinks .= "</td>
					</tr>
				</table>";
				

            return( $sPageLinks );

        } // GenerateGTELinks()


    }; // class Pagination()

?> 
