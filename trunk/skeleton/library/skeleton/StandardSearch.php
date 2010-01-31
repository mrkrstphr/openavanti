<?php

class StandardSearch extends OpenAvanti\CRUD
{
    public $iResultsPerPage = 10;
    public $sHardFilters = "";
    public $sDefaultOrder = "";
    
    public $iCurrentPage = 0;
    
    public $iResultCount = 0;
    public $results = array();
    
    public $sSort = "";
    public $sOrder = "";
    
    public $sPaginationLink = "";
    
    public $aPagination = array();
    
    public $sJavaScriptClick = "";
    
    public $aJoins = array();
    
    
    /**
     *
     */		 
    public function __construct( $sTableName, $oData = null  )
    {
        parent::__construct( $sTableName, $oData = null );
        
    } // __construct()
    
    
    /**
     *
     */
    public function Process( $iCurrentPage )
    {
        $this->iCurrentPage = $iCurrentPage;
        
        $iRecordStart = ( $iCurrentPage - 1 ) * $this->iResultsPerPage;

        $this->Sort();

        $sWhere = $this->BuildSearch();

        $this->iResultCount = $this->FindCount( array(
            "where" => $sWhere
        ) );
        
        if( empty( $this->sPaginationLink ) )
        {
            $this->sPaginationLink = "/" . $this->getTableName() . "/paginate/%s";
        }
        
        $this->aPagination = OpenAvanti\Pagination::Generate( $this->sPaginationLink, 
            $iCurrentPage, $this->iResultsPerPage, $this->iResultCount, 5 );
        
        
        $sSort = "{$this->sSort} {$this->sOrder}";
        
        // If the field we are sorting by is text or (var)char, we should lower it:
        
        // First thing to do is see if this is a joined column:
        
        $sSortColumn = $this->sSort;
        $sTable = "";			
        
        if( strpos( $this->sSort, "." ) !== false && !empty( $this->aJoins ) )
        {			
            $aColumn = explode( ".", $this->sSort );
                            
            $sAlias = $aColumn[ 0 ];
            $sSortColumn = $aColumn[ 1 ];
            
            if( $sAlias == "_" . StringFunctions::ToSingular($this->getTableName()))
            {
                // Yet it might actually be the primary table -- ugh:
                $sTable = $this->getTableName();
                $aSchema = $this->_database->getTableDefinition( $sTable );  
            }
            else
            {
                // Let's find the join:
                
                $aJoin = array();
                                
                foreach( $this->aJoins as $aJoinTmp )
                {
                    // We must define "as" for search relationships!
                    if( isset( $aJoinTmp[ "as" ] ) && $aJoinTmp[ "as" ] == $sAlias )
                    {
                        $aJoin = $aJoinTmp;
                        break;
                    }
                }
                
                if( empty( $aJoin ) )
                {
                    throw new Exception( "Attempting to sort unknown column: {$this->sSort}" );
                } 
                
                $sTable = $aJoin[ "table" ];
                
                $aSchema = $this->_database->getTableDefinition($aJoin["table"]);   
            }     
        }
        else
        {
            $sTable = $this->getTableName();
            
            $aSchema = $this->_database->getTableDefinition($this->getTableName());
        }
        
        if( isset( $aSchema[ "fields" ][ $sSortColumn ] ) )
        {
            $sColumnType = $this->oDatabase->GetColumnType( $sTable, $sSortColumn );
            
            if( $sColumnType == "text" || stripos( $sColumnType, "char" ) !== false )
            {
                $sSort = "LOWER( {$this->sSort} ) {$this->sOrder}";
            }
        }
        
        $this->results = $this->find( array(
            "where" => $sWhere,
            "offset" => $iRecordStart,
            "limit" => $this->iResultsPerPage,
            "join" => $this->aJoins,
            "order" => $sSort
        ) );
        
    } // Process()
    
    
    /**
     *
     */
    protected function Sort()
    {
        // Note, only works for basic sorts "ORDER BY X Y":
        
        $aSort = explode( " ", $this->sDefaultOrder );
        
        if( isset( $_REQUEST[ "sort" ] ) )
        {
            $this->sSort = $_REQUEST[ "sort" ];
        }
        else
        {
            $this->sSort = $aSort[ 0 ];
        }
        
        if( isset( $_REQUEST[ "order" ] ) )
        {
            $this->sOrder = $_REQUEST[ "order" ];
        }
        else
        {
            $this->sOrder = $aSort[ 1 ];
        }
        
    } // Sort()
    
    
    /**
     *
     */
    protected function BuildSearch()
    {
        $sWhere = $this->sHardFilters;
        
        if( isset( $_REQUEST[ "search" ] ) && is_array( $_REQUEST[ "search" ] ) )
        {
            foreach( $_REQUEST[ "search" ] as $sKey => $sValue )
            {
                if( !empty( $sValue ) )
                {
                    $sWhere .= !empty( $sWhere ) ? " AND " : "";
                
                    $sWhere .= " {$sKey} = '" . addslashes( $sValue ) . "' ";
                }
            }
        }
        
        return( $sWhere );

    } // Search()
    
    
    /**
     *
     */
    public function SortableColumnString( $sColumn, $aArguments = array() )
    {
        $sNewOrder = "ASC";
        
        if( $sColumn == $this->sSort )
        {
            $sNewOrder = $this->sOrder == "ASC" ? "DESC" : "ASC";
        }
        
        $aHardArguments = array( 
            "sort" => $sColumn, 
            "order" => $sNewOrder 
        );
        
        if( is_array( $aArguments ) )
        {
            $aHardArguments += $aArguments;
        }
        
        $sQueryString = self::GenerateQueryString( $aHardArguments );
        
        return( $sQueryString );
        
    } // SortableColumnString()
    
    
    /**
     *
     */
    public static function GenerateQueryString( $aArgs, $bSprintfAble = false )
    //
    // Description:
    //      Analyzes the existing query string and properly places an element value
    //      or appends it if the argument does not already exist in the query
    //      string.
    //
    // Note:
    //      Instead of calling this method multiple times, try passing %d or %s
    //      as the argument and using sprintf() to replace it with the proper
    //      page value to prevent repeated preg_replace() calls.
    //
    {	
        $sQueryString = self::BuildQueryString();
        
        $sQueryString = $bSprintfAble ? 
            str_replace( '%', '%%', $sQueryString ) : $sQueryString;
        
        foreach( $aArgs as $sArg => $sValue )
        {
            if( !empty( $sQueryString ) )
            {
                if( stristr( $sQueryString, "$sArg=" ) )
                {
                    $sArg = str_replace( array( "-", "[", "]" ), array( "\-", "\[", "\]" ),  $sArg );
                    
                   $sFind = "/$sArg=([-_A-Za-z0-9:%.]*)/";
                   $sReplace = $sArg . "=" . $sValue;
                
                   $sQueryString = preg_replace( $sFind, $sReplace, $sQueryString );
                }
                else
                {
                    $sQueryString .= empty( $sQueryString ) ? "?" : "&amp;";
                    $sQueryString = $sQueryString . $sArg . "=" . $sValue;
                }
            }
            else
            {
              $sQueryString = "?{$sArg}={$sValue}";
            }
        }
        
        return( $sQueryString );
    
    } // GenerateQueryString()
    
    
    /**
     *
     */
    public static function BuildQueryString( $aArray = null, $sQueryString = "", $sPrefix = "" )
    {
        if( empty( $aArray ) )
        {
            $aArray = ( $_POST + $_GET );
        }

        foreach( $aArray as $sKey => $sValue )
        {				
            if( is_array( $sValue ) )
            {
                if( !empty( $sPrefix ) )
                {
                    $sQueryString .= self::BuildQueryString( $sValue, $sQueryString, "{$sPrefix}[{$sKey}]" );
                }
                else
                {
                    $sQueryString .= self::BuildQueryString( $sValue, $sQueryString, $sKey );
                }
            }
            else
            {
                $sQueryString .= empty( $sQueryString ) ? "?" : "&amp;";
                
                if( !empty( $sPrefix ) )
                {
                    $sQueryString .= "{$sPrefix}[{$sKey}]={$sValue}";
                }
                else
                {
                    $sQueryString .= "{$sKey}={$sValue}";
                }
            }
        }
    
        return( $sQueryString );
    
    } // BuildQueryString()
    
} // Search()

?>
