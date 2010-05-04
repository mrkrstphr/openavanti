<?php
/**
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson <kwilson@shuttlebox.net>
 * @copyright       Copyright (c) 2007-2010, Kristopher Wilson
 * @package         openavanti 
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         SVN: $Id$
 */

namespace OpenAvanti\Service;

/**
 * An implementation of the ReCaptcha API.
 *
 * @category    Controller
 * @author      Kristopher Wilson
 * @package     openavanti
 * @link        http://www.openavanti.com/documentation/1.4.0/ServiceRecaptcha
 */
class ReCaptcha 
{
    /**
     *
     */
    const API_SERVER = 'http://api.recaptcha.net';
    
    /**
     *
     */
    const API_SERVER_SECURE = 'https://api-secure.recaptcha.net';
    
    /**
     *
     */
    const API_SERVER_VERIFY = 'http://api-verify.recaptcha.net';
   
    /**
     *
     */
    private $_httpClient = null;

    /**
     *
     */
    private $_publicKey = '';
    
    /**
     *
     */
    private $_privateKey = '';
    
    
    /**
     *
     *
     * @param string $publicKey
     * @param string $privateKey
     */
    public function __construct($publicKey, $privateKey)
    {
        $this->_publicKey = $publicKey;
        $this->_privateKey = $privateKey;

        $this->_httpClient = new \OpenAvanti\Http\Client();
    }

    
    /**
     *
     *
     * @param string $challenge
     * @param string $response
     */
    public function verifyResponse($challenge, $response)
    {
        $postData = array(
            'privatekey' => $this->_privateKey, 
            'remoteip' => $_SERVER['REMOTE_ADDR'],
            'challenge' => $challenge, 
            'response' => $response
        );
        
        $response = $this->_httpClient->sendPost(self::API_SERVER_VERIFY, '/verify', $postData);

        $responseBody = explode("\n", $response->body);
        
        return trim($responseBody[0]) == 'true';
    }

    
    /**
     *
     *
     * @param bool $useSsl Optional; Should the JavaScript use SSL protocol? Default: false
     */
    public function getHtml($useSsl = false)
    {
        $server = $useSsl ? self::API_SERVER_SECURE : self::API_SERVER;
        
        $html = <<<HTML
<script type="text/javascript" src="{$server}/challenge?k={$this->_publicKey}"></script>

<noscript>
    <iframe src="{$server}/noscript?k={$this->_publicKey}" height="300" width="500" frameborder="0"></iframe><br/>
    <textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
    <input type="hidden" name="recaptcha_response_field" value="manual_challenge" />
</noscript>
HTML;

        return $html;
    }
    
}

