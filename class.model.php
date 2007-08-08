<?php

	class Model
	{
		private $sTableName = "";
		
		public function __construct()
		{
			$this->sTableName = get_class( $this );
		}
		
		public function __default()
		{
			$this->search();
		}
				
				
		public function search()
		{
		
		
			$_SESSION[ "breadcrumb" ] = $this->sTableName . " >> search";
		
			$_SESSION[ "view" ] = "search.php";
		}
		
		
		public function grid()
		{
			$sPath = BASE_PATH . "/xml/grids/{$this->sTableName}.xml";
			
			if( file_exists( $sPath ) )
			{
				$oGrid = new dataGrid( "search-grid" );
				
				$oGrid->LoadXML( $sPath );
				
				return( $oGrid->Render( true ) );
			}
			
			return( "" );
		}
		
		
		public function add()
		{
		
		}
		
		
		public function edit( $iID )
		{
			$data = new cruder( $this->sTableName, $iID );
			
			
		}
		
		public function save( $iID )
		{
		
		}
		
		public function getTableName()
		{
			return( $this->sTableName );
			
		}
		
		public function getSingularName()
		{
			$sName = $this->Unpluralize( $this->sTableName );
			$sName = str_replace( "_", " ", $sName );
			$sName = ucwords( $sName );
			
			return( $sName );
			
		}
		
		public function getPluralName()
		{
			$sName = str_replace( "_", " ", $this->sTableName );
			$sName = ucwords( $sName );
			
			return( $sName );
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////
		protected function pluralize( $sString )
		//
		// Description:
		//		Attempts to turn a word into a plural form from a singular form.
		//		WARNING: this method makes horrible assumptions. Consider overloading/extending
		//		this method if a word does not follow the following rules:
		//
		//		{*}y => {*}ies
		//		{*} => {*}s
		//
		{
		
			if( substr( $sString, strlen( $sString ) - 1, 1 ) == "y" )
			{
				$sString = substr( $sString, 0, strlen( $sString ) - 1 ) . "ies";
			}
			else if( substr( $sString, strlen( $sString ) - 1, 1 ) != "s" )
			{
				$sString += "s";
			}
			
			return( $sString );
		
		} // Pluralize()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		protected function unpluralize( $sString )
		//
		// Description:
		//		Attempts to turn a word into a singular form from a plural form.
		//		WARNING: this method makes horrible assumptions. Consider overloading/extending
		//		this method if a word does not follow the following rules:
		//
		//		{*}ies => {*}y
		//		{*}es => {*}
		//		{*}s => {*}
		//
		{
		
			if( substr( $sString, strlen( $sString ) - 3, 3 ) == "ies" )
			{
				$sString = substr( $sString, 0, strlen( $sString ) - 3 ) . "y";
			}
			else if( substr( $sString, strlen( $sString ) - 2, 2 ) == "es" )
			{
				$sString = substr( $sString, 0, strlen( $sString ) - 2 );
			}
			else if( substr( $sString, strlen( $sString ) - 1, 1 ) == "s" )
			{
				$sString = substr( $sString, 0, strlen( $sString ) - 1 );
			}
			
			return( $sString );
		
		} // Unpluralize()
	
	};

?>
