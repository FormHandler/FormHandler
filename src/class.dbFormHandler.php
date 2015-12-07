<?php
/**
 * Database extension class for FormHandler
 *
 * @package FormHandler
 */

// include the "basic" formhandler
include_once( dirname(__FILE__).'/class.FormHandler.php' );

// make sure this file is not accessed directly
if(strtolower(basename($_SERVER['PHP_SELF'])) == strtolower(basename(__FILE__)))
{
	die('This file cannot be accessed directly! Include it in your script instead!');
}

/**
 * class dbFormHandler
 *
 * Extension of FormHandler with all db functions in it.
 *
 * @author Teye Heimans
 * @link http://www.formhandler.net
 * @see FormHandler
 */
class dbFormHandler extends FormHandler
{
	var $_onSaved;          // string: the callback function when the form is saved
	var $_sql;              // array: contains the names of the added values which are sql functions
	var $_dontSave;         // array: dont save these fields
	var $_db;               // object: contains the database object if the option is used
	var $_id;               // array: the id(s) which we are editing
	var $_dbData;           // array: the database data
	var $_table;			// string: the table name where we should save the data in
	var $_editName;			// name of the primary key to edit

	// public
	var $insert;            // boolean: if the form is an insert-form
	var $edit;              // boolean: if the form is an edit-form
	var $dieOnQuery;        // boolean: debugging option... show query which is going to be executed

	protected $_tableConfig = array();

	/**
     * dbFormHandler::dbFormHandler()
     *
     * Constructor: initialize some needed vars
     *
     * @param string $name: the name for the form (used in the <form> tag
     * @param string $action: the action for the form (used in <form action="xxx">)
     * @param string $extra: extra css or js which is included in the <form> tag
     * @author Teye Heimans
     * @return dbFormHandler
     */
	function dbFormHandler( $name = null, $action = null, $extra = null )
	{
		$this->_sql           = array();
		$this->_dbData        = array();
		$this->_dontSave      = array();
		$this->_id            = array();
		$this->dieOnQuery     = false;

		parent::FormHandler( $name, $action, $extra );

		$this->setEditName( FH_EDIT_NAME );
		$this->initFHEditName();
	}

	/**
	 * Initialize the editName
	 *
	 */
	function initFHEditName()
	{
		// initialisation
		$this->insert = !isset($_GET[$this->_editName]);
		$this->edit   = !$this->insert;

		// get the ID if it's an edit form
		if($this->edit)
		{
			$this->_id = $_GET[$this->_editName];
			if( !is_array($this->_id) )
			{
				$this->_id = array( $this->_id );
			}
		}
	}

	/**
	 * Set the id which we are watching in the URL of the form is an edit form
	 *
	 * @param srting $sEditName
	 * 
	 * @author Remco van Arkelen & Johan Wiegel
	 * @since 01-03-2010
	 */
	function setEditName( $sEditName )
	{
		if( isset( $this->_table ) )
		{
			trigger_error( 'Function setEditName should be use before dbInfo and/or dbConnect' );
		}
		else
		{
			$this->_editName = $sEditName;
			$this->initFHEditName();
		}
	}

	/********************************************************/
	/************* FIELDS ***********************************/
	/********************************************************/

	/**
     * dbFormHandler::dbCheckBox()
     *
     * Create a CheckBox on the form with records loaded from a table
     *
     * @param string $title: The title of the field
     * @param string $name: The name of the field
     * @param string $table: The table where the records are retrieved from
     * @param mixed  $fields: String or array with the field(s) which are retrieved from the table and put into the select field
     * @param string $extraSQL: Extra SQL
     * @param string $validator: The validator which should be used to validate the value of the field
     * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag
     * @param string $mask: if more the 1 options are given, glue the fields together with this mask
     * @return void
     * @access public
     * @author Johan Wiegel
     * @since 11-04-2008
     */
	function dbCheckBox(
	$title,
	$name,
	$table,
	$fields,
	$extraSQL   = null,
	$validator  = null,
	$extra      = null,
	$sMask	  = null)
	{
		require_once(FH_INCLUDE_DIR.'fields/class.CheckBox.php');
		require_once(FH_INCLUDE_DIR.'fields/class.dbCheckBox.php');

		if( !is_object($this->_db) )
		{
			trigger_error(
			'Error, you have to make use of the database option to make use of the field dbSelectField. '.
			'Use selectField() instead!',
			E_USER_WARNING
			);
			return;
		}

		// create new checkbox
		$fld = new dbCheckBox( $this, $name, $this->_db, $table, $fields, $extraSQL );

		if(!empty($validator))  $fld->setValidator( $validator );
		if(!empty($extra))      $fld->setExtra( $extra );
		if(!empty($sMask))       $fld->setMask( $sMask );

		// register the field
		$this->_registerField( $name, $fld, $title );
	}

	/**
     * dbFormHandler::dbTextSelectField()
     *
     * Create a TextSelectField on the form with records loaded from a table
     *
     * @param string $title: The title of the field
     * @param string $name: The name of the field
     * @param string $table: The table where the records are retrieved from
     * @param string  $field: String with the field which are retrieved from the table and put into the select part of this text field
     * @param string $extraSQL: Extra SQL
     * @param string $validator: The validator which should be used to validate the value of the field
     * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag    
     * @return void
     * @access public
     * @author Johan Wiegel
     * @since 22-10-2008
     */
	function dbTextSelectField(
	$title,
	$name,
	$table,
	$field,
	$extraSQL   = null,
	$validator  = null,
	$extra      = null
	)
	{
		require_once(FH_INCLUDE_DIR.'fields/class.TextField.php');
		require_once(FH_INCLUDE_DIR.'fields/class.TextSelectField.php');
		require_once(FH_INCLUDE_DIR.'fields/class.dbTextSelectField.php');

		if( !is_object($this->_db) )
		{
			trigger_error(
			'Error, you have to make use of the database option to make use of the field dbSelectField. '.
			'Use selectField() instead!',
			E_USER_WARNING
			);
			return;
		}

		// create new checkbox
		$fld = new dbTextSelectField( $this, $name, $this->_db, $table, $field, $extraSQL );

		if(!empty($validator))  $fld->setValidator( $validator );
		if(!empty($extra))      $fld->setExtra( $extra );

		// register the field
		$this->_registerField( $name, $fld, $title );
	}


