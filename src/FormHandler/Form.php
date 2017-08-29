<?php
namespace FormHandler;

use FormHandler\Encoding\InterfaceEncodingFilter;
use FormHandler\Encoding\Utf8EncodingFilter;
use FormHandler\Field\AbstractFormButton;
use FormHandler\Field\AbstractFormField;
use FormHandler\Field\CheckBox;
use FormHandler\Field\Element;
use FormHandler\Field\HiddenField;
use FormHandler\Field\ImageButton;
use FormHandler\Field\PassField;
use FormHandler\Field\RadioButton;
use FormHandler\Field\SelectField;
use FormHandler\Field\SubmitButton;
use FormHandler\Field\TextArea;
use FormHandler\Field\TextField;
use FormHandler\Field\UploadField;
use FormHandler\Renderer\AbstractRenderer;
use FormHandler\Renderer\XhtmlRenderer;
use FormHandler\Validator\CsrfValidator;

/**
 * The Form Object which represents a form and all it's functionality.
 *
 * The main functionality of this object is to check if it's submitted and
 * if it's valid. To do the last, all the fields in the form are checked to be valid.
 *
 * A basic example usage of the form is below.
 *
 * ```php
 * <?php
 *
 * // create the form
 * $form = new Form('/url/to/submit');
 * $form -> setMethod( Form::METHOD_POST ); # not really needed, because post is default
 *
 * // add the fields
 * $form -> textField('field_name_here')
 *       -> setSize( 20 )
 *       -> addValidator( new StringValidator( 2, 50) );
 *
 * $form -> passField('another_field_name')
 *       -> setStyle('border: 1px solid black');
 *
 * $form -> checkBox('agree', 'on')
 *       -> setId('the_id_here');
 *
 * // ... more ...
 *
 * // if the form is submitted
 * if( $form -> isSubmitted() )
 * {
 *     // check if all fields are valid
 *     if( $form -> isValid() )
 *     {
 *         // here, do your thing, like
 *         // save the data in your database.
 *     }
 * }
 * ```
 *
 * Then, in your view, you can display the fields where you like:
 * ```php
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
 * <-- of course you can also just create a submitBtn(), but this is still allowed -->
 * <input type="submit" value="Submit" />
 * </code>
 * ```
 */
class Form extends Element
{
    /**
     * Constant which can be used to set the form's submit method to GET
     */
    const METHOD_GET = 'get';

    /**
     * Constant which can be used to set the form's submit method to POST (default)
     */
    const METHOD_POST = 'post';

    /**
     * No characters are encoded. This value is required when you are using forms that have a file upload control
     */
    const ENCTYPE_MULTIPART = 'multipart/form-data';

    /**
     * Spaces are converted to "+" symbols, but no special characters are encoded
     */
    const ENCTYPE_PLAIN = 'text/plain';

    /**
     * Default. All characters are encoded before sent
     * (spaces are converted to "+" symbols, and special characters are converted to ASCII HEX values)
     */
    const ENCTYPE_URLENCODED = 'application/x-www-form-urlencoded';

    /**
     * The default renderer which will be used for all FromHandler instances.
     * @var Renderer\AbstractRenderer
     */
    protected static $defaultRenderer = null;

    /**
     * The default encoding filter which will be used for all FormHandler instancens.
     * @var Encoding\InterfaceEncodingFilter
     */
    protected static $defaultEncodingFilter = null;

    /**
     * Out default settings for our csrf protection which will be used for all FormHandler instances.
     * @var boolean
     */
    protected static $defaultCsrfProtectionEnabled = true;

    /**
     * After parsing the submitted values we cache these so that we don't have to analyse them more then once.
     * @var array
     */
    protected static $cache = [];

    /**
     * The action of the Form (location where the form is sent to).
     * When the action is empty, it will be posted to itsself (default)
     * @var string
     */
    protected $action;

    /**
     * The target window where the form should posted to
     * @var string
     * @deprecated
     */
    protected $target = '';

    /**
     * The name of the form.
     * @var string
     */
    protected $name;

    /**
     * The method how the form is submitted. Can either be POST or GET
     * @var string
     */
    protected $method = self::METHOD_POST;

    /**
     * The encoding type of the form. Use one of the ENCTYPE_* constants.
     * @var string
     */
    protected $enctype = self::ENCTYPE_URLENCODED;

