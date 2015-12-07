<?php
/**
 * Error handler of FormHandler
 *
 * @package FormHandler
 * @author Teye Heimans
 */



/**
 * catchErrors()
 *
 * Saves all PHP errors occoured in the form
 *
 * @return array: the array of occured errors
 * @access public
 * @author Teye Heimans
 */
function &catchErrors()
{
    static $errors = array();

    // Error event has been passed
    if ( func_num_args() >= 4 )
    {
    	// @ error! do nothing
    	if( ini_get('error_reporting') == 0 )
    	{
    	    $var = null;
    		return $var;
    	}

    	// dont save E_STRICT error messages
    	if( defined('E_STRICT') && func_get_arg(0) == E_STRICT )
    	{
    	    $var = null;
    		return $var;
        }

        // save the error details
        $errors[] = array(
          'no'   => func_get_arg(0),
          'text' => func_get_arg(1),
          'file' => func_get_arg(2),
          'line' => func_get_arg(3),
          'vars' => (func_num_args() == 5 ? func_get_arg(4) : null )
        );

        // is it a ERROR? then display and quit
	    if(func_get_arg(0) == E_USER_ERROR )
	    {
	    	// display all errors first..
	    	foreach( $errors as $error )
	    	{
	    		switch ($error['no']) {
	    			case E_WARNING:
	        		case E_USER_WARNING:
	        		  $type = 'Warning'; break;
	        		case E_NOTICE:
	        		case E_USER_NOTICE:
	        		  $type = 'Notice';  break;
	        		case E_ERROR:
	        		case E_USER_ERROR:
	        		  $type = 'Error';   break;
	        		default:
	        		  $type = 'Warning ('.$error['no'].')'; break;
	        	}
	    		echo "<b>".$type."</b> ".basename($error["file"])." at ".$error["line"]." ". $error["text"]."<br />\n";
	    	}
	    	exit; // if we dont exit, php will
	    }
    }

    // call for the errors. Return the reference
    if ( func_num_args() == 0 )
    {
        return $errors;
    }

    $var = null;
    return $var;
}

?>