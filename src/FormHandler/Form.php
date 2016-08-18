<?php
namespace FormHandler;

/**
 * The Form Object which represents a form and all it's functionality.
 *
 * The main functionality of this object is to check if it's submitted and
 * if it's valid. To do the last, all the fields in the form are checked to be valid.
 *
 * A basic example usage of the form is below.
 * Informations about formatters, {@see AbstractFormatter}
 * Information about the default working of the form fields, {@see AbstractFormField}
 * Information about validators, {@see AbstractValidator}
 *
 * <code>
 * <?php
 *
 * // create the form
 * $form = new Form('/url/to/submit')
 * -> setMethod( Form::METHOD_POST ); # not really needed, because post is default
 *
 * // add the fields
 * $form -> textField('field_name_here')
 * -> setSize( 20 )
 * -> addValidator( new StringValidator( 2, 50) );
 *
 * $form -> passField('another_field_name')
 * -> setStyle('border: 1px solid black');
 *
 * $form -> checkBox('agree', 'on')
 * -> setId('the_id_here');
 *
 * // ... more ...
 *
 * // if the form is submitted
 * if( $form -> isSubmitted() )
 * {
 * // check if all fields are valid
 * if( $form -> isValid() )
 * {
 * // here, do your thing, like
 * // save the data in your database.
 * }
 * }
 *
 * ?>
 *
 * <!--
 * Here your HTML.
 * Please note the different methods about retrieveing the fields:
 * - getFieldById()
 * - getFieldByName()
 * -->
 * Your name:
 * <?php echo $form -> getFieldByName('field_name_here'); ?><br /><br />
 *
 * Your password:
 * <?php echo $form -> getFieldByName('another_field_name'); ?><br /><br />
 *
 * Do you agree?
 * <?php echo $form -> getFieldById('the_id_here'); ?><br /><br />
 *
 * <input type="submit" value="Submit" />
 * </code>
 */
class Form extends Element
{

    protected $action;

    protected $target;

    protected $name;

    protected $method = 'post';

    protected $enctype;

    protected $accept_charset;

    protected $accept;

    protected $fields = array();

    protected $formatter;

    protected $encodingFilter;

    protected $submitted = null;

    protected static $defaultFormatter = null;

    protected static $defaultEncodingFilter = null;

    protected static $defaultCsrfProtectionEnabled = null;

    protected $csrfProtection;
    // Should we enable Cross Site Request Forgery protection? If not given, we will use possible default settings. If those are not set, we will enable it for POST forms.
    const METHOD_GET = 'get';

    const METHOD_POST = 'post';

    const ENCTYPE_MULTIPART = 'multipart/form-data';

    const ENCTYPE_PLAIN = 'text/plain';

    const ENCTYPE_URLENCODED = 'application/x-www-form-urlencoded';

    /**
     * Create a new Form object.
     *
     * @param string $action
     *            The action where the form should be submitted to. If left empty, it will submit to the current url (default)
     * @param string $csrfprotection
     *            Should we enable Cross Site Request Forgery protection? If not given, we will use possible default settings. If those are not set, we will enable it for POST forms.
     */
    public function __construct($action = '', $csrfprotection = null)
    {
        // store our CSRF state.
        $this->csrfProtection = $csrfprotection;

        $this->action = $action;
        $this->setFormatter(Form::$defaultFormatter ? Form::$defaultFormatter : new PlainFormatter());
        $this->setEncodingFilter(Form::$defaultEncodingFilter ? Form::$defaultEncodingFilter : new Utf8EncodingFilter());
    }

    /**
     * Set a default formatter for all the form objects which are created.
     *
     * This could be useful when creating multiple forms in your project, and
     * if you don't want to set a custom formatter for every Form object.
     *
     * Example:
     * <code>
     * <?php
     *
     * // set the default formatter which should be used
     * Form::setDefaultFormatter( new MyCustomFormatter() );
     *
     * ?>
     * </code>
     *
     * For more information about formatters, {@see AbstractFormatter}
     *
     * @param AbstractFormatter $formatter
     */
    static function setDefaultFormatter(AbstractFormatter $formatter)
    {
        Form::$defaultFormatter = $formatter;
    }