	/**
     * dbFormHandler::dbRadioButton()
     *
     * Create a RadioButton on the form with records loaded from a table
     *
     * @param string $title: The title of the field
     * @param string $name: The name of the field
     * @param string $table: The table where the records are retrieved from
     * @param mixed  $fields: String or array with the field(s) which are retrieved from the table and put into the select field
     * @param string $extraSQL: Extra SQL
     * @param string $validator: The validator which should be used to validate the value of the field
     * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag
     * @param string $mask: if more the 1 options are given, glue the fields together with this mask
     * @return void
     * @access public
     * @author Johan Wiegel
     * @since 11-04-2008
     */
	function dbRadioButton(
	$title,
	$name,
	$table,
	$fields,
	$extraSQL   = null,
	$validator  = null,
	$extra      = null,
	$sMask	  = null)
	{
		require_once(FH_INCLUDE_DIR.'fields/class.RadioButton.php');
		require_once(FH_INCLUDE_DIR.'fields/class.dbRadioButton.php');

		if( !is_object($this->_db) )
		{
			trigger_error(
			'Error, you have to make use of the database option to make use of the field dbSelectField. '.
			'Use selectField() instead!',
			E_USER_WARNING
			);
			return;
		}

		// create new RadioButton
		$fld = new dbRadioButton( $this, $name, $this->_db, $table, $fields, $extraSQL );

		if(!empty($validator))  $fld->setValidator( $validator );
		if(!empty($extra))      $fld->setExtra( $extra );
		if(!empty($sMask))       $fld->setMask( $sMask );

		// register the field
		$this->_registerField( $name, $fld, $title );
	}

	/**
     * dbFormHandler::dbSelectField()
     *
     * Create a selectField on the form with records loaded from a table
     *
     * @param string $title: The title of the field
     * @param string $name: The name of the field
     * @param string $table: The table where the records are retrieved from
     * @param mixed $fields: String or array with the field(s) which are retrieved from the table and put into the select field
     * @param string $extraSQL: Extra SQL
     * @param string $validator: The validator which should be used to validate the value of the field
     * @param boolean $multiple: Should it be possible to select multiple options ? (Default: false)
     * @param int $size: The size of the field (how many options are displayed)
     * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag
     * @param array $mergeArray: Default value(s) for the field
     * @return void
     * @access public
     * @author Teye Heimans
     */
	function dbSelectField(
	$title,
	$name,
	$table,
	$fields,
	$extraSQL   = null,
	$validator  = null,
	$multiple   = null,
	$size       = null,
	$extra      = null,
	$mergeArray = null)
	{
		require_once(FH_INCLUDE_DIR.'fields/class.SelectField.php');
		require_once(FH_INCLUDE_DIR.'fields/class.dbSelectField.php');

		if( !is_object($this->_db) )
		{
			trigger_error(
			'Error, you have to make use of the database option to make use of the field dbSelectField. '.
			'Use selectField() instead!',
			E_USER_WARNING
			);
			return;
		}

		// create new selectfield
		$fld = new dbSelectField( $this, $name, $this->_db, $table, $fields, $extraSQL, $mergeArray );

		if(!empty($validator))  $fld->setValidator( $validator );
		if($multiple)           $fld->setMultiple( $multiple );
		if(!empty($extra))      $fld->setExtra( $extra );

		// set the size if given
		if(!empty($size))
		{
			$fld->setSize( $size );

		}
		// if no size is set and multiple is enabled, set the size default to 4
		else if( $multiple )
		{
			$fld->setSize( 4 );
		}

		// register the field
		$this->_registerField( $name, $fld, $title );
	}

	/**
     * dbFormHandler::dbListField()
     *
     * Creates a listfield with values retrieved from the database
     *
     * @param string $title: The title of the field
     * @param string $name: The name of the field
     * @param string $table: The table where the records are retrieved from
     * @param mixed $fields: String or array with the field(s) which are retrieved from the table and put into the select field
     * @param string $validator: The validator which should be used to validate the value of the field
     * @param string $onTitle: The title used above the ON section of the field
     * @param string $offTitle: The title used above the OFF section of the field
     * @param int $size: The size of the field (how many options are displayed)
     * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag
     * @param string $verticalMode: Verticale mode
     * @return void
     * @access public
     * @author Teye Heimans
     */
	function dbListField(
	$title,
	$name,
	$table,
	$fields,
	$extraSQL  = null,
	$validator = null,
	$onTitle   = null,
	$offTitle  = null,
	$size      = null,
	$extra     = null,
	$verticalMode     = null
	)
	{
		require_once(FH_INCLUDE_DIR.'fields/class.SelectField.php');
		require_once(FH_INCLUDE_DIR.'fields/class.ListField.php');
		require_once(FH_INCLUDE_DIR.'fields/class.dbListField.php');

		if( !is_object($this->_db) )
		{
			trigger_error(
			'Error, you have to make use of the database option to make use of the field dbListField. '.
			'Use listField() instead!',
			E_USER_WARNING
			);
			return;
		}

		// create new selectfield
		$fld = new dbListField( $this, $name, $this->_db, $table, $fields, $extraSQL );

		if(!empty($validator))  $fld -> setValidator( $validator );
		if(!empty($extra))      $fld -> setExtra    ( $extra );
		if(!empty($onTitle))	$fld -> setOnTitle  ( $onTitle );
		if(!empty($offTitle))   $fld -> setOffTitle ( $offTitle );
		if(!empty($verticalMode))         $fld->setVerticalMode( $verticalMode );
		// set the size if given
		if(!empty($size))
		{
			$fld->setSize( $size );

		}

		// register the field
		$this->_registerField( $name, $fld, $title );
	}

