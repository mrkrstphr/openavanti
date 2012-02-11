<?php
/**
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5.3+
 *
 * @author          Kristopher Wilson
 * @copyright       Copyright (c) 2007-2011, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.3.0-beta
 */

namespace OpenAvanti;

use \Exception;

 
/**
 * Dispatcher to route URI request to appropriate controller / method, and
 * loads view files based on instructions from the controller, passing data
 * setup by the controller from the controller to the view file.
 */
class Dispatcher
{
    /**
     * Stores a list of routes that determine how to map a URI.
     * 
     * @var array $_routes
     */
    protected $_routes = array();
    
    /**
     * Stores the name of the error controller.
     * 
     * @var string $_errorController
     */
    protected $_errorController = 'ErrorController';
    
    /**
     * Stores the Application object for this application instance.
     * 
     * @var Application $_application
     */
    protected $_application = null;
    
    /**
     * Stores a reference to the Request object for this request.
     * 
     * @var Request $_request
     */
    protected $_request = null;
    
    /**
     * Stores a reference to the Response object for this transaction.
     * 
     * @var Response $_response
     */
    protected $_response = null;
    
    /**
     * Stores a reference to the Controller that will handle this request.
     * 
     * @var Controller $_controller
     */
    protected $_controller = null;
    
    /**
     * Stores a reference to the View that will render the page.
     * 
     * @var View $_view
     */
    protected $_view = null;
    
    /**
     * A set of callback methods to invoke before the dispatching process.
     * 
     * @var array $_preDispatchCallbacks
     */
    protected $_preDispatchCallbacks = array();
    
    /**
     * A set of callback methods to invoke after the dispatching process.
     * 
     * @var array $_postDispatchCallbacks
     */
    protected $_postDispatchCallbacks = array();


    /**
     * The constructor is responsible for accepting and storing a reference to the Application
     * object that created this dispatcher. 
     *
     * @param Application $application The application object that created this dispatcher
     */
    public function __construct(Application $application)
    {
        $this->_application = $application;
    }

    /**
     * Sets up a custom route to match a URI against when dispatching the URI. A custom route
     * is simply run by running a preg_match against the URI, and rewriting it to the 
     * replacement on a valid match using preg_replace. 
     * 
     * @param string $pattern The pattern to match against
     * @param string $replacement The string to rewrite to
     * @return \OpenAvanti\Dispatcher
     */
    public function addRoute($pattern, $replacement)
    {
        $this->_routes[] = array(
            'pattern' => $pattern,
            'replace' => $replacement
        );
        
        return $this;
    }
    
    /**
     * Returns a reference to the Application class for this application instance (responsible for
     * creating the Dispatcher that handled the current request and loaded this controller)
     *
     * @return \OpenAvanti\Application
     */
    public function getApplication()
    {
        return $this->_application;
    }
    
    /**
     * Returns the Request object for the current request
     * 
     * @return \OpenAvanti\Request
     */
    public function getRequest()
    {
        return $this->_request;
    }
    
    /**
     * Returns the Request object for the current request
     * 
     * @return \OpenAvanti\Response
     */
    public function getResponse()
    {
        return $this->_response;
    }
    
    /**
     * Returns the controller for this request
     *
     * @return \OpenAvanti\Controller
     */
    public function getController()
    {
        return $this->_controller;
    }
    
    /**
     * Takes a URI controller name and reforms it into a valid class name.
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
     * @param string $requestUri The current request URI
     */
    public function connect($requestUri)
    {
        $this->_request = new Request();
        $this->_request->setUri($requestUri);

        $this->_response = new Response();

        $controller = '';
        $action = '';
        $arguments = array();

        $requestUri = trim($requestUri, '/');

        // Explode the request on /

        $request = explode('/', $requestUri);

        $defaultModule = $this->getApplication()->getDefaultModule();
        
        $moduleName = $defaultModule;

        if ($this->getApplication()->getUseModules()) {
            $moduleName = count($request) > 0 ? $request[0] : $defaultModule;
            
            if($this->getApplication()->moduleExists($moduleName))
                array_shift($request);
            else
                $moduleName = $defaultModule;
        }

        // Tell the Application class to initalize our module:

        $this->getApplication()->moduleInitialization($moduleName);

        // Apply any URI rewritting rules now that we've removed the module:

        $requestUri = implode('/', $request);
        
        foreach ($this->_routes as $route) {
            if (preg_match($route['pattern'], $requestUri) != 0) {
                $requestUri = preg_replace($route['pattern'], $route['replace'], $requestUri);
            }
        }
        
        $this->_request->setRewrittenUri($requestUri);

        $request = explode('/', $requestUri);

        // normalize the controller -
        // example action_items should become ActionItemsController

        $controllerName = count($request) > 0 ? array_shift($request) : 'index';
        $controllerName = !empty($controllerName) ? $controllerName : 'index';
        $controllerName = 'controller\\' . $this->normalizeControllerName($controllerName);
        
        if ($this->getApplication()->getUseModules()) {
            $controllerName = $this->getApplication()->getCurrentModule() .
                '\\' . $controllerName;
        }
        
        $controllerName = '\\' . $this->getApplication()->getNamespace() .
            '\\' . $controllerName;
        
        $this->_request->setControllerName($controllerName);

        $actionName = count($request) > 0 ?
            str_replace('-', '_', array_shift($request)) : 'index';
        $actionName = \OpenAvanti\Util\String::toCamelCase($actionName);
        $actionName .= 'Action';

        $this->_request->setActionName($actionName);
        $this->_request->setArguments(!empty($request) ? $request : array());
        $this->_request->setModuleName($moduleName);
        
        $this->preDispatch();
        
        // If we've found a controller and the class exists:
        if ($this->_request->getControllerName() && class_exists($this->_request->getControllerName(), true)) {
            $controllerName = $this->_request->getControllerName();
            // Replace our empty controller with the routed one:                
            $this->_controller = new $controllerName($this);
            
            $this->_view = $this->_controller->getView();
            
            // Attempt to invoke an action on this controller:              
            $this->invokeAction();
        } else {
            // If we can't find the controller, we must throw an exception:
            throw new ControllerNotFoundException(
                'Controller "' . $this->_request->getControllerName() . '" does not exist'
            );
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
        foreach ($this->_preDispatchCallbacks as $callback) {
            $dispatcher = &$this;
            
            if (is_callable($callback)) {
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
        foreach ($this->_postDispatchCallbacks as $callback) {
            $dispatcher = &$this;
            
            if (is_callable($callback)) {
                call_user_func_array($callback, array($dispatcher));
            }
        }
    }
    
    /**
     * Called from connect(), responsible for calling the method of the controller
     * routed from the URI
     */
    protected function invokeAction()
    {
        // is_callable() is used over method_exists() in order to properly utilize __call()
        
        if ($this->_request->getActionName() && 
            method_exists($this->_controller, $this->_request->getActionName())) {
            $reflection = new \ReflectionMethod($this->_request->getControllerName(), $this->_request->getActionName());

            $numReqArgs = $reflection->getNumberOfRequiredParameters();

            if($numReqArgs > count($this->_request->getArguments()))
                throw new ControllerActionNotFoundException('Controller with matching arguments not found');

            $args = array();

            foreach($this->_request->getArguments() as $arg)
                $args[] = urldecode($arg);

            call_user_func_array(
                array($this->_controller, $this->_request->getActionName()), 
                $args
            );
        } else if (!$this->_request->getActionName()) {
            // Default to the index file:
            $this->_controller->index();
        } else {
            // Action is not callable, throw an exception:
            
            throw new ControllerActionNotFoundException();
        }
    }
}