    /**
     * Get the default formatter.
     *
     * For more information about formatters, {@see AbstractFormatter}
     *
     * @return AbstractFormatter
     */
    static function getDefaultFormatter()
    {
        return Form::$defaultFormatter;
    }

    /**
     * Set a default encoding filter.
     * This will be used to filter the input to make sure it's the correct encoding.
     *
     * This could be useful when creating multiple forms in your project, and
     * if you don't want to set a custom encoding filter for every Form object.
     *
     * Example:
     * <code>
     * <?php
     *
     * // set the default formatter which should be used
     * Form::setDefaultEncodingFilter( new Utf8EncodingFilter() );
     *
     * ?>
     * </code>
     *
     * @param InterfaceEncodingFilter $formatter
     */
    static function setDefaultEncodingFilter(InterfaceEncodingFilter $filter)
    {
        Form::$defaultEncodingFilter = $filter;
    }

    /**
     * Return the default encoding filter.
     *
     * @return InterfaceEncodingFilter
     */
    static function getDefaultEncodingFilter()
    {
        return Form::$defaultEncodingFilter;
    }

    /**
     * Return if CSRF protection is enabled by default.
     *
     * @return boolean
     */
    static function getDefaultCsrfProtectionEnabled()
    {
        return Form::$defaultCsrfProtectionEnabled;
    }

    /**
     * Set if CSRF protection is enabled by default.
     *
     * @param boolean $enabled
     */
    static function setDefaultCsrfProtectionEnabled($enabled)
    {
        Form::$defaultCsrfProtectionEnabled = (bool) $enabled;
    }

    /**
     * Should we enable Cross Site Request Forgery protection?
     * If not given, we will use possible default settings.
     * If those are not set, we will enable it for POST forms.
     * Set the value for csrfProtection
     *
     * @return Form
     */
    public function setCsrfProtection($value)
    {
        $this->csrfProtection = $value;
        return $this;
    }

    /**
     * Should we enable Cross Site Request Forgery protection?
     * If not given, we will use possible default settings.
     * If those are not set, we will enable it for POST forms.
     * Get the value for csrfProtection
     *
     * @return string
     */
    public function getCsrfProtection()
    {
        return $this->csrfProtection;
    }

    /**
     * Return's the fields value from the GET or POST array, or null if not found.
     * NOTE: This function should NOT be used for retrieving the value which is set in the field itsself,
     * because this function will only retrieve the value from GET/POST array.
     *
     * To retrieve the current value for the field itsself, please use:
     * <code>
     * <?php
     *
     * $form -> getFieldByName('x') -> getValue();
     *
     * ?>
     * </code>
     *
     * This method can handle field names with "square brackets" in it, but it
     * only works well if the square brackets have a 'name' in it.
     *
     * For example; "record[1]" as name works fine, but if you name your fields
     * "record[]", we don't known the differences between the fields, and is thus not
     * recommended!
     *
     * All fields will call this method to 'ask' for their value if the form was submitted.
     *
     * @param string $name
     * @return mixed
     */
    public function getFieldValue($name)
    {
        static $cache = array();

        // if not yet in the cache, look it up
        if (! array_key_exists($name, $cache)) {
            if ($this->getMethod() == Form::METHOD_GET) {
                $list = $_GET;
            } else
                if ($this->getMethod() == Form::METHOD_POST) {
                    $list = $_POST;
                } else {
                    return null;
                }

            // look it up directly in the get/post array
            if (array_key_exists($name, $list)) {
                $cache[$name] = $list[$name];
            }

            // check for braces (array names?)
            else
                if (preg_match_all('/\[(.*)\]/U', $name, $match, PREG_OFFSET_CAPTURE)) {
                    // Walk all found matches and create a list of names
                    // (could be more, like "field[name1][name2]
                    $names = array();
                    foreach ($match[0] as $i => $part) {
                        if ($i == 0) {
                            $names[] = substr($name, 0, $part[1]);
                        }

                        $names[] = substr($name, $part[1] + 1, strlen($part[0]) - 2);
                    }

                    // now, create a copy of our input array and walk the array.
                    // First look for name1, then go into that array and continue with name2, etc.
                    $base = $list;
                    foreach ($names as $keyName) {
                        if ((string) $keyName !== "") {
                            if (is_array($base) && array_key_exists($keyName, $base)) {
                                $base = $base[$keyName];
                            }  // no value found!
else {
                                $base = null;
                            }
                        }
                    }

                    $cache[$name] = $base;
                } else {
                    $cache[$name] = null;
                }

            // do we have a value?
            if ($cache[$name]) {
                // make sure we filter the input
                if ($this->encodingFilter instanceof InterfaceEncodingFilter) {
                    // filter the input
                    $cache[$name] = $this->encodingFilter->filter($cache[$name]);
                }
            }
        }

        return $cache[$name];
    }