	/********************************************************/
	/************* Data handling ****************************/
	/********************************************************/

	/**
	 * dbFormHandler::getDBValue()
	 * 
	 * Get the value of the requested field from the database
	 *
	 * @param string $sField
	 * @return string
	 * 
	 * @since 17-02-2010
	 * @author Winus van Heumen
	 */
	function getDBValue( $sField )
	{
		if( array_key_exists( $sField, $this->_dbData ) )
		{
			return $this->_dbData[$sField];
		}
		/**
         * Field not found.
         */
		trigger_error(
		' field ('.$sField.') is not found.',
		E_USER_WARNING
		);
		return '';
	}

	/**
     * dbFormHandler::value()
     *
     * Get the value of the requested field
     *
     * @param string $field: The field which value we have to return
     * @return string
     * @access public
     * @author Teye Heimans
     */
	function value( $field )
	{
		if(!_global) global $_POST;

		// is it a field?
		if( isset( $this->_fields[$field] ) && is_object($this->_fields[$field][1]) && method_exists($this->_fields[$field][1], 'getvalue')  )
		{
			return $this->_fields[$field][1]->getValue();
		}
		// is it an user added value ?
		else if( isset($this->_add[$field]) )
		{
			return $this->_add[$field];
		}
		// _chache contains the values of the fields after flush() is called
		// (because then all objects are removed from the memory)
		else if( isset( $this->_cache[$field]) )
		{
			return $this->_cache[$field];
		}
		// is it a set value of a field which does not exists yet ?
		else if( isset( $this->_buffer[$field]) )
		{
			return $this->_buffer[$field][1];
		}
		// is it a value from the $_POST array ?
		else if( isset( $_POST[$field] ) )
		{
			// give a notice
			//trigger_error(
			//  'Notice: the value retrieved from the field "'.$field.'" could '.
			//  'only be fetched from the $_POST array. The field is not found in the form...',
			//  E_USER_NOTICE
			//);

			return $_POST[$field];
		}
		// is the database function used ?
		else if( isset( $this->_db ) && is_object( $this->_db ) && !empty( $this->_table ) )
		{
			// fetch the database fields
			$fields = $this->_db->getFieldNames( $this->_table );

			// does the field exists in the table ?
			if( in_array( $field, $fields ) )
			{
				// is an edit value known ?
				if( is_array( $this->_dbData ) && array_key_exists( $field, $this->_dbData ) )
				{
					return $this->_dbData[$field];
				}
				// no db value known
				else
				{
					// return an empty string.. the field is a table column
					return '';
				}
			}
		}

		trigger_error(
		'Try to get the value of an unknown field "'.$field.'"!',
		E_USER_WARNING
		);

		return null;
	}

	/**
     * dbFormHandler::setValue()
     *
     * Set the value of the specified field
     *
     * @param string $field: The field which value we have to set
     * @param string $value: The value we have to set
     * @param boolean $overwriteCurrentValue: Do we have to overwrite the current value of the field (posted or db-loaded values)
     * @return void
     * @access public
     * @author Teye Heimans
     */
	function setValue( $sField, $sValue, $bOverwriteCurrentValue = false )
	{
		if( is_array( $sValue ) )
		{
			$sValue = implode(', ',$sValue );
		}
		// check if the field exists
		if( $this->fieldExists( $sField ) )
		{
			// if the field does not exists in the database and there is no post value,
			// or when we want to overwrite the current value
			if( $bOverwriteCurrentValue || (!isset($this->_dbData[$sField]) && !$this->isPosted() ) ||
			// only set the value if the page is not 'done' yet, otherwise
			// we will overwrite it
			$this->_curPage < $this->_pageCounter )
			{
				$this->_fields[$sField][1]->setValue( $sValue );
			}
		}
		// the field does not exists. Save the value in the buffer.
		// the field will check this buffer and use it value when it's created
		else
		{
			// save the data untill the field exists
			$this->_buffer[$sField] = array( $bOverwriteCurrentValue, $sValue );
		}
	}

	/********************************************************/
	/************* DATABASE METHODS *************************/
	/********************************************************/

	/**
	 * Add a juintionTable
	 *
	 * @param string $sTable
	 * @param string $sDestinationField foreign key to the table
	 * @param string $sSourceField foreign key to this id
	 * @return mixed
	 */
	public function addJunctionTable( $sTable, $sDestinationField, $sSourceField )
	{
		if( !isset( $this->_tableConfig[$sTable] ) )
		{
			$this->_tableConfig[$sTable] = array( $sDestinationField, $sSourceField );

			// Only create a juntion if the field exists
			if( !isset( $this->_fields[ $sDestinationField ] ) )
			{
				trigger_error(
				"The field '$sDestinationField' doesn't exist in the form",
				E_USER_WARNING
				);
				return;
			}
			// Laad de data
			if( $this->edit )
			{
				$this->_loadDbData();
			}
			return true;
		}
		else
		{
			trigger_error(
			"The junctiontable '$sTable' is already defined",
			E_USER_WARNING
			);
			return;
		}
	}

