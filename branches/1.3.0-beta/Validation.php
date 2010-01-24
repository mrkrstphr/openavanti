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


/**
 * Data validation class helper
 *
 * @category    Database
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/validation
 */
class Validation
{       
    private static $aErrorList = array();
    
    /**
     * This class is static and cannot be instantiated
     */
    private function __construct() {}
          
  
    /**
     * Returns whether or not any errors were noted through validation calls
     *
     * @returns boolean Returns true if there are any errors stored in the error list, or
     *  false otherwise      
     */
    public static function HasErrors()
    {       
        return( count( self::$aErrorList ) > 0 );
    
    } // HasErrors()
    
    
    /**
     * Returns whether or not the specified field has any errors noted through validation calls
     *
     * @argument string The name of the field to check for errors on         
     * @returns boolean Returns true if there are any errors stored in the error list for the
     *  specified field, or false otherwise      
     */
    public static function FieldHasErrors( $sField )
    {
        return( isset( self::$aErrorList[ $sField ] ) );
        
    } // FieldHasErrors()
    
    
    /**
     * Returns the list of errors generated through validation calls. Returns in the format of:
     * 
     * Array(
     *     field1 => array(
     *         0 => "field1 must be between 2 and 32 characters",
     *         1 => "field1 is not between valid email address"       
     *     ),
     *     field2 => array(
     *         0 => "field2 is required"
     *     )                                
     * )                                
     * 
     * @returns array An array of error messages generated by validation calls. This array 
     *  contains a set of arrays for each element name provided to validation calls.                        
     */
    public static function GetErrors()
    {
        return( self::$aErrorList );
        
    } // GetErrors()
    

    /**
     * Stores the supplied error in the static error list
     * 
     * @argument string The name of the form field the error occurred on
     * @argument string The error message
     * @returns void                                
     */
    public static function SetError( $sKey, $sError )
    {
        self::$aErrorList[ $sKey ][] = $sError;
        
    } // SetError()
    
    
    /**
     * Clears any errors in the error list
     * 
     * @returns void                
     */
    public static function Clear()
    {
        self::$aErrorList = array();
    
    } // Clear()
    

