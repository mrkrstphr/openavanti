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


namespace OpenAvanti;

use \Exception;

/**
 * A library of form field generation helpers, mainly useful automate data preservation on
 * form errors   
 *
 * @category    Forms
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/form
 */
class Form 
{
    /**
     * Used to set auto references to use the id of the form element
     */
    const AutoReferenceStringID = "id";
    
    /**
     * Used to set auto references to use the name of the form element
     */
    const AutoReferenceStringName = "name";
    
    /**
     * Stores all the elements assigned to this form
     */
    public $_elements = array();
    
    /**
     * Stores the loaded or submitted data for this form
     */
    public $_data = array();
    
    /**
     *
     */
    private $_autoReferenceString = "id";
    
    
    /**
     * Sets up the form class and calls the init() method for user initialization.
     *
     * @param array $initialData Optional array of data to load into the form class
     */
    final public function __construct($initialData = null)
    {
        if(!empty($initialData))
        {
            $this->loadData($initialData);
        }
        
        $this->init();
        
    } // __construct()
    
    
    /**
     * Provides a mechanism for user initialization outside of the constructor. 
     * 
     * @return void
     */
    public function init()
    {
        // nothing here in the base class!
        
    } // init()
    
    
    /**
     * Loads the specified array or object into the classes aFields array. These values are 
     * later used by the field generation helpers for setting the value of the form field. This 
     * method will recursively iterate through a multidimensional array, or an object with 
     * member objects to load all data within the array or object.       
     * 
     * @param mixed An array or object of keys and values to load into the forms data array
     * @param array 
     * @return void
     */
    public function loadData($data, &$target = null)
    {
        is_null($target) ? $target = &$this->_data : $target = $target;
        
        foreach($data as $key => $value)
        {
            if(is_scalar($value))
            {
                $target[$key] = $value;
            }
            elseif(is_object($value) || is_array($value))
            {
                if(!isset($target[$key]))
                {
                    $target[$key] = array();
                }
                
                $this->loadData($value, $target[$key]);
            }
        }
        
        $this->propagateFormValues();
        
    } // loadData()
    
    
    /**
     * Loads data from the POST request, sanitizes it and stores it in this
     * form object.
     *
     * @return array The loaded data
     */
    public function loadSanitizedPost()
    {
        $data = $_POST;
        
        // TODO sanitize the data
        
        $this->_data += $data;
        
        $this->propagateFormValues();
        
        return $this->_data;
    
    } // loadSanitizedPost()
    
    
    /**
     * Loads data from the GET request, sanitizes it and stores it in this
     * form object.
     *
     * @return array The loaded data
     */
    public function loadSanitizedGet()
    {
        $data = $_GET;
        
        // TODO sanitize the data
        
        $this->_data += $data;
        
        $this->propagateFormValues();
        
        return $this->_data;
    
    } // loadSanitizedGet()
    
    
    /**
     * Loads data from the request (POST + GET), sanitizes it and stores it
     * in this form object.
     *
     * @return array The loaded data
     */
    public function loadSanitizedRequest()
    {
        $this->loadSanitizedGet();
        $this->loadSanitizedPost();
        
        $this->propagateFormValues();
        
        return $this->_data;
        
    } // loadSanitizedRequest()
    
    
    /**
     * Loops each form element and attempts to populate it with a value,
     * either user supplied, or from GET or POST. This method is called by
     * the load() methods to propagate form values to the form elements.
     */
    protected function propagateFormValues()
    {
        foreach($this->_elements as &$element)
        {
            $value = $this->translatePathForValue($element->getName());
            
            if(!empty($value))
            {
                $element->setValue($value);
            }
        }
        
    } // propagateFormValues()
    
    
    /**
     * Adds a new form element to this form class.
     *
     * @param FormElement $element The FormElement to add to this form
     */
    public function &addElement(FormElement $element)
    {
        $this->_elements[] = $element;
        
        return $element;
        
    } // addElement()
    
    
    /**
     * Sets the auto reference string to either id or name, which controls
     * which attribute __get() and getElement() use to find an element
     *
     * @param string $reference Either of the AutoReference constants
     * @return Form This object to use in chaining
     */
    public function autoReferenceString($reference)
    {
        if(!in_array($string, array(self::AutoReferenceID, self::AutoReferenceName)))
        {
            throw new Exception("Unknown reference string provided: {$reference}");
        }
        
        $this->_autoReferenceString = $reference;
        
    } // autoReferenceString()
    
    
    /**
     * Returns the requested form element, or null if not found. By default,
     * this method searches for the element by ID. This can be changed
     * to search by name by using the autoReferenceString method and
     * passing Form::AutoReferenceName. 
     *
     * @param string $reference The name of the form element to retrieve
     * @return FormElement The requested form element
     */
    public function __get($reference)
    {
        return $this->getElement($reference);
        
    } // __get()
    
    
    /**
     * Returns the requested form element, or null if not found. By default,
     * this method searches for the element by ID. This can be changed
     * to search by name by using the autoReferenceString method and
     * passing Form::AutoReferenceName. 
     *
     * @param string $reference The reference for the form element to
     *      retrieve
     * @return FormElement The requested form element
     */
    public function &getElement($reference)
    {
        if($this->_autoReferenceString == self::AutoReferenceStringID)
        {
            return $this->getElementById($reference);
        }
        else
        {
            return $this->getElement($reference);
        }
    
    } // getElement()
    
    
    /**
     * Finds and returns an element by the name attribute
     *
     * @param string $name The name of the attribute
     * @return FormElement The element being sought
     */
    public function &getElementByName($name)
    {
        $element = null;
        
        foreach($this->_elements as $testElement)
        {
            if($testElement->getName() == $name)
            {
                $element = &$testElement;
            }
        }
        
        return $element;
    
    } // getElementByName()
    
    
    /**
     * Finds and returns an element by the id attribute
     *
     * @param string $id The id of the attribute
     * @return FormElement The element being sought
     */
    public function &getElementById($id)
    {
        $element = null;
        
        foreach($this->_elements as &$testElement)
        {
            if($testElement->getId() == $id)
            {
                $element = &$testElement;
            }
        }
        
        return $element;
    
    } // getElementById()
    
    
    /**
     * Provides a mechanism for easily generating form fields and adding
     * them to the form class
     *
     * @param string $name The name of the element to add, which should
     *      match the name of a valid FormElement class
     * @param string $arguments Arguments to pass to the constructor of
     *      the FormElement
     * @return FormElement The generated form element
     */
    public function __call($name, $arguments)
    {
        $element = null;
        
        if($this->elementExists($name))
        {
            $elementName = "{$name}Element";
            
            $element = new $elementName($arguments);
            
            return $element;
        }
        
        throw new Exception("Unknown form element {$name}");
        
    } // __callStatic()
    
    
    /**
     * Determines if a form element class exists and is properly subclassed
     *
     * @param string The suffix of the element class
     * @return boolean True if the element class exists and is valid,
     *      false otherwise
     */
    protected function elementExists($elementType)
    {
        $elementName = "{$elementType}Element";
        
        if(class_exists($elementName, true))
        {
            if(is_subclass_of($elementName, "FormElement"))
            {
                return true;
            }
        }
        
        return false;
        
    } // elementExists()
    
    
    /**
     * This internal method is used to find the value of a form field, by
     * name, working with array elements to resolve them.
     *
     * @param string $name The name attribute of the form field
     * @return string The value of the form field
     */
    private function translatePathForValue($name)
    {
        $value = false;
        
        $path = str_replace("[", "/", $name);
        $path = str_replace("]", "", $path);
        
        $keys = explode("/", $path);
        
        $data = $this->_data;
        
        foreach($keys as $key)
        {
            if(isset($data[$key]))
            {
                $data = $data[$key];
            }
            else
            {
                return false;
            }
        }
        
        if(!is_array($data))
        {
            $value = $data;
        }
        
        if($value === true)
        {
            $value = "t";
        }
        else if($value === false)
        {
            $value = "f";
        }
        
        return $value;
        
    } // translatePathForValue()
    
} // Form()

?>
