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


namespace OpenAvanti\Util;
 
/**
 * A simple library to handle calculating pagination values and returning a set of pagination
 * links to output.  
 *
 * @category    Pagination
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/pagination
 */
class Pagination
{

    /**
     * Generates an array of pagination data based on the supplied arguments. This array 
     * contains an array of individual page links to show, as well as a previous link (if
     * there is a previous page) and a next link (if there is a next page). The returned array
     * is in the format of:
     * 
     * Array(
     *    links => Array(
     *        Array(         
     *            page => 1,              // denotes the page number
     *            link => '?page=1'       // denotes the link for the page 
     *        ),
     *        Array(         
     *            page => 2,              // denotes the page number
     *            link => ''              // empty if this is the current page
     *        ),
     *        Array(         
     *            page => 3,              // denotes the page number
     *            link => '?page=3'       // denotes the link for the page 
     *        ),         
     *    ),
     *    next => Array(                  // empty if no next link
     *        page => 3,                  // denotes the page number
     *        link => '?page=3'           // denotes the link for the page
     *    ),
     *    previous => Array(              // empty if no previous link
     *        page => 1,                  // denotes the page number
     *        link => '?page=1'           // denotes the link for the page
     *    )
     * )                                                                      
     * 
     * @param string $link The URL for each pagination link, containing a %s to
     *      denote where to place the page number for each page
     * @param integer $start The starting page number
     * @param integer $perPage The number of elements to show per page
     * @param integer $totalResults The total number of elements to be paginated
     * @param integer $linksShown The number of pagination links shown at a
     *      given time
     * @return array An array of pagination data            
     */
    public static function generate($link, $start, $perPage, $totalResults, $linksShown)
    {
        $data = array();
        
        $totalPages = ceil($totalResults / ($perPage == 0 ? $totalResults : $perPage));
        $startPage = 1; $end = $totalPages;
        
        if ($totalPages > $linksShown) {
            if ($start <= ceil($linksShown / 2)) {
                $startPage = 1;
                $end = $linksShown;
            } else if ($start >= $totalPages - floor($linksShown / 2)) {
                $startPage = $totalPages - ($linksShown - 1);
                $end = $totalPages;
            } else {
                $startPage = $start - floor($linksShown / 2);
                $end = $start + floor($linksShown / 2);
            }
        }
        
        $recordsStart = (($start - 1) * $perPage) + 1;
        $recordsEnd = $recordsStart + $perPage - 1 > $totalResults ? 
        $totalResults : $recordsStart + $perPage - 1;
        
        $data['start'] = $recordsStart;
        $data['end'] = $recordsEnd;
        $data['total'] = $totalResults;
        
        $data['links'] = array();
        $data['previous'] = array();
        $data['next'] = array();
        
        if ($start != 1) {
            $data['previous'] = array(
                'page' => $start - 1,
                'link' => sprintf($link, $start - 1)
            );
        }
        
        for ($i = $startPage; $i <= $end; $i++) {
            if($i == $start) {                         
                $data['links'][$i] = array(
                    'page' => $i,
                    'link' => ''
                );
            } else {                         
                $data['links'][$i] = array(
                    'page' => $i,
                    'link' => sprintf($link, $i)
                );
            }
        }
        
        if ($start != $totalPages) {
            $data['next'] = array(
                'page' => $start + 1,
                'link' => sprintf($link, $start + 1)
            );
        }
        
        return $data;
    }
}
