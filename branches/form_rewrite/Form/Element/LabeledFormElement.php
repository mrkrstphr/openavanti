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
    abstract class LabeledFormElement extends FormElement
    {
        /**
         *
         */
        protected $_label = null;
        
        
        /**
         *
         *
         */
        public function __construct($name, $label, $attributes)
        {
            $this->label = new LabelElement($name, $label);
            
            parent::__construct($name, $attributes);
            
        } // __construct()
        
        
        /**
         *
         *
         */
        public function &getLabel()
        {
            return $this->_label;
            
        } // getLabel()
        
        
        /**
         *
         *
         */
        public function __get($name)
        {
            if($name == "label")
            {
                return $this->getLabel();
            }
            
        } // __get()

    } // LabeledFormElement()

?>
