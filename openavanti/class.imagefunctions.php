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
     * A library for manipulating images
     *
     * @category    Images
     * @author      Kristopher Wilson
     * @link            http://www.openavanti.com/docs/imagefunctions
     */
    class ImageFunctions
    {
        
        /**
         *
         *
         */                     
        private function __construct()
        {
            // this class cannot be instantiated
            
        } // __construct()
        
        
        /**
         * This method creates a thumbnail image of the supplied image, assuming it exists,
         * based on the supplied width and height. The generated thumbnail will not be
         * these exact dimensions, but instead is generated based on these rules:
         * 
         * 1. If the width and height of the image are less than the supplied with and height for
         *    the thumbnail, then the actual size of the image is used.
         * 2. If the ratio of the max height / current height is < current height, then
         *    an image is generated with a height of height * ratio and width of max width.
         * 3. If the ratio of the max width / current width is < current width, then
         *    an image is generated with a width of width * ratio and height of max height.                                                              
         * 
         * @argument string The path and file name to the file to create a thumbnail from
         * @argument string The path and file name of the thumbnail to create
         * @argument array An array of width and height to limit the image to 0 => x, 1 => y
         * @returns bool True if the thumbnail is created, false otherwise
         */
        public static function generateThumb($fileName, $thumbName, $maxDimensions)
        {
            $maximumWidth = $maxDimensions[0];
            $maximumHeight = $maxDimensions[1];
            
            $fileExtension = FileFunctions::GetFileExtension($fileName);
            
            if(!file_exists($fileName))
            {   
                return false;
            }
        
            $imageResource = null;
            
            switch(strtolower($fileExtension))
            {
                case "jpg":
                case "jpeg":
                    $imageResource = imagecreatefromjpeg($fileName);
                break;
                
                case "gif":
                    $imageResource = imagecreatefromgif($fileName);
                break;
                
                case "png":
                    $imageResource = imagecreatefrompng($fileName);
                break;
                
                default:
                    throw new Exception("Invalid image type: {$fileExtension}");
            }
        
            $imageDetails = getimagesize($fileName);
        
            list($imageWidth, $imageHeight, $sType, $sAttr) = $imageDetails;
        
            $xRatio = $maximumWidth / $imageWidth;
            $yRatio = $maximumHeight / $imageHeight;


           if(($imageWidth <= $maximumWidth) && ($imageHeight <= $maximumHeight))
           {
              $newWidth = $imageWidth;
              $newHeight = $imageHeight;
           }
           else if(($xRatio * $imageHeight ) < $maximumHeight)
           {
              $newHeight = ceil($xRatio * $imageHeight);
              $newWidth = $maximumWidth;
           }
           else
           {
              $newWidth = ceil($yRatio * $imageWidth);
              $newHeight = $maximumHeight;
           }

            $thumbResource = imagecreatetruecolor( $newWidth, $newHeight );

            imagecopyresampled( $thumbResource, $imageResource, 0, 0, 0, 0, $newWidth,
                $newHeight, imagesx( $imageResource ), imagesy( $imageResource ) );

            switch(strtolower($fileExtension))
            {
                case "jpg":
                case "jpeg":
                    imagejpeg($thumbResource, $thumbName);
                break;
                
                case "gif":
                    imagegif($thumbResource, $thumbName);
                break;
                
                case "png":
                    imagepng($thumbResource, $thumbName);
                break;
            }
        
            imagedestroy($thumbResource);
            imagedestroy($imageResource);
            
            
            return true;         
            
        } // generateThumb()
        
        
    } // ImageFunctions()

?>
