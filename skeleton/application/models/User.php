<?php

use OpenAvanti\Validation;

class User extends OpenAvanti\Model
{
    const StatusActive = 'active';
    const StatusInactive = 'inactive';
    
    public static $_statuses = array(
        self::StatusActive => 'Active',
        self::StatusInactive => 'Inactive'
    );
    
    // virtual attributes
    
    public $confirm_password = "";
    
    
    /**
     *
     *
     */
    public function init()
    {
        $this->addSaveEvent("LoginKey", null,
            array($this, "validateInsert"),array($this, "validateSaveLoginKey"));
        
    } // init()
        
        
    /**
     * Validation rules for adding new users
     *
     * @returns bool Whether validation passed (true) or failed (false)
     */
    public function validateInsert()
    {
        Validation::validateLengthRange("password", $this->password, 3, 32);
        Validation::validateEqualTo("password", $this->password, $this->confirm_password, 
            "Password and confirmation password do not match.");
        
        return !Validation::hasErrors();
        
    } // validateInsert()
    
    
    /**
     * Validation rules for editing existing users
     *
     * @returns bool Whether validation passed (true) or failed (false)
     */
    public function validateUpdate()
    {			
        if(!empty($this->password))
        {			
            Validation::validateLengthRange("password", $this->password, 3, 32);
            Validation::validateEqualTo("password", $this->password, $this->confirm_password, 
                "Password and confirmation password do not match.");
        }
        
        return !Validation::hasErrors();
        
    } // validateInsert()
    
    
    /**
     *
     *
     */
    public function validateSaveLoginKey()
    {
        Validation::validatePresent("login_key", $this->login_key);
        
        return !Validation::hasErrors();
        
    } // validateSaveLoginKey()
    
    
    /**
     * Makes modifications to the user data before updating it in the database
     *
     * @returns bool True. Always true.
     */
    public function onBeforeUpdate()
    {
        // Prevent the password from being blanked out if none was supplied:
        
        if(empty($this->password))
        {
            unset($this->password);
        }
        
        return true;
        
    } // onBeforeUpdate()
    
    
    /**
     * Makes modifications to the user data before updating or inserting it
     * in the database
     *
     * @returns bool True. Always true.
     */
    public function onBeforeSave()
    {
        // If a new password was supplied, md5 it:
        
        if(!empty($this->password))
        {
            $this->password = hash("sha256", $this->password);
        }
        
        return true;
    
    } // onBeforeSave()	

} // User()

?>