	/**
     * dbFormHandler::dbInfo()
     *
     * Set the DBInfo.
     *
     * @param string $db: the db which we are using
     * @param string $table: the table where we have to save the data
     * @param string $type: the type of db which is used
     * @return void
     * @access public
     * @author Teye Heimans
     */
	function dbInfo( $db, $table, $type = null )
	{
		require_once( FH_YADAL_DIR . 'class.Yadal.php' );

		// if no db-type is given, set the default
		if(is_null($type))
		{
			$type = FH_DEFAULT_DB_TYPE;
		}

		// set the table to use
		$this->_table = $table;

		// create a new yadal object
		$this->_db = newYadal( $db, $type );

		// try to fetch the database data if we are connected
		if( $this->edit && $this->_db->isConnected() )
		{
			$this->_loadDbData();
		}
	}

	/**
     * dbFormHandler::dbConnect()
     *
     * Connect to the database
     *
     * @param string $username: the username used to login
     * @param string $password: the password used to login
     * @return void
     * @access public
     * @author Teye Heimans
     */
	function dbConnect($host = null, $username = '', $password = '')
	{
		// check if the database info is set
		if( is_object( $this->_db ) )
		{
			// if no host is given, use the default host
			if(is_null($host))
			{
				$host = FH_DEFAULT_DB_HOST;
			}

			// try to connect
			if( !$this->_db->connect( $host, $username, $password ) )
			{
				// connection failed..
				trigger_error(
				'Error, database connection failed: '.$this->_db->getError(),
				E_USER_WARNING
				);

				return false;
			}
		}
		// the database info is not set yet!
		else
		{
			// trigger an error
			trigger_error(
			'No database object available! Make sure you call DBInfo() first '.
			'or use setConnectionResource() to use an already opend connection!',
			E_USER_WARNING
			);
			$this->_db = null;

			return;
		}

		// load the database values only on a edit form...
		if( $this->edit && ( !is_array($this->_dbData) || sizeof($this->_dbData) == 0) )
		{
			$this->_loadDbData();
		}
	}

	/**
     * dbFormHandler::setConnectionResource()
     *
     * Use an already opened connection instead of opening a new one.
     *
     * @param resource $conn:
     * @param string $table: The table which should be used to save the data in
     * @param string type: The type of database you are using
     * @return void
     * @access public
     * @author Teye Heimans
     */
	function setConnectionResource( $conn, $table = null, $type = null )
	{
		require_once( FH_YADAL_DIR.'class.Yadal.php' );

		// if no db-type is given, set the default
		if(is_null($type))
		{
			$type = FH_DEFAULT_DB_TYPE;
		}

		// make this function backwards compatible ( $table, $conn, $type  as arguments )
		if( is_string( $conn ) && is_resource( $table ) )
		{
			$tmp   = $table;
			unset( $table );
			$table = $conn;
			unset( $conn );
			$conn  = $tmp;
			unset( $tmp );
		}

		// save the table name
		$this->_table = $table;

		// create the yadal object
		$this->_db = newYadal(null, $type);

		// set the sql resource which we should use
		$this->_db->setConnectionResource( $conn );

		// try to fetch the database data already
		if( $this->edit )
		{
			$this->_loadDbData();
		}
	}

	/**
     * dbFormHandler::onSaved()
     *
     * Set the function  which has to be called when the form data is saved in the database
     *
     * @param string $callback: The name of the function
     * @return void
     * @access public
     * @author Teye Heimans
     */
	function onSaved( $callback )
	{
		// is the given value a string ?
		if(!is_array($callback))
		{
			// does the function exists ?
			if( function_exists($callback) )
			{
				$this->_onSaved = $callback;
			}
			// the given callback function does not exists
			else
			{
				trigger_error(
				'Error, the onSaved function "'.$callback.'" does not exists!',
				E_USER_ERROR
				);
			}
		}
		// we have to call a mehtod
		else
		{
			// check if the method exists in the given object
			if( method_exists( $callback[0], $callback[1]) )
			{
				$this->_onSaved = $callback;
			}
			// the method does not exists
			else
			{
				trigger_error(
				'Error, the onSaved method "'.$callback[1].'" does not exists in the given object'.
				(is_object($callback[0]) ? ' "'.get_class($callback[0]).'"!' : '!'),
				E_USER_ERROR
				);
			}
		}
	}


	/********************************************************/
	/************* GENERAL **********************************/
	/********************************************************/

	/**
     * dbFormHandler::isCorrect()
     *
     * Return if the form is filled correctly (for the fields which are set!)
     *
     * @return boolean: the form values valid or not
     * @access public
     * @author Teye Heimans
     */
	function isCorrect()
	{
		$result = parent::isCorrect();

		// check if there is no unique field error when the field is completly correct
		if( $result )
		{
			return $this->_checkUniqueFields();
		}

		return false;
	}

