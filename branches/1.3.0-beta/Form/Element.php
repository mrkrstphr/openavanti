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

namespace OpenAvanti\Form;

/**
 * Abstract class for the definition of a basic HTML form element
 *
 * @category    Forms
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/form
 */
abstract class Element
{
    /**
     * @var string The name of the HTML form element
     */
    protected $_name = null;
    
    /**
     * @var string The ID of the HTML form element
     */
    protected $_id = null;
    
    /**
     * @var array Form element node attributes for the form element
     */
    protected $_attributes = array();
    
    /**
     * @var string The value of the form element
     */
    protected $_value = null;
    
    /**
     *
     */
    protected $_errors = array();
    
    /**
     * Constructs a form element object based on the supplied element name
     * and additional attributes. Note that the supplied attributes are not 
     * validated to be valid attributes for the element. Each element
     * provided is added to the XHTML tag.
     *
     * @param string $name The name of the HTML form element
     * @param array $attributes Additional node attributes for the form
     *      element
     */
    public function __construct($name, $attributes)
    {
        $this->_name = $name;
        
        // Normalize the name by removing array characters and adding
        // underscores in their place:
        
        $this->_id = str_replace(array("[", ".", "]"), array("_", "_", ""), $name);
        
        $this->_attributes = $attributes;
        
        $this->init();
    }
    
    /**
     * Provides a method for developers to setup element initialization
     * without having to bother with the constructor and properly passing
     * arguments to parent constructors
     */
    public function init()
    {
        
    }
    
    /**
     * Sets the name of the form element
     * 
     * @param string $name The name attribute for this form element
     * @return Element The form element for chaining
     */
    public function setName($name)
    {
        $this->_name = $name;
        
        return $this;
    }
    
    /**
     * Returns the name attribute of the form element
     *
     * @return string The name attribute of the form element
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * Sets the ID of the form element
     * 
     * @param string $id The ID attribute for this form element
     * @return Element The form element for chaining
     */
    public function setId($id)
    {
        $this->_id = $id;
        
        return $this;
    }
    
    /**
     * Returns the id attribute of the form element
     *
     * @return string THe id attribute of the form element
     */
    public function getId()
    {
        return $this->_id;
    }
    
    /**
     * Sets the value of the form element
     *
     * @param string $value The value for the form element
     * @return Element The form element for chaining
     */
    public function setValue($value)
    {
        $this->_value = $value;
        
        return $this;
    }
    
    /**
     * Returns the value of the form field element
     *
     * @return string The value of the form element
     */
    public function getValue()
    {
        return $this->_value;
    }
    
    /**
     * Sets an attribute of the form element
     *
     * @param string $name The name of the attribute to set
     * @param string $value The value of the attribute to set
     * @return Element The form element for chaining
     */
    public function setAttribute($name, $value)
    {
        $this->_attributes[$name] = $value;
        
        return $this;
    }
    
    /**
     * Sets the attributes for the form element, overwriting any existing
     * assignments.
     *
     * @param array $attributes The attributes to assign to the element
     * @return Element The form element for chaining
     */
    public function setAttributes(array $attributes)
    {
        $this->_attributes = $attributes;
        
        return $this;
    }
    
    /**
     * Appends the attributes for the form element with those supplied
     *
     * @param array $attributes The attributes to append to the element
     * @return Element The form element for chaining
     */
    public function appendAttributes(array $attributes)
    {
        $this->_attributes += $attributes;
        
        return $this;
    }
    
    /**
     * Returns an array of all attributes assigned to this form element
     *
     * @return array An array of attributes for this form element
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }
    
    /**
     * Returns the value of an attribute assigned to this form element
     *
     * @param string $attribute The name of the attribute to retrieve
     * @return string The value of the attribute
     */
    public function getAttribute($attribute)
    {
        if(isset($this->_attributes[$attribute]))
        {
            return $this->_attributes[$attribute];
        }
        
        return null;
    }
    
    /**
     * Loops all the attributes of this element and creates an HTML node
     * attribute string from the names and values.
     *
     * @return string The attributes of the form element as a string
     */
    protected function generateAttributeString()
    {
        $attributes = "";
        
        foreach($this->_attributes as $key => $value)
        {
            if(!is_scalar($value)) continue;
            
            $attributes .= !empty($attributes) ? " " : "";
            $attributes .= "{$key}=\"{$value}\"";
        }
        
        return $attributes;
    }
    
    /**
     * Returns whether the form element has errors.
     * 
     * @return bool
     */
    public function hasError()
    {
        return count($this->_errors) > 0;
    }
    
    /**
     * Sets errors on the form element.
     * 
     * @param array $errors
     * @return \OpenAvanti\Form\Element
     */
    public function setErrors(array $errors)
    {
        $this->_errors = $errors;
        return $this;
    }
    
    /**
     * Adds an error to the element's error list.
     * 
     * @param string $error 
     * @return \OpenAvanti\Form\Element
     */
    public function addError($error)
    {
        $this->_errors[] = $error;
        return $this;
    }
    
    /**
     * Returns an array of errors on this form element.
     * 
     * return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }
    
    /**
     * Returns the errors on this element as a string.
     * 
     * return string
     */
    public function getErrorString()
    {
        return implode(' ', $this->_errors);
    }
    
    /**
     * Renders the form element as HTML and returns the HTML string
     *
     * @return string The HTML of the rendered form element
     */
    abstract public function render();
    
    /**
     * An alias of render(), renders the form element as HTML and returns
     * the HTML string.
     *
     * @return string The HTML of the rendered form element
     */
    public function __toString()
    {
        return $this->render();
    }
}
