<?php

	class image
	{
	
		public function generateThumb( $filename, $thumbname, $size )
		{
			$maxWidth = $size[ 0 ];
			$maxHeight = $size[ 1 ];
			
			$ext = substr( $filename, strrpos( $filename, "." ) + 1 );
			
			if( file_exists( $filename ) )
			{	
				$img = null;
				
				switch( $ext )
				{
					case "jpg":
					case "jpeg":
						$img = imagecreatefromjpeg( $filename );
					break;
					
					case "gif":
						$img = imagecreatefromgif( $filename );
					break;
					
					case "png":
						$img = imagecreatefrompng( $filename );
					break;
					
					default:
						trigger_error( "Invalid image type: {$ext}", E_USER_ERROR );
						exit;
				}
				
				
				// stolen from luxury fabrics:
			
				$aImage = getimagesize( $filename );
			
			
				list( $width, $height, $type, $attr ) = $aImage;
			
			   $iMax_Width = is_null( $maxWidth ) ? $width : $maxWidth;
			   $iMax_Height = is_null( $maxHeight ) ? $height : $maxHeight;
			
			   $xRatio = $iMax_Width / $width;
			   $yRatio = $iMax_Height / $height;
			
			   if ( ( $width <= $iMax_Width ) && ( $height <= $iMax_Height ) )
			   {
			      $newWidth = $width;
			      $newHeight = $height;
			   }
			   else if ( ( $xRatio * $height ) < $iMax_Height )
			   {
			      $newHeight = ceil( $xRatio * $height );
			      $newWidth = $iMax_Width;
			   }
			   else
			   {
			      $newWidth = ceil( $yRatio * $width );
			      $newHeight = $iMax_Height;
			   }
			
			
				$img_thumb = imagecreatetruecolor( $newWidth, $newHeight );
			
				imagecopyresampled(
					$img_thumb,
					$img,
					0, 0, 0, 0,
					$newWidth,
					$newHeight,
					imagesx( $img ),
					imagesy( $img )
				);


			
				switch( $ext )
				{
					case "jpg":
					case "jpeg":
						imagejpeg( $img_thumb, $thumbname );
					break;
					
					case "gif":
						imagegif( $img_thumb, $thumbname );
					break;
					
					case "png":
						imagepng( $img_thumb, $thumbname );
					break;
				}
			
				imagedestroy ($img_thumb);
				imagedestroy ($img);
			}
			
		}
		
		
	}

?>