	/**
     * dbFormHandler::flush()
     *
     * prints or returns the form
     *
     * @return string: the form or null when the form should be printed
     * @access public
     * @author Teye Heimans
     */
	function flush( $return = false )
	{
		if( $this->_ajaxValidator === true )
		{
			require_once( FH_INCLUDE_DIR . 'includes/class.AjaxValidator.php' );
			$oAjaxValidator = new AjaxValidator( $this->_ajaxValidatorScript );
			$oAjaxValidator->CreateObservers( $this );
		}

		// when the form is not posted or the form is not valid
		if( !$this->isPosted() || !$this->isCorrect() )
		{
			// check if a value is set of an unknown field
			if( sizeof( $this->_buffer ) > 0 )
			{
				// error messages for the values for unknown fields
				foreach($this->_buffer as $sField => $a)
				{
					trigger_error('Value set of unknown field "'.$sField.'"', E_USER_WARNING );
				}
			}

			// get the form
			$form = $this->_getForm();
		}
		// when the form is not totaly completed yet (multiple pages)
		else if( $this->_curPage < $this->_pageCounter )
		{
			// upload and convert uploads
			$this->_handleUploads();

			// get the next form
			$form = $this->_getForm( $this -> _curPage + 1 );

		}
		// when the form is valid
		else
		{
			// upload and convert uploads
			$this->_handleUploads();

			// generate the data array
			$data = array();
			foreach($this->_fields as $name => $fld)
			{
				if( is_object( $fld[1] ) &&  method_exists($fld[1], 'getValue') && $name != $this->_name.'_submit')
				{
					$data[$name] = $fld[1]->getValue();
				}
			}

			// add the user added data to the array
			$data = array_merge( $data, $this->_add );

			// call the users oncorrect function
			if(!empty($this->_onCorrect))
			{
				if(is_array($this->_onSaved))
				{
					$hideForm = call_user_func_array( array(&$this->_onCorrect[0], $this->_onCorrect[1]), array($data, &$this) );
				}
				else
				{
					$hideForm = call_user_func_array( $this->_onCorrect, array($data, &$this) );
				}
			}

			// add the user added data again to the array (could have been changed!)
			$data = array_merge( $data, $this->_add );

			// if the db option is used
			if( !is_null($this->_db) && !empty($this->_table) && (!isset($hideForm) || $hideForm) )
			{
				// save the data into the databse
				$id = $this->_saveDbData( $data );

				// query error ?
				if( $id === -1 )
				{
					// something went wrong with the query.. display the form again
					$hideForm = false;
				}
				else
				{
					// got id back ?
					if(is_array($id) && sizeof($id) == 1)
					{
						$id = $id[0];
					}

					// call the onsaved function
					if(!is_null($this->_onSaved))
					{
						if(is_array($this->_onSaved))
						{
							$hideForm = call_user_func_array( array(&$this->_onSaved[0], $this->_onSaved[1]), array($id, $data, &$this) );
						}
						else
						{
							$hideForm = call_user_func_array( $this->_onSaved, array($id, $data, &$this) );
						}
					}
				}
			}

			// display the form again if wanted..
			if(isset($hideForm) && $hideForm === false)
			{
				$form = $this->_getForm();
			}
			// the user want's to display something else..
			else if(isset($hideForm) && is_string($hideForm))
			{
				$form = $hideForm;
			}
			// dont display the form..
			else
			{
				$form = '';
			}
		}

		// cache all the fields values for the function value()
		foreach( $this->_fields as $fld => $value )
		{
			// check if it's a field
			if( is_object($this->_fields[$fld][1]) && method_exists($this->_fields[$fld][1], "getvalue"))
			{
				$this->_cache[ $fld ] = $this->_fields[$fld][1]->getValue();
			}
		}

		/*
		// remove all vars to free memory
		foreach( get_object_vars($this) as $name => $value )
		{
		// remove all vars except these ones..
		if( !in_array($name, array('_cache', 'edit', 'insert', '_posted', '_name' ) ) )
		{
		unset( $this->{$name} );
		}
		}*/

		// disable our error handler!
		if( FH_DISPLAY_ERRORS )
		{
			restore_error_handler();
		}

		// return or print the form
		if( $return )
		{
			return $form;
		}
		else
		{
			echo $form;
			return null;
		}

	}
	/********************************************************/
	/************* BELOW IS ALL PRIVATE!! *******************/
	/********************************************************/

