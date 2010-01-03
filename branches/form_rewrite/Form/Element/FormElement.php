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
     * 
     *
     * @category    Forms
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/form
     */
    abstract class FormElement
    {
        /**
         *
         */
        protected $_name = array();
        
        /**
         *
         */
        protected $_attributes = array();
        
        /**
         *
         */
        protected $_value = null;
        
        
        /**
         *
         *
         */
        public function __construct($name, $attributes)
        {
            $this->_name = $name;
            $this->_attributes = $attributes;
            
            $this->init();
            
        } // __construct()
        
        
        /**
         *
         *
         */
        public function init()
        {
            
        } // init()
        
        
        /**
         *
         */
        public function getName()
        {
            return $this->_name;
            
        } // getName()
        
        
        /**
         *
         */
        public function getValue()
        {
            return $this->_value;
        
        } // getValue()
        
        
        /**
         *
         */
        public function setValue($value)
        {
            $this->_value = $value;
            
        } // setValue()
        
        
        /**
         *
         *
         */
        public function setAttribute($name, $value)
        {
            $this->_attributes[$name] = $value;
            
        } // setAttribute()
        
        
        /**
         *
         *
         */
        public function getAttributes()
        {
            return $this->_attributes;
            
        } // getAttributes()
        
        
        /**
         *
         *
         */
        public function getAttribute($attribute)
        {
            if(isset($this->_attributes[$attribute]))
            {
                return $this->_attributes[$attribute];
            }
            
            return null;
            
        } // getAttribute()
        
        
        /**
         *
         *
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
            
        } // generateAttributesString()
        
        
        /**
         *
         *
         */
        abstract public function render();
        
        
        /**
         *
         *
         */
        public function __toString()
        {
            return $this->render();
            
        } // __toString()

    } // FormElement()

?>
