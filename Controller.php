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

namespace OpenAvanti;
 
/**
 * A default controller class to be extended
 *
 * @category    Controller
 * @author      Kristopher Wilson
 * @package     openavanti
 * @link        http://www.openavanti.com/documentation/1.4.0/Controller
 */
class Controller
{
    /** 
     * Stores a reference to the dispatcher that spawned this controller
     */
    protected $_dispatcher = null;
    
    
    /**
     * Stores a reference to the view file that will render the page
     */
    public $view = null;
    
    
    /**
     * The final constructor; sets up data for the controller and calls 
     * init() to do user intialization.
     * 
     * @param Dispatcher $dispatcher The dispatcher class that loaded this controller
     */
    public final function __construct(&$dispatcher)
    {
        $this->_dispatcher = &$dispatcher;
        
        $this->view = new View($this);
        
        $this->setDefaultView();
        
        $this->init();
        
    } // __construct()
    
    
    /**
     * Provides initialization mechanism for the Controller class and is called by the
     * constructor. Subclasses cannot override the constructor due to the possibility of not
     * passing the correct required parameters.
     */
    public function init()
    {
    
    } // init()
    
    
    /**
     * This method uses the name of a controller and the name of an action
     * to try to guess the name of the view file that should be loaded. 
     * The constructed view file name is in the form of: 
     *  
     *      [controller]/[action].php
     * 
     * If no arguments are passed to this method, the current controller
     * and action are pulled from the Request object to construct the 
     * view file name.
     * 
     * @param string $controller Optional; The name of the controller. Default: null
     * @param string $action Optional; The name of the action. Default: null
     */
    protected function setDefaultView($controller = null, $action = null)
    {
        if(empty($controller))
        {
            $controller = substr(get_class($this), 0, 
                strlen(get_class($this)) - strlen("Controller"));
        }
        
        if(empty($action))
        {
            $action = $this->getRequest()->getAction();
        }
    
        if(strtolower(substr($action, strlen($action) - 6)) == 'action')
        {
            $action = substr($action, 0, strlen($action) - 6);
        }

        $action = \OpenAvanti\Util\String::fromCamelCase($action, '-');

        $this->view->setViewScript(strtolower($controller . "/" . $action . View::$_viewFileExtension));
        
    } // setDefaultView()
    
    
    /**
     * Every controller must have an index() method defined for default requests to the 
     * controller that do not define a method. Since it is a requirement for this method to 
     * exist, it is defined in the parent controller class.
     */
    public function indexAction()
    {
        $this->getResponse()->set404Error( true );
            
    } // index()
    
    
    /**
     * Returns a copy of the Dispatcher class that handled the current request and loaded
     * this controller. 
     * 
     * @return Dispatcher The Dispatcher class that handled this request and loaded the 
     *      controller
     */
    public function &getDispatcher()
    {
        return $this->_dispatcher;
    
    } // getDispatcher()
    
    
    /**
     * Returns a reference to the Application class for this application instance (responsible for
     * creating the Dispatcher that handled the current request and loaded this controller)
     *
     * @return Application The Application class for this application instance
     */
    public function &getApplication()
    {
        return $this->getDispatcher()->getApplication();
        
    } // getApplication()
    
    
    /**
     * Returns a copy of the Dispatcher's Request object which contains information about
     * the current request.
     *
     * @returns Request The Request object containing information about the current request
     */
    public function &getRequest()
    {
        return $this->_dispatcher->getRequest();
        
    } // getRequest()
    
    
    /**
     * Returns a copy of the Dispatcher's Response object which contains information about
     * the HTTP response.
     *
     * @return Request The Response object containing information about the HTTP response
     */
    public function &getResponse()
    {
        return $this->_dispatcher->getResponse();
        
    } // getResponse()
    
    
    /**
     * This method redirects the browser to the specified URL. The second argument controls
     * whether or not the 301 HTTP response code is used to signal a permanent redirect. Using
     * this response code enable the user to hit refresh afterwards without resubmitting any
     * form data from the original request.
     * 
     * If headers have already been sent to the browser, this method will return false and will
     * not call the redirect. Otherwise this method will always return true.                                                     
     *
     * @param string $url The URL to redirect to 
     * @param bool $permanentRedirect Optional; True to signal a permanent redirect, false to 
     *      not set the HTTP response code. Default: true  
     * @return bool True if the redirect was sucessfull, false otherwise        
     */ 
    public function redirectTo($url, $permanentRedirect = true)
    {
        if(!headers_sent())
        {
            header("Location: {$url}", true, $permanentRedirect ? 301 : null);
            
            return true;
        }
        
        return false;
        
    } // redirectTo()
    
    
    /**
     * Similar to RedirectTo(), forwardAction() sends processing to the specified action, 
     * or controller and action. An optional arguments parameter allows you to pass information
     * to this action, and the view and data setup by the new action will be copied to this
     * controller.
     * 
     * @param string $action The action to which processing should be forwarded
     * @param string $controllerName Optional; The controller to which processing should be 
     *      forwarded to, used in conjunction with the specified action. Default: null
     * @param mixed $arguments Optional; The data to be forwarded as arguments to the action. Can 
     *      be a scalar value or an array of values. Default: null
     */
    public function forwardAction($action, $controllerName = null, $arguments = null)
    {
        $action = \OpenAvanti\Util\String::toCamelCase($action);

        if(strtolower(substr($action, strlen($action) - 6)) != 'action')
        {
            $action .= 'Action';
        }

        $this->setDefaultView($controllerName, $action);
        
        $controller = &$this;
        
        if(!is_null( $controllerName))
        {
            $controllerName = $controllerName . "Controller";
            $controller = new $controllerName($this->getDispatcher());
        }
        
        if(!is_callable(array($controller, $action)))
        {
            return;
        }
        
        if(is_array($arguments))
        {
            call_user_func_array(array($controller, $action), $arguments);
        }
        else if(is_scalar($arguments))
        {
            $controller->$action($arguments);
        }
        else
        {
            $controller->$action();
        }
        
        $this->view->_data = array_merge($this->view->_data, $controller->view->_data);
        
        $this->view->setViewScript($controller->view->getViewScript()); 
        
    } // forwardAction()
    
    
    /**
     * Returns a reference to the View object that will render the page
     * 
     * @return View A refernece to the View that will render the page
     */ 
    public function &getView()
    {
        return $this->view;
    
    } // getView()
    
    
    /**
     * Sets a session variable called flash with the supplied message. This can be used on a
     * redirect to display a success message (in conjunction with the RedirectTo() method).      
     *
     * If a flash message is already set, it will be overwritten on subsequent calls.
     * 
     * @param string $message The message to set in the flash session variable
     * @param string $scope Optional; The scope or name of the flash message to set. 
     *      Default: flash
     */ 
    public function setFlash($message, $scope = "flash")
    {
        $_SESSION[(string)$scope] = $message;
        
    } // setFlash()
    
    
    /**
     * Retrieves any flash message stored in the flash session variable, if any. See the
     * SetFlash() method.
     *
     * @param string $scope Optional; The scope or name of the flash message to get. 
     *      Default: flash
     * @return string The flash message, if any, stored in the session
     */ 
    public function getFlash($scope = "flash")
    {
        $flash = isset($_SESSION[(string)$scope]) ? 
            $_SESSION[(string)$scope] : "";
        
        unset($_SESSION[(string)$scope]);
        
        return $flash;
        
    } // getFlash()
    
    
    /**
     * __call() magic method setup to load action helpers as requested
     *
     * @param string $method The method being called, which translates to the helper class
     * @param array $arguments An array of arguments to pass to the render() method of the helper
     * @return mixed The return value of the helper, if any
     */
    public function __call($method, $arguments)
    {
        $method = $method . 'Helper';
        
        if($this->getApplication()->actionHelperExists($method))
        {
            return call_user_func_array(array($method, 'process'), $arguments);
        }
       
        throw new ControllerActionNotFoundException();

    } // __call()

} // Controller()

?>
