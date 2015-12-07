<?php

/**
 * class ImageConverter
 *
 * Convert images: resize or merge an image
 *
 * @author Teye Heimans
 * @package FormHandler
 */
class ImageConverter
{
	// private vars!
    var $_sImage;
    var $_sError;
    var $_aSize;
    var $_aNewSize;
    var $_iQuality;
    var $_bConstrainProportions;

    /**
     * ImageConverter::ImageConverter()
     *
     * constructor: Create a new ImageConverter object
     *
     * @param string $sImage: The image to work with
     * @return ImageConverter
     * @author Teye Heimans
     * @access public
     */
    function ImageConverter( $sImage )
    {
        $this->_bConstrainProportions = true;
        $this->_sError   = '';
        $this->_sImage   = $sImage;
        $this->_iQuality = 80;

        // does the file exists ?
        if( file_exists($sImage) )
        {
            $this->_aSize     = getimagesize( $sImage );
            $this->_aNewSize  = $this->_aSize;

            // is the type of the image right to convert it ?
            if(!in_array(
              $this->_getExtension( $sImage ) ,
              array('jpg', 'png', 'jpeg', 'gif')))
            {
                $this->_sError = 'Only gif, jpg, jpeg and png files can be converted!';
                return;
            }
        }
        // file does not exitst
        else
        {
            $this->_sError = 'File not found: '.$sImage;
            return;
        }
    }

    /**
     * ImageConverter::setQuality()
     *
     * Set the quality of the new resized image
     *
     * @param int $iQuality: the quality
     * @return void
     * @author Teye Heimans
     * @access public
     */
    function setQuality( $iQuality )
    {
    	if( !empty( $iQuality ) && is_numeric( $iQuality ) )
    	{
        	$this->_iQuality = (int) $iQuality;
    	}
    }

    /**
     * ImageConverter::getError()
     *
     * Return the last error occoured
     *
     * @return string: the last occoured error
     * @author Teye Heimans
     * @access public
     */
    function getError()
    {
    	return isset($this->_sError) ? $this->_sError : '';
    }

    /**
     * ImageConverter::doResize()
     *
     * Resize the image
     *
     * @param string $sDestination: The file how we have to save it
     * @param int $iNewWidth: The new width of the image
     * @param int $iNewHeight: The new height of the image
     * @return void
     * @author Teye Heimans
     * @access public
     */
    function doResize( $sDestination , $iNewWidth, $iNewHeight )
    {
        // if no errors occourd
        if($this->_sError == '')
        {
        	// set the new size
        	$this->_setSize( $iNewWidth, $iNewHeight );

        	// check if the destination dir exists
        	if(!is_dir( dirname( $sDestination ) ))
        	{
        		$this->_sError = 'Destination dir does not exists: '.dirname( $sDestination );
        		return;
        	}

        	// when no filename is given as destination, use the original filename
        	$c = substr($sDestination, -1, 1);
        	if( $c == '/' || $c == '\\' )
        	{
        		$sDestination .= basename( $this->_sImage );
        	}

        	// does the destination has an extension attached ?
        	if( !in_array( $this->_getExtension( $sDestination ), array('jpg', 'jpeg', 'png', 'gif')))
        	{
        		// if not, put the extension of the original file behind it
        		$sDestination .= '.'.$this->_getExtension($this->_sImage);
        	}

        	// get the resource of the original file
            $rOrg = $this->_imageCreate( $this->_sImage );

            // get the old and new sizes of the image
            list($iOrgWidth, $iOrgHeight) = $this->_aSize;
            list($iNewWidth, $iNewHeight) = $this->_aNewSize;

            // generate the new image
            if($this->GDVersion() >= 2)
            {
                $rImgResized = ImageCreateTrueColor( $iNewWidth, $iNewHeight );

                if( $this->_getExtension( $sDestination ) == 'png' )
                {
					imagealphablending($rImgResized, false);
					imagesavealpha($rImgResized, true);
                }

                ImageCopyResampled( $rImgResized, $rOrg, 0, 0, 0, 0, $iNewWidth, $iNewHeight, $iOrgWidth, $iOrgHeight );
            }
            else
            {
                $rImgResized = ImageCreate( $iNewWidth, $iNewHeight );
                ImageCopyResized( $rImgResized, $rOrg, 0, 0, 0, 0, $iNewWidth, $iNewHeight, $iOrgWidth, $iOrgHeight );
            }

            // save the image to file
            $this->_saveImage( $rImgResized, $sDestination, $this->_iQuality );

            // // set the new width of the image if we are overwriting the original file
            if( $sDestination == $this->_sImage )
            {
	            $this->_aSize[0] = $iNewWidth;
	            $this->_aSize[1] = $iNewHeight;
            }

            // clean up
            ImageDestroy( $rOrg );
            ImageDestroy( $rImgResized );
        }
    }

