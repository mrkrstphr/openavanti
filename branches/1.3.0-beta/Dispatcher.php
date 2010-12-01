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

namespace OpenAvanti;

use \Exception;

 
/**
 * Dispatcher to route URI request to appropriate controller / method, and loads view files
 * based on instructions from the controller, passing data setup by the controller from the
 * controller to the view file.          
 *
 * @category    Database
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/dispatcher
 */
class Dispatcher
{
    // Stores a list of routes that determine how to map a URI:
    protected $_routes = array();
    
    // Stores the name of the error controller that will handle 
    // dispatching errors:
    protected $_errorController = 'ErrorController';
    
    /**
     * Stores a reference to the Application object for this application instance
     */
    protected $_application = null;
    
    // Stores a reference to the Request object for this request:
    protected $_request = null;
    
    // Stores a reference to the Response object for this transaction:
    protected $_response = null;
    
    // Stores a reference to the Controller that will handle this requet:
    protected $_controller = null;
    
    // Stores a reference to the View that will render the page:
    protected $_view = null;
    
    /**
     * A set of callback methods to invoke before the dispatching process
     */
    protected $_preDispatchCallbacks = array();
    
    /**
     * A set of callback methods to invoke after the dispatching process
     */
    protected $_postDispatchCallbacks = array();


    /**
     * The constructor is responsible for accepting and storing a reference to the Application
     * object that created this dispatcher. 
     *
     * @param Application $application The application object that created this dispatcher
     */
    public function __construct(Application &$application)
    {
        $this->_application = &$application;
    }


    /**
     * Sets up a custom route to match a URI against when dispatching the URI. A custom route
     * is simply run by running a preg_match against the URI, and rewriting it to the 
     * replacement on a valid match using preg_replace. 
     * 
     * @argument string The pattern to match against
     * @argument string The string to rewrite to
     * @returns void
     */
    public function addRoute($pattern, $replacement)
    {
        $this->_routes[] = array(
            'pattern' => $pattern,
            'replace' => $replacement
        );
    }

    
    /**
     * Returns a reference to the Application class for this application instance (responsible for
     * creating the Dispatcher that handled the current request and loaded this controller)
     *
     * @returns Application The Application class for this application instance
     */
    public function &getApplication()
    {
        return $this->_application;
    }


    /**
     * Returns the Request object for the current request
     * 
     * @returns Request The current Request object
     */
    public function &getRequest()
    {
        return $this->_request;
    }


    /**
     * Returns the Request object for the current request
     * 
     * @returns Request The current Request object
     */
    public function &getResponse()
    {
        return $this->_response;
    }
    
    
    /**
     *
     *
     * @param string $controllerName
     * @return string
     */
    protected function normalizeControllerName($controllerName)
    {
        $controllerName = ucwords(str_replace(array('-', '_'), ' ', $controllerName));
        $controllerName = str_replace(' ', '', $controllerName);
        
        return $controllerName;
    }
    

