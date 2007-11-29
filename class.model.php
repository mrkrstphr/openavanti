<?php

	class Model
	{
		protected $oDatabase = null;
		
		public function __construct()
		{
			$this->oDatabase = new PostgresDatabase();
			
		} // __construct()
	
	
	}; // Model()

?>
