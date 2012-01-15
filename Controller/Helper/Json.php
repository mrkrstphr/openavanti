<?php
/**
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5.3+
 *
 * @author          Kristopher Wilson
 * @copyright       Copyright (c) 2007-2012, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.3.0-beta
 */

namespace OpenAvanti\Controller\Helper;

/**
 * This controller helper facilitates in passing back JSON data to the browser
 * during an AJAX request by disabling normal view rendering, setting the
 * response content-type to 'application/json' and JSON formatting the passed
 * data.
 */
class Json extends \OpenAvanti\Controller\HelperAbstract
{
    /**
     * Prepares a JSON response to the browser.
     *
     * @param string $data The JSON data to return to the browser
     */
    public function process($data)
    {
        $this->_controller->getView()->disableAllRendering();
        
        $this->_controller->getResponse()->setHeader('Content-type', 'application/json');
        
        echo json_encode($data);
    }
}