    /**
     * Return an array with all fields from this form.
     * This method will return an array.
     * When there are no fields, it will return an empty array.
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Remove a field from the form
     *
     * @param Element $field
     * @return Form
     */
    public function removeField(Element $field)
    {
        foreach ($this->fields as $i => $elem) {
            if ($elem == $field) {
                unset($this->fields[$i]);
                break;
                ;
            }
        }

        return $this;
    }

    /**
     * Remove a field from the form by the name of the field
     *
     * @param Element $field
     * @return Form
     */
    public function removeFieldByName($name)
    {
        foreach ($this->fields as $i => $field) {
            if ($field->getName() == $name) {
                unset($this->fields[$i]);
                break;
            }
        }

        return $this;
    }

    /**
     * Remove a field from the form by the name of the field
     *
     * @param Element $field
     * @return Form
     */
    public function removeFieldById($id)
    {
        foreach ($this->fields as $i => $field) {
            if ($field->getId() == $id) {
                unset($this->fields[$i]);
                break;
            }
        }

        return $this;
    }

    /**
     * Overwrite the isSubmitted value.
     * Could be useful sometimes to
     * allow a form to be submitted even if some fields are missing.
     *
     * @param boolean $status
     */
    public function setSubmitted($status)
    {
        $this->submitted = (bool) $status;
    }