    /**
     * Validates that the supplied value is not empty. If not, an error message is added to the 
     * list of errors. 
     * 
     * @argument string The name of the field being validated
     * @argument string The data to validate against
     * @argument string Optional, the message to store in the list of validation errors if 
     *       validation fails
     * @returns boolean True if the data passes validation, false otherwise                                                                 
     */
    public static function ValidatePresent( $sName, $sValue, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) . " is required.";
        }
        
        $sValue = trim( $sValue );
        
        if( empty( $sValue ) )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( true );
        
    } // ValidatePresent()      

    
    /**
     * Validates that the two supplied values are equal. This method is case insensistive, and
     * also compares the trimmed values. If validation fails, an error message is added to the 
     * list of errors.      
     * 
     * @argument string The name of the field being validated
     * @argument string The value of the field being validated
     * @argument string The value of the field being compared
     * @argument string Optional, the message to store in the list of validation errors if 
     *       validation fails
     * @returns boolean True if the data passes validation, false otherwise                                                             
     */
    public static function ValidateEqualTo( $sName, $sValue, $sMatchValue, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) . " does not match the " . 
                "required value " . $sMatchValue;
        }
                    
        if( strtolower( trim( $sValue ) ) != strtolower( trim( $sMatchValue ) ) )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( true );
    
    } // ValidateEqualTo()
    

    /**
     * Validates that the supplied data is at least iMin characters in length. This method trims
     * the data before validating. If not, an error message is added to the list of errors. 
     * 
     * @argument string The name of the field being validated
     * @argument string The data to validate against
     * @argument integer The minimum number of characters the supplied data must be in length to 
     *       pass validation
     * @argument string Optional, the message to store in the list of validation errors if 
     *       validation fails
     * @returns boolean True if the data passes validation, false otherwise                                                                 
     */
    public static function ValidateMinLength( $sName, $sValue, $iMin, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) . " must more than " . 
                "{$iMin} characters in length.";
        }
        
        $sValue = trim( $sValue );
        
        if( strlen( $sValue ) < $iMin )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( true );
    
    } // ValidateMinLength()
    

    /**
     * Validates that the supplied data is not greater than iMax characters in length. This 
     * method trims the data before validating. If not, an error message is added to the list 
     * of errors. 
     * 
     * @argument string The name of the field being validated
     * @argument string The data to validate against
     * @argument integer The maximum number of characters the supplied data must not surpass in 
     *       length to pass validation
     * @argument string Optional, the message to store in the list of validation errors if 
     *       validation fails
     * @returns boolean True if the data passes validation, false otherwise                                                                 
     */
    public static function ValidateMaxLength( $sName, $sValue, $iMax, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) . " must less than " . 
                "{$iMax} characters in length.";
        }
        
        $sValue = trim( $sValue );
        
        if( strlen( $sValue ) > $iMax )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( true );
    
    } // ValidateMaxLength()


    /**
     * Validates that the supplied value's length is between the supplied minimum and maximum
     * range. This method DOES NOT determine whether the value is in the range, but the number
     * characters in the value is in the supplied range.  This method trims the data before 
     * validating. If not, an error message is added to the list of errors. 
     * 
     * @argument string The name of the field being validated
     * @argument string The data to validate against
     * @argument integer The minimum number of characters the supplied data must be in length to 
     *       pass validation
     * @argument integer The maximum number of characters the supplied data must not surpass in 
     *       length to pass validation
     * @argument string Optional, the message to store in the list of validation errors if 
     *       validation fails
     * @returns boolean True if the data passes validation, false otherwise                                                                     
     */
    public static function ValidateLengthRange( $sName, $sValue, $fMin, $fMax, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) . " must be between " . 
                "{$fMin} and {$fMax} characters in length.";
        }
        
        $sValue = trim( $sValue );
        
        if( strlen( $sValue ) < $fMin || strlen( $sValue ) > $fMax )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( true );
        
    } // ValidateLengthRange()
    

    /**
     * Validates that the supplied data is a valid numeric value. If not, an error message is 
     * added to the list of errors. 
     * 
     * @argument string The name of the field being validated
     * @argument string The data to validate against
     * @argument string Optional, the message to store in the list of validation errors if 
     *       validation fails
     * @returns boolean True if the data passes validation, false otherwise                                                                 
     */
    public static function ValidateNumeric( $sName, $sValue, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) . " is not a valid numeric " . 
                "value.";
        }
        
        if( !is_numeric( $sValue ) )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( true );
    
    } // ValidateNumeric()
    

    /**
     * Validates that the supplied data is a valid integer. If not, an error message is added
     * to the list of errors. 
     * 
     * @argument string The name of the field being validated
     * @argument string The data to validate against
     * @argument string Optional, the message to store in the list of validation errors if 
     *       validation fails
     * @returns boolean True if the data passes validation, false otherwise                                                                 
     */
    public static function ValidateInteger( $sName, $sValue, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) . " must be an integer number.";
        }
        
        if( strval( intval( $sValue ) ) != $sValue )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( true );
        
    } // ValidateInteger()
                    

    /*
     * Validates that the supplied data only contains alphabetical characters. If not, an error 
     * message is added to the list of errors. 
     * 
     * @argument string The name of the field being validated
     * @argument string The data to validate against
     * @argument string Optional, the message to store in the list of validation errors if 
     *       validation fails
     * @returns boolean True if the data passes validation, false otherwise                                                             
     */
    public static function ValidateAlpha( $sName, $sValue, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) . " can only contain " . 
                "alphabetical characters.";
        }
        
        if( !preg_match( "/^([-a-z])+$/i", $sValue ) )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( true );
    
    } // ValidateAlpha()
    

    /*
     * Validates that the supplied data only contains alphanumeric characters. If not, an error 
     * message is added to the list of errors. 
     * 
     * @argument string The name of the field being validated
     * @argument string The data to validate against
     * @argument string Optional, the message to store in the list of validation errors if 
     *       validation fails
     * @returns boolean True if the data passes validation, false otherwise                                                                 
     */
    public static function ValidateAlphaNumeric( $sName, $sValue, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) . " can only contain " . 
                "alphabetical and/or numeric characters.";
        }
        
        if( !preg_match( "/^([-a-z0-9])+$/i", $sValue ) )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( true );
    
    } // ValidateAlphaNumeric()
    
    
    /*
     * Validates that the supplied data is a valid email address. This method validates both the
     * format of the email address, and that the supplied domain exists. If not, an error message 
     * is added to the list of errors. 
     * 
     * @argument string The name of the field being validated
     * @argument string The data to validate against
     * @argument string Optional, the message to store in the list of validation errors if 
     *       validation fails
     * @returns boolean True if the data passes validation, false otherwise                                                                 
     */
    public static function ValidateEmail( $sName, $sValue, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) . " is not a valid " . 
                "email address.";
        }
        
        $sValue = trim( $sValue );
        
        // Format
        
        $sPreg = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/";
                                
        if( !preg_match( $sPreg, $sValue ) )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        
        // MX Records
        
        /*$bValidMXRecord = false;

        list( $sLocal, $sDomain ) = split( "@", $sValue );
        
        $aMXHosts = array();
        
        if( !getmxrr( $sDomain, $aMXHosts ) )
        {
            if( @fsockopen( $sDomain, 25, $iErrorNo, $sError, 30 ) )
            {
                $bValidMXRecord = true;
            }
        }
        else
        {
            foreach( $aMXHosts as $sHost )
            {
                if( @fsockopen( $sHost, 25, $iErrorNo, $sError,30 ) )
                {
                    $bValidMXRecord = true;
                }
            }
        }
        
        if( !$bValidMXRecord )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }*/
                
        return( true );
    
    } // ValidateEmail()
    
    
    /*
     * Validates that the supplied data is a valid US postal code format, either a 5 or 9 
     * character format. This method does not validate that the postal code is a valid zip code
     * registered with USPS. If not, an error message is added to the list of errors. 
     * 
     * @argument string The name of the field being validated
     * @argument string The data to validate against
     * @argument string Optional, the message to store in the list of validation errors if 
     *       validation fails
     * @returns boolean True if the data passes validation, false otherwise                                                                 
     */
    public static function ValidateZipCode( $sName, $sValue, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) . " does not appear to be " . 
                "a valid US zip code.";
        }
        
        $sValue = trim( $sValue );
        $sValue = str_replace( "-", "", $sValue );
        
        if( strlen( $sValue ) != 5 && strlen( $sValue ) != 9 )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        if( strval( intval( $sValue ) ) != $sValue )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( true );
    
    } // ValidateZipCode()


    /**
     * Validates that a file was uploaded based on the name of the file field. If validation 
     * fails, an error message is added to the list of errors.      
     * 
     * @argument string The name of the file field to validate
     * @argument string Optional, the message to store in the list of validation errors if 
     *       validation fails           
     * @returns boolean True if the data passes validation, false otherwise                                                                 
     */
    public static function ValidateFilePresent( $sName, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = "No file was uploaded for " . 
                ucwords( str_replace( "_", " ", $sName ) ) . ".";
        }
        
        if( !isset( $_FILES[ $sName ] ) || empty( $_FILES[ $sName ][ "tmp_name" ] ) )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( true );
    
    } // ValidateFilePresent()


    /**
     * Validates that an uploaded file is one of the supplied mime types. If FileInfo extension 
     * is not installed, this method relies on the mime type sent by the browser, and this type
     * cannot be trusted, or may not even be present. OpenAvanti recommends installing FileInfo. 
     * If validation fails, an error message is added to the list of errors.      
     * 
     * @argument string The name of the file field to validate
     * @argument string The name of the uploaded temporary file      
     * @argument array An array of file extensions that are valid for the uploaded file       
     * @argument string Optional, the message to store in the list of validation errors if 
     *       validation fails           
     * @returns boolean True if the data passes validation, false otherwise                                                                 
     */
    public static function ValidateFileMimeType( $sName, $sTmpFileName, $aMimeTypes, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) . " must be one of the " . 
                "following mime types: " . implode( ", ", $aMimeTypes ) . ".";
        }
        
        if( file_exists( $sTmpFileName ) )
        {
            $sMimeType = FileFunctions::GetMimeType( $sTmpFileName );
            
            if( !in_array( $sMimeType, $aMimeTypes ) )
            {
                self::SetError( $sName, $sMessage );
                return( false );
            }
        }
        
        return( true );
    
    } // ValidateFileMimeType()


    /**
     * Validates that the size of an uploaded file does not exceed a certain file size. If 
     * validation fails, an error message is added to the list of errors. 
     * 
     * @argument string The name of the file field to validate
     * @argument integer The maximum size the file is allowed to be     
     * @argument string Optional, the message to store in the list of validation errors if 
     *       validation fails           
     * @returns boolean True if the data passes validation, false otherwise                                                                 
     */
    public static function ValidateFileMaxSize( $sName, $iSizeInBytes, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) . " must be less than " . 
                FileFunctions::HumanReadableSize( $iSizeInBytes ) . " in size.";
        }
        
        if( isset( $_FILES[ $sName ] ) && !empty( $_FILES[ $sName ][ "size" ] ) )
        {               
            if( $_FILES[ $sName ][ "size" ] > $iSizeInBytes )
            {
                self::SetError( $sName, $sMessage );
                return( false );
            }
        }
        
        return( true );
    
    } // ValidateFileMaxSize()
    
    
    /**
     * Validates that a supplied value matches the supplied regular expression. If 
     * validation fails, an error message is added to the list of errors. 
     *                  
     * @argument string The name of the field being validated
     * @argument string The data to validate against
     * @argument string The regular expression to match against the supplied data
     * @argument string The message to store in the list of validation errors if 
     *       validation fails
     * @returns boolean True if the data passets validation, false otherwise     
     */             
    public static function ValidateByMatch( $sName, $sValue, $sExpr, $sMessage )
    {
        if( !preg_match( $sExpr, $sValue ) )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( true );
    
    } // ValidateByMatch()
    
    
    /**
     * Validates that the supplied date is a validate date. Date can be in the following 
     * formats:
     * 
     *    MM/DD/YYYY
     *    MM-DD-YYYY
     *    YYYYMMDD      
     *    
     * If validation fails, an error message is added to the list of errors.                                                         
     *       
     * @argument string The name of the field being validated
     * @argument string The data to validate against
     * @argument string The message to store in the list of validation errors if 
     *       validation fails
     * @returns boolean True if the data passets validation, false otherwise
     */
    public static function ValidateDate( $sName, $sValue, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) . " is not a valid date.";
        }
        
        $iMonth = 0;
        $iDay = 0;
        $iYear = 0;
        
        if( preg_match( "/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/", $sValue ) )
        {
            $aDateParts = explode( "/", $sValue );
            
            $iMonth = $aDateParts[ 0 ];
            $iDay = $aDateParts[ 1 ];
            $iYear = $aDateParts[ 2 ];
        }
        else if( preg_match( "/^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}$/", $sValue ) )
        {
            $aDateParts = explode( "-", $sValue );
            
            $iMonth = $aDateParts[ 0 ];
            $iDay = $aDateParts[ 1 ];
            $iYear = $aDateParts[ 2 ];
        }
        else if( preg_match( "/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $sValue ) )
        {
            $aDateParts = explode( "-", $sValue );
            
            $iYear = $aDateParts[ 0 ];
            $iMonth = $aDateParts[ 1 ];
            $iDay = $aDateParts[ 2 ];
        }
        else
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        if( strval( intval( $iMonth ) ) != strval( $iMonth ) || 
            strval( intval( $iDay ) ) != strval( $iDay ) || 
            strval( intval( $aDateParts[ 2 ] ) ) != strval( $aDateParts[ 2 ] ) )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        if( !checkdate( $iMonth, $iDay, $iYear ) )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( true );

    } // ValidateDate()
    
    
    /**
     * First validates that sValue and sMatch are validate dates using the ValidateDate() 
     * method. If these checks pass, this method then validates that sMatch is greater than
     * sValue.                                                           
     *       
     * @argument string The name of the field being validated
     * @argument string The data to validate against
     * @argument string The message to store in the list of validation errors if 
     *       validation fails
     * @returns boolean True if the data passets validation, false otherwise
     */
    public static function ValidateDateGreaterThan( $sName, $sDate, $sMatch, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) . " must be after " . $sMatch;
        }
        
        if( !Validation::ValidateDate( $sName, $sDate ) )
        {
            return( false );
        }
        
        if( !Validation::ValidateDate( $sName, $sMatch ) )
        {
            return( false );
        }
        
        $sDate = strtotime( $sDate );
        $sMatch = strtotime( $sMatch );
        
        if( $sDate < $sMatch )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( true );

    } // ValidateDateGreaterThan()
    
    
    /**
     * Validates that the supplied time value is a validate time. This method basically 
     * validates that there are two integer values separated by a colon. Seconds and any
     * AM/PM notation are stripped off. The first integer (hours) value must be between 1 and 
     * 23, and the second integer value (minutes) must be between 0 and 59.    
     * 
     * If validation fails, an error message is added to the list of errors.                                                        
     *       
     * @argument string The name of the field being validated
     * @argument string The data to validate against
     * @argument string The message to store in the list of validation errors if 
     *       validation fails
     * @returns boolean True if the data passets validation, false otherwise
     */
    public static function ValidateTime( $sName, $sValue, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) . " is not a valid.";
        }
        
        if( substr( strtolower( $sValue ), strlen( $sValue ) - 3 ) == " am" ||
            substr( strtolower( $sValue ), strlen( $sValue ) - 3 ) == " pm" )
        {
            $sValue = substr( $sValue, 0, strlen( $sValue ) - 3 );
        }
        
        $aParts = explode( ":", $sValue );
        
        if( count( $aParts ) < 2 || count( $aParts ) > 3 )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        if( !is_numeric( trim( $aParts[ 0 ] ) ) || !is_numeric( trim( $aParts[ 1 ] ) ) )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        if( intval( $aParts[ 0 ] ) < 1 || intval( $aParts[ 1 ] ) < 0 )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        if( intval( $aParts[ 0 ] ) > 12 || intval( $aParts[ 1 ] ) > 59 )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( true );

    } // ValidateTime()
    
    
    /**
    *
    *
    */
    public static function ValidateDomain( $sName, $sValue, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) .
                " is not a valid domain name";
        }
        
        $iMatches = preg_match( "/^([a-z0-9\-]{1,}\.){1,}[a-z]{2,}$/i", $sValue, $aMatches );
        
        if( $iMatches === false || $iMatches <= 0 )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( $iMatches > 0 );
    
    } // ValidateDomain()
    
    
    /**
     *
     *
     */                          
    public static function ValidateLuhnNumber( $sName, $sValue, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) .
                " is not a valid Luhn number.";
        }
    
    
        // Remove any non-numeric characters (such as dashes)
        $sValue = preg_replace( "/\D/", "", $sValue );

        $iLength = strlen( $sValue );
        $sParity = $iLength % 2;

        $iTotal = 0;
        
        for( $i = 0; $i < $iTotal; $i++ )
        {
            $iDigit = $sValue[ $i ];

            if( $i % 2 == $sParity )
            {
                $iDigit *= 2;

                if( $iDigit > 9 )
                {
                    $iDigit -= 9;
                }
            }
        
            $iTotal += $iDigit;
        }
        
        
        if( $iTotal % 10 != 0 )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( true );

    } // ValidateLuhnNumber()
    
    
    /**
     *
     *
     */
    public static function ValidateVisa( $sName, $sValue, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) .
                " is not a valid credit card number.";
        }
        
        $sValue = trim( str_replace( "-", "", $sValue ) );
        
        if( substr( $sValue, 0, 1 ) != "4" || strlen( $sValue ) != 16 )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( self::ValidateLuhnNumber( $sName, $sValue, $sMessage ) );
    
    } // ValidateVisaCard()
    
    
    /**
     *
     *
     */
    public static function ValidateMasterCard( $sName, $sValue, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) .
                " is not a valid credit card number.";
        }
        
        $sValue = trim( str_replace( "-", "", $sValue ) );
        
        if( substr( $sValue, 0, 2 ) != "51" || 
            substr( $sValue, 0, 2 ) != "55" || 
            strlen( $sValue ) != 16 )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( self::ValidateLuhnNumber( $sName, $sValue, $sMessage ) );        
    
    } // ValidateVisaCard()
    
    
    /**
     *
     *
     */
    public static function ValidateDiscover( $sName, $sValue, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) .
                " is not a valid credit card number.";
        }
        
        $sValue = trim( str_replace( "-", "", $sValue ) );
        
        if( substr( $sValue, 0, 1 ) != "6" || strlen( $sValue ) != 16 )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( self::ValidateLuhnNumber( $sName, $sValue, $sMessage ) );        
    
    } // ValidateDiscover()
    
    
    /**
     *
     *
     */
    public static function ValidateAmericanExpress( $sName, $sValue, $sMessage = "" )
    {
        if( empty( $sMessage ) )
        {
            $sMessage = ucwords( str_replace( "_", " ", $sName ) ) .
                " is not a valid credit card number.";
        }
        
        $sValue = trim( str_replace( "-", "", $sValue ) );
        
        if( substr( $sValue, 0, 2 ) != "34" || 
            substr( $sValue, 0, 2 ) != "37" || 
            strlen( $sValue ) != 15 )
        {
            self::SetError( $sName, $sMessage );
            return( false );
        }
        
        return( self::ValidateLuhnNumber( $sName, $sValue, $sMessage ) );        
    
    } // ValidateAmericanExpress()

} // Validation()

?>
