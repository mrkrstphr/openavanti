<?php

	class Model extends CRUD
	{
		
		public function __construct( $sTableName, $oData = null )
		{
			echo "-- Model constructed<br />";
			
			parent::__construct( $sTableName, $oData );
			
		} // __construct()
		
		
		public function Save()
		{
			echo "-- Save() triggered <br />";
			
			$bUpdate = parent::RecordExists();
		
			if( $bUpdate )
			{
				if( !$this->OnBeforeUpdate() || 
					 !$this->OnBeforeSave() || 
					 !$this->ValidateUpdate() ||
					 !$this->Validate() )
				{
					return( false );
				}
			}
			else
			{
				if( !$this->OnBeforeInsert() || 
					 !$this->OnBeforeSave() || 
					 !$this->ValidateInsert() ||
					 !$this->Validate() )
				{
					return( false );
				}
			}	
				
			if( !parent::Save() )
			{
				return( false );
			}
		
			if( $bUpdate )
			{
				if( !$this->OnAfterUpdate() || 
					 !$this->OnAfterSave() )
				{
					return( false );
				}
			}
			else
			{
				if( !$this->OnAfterInsert() || 
					 !$this->OnAfterSave() )
				{
					return( false );
				}
			}
			
			
			return( true );			
		
		} // Save()
		
		
		public function Destroy()
		{
			if( !$this->OnBeforeDestroy() )
			{
				return( false );
			}
			
			if( !parent::Destroy() )
			{
				return( false );
			}
		
			if( !$this->OnAfterDestroy() )
			{
				return( false );
			}			
			
			return( true );
		
		} // Destroy()
		
		
		public function DestroyAll()
		{
			if( !$this->OnBeforeDestroyAll() )
			{
				return( false );
			}
			
			if( !parent::Destroy() )
			{
				return( false );
			}
		
			if( !$this->OnAfterDestroyAll() )
			{
				return( false );
			}			
			
			return( true );
		
		} // Destroy()
		
		
		// These protected methods are called from the CRUD object
		
		protected function OnBeforeSave()
		{
			echo "---- OnBeforeSave() triggered <br />";
			
			return( true );
		
		} // OnBeforeSave()
		
		
		protected function OnBeforeInsert()
		{
			echo "---- OnBeforeInsert() triggered <br />";
			
			return( true );
		
		} // OnBeforeInsert()
		
		
		protected function OnBeforeUpdate()
		{
			echo "---- OnBeforeUpdate() triggered <br />";
			
			return( true );
		 
		} // OnBeforeUpdate()
	
		
		
		protected function OnAfterSave()
		{
			echo "---- OnAfterSave() triggered <br />";
			return( true );
		
		} // OnBeforeSave()
		
		
		protected function OnAfterInsert()
		{
			echo "---- OnAfterInsert() triggered <br />";
			return( true );		
		
		} // OnAfterInsert()
		
		
		protected function OnAfterUpdate()
		{
			echo "---- OnAfterUpdate() triggered <br />";
			return( true );		
		
		} // OnAfterUpdate()
		
		
		protected function OnBeforeDestroy()
		{
			echo "---- OnBeforeDestroy() triggered <br />";
			return( true );
		
		} // OnBeforeDestroy()
		
		
		protected function OnAfterDestroy()
		{
			echo "---- OnAfterDestroy() triggered <br />";
			return( true );
		
		} // OnBeforeDestroy()
		
		
		protected function OnBeforeDestroyAll()
		{
			echo "---- OnBeforeDestroyAll() triggered <br />";
			return( true );
		
		} // OnBeforeDestroy()
		
		
		protected function OnAfterDestroyAll()
		{
			echo "---- OnAfterDestroyAll() triggered <br />";
			return( true );
		
		} // OnBeforeDestroy()
		
		
		protected function Validate()
		{
			echo "---- Validate() triggered <br />";
			return( true );
			
		} // Validate()
		
		
		protected function ValidateInsert()
		{
			echo "---- ValidateInsert() triggered <br />";
			return( true );
			
		} // ValidateInsert()
		
		
		protected function ValidateUpdate()
		{
			echo "---- ValidateUpdate() triggered <br />";
			return( true );
		
		} // ValidateUpdate()
	
	}; // Model()

?>
