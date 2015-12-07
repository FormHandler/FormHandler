<?php
/**
 * Uses the FormHandler Validator class to validate fields on the fly
 * 
 * @package FormHandler
 * @author Johan Wiegel
 * @since 04-12-2008
 */
// includedir must be set


//echo 'validate';
//print_r( $_REQUEST );


if( isset( $_REQUEST['includedir'] ) )
{
	// to classes needed, can the be found?
	if( file_exists( $_REQUEST['includedir'].'includes/class.Validator.php' ) AND file_exists( $_REQUEST['includedir'].'includes/class.AjaxValidator.php' ) )
	{
		include( $_REQUEST['includedir'].'includes/class.Validator.php' );
		include( $_REQUEST['includedir'].'includes/class.AjaxValidator.php' );
		$oAjaxValidator = new AjaxValidator(  );
		echo $oAjaxValidator->Validate( $_REQUEST, new Validator() );
	}
	else 
	{
		echo 'AJAX validation will not work, classes can not be found, check FH_INCLUDE_DIR in config.inc.php';
	}
}
else 
{
	echo 'Something went wrong.';
}

?>