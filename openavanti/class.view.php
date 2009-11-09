<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @dependencies    Controller
 * @copyright       Copyright (c) 2007-2009, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.2.0-beta
 */
 
 
    /**
     * A View class for handling the UI
     *
     * @category    Controller
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/view
     */
    class View
    {
        // Stores a reference to the controller that is preparing this view:
        protected $oController = null;
        
        // Stores data used by the actual view file for redering:
        public $aData = array();
        
        // Stores the default layout to use if no other is specified:
        public static $sDefaultLayout = "";
        
        // Stores the layout to render, overriding any default specified:
        public $sLayout = "";
        
        // Stores the view file to render inside the layout through 
        // GetContent():
        public $sView = "";
        
        // Toggles whether to render the layout:
        public $bRenderLayout = true;
        
        // Toggles whether to render the view:
        public $bRenderView = true;
        
        
        /**
         * The final constructor; sets up data for the controller and calls init()
         * 
         * @final
         * @argument Dispatcher The dispatcher class that loaded this controller
         * @argument string Optional; the name of the view file to render
         * @returns void
         */
        public final function __construct( &$oController, $sViewFileName = "" )
        {
            $this->oController = &$oController;
            
            $this->sView = $sViewFileName;

            $this->init();
            
        } // __construct()
        
        
        /**
         * Provides initialization mechanism for the View class and is called by the
         * constructor. Subclasses cannot override the constructor due to the possibility of not
         * passing the correct required parameters.
         * 
         * @returns void
         */
        public function init()
        {
            
        } // init()
        
        
        
        /**
         * Returns a copy of the Controller class that invoked this view
         * 
         * @returns Dispatcher The Controller class
         */
        public function getController()
        {
            return( $this->oController );
        
        } // GetController()
        
        
        /**
         * Sets the layout file to use for this controller. 
         * 
         * @argument string The file name of the layout file that should be 
         *      rendered
         * @returns void
         */
        public function setLayout( $sLayoutFile )
        {
            $this->sLayout = $sLayoutFile;
            
        } // SetLayout()
        
        
        /**
         * 
         * @static 
         * @argument String The file name of the layout that should be 
         *      rendered by default if no other layout file is specified
         * @returns void
         */
        public static function setDefaultLayout( $sLayoutFile )
        {
            self::$sDefaultLayout = $sLayoutFile;
            
        } // SetDefaultLayout()
        
        
        /**
         * Sets the view file that should be loaded at the end of the request. This method does not
         * check to ensure that the file specified actually exists. It is up to the code that loads
         * the view file to do this (normally the Dispatcher class).                 
         *       
         * @argument string The file name of the view file that should be loaded.
         * @returns void
         */ 
        public function setView( $sView )
        {
            $this->sView = $sView;
        
        } // SetView()
        
        
        /**
         * Responsible for rendering the page, or the layout file specifically. 
         * The view file will be rendered when GetContent() is called by
         * the layout. 
         * 
         * @returns void
         */
        public function renderPage()
        {
            // TODO Finalize this code
            
            /*if( $this->oController->Is404Error() )
            {
                return( $this->HandleError( ErrorHandler::FILE_NOT_FOUND ) );
            }
            else*/ 
            if( !empty( $this->sView ) )
            {
                if( $this->bRenderLayout )
                {
                    // TODO deprecate this

                    extract( $this->aData );

                    if( !empty( $this->sLayout ) )
                    {
                        require( $this->sLayout );
                    }
                    else if( !empty( self::$sDefaultLayout ) )
                    {
                        require( self::$sDefaultLayout );
                    }
                }
            }

        } // RenderPage()


        /**
         * Called from the layout file to render the action specific view file 
         * into the layout.
         * 
         * @returns void
         */
        public function RenderContent()
        {
            // TODO deprecate this
            
            extract( $this->aData );

            if( ( $sView = FileFunctions::FileExistsInPath( $this->sView ) ) !== false )
            {
                require( $sView );
            }
            else
            {
                // FIXME this method doesn't exist 
                
                $this->HandleError( ErrorHandler::VIEW_NOT_FOUND );
            }
            
        } // RenderContent()
        
        
        /**
         * Used by the view file to get a data variable, which are stored in 
         * the aData array and are settable through __set(), usually by the
         * Controller.
         * 
         * 
         * @argument string The name of the data variable being retrieved
         * @returns void
         */
        public function __get( $sName )
        {
            return( $this->aData[ $sName ] );
            
        } // __get()
        
        
        /**
         * Sets a view file data variable to be used by the view file, and is
         * usually called by the Controller.
         * 
         * @argument string The name of the data variable being set
         * @argument string The value of the data variable being set
         * @returns void
         */
        public function __set( $sName, $sValue )
        {
            $this->aData[ $sName ] = $sValue;
            
        } // __set()

    } // View()

?>
