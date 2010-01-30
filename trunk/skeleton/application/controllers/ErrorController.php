<?php


    class ErrorController extends Controller
    {
        
        public function error($errorCode)
        {
            switch($errorCode)
            {
                case ErrorHandler::VIEW_NOT_FOUND:
                    //echo 'View Not Found!';
                    break;
                
                default:
                    //echo '<pre>Unknown Error:'; debug_print_backtrace(); echo '</pre>';
            }
            
        } // error()
        
    } // ErrorController()


?>
