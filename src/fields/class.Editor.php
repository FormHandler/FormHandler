<?php
/**
 * class Editor
 *
 * Create a Editor on the given form with CKEDitor 4.3.1
 *
 * @author Johan Wiegel
 * @package FormHandler
 * @subpackage Fields
 * @since 2013-12-17
 */

class Editor extends TextArea
{
	function Editor( $oForm, $sName )
	{
		parent::TextArea( $oForm, $sName );	
		
		static $bSetJS = false;

		// needed javascript included yet ?
		if(!$bSetJS)
		{

			$bSetJS = true;
				$oForm->_setJS(
					FH_FHTML_DIR."ckeditor/ckeditor.js", true
				);
		}

		$this->_oEditor = new stdClass( $sName );
		$this->_oEditor->basePath = FH_FHTML_DIR . 'ckeditor/';
        $this->_oEditor->Value = isset( $this->_mValue ) ? $this->_mValue : '';

        $this->setToolbar( 'Default' ); // Default or Basic
        $this->setServerPath( '' );
		
        // set the language
        $this->_oEditor->config['language']  = str_replace('-utf8', '', $oForm->_lang);        

		// default height & width
        $this->setWidth ( 720 );
        $this->setHeight( 400 );

        // moono
        $this->setSkin( 'moono' );

		
	}
	
    /**
     * Editor::setHeight()
     *
     * Set the height of the editor (in pixels!)
     *
     * @param integer $iHeight: the height
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function setHeight( $iHeight )
    {
        $this->_oEditor->config['height'] = $iHeight;
    }	
	
    /**
     * Editor::setValue()
     *
     * Set the value of the field
     *
     * @param string $sValue: The html to set into the field
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function setValue( $sValue )
    {
    	$this->_mValue = $sValue;
    	$this->_oEditor->Value = $sValue;
    }


    /**
     * Editor::setWidth()
     *
     * Set the width of the editor  (in pixels!)
     *
     * @param integer $iWidth: the width
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function setWidth( $iWidth)
    {
        $this->_oEditor->config['width'] = $iWidth;
    }

    /**
     * Editor::setToolbar()
     *
     * Set the toolbar we should use for the editor
     *
     * @param string $sToolbar: The toolbar we should use
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function setToolbar( $sToolbar )
    {
        $this->_oEditor->config['toolbar'] = $sToolbar;
    }


    /**
     * Editor::setConfig()
     *
     * Set extra config options for the editor
     *
     * @param array $config: The config array with extra config options to set for the fckeditor
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function setConfig( $config )
    {
        $this->_oEditor->config = array_merge( $this->_oEditor->config, $config );
    }

    /**
     * Editor::setServerPath()
     *
     * Set the server path used for browsing and uploading images
     *
     * @param string $sPath: The path
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function setServerPath( $sPath )
    {
        if( $sPath === false )
        {
            $this->_oEditor->Config['filebrowserBrowseUrl']  = false;
            $this->_oEditor->Config['filebrowserUploadUrl'] = false;
            return;
        }

    	// get the dir where the script is located in
    	$sSelfPath = $_SERVER['PHP_SELF'] ;
	    $sSelfPath = substr( $sSelfPath, 0, strrpos( $sSelfPath, '/' ) ) ;

	    // get the dir where the user want's to upload the dir in
	    $sPath = $this->_getServerPath( $sPath, $sSelfPath );
        // path (URL) to the FCKeditor...
        $char = substr(FH_FHTML_DIR, 0, 1);
        $pre  = ($char != '/' && $char != '\\' && strtolower(substr(FH_FHTML_DIR, 0, 4)) != 'http') ? str_replace('//', '/', dirname( $_SERVER['PHP_SELF'] ).'/') : '';

        $sURL =
          $pre . FH_FHTML_DIR .
          'filemanager/browser/default/browser.html?'.
          'Type=%s&Connector=../../connectors/php/connector.php?ServerPath='.$sPath
        ;

        $this->_oEditor->config['filebrowserBrowseUrl']  = ( sprintf( $sURL, 'File', $sPath ) );
        $this->_oEditor->config['filebrowserUploadUrl']  = ( sprintf( $sURL, 'File', $sPath ) );
    }


    /**
     * Editor::setSkin()
     *
     * Set the skin used for the FCKeditor
     *
     * @param string $skin
     * @return void
     * @access public
     * @author Teye Heimans
     */
    function setSkin( $sSkin )
    {
    	$this->_oEditor->config['skin'] = $sSkin;
    }        
	
    /**
     * Editor::_getServerPath()
     *
     * Get the dir which should be used for browsing...
     *
     * @param string $sDir: The dir given by the user
     * @param string $sServerPath: The dir where the script is located on the server
     * @return void
     * @access private
     * @author Teye Heimans
     */
    function _getServerPath( $sDir, $sServerPath )
    {
    	// remove ending slash at the server path
    	if( substr($sServerPath, -1) == '/' )
    	{
			$sServerPath = substr( $sServerPath, 0, -1);
		}
		// when no dir is given, just return the path where the script is located
		if( $sDir == '' )
		{
			return $sServerPath;
		}

		// dir starting with a /? Then start at the root...
		if( $sDir{0} == '/' )
		{
			return $sDir;
		}
		// dir starting with ./? Then relative from the dir where the script is located
		else if( substr( $sDir, 0, 2) == './' )
		{
			return $sServerPath.'/'.substr($sDir, 2);
		}
		// if we are at the root of the server, return the dir..
		else if( $sServerPath == '/' || $sServerPath == '')
		{
			if( $sDir{0} != '.' && $sDir{0} != '/' )
			{
				 $sDir = '/'.$sDir;
			}
			return $sDir;
		}
		// go a dir lower...
		else if( substr($sDir, 0, 3) == '../' )
		{
    		$sServerPath = substr($sServerPath, 0, -strlen( strrchr($sServerPath, "/") ));

    		return $this->_getServerPath( substr($sDir, 3), $sServerPath);
    	}
    	// none of the above, then return the dir!
    	else
    	{
    		if( $sDir{0} == '/' )
    		{
    			$sDir = substr( $sDir, 1);
    		}
    		return $sServerPath.'/'.$sDir;
    	}
    }    
    
	/**
     * Editor::getField()
     *
     * return the field
     *
     * @return string: the field
     * @author Teye Heimans
     * @access public
     */
	function getField()
	{
		// view mode enabled ?
		if( $this -> getViewMode() )
		{
			// get the view value..
			return $this -> _getViewValue();
		}

		$html = parent::getField();
	
		// add the javascript needed for the js calendar field
		$this -> _oForm -> _setJS( 
		"
			CKEDITOR.replace( '".$this->_sName."', ".json_encode( $this->_oEditor->config )." );		
		", 0, 0 );
				
		return $html;
	}	
}
?>