    /**
     * ImageConverter::setConstrainProportions()
     *
     * Should we keep the image proportional when resizing ?
     *
     * @param bool $status
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function setConstrainProportions( $status = true )
    {
        $this->_bConstrainProportions = (bool) $status;
    }

    /**
     * ImageConverter::doMerge()
     *
     * Merge the image with a stamp
     *
     * @param string $sStamp: the stamp image
     * @param string $sAlign: the horizontal alignment of the stamp (give in percentage or as top, center, bottom)
     * @param string $sValign: the vertial alignment of the stamp (give in percentage or as top, middle, bottom)
     * @param array $aTransparant: array of rgb color value which should be transparant
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function doMerge( $sStamp, $sAlign, $sValign, $aTransparant = null )
    {
        if( file_exists($sStamp))
        {
        	if(!function_exists('imagecopyresampled'))
        	{
        		trigger_error(
        		  'Error, the required function imagecopyresampled does not exists! '.
        		  'Could not generate new image.',
        		  E_USER_WARNING
        		);
        		return;
        	}

        	// Open the current file (get the resource )
            $rImgSrc = $this->_imageCreate( $this->_sImage );

            // create the "new" file recourse
            $rImgDest = ImageCreateTrueColor( $this->_aSize[0], $this->_aSize[1] );

            // Open the stamp image
            $rImgStamp = $this->_imageCreate( $sStamp );

            // Transparant color...
            if( is_array($aTransparant) )
            {
            	$color = ImageColorAllocate( $rImgStamp, $aTransparant[0], $aTransparant[1], $aTransparant[2] );
            	ImageColorTransparent($rImgStamp, $color);
            }

            // Copy the current file to the new one
            ImageCopy( $rImgDest, $rImgSrc, 0,0,0,0, $this->_aSize[0], $this->_aSize[1] );
            ImageDestroy( $rImgSrc );

            // get the new position for the stamp
            $x = ImageSX( $rImgStamp );
            $y = ImageSY( $rImgStamp );
            $posX = $this->_getPos( $this->_aSize[0], $x, $sAlign );
            $posY = $this->_getPos( $this->_aSize[1], $y, $sValign );

            // copy the stamp to the new image
            ImageCopyMerge( $rImgDest, $rImgStamp, $posX, $posY, 0, 0, $x, $y, 100 );
            //ImageCopy( $rImgDest, $rImgStamp, $posX, $posY, 0, 0, $x, $y );  # transparant isnt working!
            //ImageCopyResampled( $rImgDest, $rImgStamp, 0, 0, $x, $y, $x, $y ); # transparant isnt working!

            ImageDestroy( $rImgStamp );

            // Save the new image
            $this->_saveImage( $rImgDest, $this->_sImage, 100 );
            ImageDestroy( $rImgDest );
		}
		else
		{
			trigger_error('Error, stamp file does not exists: '. $sStamp, E_USER_WARNING );
		}
    }

    /**
	 * ImageConverter::GDVersion()
	 *
	 * Return the installed GD version
	 *
	 * @param int $user_ver: the version needed by the user
	 * @return int: the installed gd version or 0 on failure
	 * @access public
	 * @author Teye Heimans
	 */
	function GDVersion($user_ver = 0)
	{
	   	if (!extension_loaded('gd'))
	   	{
	   		return false;
	   	}

	   	static $gd_ver = 0;

	   	// Just accept the specified setting if it's 1.
	   	if ($user_ver == 1)
	   	{
	   		$gd_ver = 1;
	   		return 1;
	   	}

	   	// Use the static variable if function was called previously.
	   	if ($user_ver != 2 && $gd_ver > 0 )
	   	{
	   		return $gd_ver;
	   	}

	   	// Use the gd_info() function if possible.
	   	if (function_exists('gd_info'))
	   	{
	   		$ver_info = gd_info();
	       	preg_match('/\d/', $ver_info['GD Version'], $match);
	       	$gd_ver = $match[0];
	       	return $match[0];
	   	}
	   	// If phpinfo() is disabled use a specified / fail-safe choice...
	   	if (preg_match('/phpinfo/', ini_get('disable_functions')))
	   	{
	   		if ($user_ver == 2)
	   		{
	        	$gd_ver = 2;
	           	return 2;
	       	}
	       	else
	       	{
	           	$gd_ver = 1;
	           	return 1;
	       	}
	   	}

		// ...otherwise use phpinfo().
		ob_start();
		phpinfo(8);
		$info = ob_get_contents();
		ob_end_clean();
		$info = stristr($info, 'gd version');
		preg_match('/\d/', $info, $match);
		$gd_ver = $match[0];
		return $match[0];
	}