    /**
     * Routes the specified request to an associated controller and action (class and method). 
     * Loads any specified view file stored in the controller and passes along any data stored
     * in the controller. 
     * 
     * This method checks for custom routes first (see AddRoute()), before checking for standard
     * routes. A standard route is a UI formed as follows /[controllername]/[methodname] where
     * [controllername] is the name of the controller, without the word "controller"
     * 
     * A standard controller is named like: ExampleController. To invoke the index method of 
     * this controller, one would navigate to /example/index.
     * 
     * The data loaded via the controller's SetData() method is exploded and available for the
     * view file.                                    
     * 
     * @argument string The current request URI
     * @returns void
     */
    public function connect($requestUri)
    {
        $this->_request = new Request();
        $this->_request->_uri = $requestUri;

        $this->_response = new Response();

        $controller = '';
        $action = '';
        $arguments = array();

        $requestUri = trim($requestUri, '/');

        // Explode the request on /

        $request = explode('/', $requestUri);

        $moduleName = 'default';

        if($this->getApplication()->getUseModules())
        {
            $moduleName = count($request) > 0 ? $request[0] : 'default';

            if($this->getApplication()->moduleExists($moduleName))
                array_shift($request);
            else
                $moduleName = 'default';
        }

        // Tell the Application class to initalize our module:

        $this->getApplication()->moduleInitialization($moduleName);

        // Apply any URI rewritting rules now that we've removed the module:

        $requestUri = implode('/', $request);
        
        foreach($this->_routes as $route)
            if(preg_match($route['pattern'], $requestUri) != 0)
                $requestUri = preg_replace($route['pattern'], $route['replace'], $requestUri);
        
        $this->_request->_rewrittenUri = $requestUri;

        $request = explode('/', $requestUri);

        // normalize the controller -
        // example action_items should become ActionItemsController

        $controllerName = count($request) > 0 ? array_shift($request) : 'index';
        $controllerName = !empty($controllerName) ? $controllerName : 'index';
        $controllerName = $this->normalizeControllerName($controllerName);

        $this->_request->_controllerName = $controllerName . 'Controller';

        $actionName = count($request) > 0 ?
            str_replace('-', '_', array_shift($request)) : 'index';

        $actionName = \OpenAvanti\Util\String::toCamelCase($actionName);
        $actionName .= 'Action';

        $this->_request->_actionName = $actionName;

        $this->_request->_arguments = !empty($request) ? $request : array();

        $this->preDispatch();
        
        // If we've found a controller and the class exists:
        if(!empty($this->_request->_controllerName) && 
            class_exists($this->_request->_controllerName, true))
        {
            // Replace our empty controller with the routed one:                
            $this->_controller = new $this->_request->_controllerName($this);
            
            $this->_view = $this->_controller->getView();
            
            // Attempt to invoke an action on this controller:              
            $this->invokeAction();
        }
        else
        {
            // If we can't find the controller, we must throw an exception:
            throw new ControllerNotFoundException('Controller "' . $controllerName . '" does not exist');
        }
        
        $this->postDispatch();
        
        // Continue on with the view loader method which will put the appropriate presentation
        // on the screen:
        
        $this->_response->sendHeaders();
        
        echo $this->_view->renderPage();
        
        return $this->_request;
    }
    
    
    /**
     * Registers a preDispatch callback with this dispatcher. Callbacks
     * are called in the order they are registered.
     *
     * @param callback $callback The callback to invoke 
     * @return void
     */
    public function registerPreDispatchMethod($callback)
    {
        $this->_preDispatchCallbacks[] = $callback;
    }
    
    
    /**
     * Registers a postDispatch callback with this dispatcher. Callbacks
     * are called in the order they are registered.
     *
     * @param callback $callback The callback to invoke
     * @return void
     */
    public function registerPostDispatchMethod($callback)
    {
        $this->_postDispatchCallbacks[] = $callback;
    }
    
    
    /**
     * The preDispatch loop which calls each registered preDispatch
     * callback in the order they were registered. Passed to the callback
     * is a reference to this dispatcher.
     *
     * @return void
     */
    public function preDispatch()
    {
        foreach($this->_preDispatchCallbacks as $callback)
        {
            $dispatcher = &$this;
            
            if(is_callable($callback))
            {
                call_user_func_array($callback, array($dispatcher));
            }
        }
    }
    
    
    /**
     * The postDispatch loop which calls each registered postDispatch
     * callback in the order they were registered. Passed to the callback
     * is a reference to this dispatcher.
     *
     * @return void
     */
    public function postDispatch()
    {
        foreach($this->_postDispatchCallbacks as $callback)
        {
            $dispatcher = &$this;
            
            if(is_callable($callback))
            {
                call_user_func_array($callback, array($dispatcher));
            }
        }
    }
    
    
    /**
     * Called from connect(), responsible for calling the method of the controller
     * routed from the URI
     * 
     * @returns void
     */
    protected function invokeAction()
    {
        // is_callable() is used over method_exists() in order to properly utilize __call()
        
        if(!empty($this->_request->_actionName) && 
            method_exists($this->_controller, $this->_request->_actionName))
        {
            $reflection = new \ReflectionMethod($this->_request->_controllerName, $this->_request->_actionName);

            $numReqArgs = $reflection->getNumberOfRequiredParameters();

            if($numReqArgs > count($this->_request->_arguments))
                throw new ControllerActionNotFoundException('Controller with matching arguments not found');

            foreach($this->_request->_arguments as &$arg)
                $arg = urldecode($arg);

            // Call $oController->$sAction() with arguments $aArguments:
            call_user_func_array(array($this->_controller, $this->_request->_actionName), 
                $this->_request->_arguments);
        }
        else if(empty($this->_request->_actionName))
        {
            // Default to the index file:
            $this->_controller->index();
        }
        else
        {
            // Action is not callable, throw an exception:
            
            throw new ControllerActionNotFoundException();
        }
    }
    
}


