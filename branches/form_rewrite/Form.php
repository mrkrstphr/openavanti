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
         * Stores all the elements assigned to this form
         */
        public $_elements = array();
        
        /**
         * Stores the loaded or submitted data for this form
         */
        public $_data = array();
        
        
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
            
            return $this->_data;
            
        } // loadSanitizedRequest()
        
        
        /**
         * Adds a new form element to this form class.
         *
         * @param FormElement $element The FormElement to add to this form
         * @return void
         */
        public function addElement(FormElement $element)
        {
            $this->_elements[$element->getName()] = $element;
            
        } // addElement()
        
        
        /**
         * Returns the requested form element, or null if not found
         *
         * @param string $name The name of the form element to retrieve
         */
        public function get($name)
        {
            return $this->getElement($name);
            
        } // get()
        
        
        /**
         * Returns the requested form element, or null if not found
         *
         * @param string $name The name of the form element to retrieve
         */
        public function &getElement($name)
        {
            $element = null;
            
            if(isset($this->_elements[$name]))
            {
                $element = $this->_elements[$name];   
            }
            
            return $element;
        
        } // getElement()
        
        
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
            $elementName = "{$name}Element";
            
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
         *
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