    /**
     * Specifies the character encodings that are to be used for the form submission
     * @var string
     */
    protected $acceptCharset;

    /**
     * Not supported in HTML5.
     * Specifies a comma-separated list of file types that the server accepts
     * (that can be submitted through the file upload)
     *
     * @var string
     * @deprecated
     */
    protected $accept;

    /**
     * List of all fields in this form
     * @var array
     */
    protected $fields = [];

    /**
     * An renderer which will be used to render the fields.
     * @var Renderer\AbstractRenderer
     */
    protected $renderer;

    /**
     * An encoding filter which will be used to filter the data
     * @var Encoding\InterfaceEncodingFilter
     */
    protected $encodingFilter;

    /**
     * Remember if this form was submitted or not. When this is null, we did not check
     * yet if the form was submitted, and we will parse the complete request and store the result here.
     * @var boolean
     */
    protected $submitted = null;

    /**
     * Should we enable Cross Site Request Forgery protection?
     * If not given, we will use possible default settings. If those are not set, we will enable it for POST forms.
     * @var bool
     */
    protected $csrfProtection;

    /**
     * Create a new Form object.
     *
     * @param string $action The action where the form should be submitted to.
     *                                  If left empty, it will submit to the current url (default)
     * @param string $csrfprotection Should we enable Cross Site Request Forgery protection? If not given,
     *                                  we will use default settings. If those are not set,
     *                                  we will enable it
     */
    public function __construct($action = '', $csrfprotection = null)
    {
        // store our CSRF state.
        $this->setCsrfProtection($csrfprotection === null ? static::$defaultCsrfProtectionEnabled : $csrfprotection);

        $this->action = $action;
        $this->setRenderer(Form::$defaultRenderer ?: new XhtmlRenderer());
        $this->setEncodingFilter(Form::$defaultEncodingFilter ?: new Utf8EncodingFilter());

        // make sure that cache is cleared.
        $this->clearCache();
    }

    /**
     * Should we enable Cross Site Request Forgery protection?
     * If not given, we will use possible default settings.
     * If those are not set, we will enable it for POST forms.
     * Set the value for csrfProtection
     *
     * @param bool $value
     * @return Form
     */
    public function setCsrfProtection($value)
    {
        $this->csrfProtection = (bool)$value;

        if (!$value) {
            $this->removeFieldByName('csrftoken');
        } else {
            $field = $this->getFieldByName('csrftoken');

            // if the field does not exists yet, lets add it.
            if ($field === null) {
                $submitted = $this->isSubmitted();

                // Add a hidden 'csrftoken' field.
                $field = $this->hiddenField('csrftoken');

                $field->addValidator(new CsrfValidator());

                if (!$submitted && !$field->getValue()) {
                    $field->setValue(CsrfValidator::generateToken());
                }
            }
        }

        return $this;
    }

