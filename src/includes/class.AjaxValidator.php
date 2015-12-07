<?php
/**
 * Class AjaxValidator
 * 
 * Uses the validator class to validate FormHandler fields on the fly.
 * 
 * @since 03-12-2008
 * @author Johan Wiegel @ PHP-GLOBE
 * @package FormHandler
 * 
 */

class AjaxValidator
{
	static $bScript = true;

	function AjaxValidator( $bScript = true  )
	{
		self::$bScript = $bScript;
	}

	function CreateObservers( $oForm )
	{
		$bSetJS = false;

		// needed javascript included yet ?
		if(!$bSetJS)
		{
			$bSetJS = true;

			// add the needed javascript
			// only include library when bScript is treu
			if( self::$bScript === true )
			{
				$oForm->_setJS( FH_FHTML_DIR."js/jquery-1.4.2.min.js", true );
			}

			$oForm->_setJS( FH_FHTML_DIR."js/ajax_validator.js", true,false );

			// create observers for all fields with a validator
			$sScript = "";
			$sScript2 = "";
			/*@var $oForm FormHandler*/

			foreach( $oForm->_fields AS $sField => $aField )
			{
				if( is_object( $aField[1]) && method_exists( $aField[1], 'getValidator' ) )
				{
					if( isset( $oForm->_customMsg[$sField][0] ) AND $oForm->_customMsg[$sField][0] != '' )
					{
						$sMsg = addslashes( $oForm->_customMsg[$sField][0] );
					}
					else
					{
						$sMsg = $oForm->_text(14);
					}

					// only when a validator is defined for this field
					if( $aField[1]->getValidator() != '' )
					{
						if( is_a( $aField[1], 'selectField' ) )
						{
							$sEvent = 'change';
						}
						elseif( is_a( $aField[1], 'timeField' ))
						{
							$sField .= "_hour";
							$sEvent = 'blur';
						}
						elseif( is_a( $aField[1], 'checkBox' ) )
						{
							// no on the fly checking yet
							$sEvent = '';
						}
						elseif( is_a( $aField[1], 'radioButton' ) )
						{
							// no on the fly checking yet
							$sEvent = '';
						}
						else
						{
							$sEvent = 'blur';
						}
						if( $sEvent != '' )
						{

							$sScript  .= "  jQuery('#".$sField."').live( '".$sEvent."', function(){FH_VALIDATE( '".$aField[1]->getValidator()."', '".$sField."', '".$sField."', '".FH_FHTML_DIR."', '".FH_INCLUDE_DIR."','".$sMsg."' )});\n";
							if( $aField[1]->getValidator() <> 'FH_CHECK_DOMAIN' )
							{
								$sScript2 .= "FH_VALIDATE( '".$aField[1]->getValidator()."', '".$sField."', '".$sField."', '".FH_FHTML_DIR."', '".FH_INCLUDE_DIR."','".$sMsg."' );";
							}
						}
					}
				}
			}

			$oForm->_setJS( '$(function(){'.$sScript.'})', false, true );
			//na een post ook de AJAX validators aanroepen om de classes te switchen
			/**
			 * very alpha, we vinden dat het anders moet, maar weten nog niet hoe, nog niet documenteren en/of publiceren
			 * 
			 * @author Johan Wiegel
			 * @since 02-09-2009
			 */
			if( $oForm->isPosted() )
			{
				$oForm->_setJS( $sScript2,false,false );
			}
		}
	}

	function Validate( $aRequest, $oValidator )
	{
		// determin if there is more than one validator
		if( $aRequest['validator'] != '' AND $aRequest['msg'] != '' AND isset( $aRequest['value'] ) )
		{
			if( strpos( $aRequest['validator'], '|' ) > 0 )
			{
				$aValidators = explode( '|', $aRequest['validator'] );
			}
			else
			{
				$aValidators = array( $aRequest['validator'] );
			}

			// loop through validators
			foreach( $aValidators AS $iKey => $sValidator )
			{
				if( is_object( $oValidator ) && method_exists( $oValidator, $sValidator ) AND $sValidator != 'FH_CAPTCHA' )  // CAPTCHA can not be validated by AJAX
				{
					if( $oValidator->$sValidator( $aRequest['value'] ) == false )
					{
						return "<script type=\"text/javascript\">
						<!--//<![CDATA[	
						jQuery('#".$aRequest['msgbox']."').addClass( 'fh_error' );
						jQuery('#".$aRequest['msgbox']."').removeClass( 'fh_ok' );												
						jQuery('#".$aRequest['msgbox']."').removeClass( 'fh_mandatory' );												
						jQuery('#".$aRequest['msgbox']."').prev('input').addClass( 'error' );
						//]]>-->
						</script>".stripslashes( $aRequest['msg'] );				
						exit;  // stop if one validator fails
					}
					elseif( empty( $aRequest['value'] ) )
					{
						return "<script type=\"text/javascript\">
						<!--//<![CDATA[	
						jQuery('#".$aRequest['msgbox']."').prev('input').removeClass( 'error' );
						jQuery('#".$aRequest['msgbox']."').removeClass( 'fh_error' );
						jQuery('#".$aRequest['msgbox']."').removeClass( 'fh_ok' );
						jQuery('#".$aRequest['msgbox']."').addClass( 'fh_mandatory' );	
						//]]>-->											
						</script>";
						exit;
					}
					else
					{
						return "
						<script type=\"text/javascript\">	
						<!--//<![CDATA[	
						jQuery('#".$aRequest['msgbox']."').html('&nbsp;');
						jQuery('#".$aRequest['msgbox']."').removeClass( 'fh_mandatory' );	
						jQuery('#".$aRequest['msgbox']."').removeClass( 'fh_error' );
						jQuery('#".$aRequest['msgbox']."').addClass( 'fh_ok' );		
						jQuery('#".$aRequest['msgbox']."').prev('input').removeClass( 'error' );
						//]]>-->
						</script>";
						exit;
					}
				}
			}
		}
	}
}
?>