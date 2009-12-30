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
        protected $_options = array();
        
        /**
         *
         */
        protected $_value = null;
        
        
        /**
         *
         *
         */
        final public function __construct($name, $options)
        {
            $this->_name = $name;
            $this->_options = $options;
            
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
         *
         */
        public function setOption($name, $value)
        {
            $this->_options[$name] = $value;
            
        } // setOption()
        
        
        /**
         *
         *
         */
        public function getOptions()
        {
            return $this->_options;
            
        } // getOptions()
        
        
        /**
         *
         *
         */
        public function getOption($option)
        {
            if(isset($this->_options[$option]))
            {
                return $this->_options[$option];
            }
            
            return null;
            
        } // getOption()
        
        
        /**
         *
         *
         */
        protected function generateAttributeString()
        {
            $options = "";
            
            foreach($this->_options as $key => $value)
            {
                if(!is_scalar($value)) continue;
                
                $options = !empty($options) ? " " : "";
                $options = "{$key}=\"{$value}\"";
            }
            
            return $options;
            
        } // generateAttributesString()
        
        
        /**
         *
         *
         */
        public function render();
        
        
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