    /**
     * Return's true if the form is submitted.
     *
     * Checks if all the fields are in the correct array ($_GET or $_POST)
     * (accept for checkboxes and radio buttons, these are not available if they are unckecked)
     *
     * Please note that if you have a form with only a checkbox or radio button in it,
     * this method will always return true (when the request method is correct).
     * This is because when the form is submitted and the checkbox / radio button is not selected,
     * there is no way to check if the form is really submitted. In this case you should add
     * an hidden field, just to check if the form was submitted or not.
     *
     * @param string $reason
     *            This parameter will be filled with the "reason" why the form is not "submitted"
     * @return boolean
     */
    public function isSubmitted(&$reason = null)
    {
        $reason = '';
        if ($this->submitted === null) {
            $this->submitted = true;

            // first check if the request method is the same..
            if ($this->getMethod() == Form::METHOD_GET && $_SERVER['REQUEST_METHOD'] != 'GET') {
                $reason = 'Request method is incorrect (should be GET).';
                $this->submitted = false;
            } else
                if ($this->getMethod() == Form::METHOD_POST && $_SERVER['REQUEST_METHOD'] != 'POST') {
                    $reason = 'Request method is incorrect (should be POST).';
                    $this->submitted = false;
                }

            $list = ($this->getMethod() == Form::METHOD_GET ? $_GET : $_POST);

            // if still "true", check the fields
            if ($this->submitted) {
                foreach ($this->fields as $field) {
                    if ($field instanceof AbstractFormField && ! ($field instanceof CheckBox || $field instanceof RadioButton) && $field->getName()) {
                        $name = $field->getName();

                        // remove possible brackets
                        if (($i = strpos($name, '[')) !== false) {
                            $name = substr($name, 0, $i);
                        }

                        if ($field instanceof UploadField) {
                            if (! $field->isDisabled()) {
                                if (! array_key_exists($name, $_FILES)) {
                                    $reason = 'Upload field "' . $name . '" does not exists in $_FILES array.';
                                    $this->submitted = false;
                                }
                            }
                        } // selectfields are not in the "_POST" array when they are "multiple" selectable and
                          // nothing is selected.
                        elseif ($field instanceof SelectField) {
                            if ($field->getOptions()->count() == 0 && ! array_key_exists($name, $list)) {
                                // form can still be submitted. An empty select with no options is not included in the POST field
                            }  // of only 1 option can be selected, it should be in the submitted values!
else
                                if (! $field->isMultiple() && ! array_key_exists($name, $list)) {

                                    if (! $field->isDisabled()) {
                                        $reason = 'Selectfield "' . $name . '" does not exists in submited data array.';
                                        $this->submitted = false;
                                    }
                                }
                        } elseif (! array_key_exists($name, $list)) {
                            if (! $field->isDisabled()) {
                                $reason = 'Field "' . $name . '" does not exists in submited data array.';
                                $this->submitted = false;
                            }
                        }
                    } // submit button? Then the name => value should exists!
elseif ($field instanceof SubmitButton && $field->getName()) {

                        if (! $field->isDisabled()) {
                            if (! array_key_exists($field->getName(), $list)) {
                                $reason = 'Submitbutton "' . $name . '" does not exists in submited data array.';
                                $this->submitted = false;
                            }
                        }
                    }  // image button? Then the name_x and name_y values should exists!
else
                        if ($field instanceof ImageButton && $field->getName()) {

                            if (! $field->isDisabled()) {
                                if (! array_key_exists($field->getName() . '_x', $list) || ! array_key_exists($field->getName() . '_y', $list)) {
                                    $reason = 'Imagebutton "' . $name . '" should submit a _x and _y values, but they are not in the data array.';
                                    $this->submitted = false;
                                }
                            }
                        }
                }
            }
        }

        // is this form not submitted and we want to enable csrf protection?
        if ($this->isCsrfProtectionEnabled()) {
            // if we do not have a csrf-token field yet, add it.
            if (! $this->getFieldByName('csrftoken')) {
                // Add a hidden 'csrftoken' field.
                $field = $this->hiddenField('csrftoken')->addValidator(new CsrfValidator());

                if (! $this->submitted || ! $field->isValid()) {
                    $field->setValue(CsrfValidator::generateToken());
                }
            }
        }

        return $this->submitted;
    }