	/**
     * dbFormHandler::_checkUniqueFields()
     *
     * Check if there are no double values for unique fields
     *
     * @return boolean: false if the value is double, true otherwise
     * @access private
     * @author Teye Heimans
     */
	function _checkUniqueFields()
	{
		// only check for unique fields if the rest of the field is correct and
		// the database option is used...
		if( isset( $this->_db ) && is_object( $this->_db ) && !empty( $this->_table ))
		{
			// alias for the database object
			$db = $this->_db;

			// get the unique, not null fields and the the column types of the fields
			$unique  = $db -> getUniqueFields ( $this->_table );
			$notnull = $db -> getNotNullFields( $this->_table );
			$columns = $db -> getFieldTypes   ( $this->_table );
			$keys    = $db -> getPrKeys       ( $this->_table );

			// any unique fields found ?
			if( sizeof( $unique ) > 0)
			{
				// walk all unique indexes
				foreach( $unique as $index => $fields)
				{
					$fieldInForm = false;
					$extra       = array(); // extra query info for the where clause
					$quoted      = array(); // fields which are already quoted

					// walk all unique fields by this index
					foreach( $fields as $field )
					{
						// is the field a value which the user has added ?
						if( array_key_exists($field, $this->_add) )
						{
							// get hte value
							$value = $this->_add[$field];

							// if the value is not a sql function, quote it
							if( !in_array( $field, $this->_sql) )
							{
								$value = "'".$db->escapeString( $value )."'";
							}
						}
						// does the unique field exists in the form ?
						elseif( $this->fieldExists( $field ) )
						{
							$fieldInForm = $field;

							// is the field a datefield (in the form)?
							if( in_array( $field, $this->_date ) )
							{
								// get the column type (in the table)
								$type = strtolower( $columns[$field][0] );

								// is the field's type a date field ?
								if( strpos( strtolower($type), 'date') !== false )
								{
									// get the d, m and y value
									list( $y, $m, $d ) = $this->_fields[$field][1]->getAsArray();

									// all empty ?
									if( $d == '' && $y == '' && $m == '')
									{
										// can we save null ?
										if( !in_array( $field, $notnull ) )
										{
											$value = 'NULL';
										}
										// we cant save null. use 0000-00-00 instead
										else
										{
											$value = '0000-00-00';
										}
									}
									// not all fields are empty
									else
									{
										// make sure that there are values for each "field"
										if( $d == '' ) $d = 1;
										if( $m == '' ) $m = 1;
										if( $y == '' ) $y = date('Y');

										// save the value as date
										$value    = $db->dbDate( $y, $m, $d );
										$quoted[] = $field;
									}
								}
							}
							else
							// it's not a datefield
							{
								// get the form-value of the field
								$value = $db->escapeString( $this->_fields[$field][1]->getValue() );
							}

							// is the value null ?
							if( empty( $value ) && !in_array( $field, $notnull ) )
							{
								$value = 'NULL';
							}
							// is it an number and do we have to quote it ?
							else if(preg_match("/^-?([0-9]*\.?[0-9]+)$/", $value))
							{
								if( $db->_quoteNumbers )
								{
									$value = "'".$value."'";
								}
							}
							// quote the value
							else if( !in_array($field, $quoted))
							{
								$value = "'".$value."'";
							}
						}
						// the field does not exists in the form and is not
						// added by the user.
						else
						{
							// is the field not a primary key ?
							if( !in_array( $field, $keys ) )
							{
								/* new since 15 feb 2005 */
								// get the database value of the field
								$value = "'". $db -> escapeString( $this -> getValue( $field ) ) ."'" ;
							}
							// it's a primary key field..
							// the _getWhereClause is taking care of that ones..
							else
							{
								// we do not have a value for the field
								// so dont use it in the where clause.
								continue;
							}
						}

						$extra[$field] = $value;
					}
					// NULL in an nullable Field is not a unique constraint error!
					if (isset($value) && !array_key_exists($field, $notnull) && $value == "NULL")
					{
					    return true; 
					}
					
					$where = $this->_getWhereClause( '<>', $extra );

					// is there a selection filter ?
					if( is_array( $extra ) && sizeof( $extra ) > 0 )
					{
						// check if the entry is double
						$sql = $db->query(
						'SELECT COUNT(1) AS num FROM '.$db->quote( $this->_table ). $where
						);

						// query succeeded ?
						if( $sql )
						{
							// if so, set the error message and return false;
							if( $db->result( $sql, 0, 'num' ) > 0 )
							{
								if( !empty($fieldInForm) )
								{
									// get the error message
									$error =
									sizeof($unique[$index]) == 1 ?
									sprintf(
									$this->_text(35),
									$this->_fields[$fieldInForm][1]->getValue()
									) :
									sprintf(
									$this->_text(39),
									implode(', ', $unique[$index]),
									$index
									)
									;

									// set the error message
									$this->_fields[$fieldInForm][1]->_sError = $error;
								}
								/* new since 21 aug 2006*/
								// no field in the form (propably added with addValue)
								else
								{

									// get the error message
									$error =
									sizeof($unique[$index]) == 1 ?
									sprintf(
									$this->_text(35),
									$unique[$index][0]
									) :
									sprintf(
									$this->_text(39),
									implode(', ', $unique[$index]),
									$index
									)
									;
									//echo $error;
									// known bug: when using multple forms, the line is not added to the current page
									$this->addLine($error);
								}

								// return false (the form is not correct)
								return false;
							}
						}
						// query failed
						else
						{
							trigger_error(
							"Could not check for unique values bacause of a query error!\n".
							"Error: ". $this->_db->getError()."\n".
							"Query: ". $this->_db->getLastQuery(),
							E_USER_WARNING
							);
						}
					}
				}
			}
		}
		return true;
	}

	/**
	 * Remove data from thye junction table
	 *
	 * @param string $tableName
	 * @param string $keyField
	 * @param string $keyValue
	 * @return mixed
	 * 
	 * @author Remco van Arkelen
	 * @since 01-03-2010
	 */
	function deleteJunctionData( $tableName, $keyField, $keyValue )
	{
		$query = "DELETE FROM " . $tableName . " WHERE " . $keyField . " = '" . $keyValue . "';";
		return $this->_db->query( $query );
	}

	/**
     * Insert data into junction tbale
     *
     * @param string $tableName
     * @param string $keyField
     * @param string $destinationField
     * @param string $keyValue
     * 
     * @author Remco van Arkelen
     * @since 01-03-2010
     */

	function insertJunctionData( $tableName, $keyField, $destinationField, $keyValue )
	{
		/*@var $field Field*/
		$field = $this->_fields[ $keyField ][1];
		if( is_array( $field->getValue() ) )
		{
			foreach( $field->getValue() as $value )
			{
				$query = "INSERT INTO " . $tableName . "
	    		(" . $destinationField . ", " . $keyField . ") 
	    		VALUES ('".$keyValue."', '".$value."');";
				$this->_db->query( $query );
			}
		}
	}

