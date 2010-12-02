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

namespace OpenAvanti\Form\Element;

require_once __DIR__ . "/../Element.php";

use \OpenAvanti\Form\Element;

/**
 * A specialized FormElement subclass that combines any inherited class
 * with a LabelElement based on the assumption that most FormElements also
 * have an associated LabelElement to go with them.
 *
 * @category    Forms
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/form
 */
abstract class Labeled extends Element
{
    /**
     * @var LabelElement The label for this FormElement
     */
    protected $_label = null;
    
    
    /**
     * Sets up the FormElement and the LabelElement
     *
     * @param string $name The name attribute of the FormField
     * @param string $label The text of the label of the FormField
     * $param array $attributes An array of node attributes for the
     *      FormField
     */
    public function __construct($name, $label = null, $attributes = array())
    {        
        parent::__construct($name, $attributes);
        
        
        $this->label = new Label($this->_id, $label);
    }
    
    
    /**
     * Returns the LabelElement for the FormField
     *
     * @return LabelElement The label for the FormField
     */
    public function &getLabel()
    {
        return $this->_label;
    }
    
    
    /**
     * Provides read only access to protected or private attributes, or
     * magic attributes that don't exist.
     *
     * Current provides access to the form element's label through
     * "label"
     *
     * @param string $name The name of the attribute being accessed
     * @return string The value of the attribute being accessed.
     */
    public function __get($name)
    {
        if($name == "label")
        {
            return $this->getLabel();
        }
    }

}

