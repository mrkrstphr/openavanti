<?php

	class Model
	{
		protected $oDatabase = null;
		
		public function __construct()
		{
			$this->oDatabase = new Database();
			
		} // __construct()
	
	
	}; // Model()

?>