	/**
     * dbFormHandler::_loadDbData()
     *
     * Load the data from the database when it's a edit form
     *
     * @return void
     * @access private
     * @author Teye Heimans
     */
	function _loadDbData()
	{
		// get the data from the database
		$sql = $this->_db->query(
		'SELECT * FROM ' . $this->_db->quote( $this->_table ) . $this->_getWhereClause()
		);

		// query succeeded?
		if( $sql )
		{
			// any records found?
			if( $this->_db->recordCount( $sql ) >= 1)
			{
				if( count( $this->_dbData ) == 0 )
				{
					$this->_dbData = $this->_db->getRecord( $sql );
				}

				if( $this->edit )
				{
					// Get data from juntion table
					foreach( $this->_tableConfig as $tableName => $fields )
					{
						$qrySelectJunctiondata = "SELECT " . $fields[0] . " AS ".$fields[0]." FROM " . $tableName . " WHERE " . $fields[1] . "='".pos( $this->_id )."'";
						$sql = $this->_db->query( $qrySelectJunctiondata );
						$values = array();
						while( $rs = $this->_db->getRecord( $sql ) )
						{
							$values[] = $rs[ $fields[0] ];
						}
						$this->setValue( $fields[0], $values );
					}
				}

			}
			// no records found, auto insert ?
			else if(FH_AUTO_INSERT)
			{
				// get the keys
				$keys = $this->_db->getPrKeys( $this->_table );

				// add the primary key values to the fields
				$size1 = sizeof( $keys );
				$size2 = sizeof( $this->_id );
				$size  = ( $size1 > $size2 ? $size2 : $size1 );

				for($i = 0; $i <  $size; $i++ )
				{
					$this->addValue( $keys[$i], $this->_id[$i] );
				}

				// change the form type from update to insert
				$this->insert = true;
				$this->edit   = false;
			}
			// record not found and no auto insert..
			// trigger error message
			else
			{
				trigger_error( 'Try to edit a none existing record!', E_USER_ERROR );
			}
		}
		else
		// query error, trigger error
		{
			trigger_error(
			"Could not load database values in the form bacause of a query error!\n".
			"Error: ". $this->_db->getError()."\n".
			"Query: ". $this->_db->getLastQuery(),
			E_USER_WARNING
			);
		}
	}

	/**
     * dbFormHandler::_saveData()
     *
     * Save the data into the database
     *
     * @param array $data: associative array with the fields => values which should be saved
     * @return int: the id which was used to save the record
     * @access private
     * @author Teye Heimans
     */
	function _saveDbData( $data )
	{
		// get the not-null fields from the table
		$notNullFields = $this->_db->getNotNullFields( $this->_table );

		// get the data which should be saved
		foreach( $data as $field => $value )
		{
			if( is_array( $value ) )
			{
				$value = implode(', ', $value);
			}

			// remove unneeded spaces
			$value = trim( $value );

			// do we have to save the field ?
			if(
			!in_array($field, $this->_dontSave) && # in dont save array
			( !isset( $this -> _fields[$field] ) || # not in the fields.. (so from the addValue array)
			!method_exists($this -> _fields[$field][1], 'getViewMode' ) || # or NOT in view mode!
			!$this -> _fields[$field][1] -> getViewMode() )
			)
			{
				// is the value empty and it can contain NULL, then save NULL
				if($value == '' && !in_array($field, $notNullFields) )
				{
					$value = 'NULL';
					$this->_sql[] = $field;

					// overwrite the old value with the new one
					$data[$field] = $value;
				}
			}
			// we dont have to save this value... remove it
			else
			{
				unset( $data[$field] );
			}
		}

		// get the column types of the fields
		$fields = $this->_db->getFieldTypes( $this->_table );

		// walk all datefields
		foreach( $this->_date as $field )
		{
			// do we still have to convert the value ?
			if( isset( $data[$field]) && $data[$field] != 'NULL' )
			{
				// does the field exists in the table?
				if( isset( $fields[$field] ) )
				{
					// get the fields type
					$type = strtolower( $fields[$field][0] );

					// is the field's type a date field ?
					if( strpos( strtolower($type), 'date') !== false )
					{
						// get the value from the field
						list( $y, $m, $d) = $this->_fields[$field][1]->getAsArray();

						// are all fields empty ?
						if( empty($d) && empty($m) && empty($y) )
						{
							// save NULL if possible, otherwise 0000-00-00
							if( in_array( $field, $notNullFields ) )
							{
								// this field cannot contain NULL
								$data[$field] = '0000-00-00';
							}
							// the field can contain null
							else
							{
								$data[$field] = 'NULL';
								$this -> _sql[] = $field;
							}
						}
						// not all fields are empty
						else
						{
							// make sure that there are values for each "field"
							if( $d == '' ) $d = '00';
							if( $m == '' ) $m = '00';
							if( $y == '' ) $y = '0000'; //date('Y');

							// save the value as date
							$data[$field] = $this->_db->dbDate( $y, $m, $d );
							$this->_sql[] = $field;
						}
					}
				}
			}
		}

		// get the query
		$query = $this->_getQuery( $this->_table, $data, $this->_sql, $this->edit, $this->_id );

		// for debugging.. die when we got the query
		//$this->dieOnQuery = true;
		if( isset( $this->dieOnQuery ) && $this->dieOnQuery )
		{
			echo "<pre>";
			echo $query;
			echo "</pre>";
			exit;
		}

		// make sure that there is something to save...
		if( !$query ) {
			return 0;
		}

		// execute the query
		$sql = $this->_db->query( $query );

		// query failed?
		if( !$sql )
		{
			trigger_error(
			"Error, query failed!<br />\n".
			"<b>Error message:</b> ".$this->_db->getError()."<br />\n".
			"<b>Query:</b> ". $query,
			E_USER_WARNING
			);
			return -1;
		}
		// query succeeded
		else
		{
			// is it an edit form ? Then return the known edit id's
			if( $this->edit )
			{
				$return = $this->_id;
			}
			// it's an insert form
			else
			{
				// get the inserted id
				$id = $this->_db->getInsertId( $this->_table );

				// got an id ?
				if( $id )
				{
					$return = $id;
				}
				// no id retrieved from the getInsertId!!
				// check if the id exists in the form
				else
				{
					// fetch the keys from the table
					$keys = $this->_db->getPrKeys( $this->_table );

					// walk the keys
					$result = array();
					foreach( $keys as $key )
					{
						// check if the key exists in the "save" data
						if( array_key_exists( $key, $data ) )
						{
							// replace possible quotes arround the data
							$result[] = trim($data[$key], "'" );
						}
					}

					$size = sizeof( $result );
					$return = ($size > 1) ? $result : ( $size == 1 ? $result[0] : null);
				}
			}
		}

		// execute actions on the junction table
		if( isset( $id ) || isset( $return ) )
		{
			$pk = isset( $id ) ? $id : pos( $return );
			foreach( $this->_tableConfig as $tableName => $fields )
			{
				if( !$this -> _fields[$fields[0]][1] -> getViewMode() )
				{
					$this->deleteJunctionData( $tableName, $fields[1], $pk );
					$this->insertJunctionData( $tableName, $fields[0], $fields[1], $pk );
				}
			}
		}

		// unset the database object (we dont need it anymore)
		unset( $this->_db );

		return $return;
	}

