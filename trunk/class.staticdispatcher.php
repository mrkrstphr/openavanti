<?php

	/////////////////////////////////////////////////////////////////////////////////////////////////
	class StaticDispatcher extends Dispatcher
	//
	// Description:
	//		Extends the standard dispatcher  ...
	//
	{		
		
		//////////////////////////////////////////////////////////////////////////////////////////////
		public function __construct()
		//
		// Description:
		//		Constructor
		//
		{
			parent::__construct();
			
		} // __construct()
		
		
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
			else if( file_exists( BASE_PATH . "/views/static-" . $this->sModel . ".php" ) )
			{
				$_SESSION[ "view" ] = BASE_PATH . "/views/static-" . $this->sModel . ".php";
			}
			else
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
			
			if( !is_null( $oModel ) )
			{
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
			}
			
			
			if( isset( $_SESSION[ "view" ] ) )
			{
				require( $_SESSION[ "view" ] );
			}
		
		} // connect()
		
	
	}; // StaticDispatcher()

?>
