<?php

	/////////////////////////////////////////////////////////////////////////////////////////////////
	class Dispatcher
	//
	// Description:
	//		The dispatcher is used to route requests and map them to classes and methods. The URL
	//		must follow the following format to be routed correctly:
	//
	//		/class/method[/extra parameters]
	//
	//		When a valid match is found, the class is instantiated and the method is called. 
	//
	{
		protected $sModel;
		protected $sAction;
		protected $sID;
	
	
		//////////////////////////////////////////////////////////////////////////////////////////////
		public function __construct()
		//
		// Description:
		//
		//
		{			
			$sRequest = isset( $_REQUEST[ "request" ] ) ? 
				$_REQUEST[ "request" ] : ""; 
			
			$_SESSION[ "request" ] = explode( "/", $sRequest );
		
			$this->sModel = isset( $_SESSION[ "request" ][ 0 ] ) && 
				!empty( $_SESSION[ "request" ][ 0 ] ) ? 
					$_SESSION[ "request" ][ 0 ] : "";
				
			$this->sAction = isset( $_SESSION[ "request" ][ 1 ] ) ? 
				$_SESSION[ "request" ][ 1 ] : "";
				
			$this->sID = isset( $_SESSION[ "request" ][ 2 ] ) ? 
				$_SESSION[ "request" ][ 2 ] : "";
			
		} // __construct()
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////
		public function DefaultModel( $sModel )
		//
		// Description:
		//
		//
		{
			if( empty( $this->sModel ) )
			{
				$this->sModel = $sModel;
			}
			
		} // DefaultModel()
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////
		public function connect()
		//
		// Description:
		//
		//
		{
			$oModel = null;		
		
			if( !empty( $this->sModel ) && class_exists( $this->sModel, true ) )
			{
				$oModel = new $this->sModel();
			}
			else if( !empty( $this->sModel ) && class_exists( "model", true ) )
			{
				$sModel = "class {$this->sModel} extends Model 
				{
					public function __construct() 
					{
						parent::__construct();
					}
				};";
				
				eval( $sModel );
				
				$oModel = new $this->sModel();
			}
			else
			{
				return;
			}
			
			if( !empty( $this->sAction ) && method_exists( $oModel, $this->sAction ) )
			{
				$sAction = &$this->sAction;
				
				$oModel->$sAction( $this->sID );
			}
			else if( empty( $this->sAction ) )
			{
				$oModel->__default();
			}
			else
			{
				$_SESSION[ "view" ] = "404.php";
			}
			
			if( isset( $_SESSION[ "view" ] ) )
			{
				require( $_SESSION[ "view" ] );
			}
			
		} // connect()
	
	};

?>