	/**
     * dbFormHandler::_getQuery()
     *
     * Generate a query from the given data and return it
     *
     * @param string $table: The table name
     * @param array $data: array of field => value  which should be saved
     * @param array $sqlFields: array of field which value is an SQL function (so it should not be quoted)
     * @param boolean $edit: do we have to generate an edit or insert query
     * @param array $keys: the primary key values
     * @return string: the query
     * @access private
     * @author Teye Heimans
     */
	function _getQuery( $table, $data, $sqlFields, $edit, $keys = null )
	{
		// get the field names from the table
		$fieldNames = $this->_db->getFieldNames( $this->_table );

		// check if we got the fieldnames
		if( !$fieldNames )
		{
			trigger_error(
			'Could not fetch the fieldnames of the table '. $this->_table,
			E_USER_WARNING
			);
			return false;
		}

		// walk the data from the form
		foreach( $data as $field => $value )
		{
			// does the field exists in the table?
			if( !in_array($field, $fieldNames) )
			{
				// field does not exists, remove it from the data array (we dont need it )
				unset( $data[$field] );
			}
			// the field exists in the table
			else
			{
				// is the value an array? Implode it!
				if( is_array($value) )
				{
					$value = implode(', ', $value);
				}

				// remove spaces etc
				$value = trim( $value );

				// if the value is not a SQL function...
				if(!in_array($field, $sqlFields) )
				{
					// ecape the value for saving it into the database
					$value = $this->_db->escapeString( $value );

					// is the value a number or float?
					if( preg_match('/^-?\d*\.?\d+$/', $value))
					{
						// do we have to quote it ?
						if( $this->_db->quoteNumbers() )
						{
							$value = "'".$value."'";
						}
					}
					// the value is not a number.. just quote it
					else
					{
						$value = "'".$value."'";
					}
					// save the value. It's now db ready
					$data[$field] = $value;
				}
			}
		}

		// check if there is still something left to save...
		if( sizeof( $data ) == 0 )
		{
			return false;
		}

		// if it's an edit form
		if($edit)
		{
			// generate the update query
			$query = 'UPDATE '.$this->_db->quote( $this->_table )." SET \n";

			// walk all fields and add them to the query
			foreach($data as $field => $value)
			{
				$query .= ' '.$this->_db->quote( $field ).' = '.$value.", \n";
			}

			// add the where clause to the query
			$query = substr($query, 0, -3) . $this->_getWhereClause();
		}
		// the form is an insert form..
		else
		{
			// generate the insert query
			$query = 'INSERT INTO '.$this->_db->quote( $this->_table )." (\n";

			// add the field names to the query
			foreach( array_keys($data) as $field )
			{
				$query .= '  '. $this->_db->quote( $field ) .", \n";
			}

			// add the values to the query
			$query  = substr($query, 0, -3) . ") VALUES (\n  ";
			$query .= implode(",\n  ", $data)."\n);";
		}

		// return the query
		return $query;
	}

	/**
     * dbFormHandler::_getWhereClause()
     *
     * Get the where clause for a query
     *
     * @param string $operator The operator used for the primary key's and their values
     * @param array $extra: extra where clause arguments
     * @param string $extraOperator: operator used for the extra arguments
     * @return string
     * @access public
     * @author Teye Heimans
     */
	function _getWhereClause( $operator = '=', $extra = array(), $extraOperator = '=' )
	{
		// get the primary key fields from the table
		$keys = $this->_db->getPrKeys( $this->_table );

		// get the values for the key fields
		$record = $this->_id;

		// walk untill the field's or values are all handled
		$size1 = sizeof( $record );
		$size2 = sizeof( $keys );
		$size  = ($size1 < $size2 ? $size1 : $size2);

		$data = array();
		for($i = 0; $i < $size; $i++ )
		{
			// is the key added by the user?
			if( isset( $extra[$keys[$i]] ) && $extra[$keys[$i]] == $record[$i] )
			{
				unset( $extra[$keys[$i]] );
			}
			else
			{
				// is the value an float or integer ?
				if( preg_match("/^-?\d*\.?\d+$/", $record[$i]) )
				{
					// do we need to quote integers ?
					if( $this->_db->quoteNumbers() )
					{
						// query the value
						$record[$i] = "'".$record[$i]."'";
					}
				}
				// just quote the value
				else
				{
					$record[$i] = "'".$record[$i]."'";
				}

				// save the where clause data
				$data[] = ' '.$this->_db->quote( $keys[$i] ) .' '.$operator.' '.$record[$i].' ';
			}
		}

		// prepare the other data
		foreach ( $extra as $field => $value )
		{
			// quote the fieldname
			$field = $this -> _db -> quote( $field );

			// null value ?
			if( is_null( $value) || strtoupper( $value ) == 'NULL' )
			{
				$data[] =  $field .' IS NULL ';
			}
			// not a null value
			else
			{
				$data[] = $field .' '.$extraOperator .' '.$value;
			}
		}

		// return the where clause
		return (sizeof($data) ? " WHERE \n " . implode("\n  AND ", $data) : '');
	}
}
?>