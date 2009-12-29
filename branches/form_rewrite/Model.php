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
 * @version         1.3.0-beta
 */


    /**
     * The model class of the MVC architecture. Extends the CRUD database abstraction layer
     * to enhance it. This class should not be used directly. It should be extended with methods
     * specific to the database table it is interacting with.        
     *
     * @category    MVC
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/model
     */
    class Model extends CRUD
    {

        /**
         * The Model's constructor - accepts an optional set of data to load into the parent CRUD 
         * object. 
         * 
         * @param array|object|int Either an array or object of data to load into the Model, or
         *      an integer value for the primary key to load from the database.                                       
         */
        public final function __construct($data = null)
        {
            if(empty($this->_tableName))
            {
                $className = get_class($this);
                $tableName = ltrim(strtolower(preg_replace("/([A-Z])/", "_\$1", $className)), "_");
                $className = strtolower(get_class($this));
                
                $this->_tableName = StringFunctions::toPlural($className);
            }

            if(is_array($data) || is_object($data))
            {
                parent::__construct($this->_tableName, $data);
            }
            else if(is_numeric($data) && strval(intval($data)) == strval($data))
            {
                parent::__construct($this->_tableName);
                
                $this->find((int)$data);
            }
            else
            {
                parent::__construct($this->_tableName);
            }
            
        } // __construct()
        

        /**
         * Wraps CRUD's Save() method to invoke the events ValidateUpdate(), Validate(), 
         * OnBeforeUpdate(), OnBeforeSave(), respectively, before calling CRUD::Save() for an UPDATE
         * query. Likewise, invokes the events ValidateInsert(), Validate(), OnBeforeInsert() and
         * OnBeforeSave(), respectively, before calling CRUD::Save() for an INSERT statement. 
         * 
         * If any of these events return false, Model::Save() returns false, before calling 
         * CRUD::Save().
         * 
         * If CRUD::Save() is invoked and returns true, the events OnAfterUpdate() and OnAfterSave()
         * are invoked, respectively, for UPDATE queries. The events OnAfterInsert() and OnAfterSave()
         * are invoked, respectively, for INSERT queries.                                
         *
         * @return bool True if the object can be saved, false if not
         */ 
        public function save()
        {
            $isUpdate = parent::recordExists();
            
            if($isUpdate)
            {
                $success = $this->validate();
                $success = $this->validateUpdate() && $success;
                
                if(!$success)
                {
                    return false;
                }
                
                if(!$this->onBeforeSave() || !$this->onBeforeUpdate())
                {
                    return false;
                }
            }
            else
            {
                $success = $this->validate();
                $success = $this->validateInsert() && $success;
                
                if(!$success)
                {
                    return false;
                }
                
                if(!$this->onBeforeSave() || !$this->onBeforeInsert())
                {
                    return false;
                }
            }   
            
            if(!parent::save())
            {
                return false;
            }
            
            if($isUpdate)
            {
                if(!$this->onAfterUpdate() || !$this->onAfterSave())
                {
                    return false;
                }
            }
            else
            {
                if(!$this->onAfterInsert() || !$this->onAfterSave())
                {
                    return false;
                }
            }
            
            // Everything returned true, so should we:
            return true;         
            
        } // save()
        
        
        /**
         * This method does the same thing as Save(), but also saves all related data loaded into
         * the CRUD object as well. See CRUD::SaveAll() for more details         
         *       
         * @return bool True if the object can be saved, false if not
         */ 
        public function saveAll()
        {
            $isUpdate = parent::recordExists();
            
            if($isUpdate)
            {
                $success = $this->validate();
                $success = $this->validateUpdate() && $success;
                
                if(!$success)
                {
                    return false;
                }
                
                if(!$this->onBeforeSave() || !$this->onBeforeUpdate())
                {
                    return false;
                }
            }
            else
            {
                $success = $this->validate();
                $success = $this->validateInsert() && $success;
                
                if(!$success)
                {
                    return false;
                }
                
                if(!$this->onBeforeSave() || !$this->onBeforeInsert())
                {
                    return false;
                }
            }   
            
            if(!parent::saveAll())
            {
                return false;
            }
            
            if($isUpdate)
            {
                if(!$this->onAfterUpdate() || !$this->onAfterSave())
                {
                    return false;
                }
            }
            else
            {
                if(!$this->onAfterInsert() || !$this->onAfterSave())
                {
                    return false;
                }
            }
            
            // Everything returned true, so should we:
            return true;         
            
        } // saveAll()
        
        
        /**
         * Wraps CRUD's Destroy() method to invoke the event OnBeforeDestroy() before calling
         * CRUD::Destroy(), and to invoke the event OnAfterDestroy() afterwards. If either 
         * event returns false, execution of this method will stop and false will be returned.               
         *
         * @return bool True if the object can be destroyed, false if not
         */ 
        public function destroy()
        {
            // Run the onBeforeDestroy() event. If it fails, return false
            if(!$this->onBeforeDestroy())
            {
                return false;
            }
            
            // Invoke CRUD's Destroy() method. If it fails, return false
            if(!parent::destroy())
            {
                return false;
            }
        
            // Run the OnAfterDestroy() event. If it fails, return false
            if(!$this->onAfterDestroy())
            {
                return false;
            }           
            
            // Everything returned true, so should we:
            return true;
        
        } // destroy()
        
        
        /**
         * Triggered before a call to CRUD::Save(), for both INSERT and UPDATE actions. If this method
         * returns false, Model::Save() will be halted and false returned.
         *
         * @return bool True if the object can be saved, false if not
         */              
        protected function onBeforeSave()
        {
            // Default return true if this method is not extended:
            return true;
        
        } // onBeforeSave()
        
        
        /**
         * Triggered before a call to CRUD::Save(), for INSERT statements only. If this method
         * returns false, Model::Save() will be halted and false returned.
         *
         * @return bool True if the object can be saved, false if not
         */ 
        protected function onBeforeInsert()
        {
            // Default return true if this method is not extended:
            return true;
        
        } // onBeforeInsert()
        
        
        /**
         * Triggered before a call to CRUD::Save(), for UPDATE statements only. If this method
         * returns false, Model::Save() will be halted and false returned.
         *
         * @return bool True if the object can be saved, false if not
         */ 
        protected function onBeforeUpdate()
        {
            // Default return true if this method is not extended:
            return true;
         
        } // onBeforeUpdate()
    
        
        /**
         * Triggered after a call to CRUD::Save(), for both INSERT and UPDATE statements only. If 
         * this method returns false, Model::Save() will return false. It is up to the user at this
         * point to take the necessary actions, such as Rolling back the database transaction.       
         *
         * @return bool True if the object can be saved, false if not
         */
        protected function onAfterSave()
        {
            // Default return true if this method is not extended:
            return true;
        
        } // onBeforeSave()
        
        
        /**
         * Triggered after a call to CRUD::Save(), for INSERT only statements only. If this method 
         * returns false, Model::Save() will return false. It is up to the user at this point to 
         * take the necessary actions, such as Rolling back the database transaction.        
         *
         * @return bool True if the object can be saved, false if not
         */
        protected function onAfterInsert()
        {
            // Default return true if this method is not extended:
            return true;     
        
        } // onAfterInsert()
        
        
        /**
         * Triggered after a call to CRUD::Save(), for UPDATE only statements only. If this method 
         * returns false, Model::Save() will return false. It is up to the user at this point to 
         * take the necessary actions, such as rolling back the database transaction.        
         *
         * @return bool True if the object can be saved, false if not
         */
        protected function onAfterUpdate()
        {
            // Default return true if this method is not extended:
            return true;     
        
        } // onAfterUpdate()
        
        
        /**
         * Triggered before a call to CRUD::Destroy(). If this method returns false, Model::Destroy() 
         * will be halted and false returned.
         *
         * @return bool True if the object can be destroyed, false if not
         */
        protected function onBeforeDestroy()
        {
            // Default return true if this method is not extended:
            return true;
        
        } // onBeforeDestroy()
        
        
        /**
         * Triggered after a call to CRUD::Destroy(). If this method returns false, Model::Destroy() 
         * will return false. It is up to the user at this point to take the necessary actions, such
         * as rolling back the database transaction.         
         *
         * @return bool True if the object can be destroyed, false if not
         */
        protected function onAfterDestroy()
        {
            // Default return true if this method is not extended:
            return true;
        
        } // onBeforeDestroy()
        
        
        /**
         * Triggered before a call to CRUD::Save(), for both INSERT and UPDATE actions. If this method
         * returns false, Model::Save() will be halted and false returned.
         *
         * @return bool True if the object can be saved, false if not
         */ 
        protected function validate()
        {
            // Default return true if this method is not extended:
            return true;
            
        } // validate()
        

        /**
         * Triggered before a call to CRUD::Save(), for INSERT only. If this method returns false, 
         * Model::Save() will be halted and false returned.
         *
         * @return bool True if the object can be saved, false if not
         */ 
        protected function validateInsert()
        {
            // Default return true if this method is not extended:
            return true;
            
        } // validateInsert()
        
        
        /**
         * Triggered before a call to CRUD::Save(), for UPDATE only. If this method returns false, 
         * Model::Save() will be halted and false returned.
         *
         * @return bool True if the object can be saved, false if not
         */ 
        protected function validateUpdate()
        {
            // Default return true if this method is not extended:
            return true;
        
        } // validateUpdate()
    
    } // Model()

?>