    /**
     * Remove a field from the form by the name of the field.
     * If there are more then 1 field with the given name, then only the first one will
     * be removed.
     *
     * @param $name
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
     * Return a field by it's name. We will return null if it's not found.
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
     * @param string $reason This parameter will be filled with the "reason" why the form is not submitted
     * @return boolean
     */
    public function isSubmitted(&$reason = null)
    {
        $reason = '';

        // if we did not yet detect before if this field was submitted, then analyze the submitted values.
        if ($this->submitted === null) {
            $this->submitted = true;

            // first check if the request method is the same..
            if (empty($_SERVER['REQUEST_METHOD'])) {
                $this->submitted = false;
                $reason = 'No request method is known (should be GET or POST)';
            } elseif ($this->getMethod() == Form::METHOD_GET && $_SERVER['REQUEST_METHOD'] != 'GET') {
                $reason = 'Request method is incorrect (should be GET).';
                $this->submitted = false;
            } elseif ($this->getMethod() == Form::METHOD_POST && $_SERVER['REQUEST_METHOD'] != 'POST') {
                $reason = 'Request method is incorrect (should be POST).';
                $this->submitted = false;
            }

            $list = ($this->getMethod() == Form::METHOD_GET ? $_GET : $_POST);

            // Get our number of buttons in the form.
            // If there are multiple buttons, then the form should accept submissions which does not include
            // the button, but then we expect only 1 button (we ignore disabled buttons and buttons without a name)
            $buttonCount = 0;
            foreach ($this->fields as $field) {
                if ($field instanceof AbstractFormButton) {
                    if (!$field->isDisabled() && $field->getName()) {
                        $buttonCount++;
                    }
                }
            }


            // if still "true", check the fields
            if ($this->submitted) {
                // keep track of how many buttons we have found in our form.
                $buttonsFound = 0;

                foreach ($this->fields as $field) {
                    // ignore disabled fields/buttons and without a name
                    if ($field->isDisabled() || !$field->getName()) {
                        continue;
                    }

                    $name = $field->getName();

                    // remove possible brackets
                    if (($i = strpos($name, '[')) !== false) {
                        $name = substr($name, 0, $i);
                    }

                    // Upload field ?
                    // Then we expect the name of the field in the $_FILES array
                    if ($field instanceof UploadField) {
                        if (!array_key_exists($name, $_FILES)) {
                            $reason = 'Upload field "' . $name . '" does not exists in $_FILES array.';
                            $this->submitted = false;
                        }
                    } // Selectfields are not in the "_POST" array when they are "multiple" selectable and
                    // nothing is selected.
                    elseif ($field instanceof SelectField && true) {
                        if (sizeof($field->getOptions()) == 0 && !array_key_exists($name, $list)) {
                            // form can still be submitted.
                            // An empty select with no options is not included in the POST field
                        } // If only 1 option can be selected, it should be in the submitted values!
                        elseif (!$field->isMultiple() && !array_key_exists($name, $list)) {
                            $reason = 'Selectfield "' . $name . '" does not exists in submited data array.';
                            $this->submitted = false;
                        }
                    } // Submit button?
                    // Then it should exists but only if there is just 1 button
                    elseif ($field instanceof SubmitButton && true) {
                        if (array_key_exists($field->getName(), $list)) {
                            $buttonsFound++;
                        } elseif ($buttonCount == 1) {
                            $reason = sprintf(
                                'Submitbutton "%s" does not exists in submited data array.',
                                $field->getName()
                            );
                            $this->submitted = false;
                        }
                    } // Image button? Then the name_x and name_y values should exists!
                    // (but only if its the only 1 button)
                    elseif ($field instanceof ImageButton && true) {
                        if (array_key_exists($field->getName() . '_x', $list) &&
                            array_key_exists($field->getName() . '_y', $list)
                        ) {
                            $buttonsFound++;
                        } elseif ($buttonCount == 1) {
                            $reason = sprintf(
                                'Imagebutton "%s" should submit a _x and _y values,' .
                                'but they are not in the data array.',
                                $field->getName()
                            );
                            $this->submitted = false;
                        }
                    } // For all other fields, it should just exists in the $_POST or $_GET array.
                    // If not, the form is not submitted.
                    elseif (!$field instanceof CheckBox && !$field instanceof RadioButton &&
                        !array_key_exists($name, $list)
                    ) {
                        $reason = 'Field "' . $name . '" does not exists in submited data array.';
                        $this->submitted = false;
                    }
                }

                // if here, and the field is still "submitted", then do our final button check
                if ($this->submitted && $buttonCount > 0 && $buttonsFound == 0) {
                    $reason = sprintf(
                        'We have found %d buttons in the form, but we did not found any ' .
                        'of the buttons in the data array ($_GET or $_POST)',
                        $buttonCount
                    );

                    $this->submitted = false;
                }
            }
        } else {
            $reason = 'The form is invalid because of a previous check which failed. We did not re-analyze the ' .
                'submitted form. If you want this, please call clearCache() first.';
        }

        return $this->submitted;
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
        $this->submitted = (bool)$status;
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
     * @throws \Exception
     */
    public function setMethod($method)
    {
        $method = strtolower($method);

        if (in_array($method, [Form::METHOD_GET, Form::METHOD_POST])) {
            $this->method = $method;
        } else {
            throw new \Exception('Incorrect method value given');
        }
        return $this;
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
     * Clear our cache of the submitted values.
     * This could be useful when you have changed the submitted values and want to re-analyze them.
     * @return Form
     */
    public function clearCache()
    {
        static::$cache = [];

        // remove our "remembered" submitted value
        $this->submitted = null;

        // remove our "remembered" valid value
        foreach ($this->fields as $field) {
            if ($field instanceof AbstractFormField) {
                $field->clearCache();
            }
        }
        return $this;
    }

    /**
     * Get the default formatter.
     *
     * For more information about formatters, {@see AbstractRenderer}
     *
     * @return AbstractRenderer
     */
    public static function getDefaultRenderer()
    {
        return Form::$defaultRenderer;
    }

    /**
     * Set a default formatter for all the form objects which are created.
     *
     * This could be useful when creating multiple forms in your project, and
     * if you don't want to set a custom formatter for every Form object.
     *
     * Example:
     * ```php
     * // set the default formatter which should be used
     * Form::setDefaultRenderer( new MyCustomRenderer() );
     * ```
     *
     * For more information about renderers, {@see AbstractRenderer}
     *
     * @param AbstractRenderer $renderer
     */
    public static function setDefaultRenderer(AbstractRenderer $renderer)
    {
        Form::$defaultRenderer = $renderer;
    }

    /**
     * Return the default encoding filter.
     *
     * @return InterfaceEncodingFilter
     */
    public static function getDefaultEncodingFilter()
    {
        return Form::$defaultEncodingFilter;
    }

    /**
     * Set a default encoding filter.
     * This will be used to filter the input to make sure it's the correct encoding.
     *
     * This could be useful when creating multiple forms in your project, and
     * if you don't want to set a custom encoding filter for every Form object.
     *
     * Example:
     * ```php
     * // set the default formatter which should be used
     * Form::setDefaultEncodingFilter( new Utf8EncodingFilter() );
     * ```
     *
     * @param InterfaceEncodingFilter $filter
     */
    public static function setDefaultEncodingFilter(InterfaceEncodingFilter $filter)
    {
        Form::$defaultEncodingFilter = $filter;
    }

    /**
     * Return if CSRF protection is enabled by default.
     *
     * @return bool
     */
    public static function isDefaultCsrfProtectionEnabled()
    {
        return Form::$defaultCsrfProtectionEnabled;
    }

    /**
     * Set if CSRF protection is enabled by default.
     *
     * @param boolean $enabled
     */
    public static function setDefaultCsrfProtectionEnabled($enabled)
    {
        Form::$defaultCsrfProtectionEnabled = (bool)$enabled;
    }

    /**
     * Get the values of all fields as an array.
     * @return array
     */
    public function getDataAsArray()
    {
        $fields = $this->getFields();

        if (!$fields) {
            return [];
        }

        $result = [];

        foreach ($fields as $field) {
            //ignore non-fields
            if (!$field instanceof AbstractFormField) {
                continue;
            }

            // if a field is disabled, we will ignore the field and only set an empty string as value
            if ($field->isDisabled()) {
                if (!array_key_exists($field->getName(), $result)) {
                    $result[$field->getName()] = '';
                }
                continue;
            }


            if ($field instanceof CheckBox) {
                $result[$field->getName()] = $field->isChecked() ? $field->getValue() : '';
            } elseif ($field instanceof RadioButton) {
                // if the field is not checked...
                if (!$field->isChecked()) {
                    // there was no other field with the same name yet
                    if (!array_key_exists($field->getName(), $result)) {
                        // then set the field with an empty value
                        $result[$field->getName()] = '';
                    }
                } // the field is checked
                else {
                    $result[$field->getName()] = $field->getValue();
                }
            } elseif ($field instanceof UploadField) {
                $value = $field->getValue();
                if (!$value ||
                    !isset($value['error']) ||
                    $value['error'] == UPLOAD_ERR_NO_FILE ||
                    empty($value['name'])
                ) {
                    continue;
                }
                $result[$field->getName()] = $value['name'];
            } elseif ($field instanceof AbstractFormField) {
                $result[$field->getName()] = $field->getValue();
            }
        }
        return $result;
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
     * Get an array with all validation errors
     * @return array
     */
    public function getValidationErrors()
    {
        $fields = $this->getFields();
        if (!$fields) {
            return [];
        }

        $result = [];
        foreach ($fields as $field) {
            if ($field instanceof AbstractFormField) {
                if (!$field->isValid()) {
                    $result = array_merge($result, $field->getErrorMessages());
                }
            }
        }

        return $result;
    }

    /**
     * Auto fill the given form with the given values.
     * $values can either be an object or an array. The array key's or object's properties must match the
     * names of the field in the form.
     *
     * Example:
     * ```php
     * // here an array with fieldname => value format
     * $default = array(
     *   'myfield' => 'Value',
     *   'field2'  => 'Another value'
     * );
     *
     * // here we fill the form with the values
     *  $form -> fill( $form, $default );
     * ```
     *
     * @param object|array $values
     * @throws \Exception
     * @internal param Form $form
     */
    public function fill($values)
    {
        // make sure its a composite type
        if (!is_array($values) && !is_object($values)) {
            throw new \Exception('Only composite types can be used for filling a form!');
        }

        foreach ($values as $key => $value) {
            $fields = $this->getFieldsByName($key);

            foreach ($fields as $field) {
                if ($field instanceof CheckBox || $field instanceof RadioButton) {
                    $field->setChecked($value == $field->getValue());
                } else {
                    $field->setValue($value);
                }
            }
        }
    }

    /**
     * Return a list of fields which have the name which equals the given name
     *
     * @param string $name
     * @return AbstractFormField[]
     */
    public function getFieldsByName($name)
    {
        $result = [];

        foreach ($this->fields as $field) {
            if ($field->getName() == $name) {
                $result[] = $field;
            }
        }

        return $result;
    }

    /**
     * Return a list of fields which have the name which equals the given name
     *
     * @param string $className
     * @return AbstractFormField[]
     */
    public function getFieldsByClass($className)
    {
        $result = [];

        foreach ($this->fields as $field) {
            if ($field instanceof $className ||
                get_class($field) == $className ||
                (new \ReflectionClass($field))->getShortName() == $className
            ) {
                $result[] = $field;
            }
        }

        return $result;
    }

    /**
     * This shorthand will do a getFieldByName search and return the field.
     * This allows you to also search the field like this:
     * ```php
     * // create a form
     * $form = new Form();
     *
     * // create a field
     * $form -> textField('name');
     *
     * // get the field:
     * $field = $form('name');
     * ```
     *
     * @param string $name
     * @return AbstractFormField
     */
    public function __invoke($name)
    {
        return $this->getFieldByName($name);
    }

    /**
     * Return's the fields value from the GET or POST array, or null if not found.
     * NOTE: This function should NOT be used for retrieving the value which is set in the field itsself,
     * because this function will only retrieve the value from GET/POST array.
     *
     * To retrieve the current value for the field itsself, please use:
     * ```php
     * $form -> getFieldByName('x') -> getValue();
     * ```
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
        // if not yet in the cache, look it up
        if (!array_key_exists($name, static::$cache)) {
            if ($this->getMethod() == Form::METHOD_GET) {
                $list = $_GET;
            } else {
                $list = $_POST;
            }

            // look it up directly in the get/post array
            if (array_key_exists($name, $list)) {
                static::$cache[$name] = $list[$name];
            } // check for braces (array names?)
            elseif (preg_match_all('/\[(.*)\]/U', $name, $match, PREG_OFFSET_CAPTURE)) {
                // Walk all found matches and create a list of names
                // (could be more, like "field[name1][name2]
                $names = [];
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
                    if ((string)$keyName !== "") {
                        if (is_array($base) && array_key_exists($keyName, $base)) {
                            $base = $base[$keyName];
                        } // no value found!
                        else {
                            $base = null;
                        }
                    }
                }

                static::$cache[$name] = $base;
            } else {
                static::$cache[$name] = null;
            }

            // do we have a value?
            if (static::$cache[$name]) {
                // make sure we filter the input
                if ($this->encodingFilter instanceof InterfaceEncodingFilter) {
                    // filter the input
                    static::$cache[$name] = $this->encodingFilter->filter(static::$cache[$name]);
                }
            }
        }

        return static::$cache[$name];
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
            }
        }

        return $this;
    }

    /**
     * Remove all fields by the given name.
     * If there are more then 1 field with the given name, then all will be removed.
     *
     * @param $name
     * @return Form
     */
    public function removeAllFieldsByName($name)
    {
        foreach ($this->fields as $i => $field) {
            if ($field->getName() == $name) {
                unset($this->fields[$i]);
            }
        }

        return $this;
    }

    /**
     * Remove a field from the form by the name of the field
     *
     * @param $id
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
     * Return the form close tag: ```</form>```
     * @return string
     */
    public function close()
    {
        return '</form>';
    }

    /**
     * Return a field by it's id, or null when its not found.
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
        if (!$this->isCsrfProtectionEnabled()) {
            return true;
        }

        // the form is not submitted
        if (!$this->isSubmitted()) {
            return true;
        }

        // the form is valid, so the csrf is also valid.
        if ($this->isValid()) {
            return true;
        }

        // the form is invalid. Let's check if the CSRF value is the problem.
        foreach ($this->fields as $field) {
            if ($field instanceof AbstractFormField && $field->getName() == 'csrftoken' && !$field->isValid()) {
                return false;
            }
        }

        // the field is not the problem...
        return true;
    }

    /**
     * Returns the current CSRF protection state.
     * CSRF stands for Cross Site Request Forgery.
     *
     * This method returns if we want to use a token to protect ourselfs agains this kind of attacks.
     * NOTE: This requires that we can make use of sessions. If this is disabled, we will return false!
     *
     * @return boolean
     */
    public function isCsrfProtectionEnabled()
    {
        // is there no session available? Then always disable CSRF protection.
        if (session_id() == '') {
            if (!headers_sent()) {
                // @todo: cookie wegschrijven en controleren of deze matched in de post waarde
            } else {
                return false;
            }
        }


        return $this->csrfProtection;
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
            if ($field instanceof AbstractFormField && !$field->isValid()) {
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
        $errors = [];
        foreach ($this->fields as $field) {
            if ($field instanceof AbstractFormField && !$field->isValid()) {
                $fldErrors = $field->getErrorMessages();
                $errors = array_merge($errors, $fldErrors);
            }
        }

        return $errors;
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
     * add a field to this form, so that it can be retrieved by the method getFieldByName
     *
     * @param Element $field
     */
    public function addField(Element &$field)
    {
        $this->fields[] = $field;
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
     * Set the action of this form and return the Form reference.
     * NOTE: The action is added in the HTML without any escaping!
     * Make sure YOU have escaped possible dangerous characters!
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
     * Set the target and return the Form reference
     *
     * @deprecated
     *
     * @param string $target
     * @see http://www.w3schools.com/tags/tag_form.asp
     * @deprecated
     * @return Form
     */
    public function setTarget($target)
    {
        $this->target = $target;
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
     * Return mime-types of files that can be submitted through a file upload.
     *
     * Specifies a comma-separated list of file types that the server accepts
     * (that can be submitted through the file upload)
     *
     * @deprecated
     * @return string
     */
    public function getAccept()
    {
        return $this->accept;
    }

    /**
     * Set the mime-types of files that can be submitted through a file upload
     * and return the Form reference
     *
     * @param string $accept
     * @deprecated
     * @return Form
     */
    public function setAccept($accept)
    {
        $this->accept = $accept;
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
     * Set how form-data should be encoded before sending it to a server
     * Possible values:
     * - application/x-www-form-urlencoded
     * sdfkjsdhfkjhsdjfhkjsdhfkjhsdfkjhsdkjhsdfkjhsdkjfhsdkjhfsdkjhfkjshdkfjhsdkjfhsdkjhfkjsdhfkjsdhfkjhsdkjfhsdkjfhkjsdhfkjdhdkjfh
     * - multipart/form-data
     * - text/plain
     *
     * @param string $enctype
     * @return Form
     * @throws \Exception
     */
    public function setEnctype($enctype)
    {
        $enctype = strtolower(trim($enctype));

        if (in_array($enctype, [
            self::ENCTYPE_MULTIPART,
            self::ENCTYPE_PLAIN,
            self::ENCTYPE_URLENCODED
        ])) {
            $this->enctype = $enctype;
        } else {
            throw new \Exception('Incorrect enctype given!');
        }

        return $this;
    }

    /**
     * Returns the character-sets the server can handle for form-data
     *
     * @return string
     */
    public function getAcceptCharset()
    {
        return $this->acceptCharset;
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
        $this->acceptCharset = $charset;
        return $this;
    }

    /**
     * Create a new textfield
     *
     * @param string $name
     * @return TextField;
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
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Return a string representation of the form tag
     *
     * @return string
     */
    public function render()
    {
        return $this->getRenderer()->render($this);
    }

    /**
     * Return the formatter
     *
     * @return AbstractRenderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Set a renderer object
     *
     * @param AbstractRenderer $renderer
     * @return Form
     */
    public function setRenderer(AbstractRenderer $renderer)
    {
        $this->renderer = $renderer;
        return $this;
    }
}