    /******************************
     *      Private methods       *
     ******************************/


     /**
      * ImageConverter::_setSize()
      *
      * Set the new size of the image and calculate the new size directly
      *
      * @param int $x: the new width
      * @param int $y: the new height
      * @return void
      * @access private
      * @author Teye Heimans
      */
    function _setSize( $x, $y )
    {
        // if no errors occourd
        if($this->_sError == '')
        {
            // calculate the new sizes if we have to contrain the proportions
            if( $this->_bConstrainProportions )
            {
                // get the current sizes
                list( $iWidth, $iHeight ) = $this->_aSize;

                // get the new size
                if( $iWidth > $x )
                  $this->_getNewSize( $iWidth, $iHeight, $x );

                if( $iHeight > $y )
                  $this->_getNewSize( $iHeight, $iWidth, $y );
            }
            // we dont have to contrain the proportions, just use the sizes
            else
            {
                $iWidth  = $x;
                $iHeight = $y;
            }

            $this->_aNewSize = array( $iWidth, $iHeight );
        }
    }


	/**
	 * ImageConverter::_getPos()
	 *
	 * Get the position of the new stamp
	 *
	 * @param int $size: the size of the image (width of height)
	 * @param int $stampSize: the size of the stamp (width of height)
	 * @param string $where: position where to put the stamp on the image
	 * @return int
	 * @access private
	 * @author Teye Heimans
	 */
    function _getPos( $size, $stampSize, $where )
    {
    	// percentage ?
    	if(strpos($where, '%') !== false)
    	{
    		$percent = str_replace( '%', '', $where );
    		$part    = $size / 100;
    		$x       = ceil( $percent * $part );
    	}
    	else
    	{
	        // get the pos for the copyright stamp
	        switch (StrToLower($where))
	        {
	            case 'top':
	            case 'left':
	              $x = 0;
	              break;
	            case 'middle':
	            case 'center':
	              $x = ceil($size / 2) - ceil($stampSize / 2);
	              break;
	            case 'bottom':
	            case 'right':
	              $x = $size - $stampSize;
	              break;
	            default:
	              $x = 0;
	        }
    	}

        return $x;
    }

    /**
     * ImageConverter::_getNewSize()
     *
     * Calculate the new size
     *
     * @param int $x: The old width
     * @param int $y: The old height
     * @param int $max: The max width/height allowed
     * @return void
     * @access private
     * @author Teye Heimans
     */
    function _getNewSize( &$x, &$y, $max )
    {
        $procent = $x / 100;
        $scale   = $max / $procent;
        $x       = $scale * $procent;
        $y       = $scale * ($y / 100);
    }

    /**
     * ImageConverter::_getExtension()
     *
     * Return the extension of the given file
     *
     * @param string $sFile: the file where we have to retrieve the extension from
     * @return string
     * @access private
     * @author Teye Heimans
     */
    function _getExtension( $sFile )
    {
        $fp = explode( '.', $sFile );
        return StrToLower( $fp[ count($fp) -1 ] );
    }

    /**
     * ImageConverter::_imageCreate()
     *
     * Create a new image resource based on the extension of the given file
     *
     * @param string $sFile: The file
     * @return resource or false on failure
     * @author Teye Heimans
     * @access private
     */
    function _imageCreate( $sFile )
    {
        $sExt = $this->_getExtension( $sFile );

        // got extension ?
        if( $sExt )
        {
            if($sExt == 'jpg' || $sExt == 'jpeg')
            {
                return ImageCreateFromJPEG( $sFile );
            }
            elseif($sExt == 'png')
            {
                return ImageCreateFromPNG( $sFile);
            }
            elseif($sExt == 'gif' && function_exists('imagecreatefromgif'))
            {
                return ImageCreateFromGIF( $sFile );
            }
        }

        // something went wrong
        return false;
    }

    /**
     * ImageConverter::_saveImage()
     *
     * Function to save the new image
     *
     * @param resource $rImg: the image to save
     * @param string $sDestination: how to save the new image
     * @param int $iQuality: the quality of the new image
     * @return bool: true of succes and false on failure
     * @access private
     * @author Teye Heimans
     */
    function _saveImage( &$rImage, $sDestination, $iQuality = null )
    {
        $sExt = $this->_getExtension( $sDestination );

        if($sExt == 'jpg' || $sExt == 'jpeg')
        {
            return ImageJPEG($rImage, $sDestination, $iQuality);
        }
        elseif($sExt == 'png')
        {
            return ImagePNG($rImage, $sDestination);
        }
        elseif( $sExt == 'gif' && function_exists('imagegif') )
        {
            return imagegif($rImage, $sDestination);
        }
        else
        {
            trigger_error('Wrong destination given!', E_USER_WARNING );
            return false;
        }
    }
}
?>