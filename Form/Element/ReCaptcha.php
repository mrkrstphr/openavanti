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

require_once __DIR__ . "/Element.php";

/**
 *
 * @category    Forms
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/form
 */
class ReCaptcha extends Element
{
    /**
     * Renders the form element as HTML and returns the HTML string
     *
     * @return string The HTML of the rendered form element
     */
    public function render()
    {
        $recaptcha = new \OpenAvanti\Service\ReCaptcha($this->_attributes['publicKey'], null);

        return $recaptcha->getHtml();

    } // render()

    
    /**
     *
     *
     */
    public function setPublicKey($key)
    {
        $this->_attributes['publicKey'] = $key;

    } // setPublicKey()

} // ReCaptcha()

?>