    /**
     * Return a field by it's name
     *
     * @param string $name
     * @return AbstractFormField
     */
    public function getFieldByName($name)
    {
        foreach ($this->fields as $field) {
            if ($field->getName() == $name) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Return a list of fields which have the name which equals the given name
     *
     * @param string $name
     * @return ArrayObject
     */
    public function getFieldsByName($name)
    {
        $result = new ArrayObject();

        foreach ($this->fields as $field) {
            if ($field->getName() == $name) {
                $result->append($field);
            }
        }

        return sizeof($result) > 0 ? $result : null;
    }

    /**
     * Return a field by it's id
     *
     * @param string $id
     * @return AbstractFormField
     */
    public function getFieldById($id)
    {
        foreach ($this->fields as $field) {
            if ($field->getId() == $id) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Check if the Cross-Site-Request-Forgery (CSRF) security token was valid or not.
     *
     * @return boolean
     */
    public function isCsrfValid()
    {
        // csrf protection is disabled.
        if (! $this->isCsrfProtectionEnabled()) {
            return true;
        }

        // the form is not submitted
        if (! $this->isSubmitted()) {
            return true;
        }

        // the form is valid, so the csrf is also valid.
        if ($this->isValid()) {
            return true;
        }

        // the form is invalid. Let's check if the CSRF value is the problem.
        foreach ($this->fields as $field) {
            if ($field instanceof AbstractFormField && $field->getName() == 'csrftoken' && ! $field->isValid()) {
                return false;
            }
        }

        // the field is not the problem...
        return true;
    }

    /**
     * Check if the fields within the form are valid
     *
     * @return boolean
     */
    public function isValid()
    {
        $valid = true;
        foreach ($this->fields as $field) {
            if ($field instanceof AbstractFormField && ! $field->isValid()) {
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Fetch all error messages for fields
     *
     * @return array
     */
    public function getErrorMessages()
    {
        $errors = array();
        foreach ($this->fields as $field) {
            if ($field instanceof AbstractFormField && ! $field->isValid()) {
                $fldErrors = $field->getErrorMessages();
                $errors = array_merge($errors, $fldErrors);
            }
        }

        return $errors;
    }

    /**
     * Set a formatter object
     *
     * @param AbstractFormatter $formatter
     * @return Form
     */
    public function setFormatter(AbstractFormatter $formatter)
    {
        $this->formatter = $formatter;
        $this->formatter->setForm($this);
        return $this;
    }

    /**
     * Return the formatter
     *
     * @return AbstractFormatter
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * Set an encodingFilter.
     * This filter will be used to filter all the imput so that its of the correct character encoding.
     *
     * @param InterfaceEncodingFilter $value
     * @return Form
     */
    public function setEncodingFilter(InterfaceEncodingFilter $value)
    {
        $this->encodingFilter = $value;

        // initialize the encoder.
        $this->encodingFilter->init($this);
        return $this;
    }

    /**
     * Return the EncodingFilter, which is used to filter all the imput so that its of the correct character encoding.
     *
     * @return InterfaceEncodingFilter
     */
    public function getEncodingFilter()
    {
        return $this->encodingFilter;
    }

    /**
     * add a field to this form, so that it can be retrieved by the method getFieldByName
     *
     * @param Element $element
     */
    public function addField(Element &$field)
    {
        $this->fields[] = $field;
    }

    /**
     * Set the action of this form and return the Form reference.
     * NOTE: The action is added in the HTML without any escaping! Make sure YOU have escaped possible dangerous characters!
     *
     * @param string $action
     * @return Form
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Return the action of this form
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the target and return the Form reference
     *
     * @deprecated
     *
     * @param string $target
     * @see http://www.w3schools.com/tags/tag_form.asp
     * @return Form
     */
    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }

    /**
     * Return the target
     *
     * @deprecated
     *
     * @see http://www.w3schools.com/tags/tag_form.asp
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set the name of the form and return the Form reference
     *
     * @param string $name
     * @return Form
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Return the name of the form
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the mime-types of files that can be submitted through a file upload
     * and return the Form reference
     *
     * @param string $accept
     * @return Form
     */
    public function setAccept($accept)
    {
        $this->accept = $accept;
        return $this;
    }

    /**
     * Return mime-types of files that can be submitted through a file upload
     *
     * @return string
     */
    public function getAccept()
    {
        return $this->accept;
    }

    /**
     * Set how form-data should be encoded before sending it to a server
     * Possible values:
     * - application/x-www-form-urlencoded
     * - multipart/form-data
     * - text/plain
     *
     * @param string $enctype
     * @return Form
     */
    public function setEnctype($enctype)
    {
        $enctype = strtolower(trim($enctype));

        if (in_array($enctype, array(
            'application/x-www-form-urlencoded',
            'multipart/form-data',
            'text/plain'
        ))) {
            $this->enctype = $enctype;
        }

        return $this;
    }

    /**
     * Return how form-data should be encoded before sending it to a server
     *
     * @return string
     */
    public function getEnctype()
    {
        return $this->enctype;
    }

    /**
     * Return the method how this form should be submitted
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the method how this form should be submitted
     *
     * @param string $method
     * @return Form
     */
    public function setMethod($method)
    {
        $method = strtolower($method);

        if (in_array($method, array(
            Form::METHOD_GET,
            Form::METHOD_POST
        ))) {
            $this->method = $method;
        }
        return $this;
    }

    /**
     * Specifies the character-sets the server can handle for form-data
     * Returns the Form reference
     *
     * @param string $charset
     * @return Form
     */
    public function setAcceptCharset($charset)
    {
        $this->accept_charset = $charset;
        return $this;
    }

    /**
     * Returns the character-sets the server can handle for form-data
     *
     * @return string
     */
    public function getAcceptCharset()
    {
        return $this->accept_charset;
    }

    /**
     * Return a string representation of the form tag
     *
     * @return string
     */
    public function render()
    {
        $str = '<form action="' . $this->action . '"';

        if (! empty($this->name)) {
            $str .= ' name="' . $this->name . '"';
        }

        if (! empty($this->accept)) {
            $str .= ' accept="' . $this->accept . '"';
        }

        if (! empty($this->accept_charset)) {
            $str .= ' accept-charset="' . $this->accept_charset . '"';
        }

        if (! empty($this->enctype)) {
            $str .= ' enctype="' . $this->enctype . '"';
        }

        if (! empty($this->method)) {
            $str .= ' method="' . $this->method . '"';
        }

        if (! empty($this->target)) {
            $str .= ' target="' . $this->target . '"';
        }

        $str .= parent::render();
        $str .= ">";

        return $str;
    }

    /**
     * Create a new textfield
     *
     * @param string $name
     * @return TextField
     */
    public function textField($name)
    {
        return new TextField($this, $name);
    }

    /**
     * Create a new passfield
     *
     * @param string $name
     * @return PassField
     */
    public function passField($name)
    {
        return new PassField($this, $name);
    }

    /**
     * Create a checkbox
     *
     * @param string $name
     * @param string $value
     * @return CheckBox
     */
    public function checkBox($name, $value = '1')
    {
        return new CheckBox($this, $name, $value);
    }

    /**
     * Return a new hiddenfield
     *
     * @param string $name
     * @return HiddenField
     */
    public function hiddenField($name)
    {
        return new HiddenField($this, $name);
    }

    /**
     * Return a new radiobutton
     *
     * @param string $name
     * @param string $value
     * @return RadioButton
     */
    public function radioButton($name, $value = null)
    {
        return new RadioButton($this, $name, $value);
    }

    /**
     * Return a new selectfield
     *
     * @param string $name
     * @return SelectField
     */
    public function selectField($name)
    {
        return new SelectField($this, $name);
    }

    /**
     * Return a new uploadfield
     *
     * @param string $name
     * @return UploadField
     */
    public function uploadField($name)
    {
        return new UploadField($this, $name);
    }

    /**
     * Return a new textarea
     *
     * @param string $name
     * @param int $cols
     * @param int $rows
     * @return TextArea
     */
    public function textArea($name, $cols = 40, $rows = 7)
    {
        return new TextArea($this, $name, $cols, $rows);
    }

    /**
     * Create a new SubmitButton
     *
     * @param string $name
     * @param string $value
     * @return SubmitButton
     */
    public function submitButton($name, $value = '')
    {
        $button = new SubmitButton($this, $value);
        $button->setName($name);
        return $button;
    }

    /**
     * Create a new ImageButton
     *
     * @param string $name
     * @param string $src
     * @return ImageButton
     */
    public function imageButton($name, $src = '')
    {
        $button = new ImageButton($this, $src);
        $button->setName($name);
        return $button;
    }

    /**
     * Return the HTML field formatted
     */
    public function __toString()
    {
        $formatter = $this->getFormatter();
        if ($formatter) {
            return $formatter->format($this);
        }

        return $this->render();
    }

    /**
     * Returns the current CSRF protection state.
     * CSRF stands for Cross Site Request Forgery.
     *
     * This method returns if we want to use a token to protect ourselfs agains this kind of attacks.
     *
     * @return boolean
     */
    protected function isCsrfProtectionEnabled()
    {
        // is there no session available? Then always disable CSRF protection.
        if (session_id() == '') {
            return false;
        }

        if ($this->csrfProtection === null) {
            $this->csrfProtection = self::$defaultCsrfProtectionEnabled;
        }

        if ($this->csrfProtection === null) {
            return $this->getMethod() == Form::METHOD_POST;
        }

        return $this->csrfProtection;
    }
}