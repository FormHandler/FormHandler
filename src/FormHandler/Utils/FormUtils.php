<?php
namespace FormHandler\Utils;

use FormHandler\Field\AbstractFormField;
use FormHandler\Field\CheckBox;
use FormHandler\Field\RadioButton;
use FormHandler\Field\SelectField;
use FormHandler\Field\UploadField;
use FormHandler\Form;

/**
 * This class contains some static helper methods which can be handy.
 *
 * @package form
 */
class FormUtils
{
    /**
     * Auto fill the given form with the given values.
     * $values can either be an object or an array. The array key's or object's properties must match the
     * names of the field in the form.
     *
     * Example:
     * <code>
     * // here an array with fieldname => value format
     * $default = array(
     *   'myfield' => 'Value',
     *   'field2'  => 'Another value'
     * );
     *
     * // here we fill the form with the values
     * FormUtils::autoFill( $form, $default );
     * </code>
     *
     * @param Form $form
     * @param object|array $values
     * @throws \Exception
     * @return void
     */
    public static function autoFill(Form &$form, $values)
    {
        // make sure its a composite type
        if (!is_array($values) && !is_object($values)) {
            throw new \Exception('Only composite types can be used for filling a form!');
        }

        foreach ($values as $key => $value) {
            $fields = $form->getFieldsByName($key);

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
     * Get an array with all validation errors
     * @param Form $form
     * @return array
     */
    public static function getValidationErrors(Form &$form)
    {
        $fields = $form->getFields();
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
     * Get the values of all fields in the given form as an array.
     * @param Form $form
     * @return array
     */
    public static function getDataAsArray(Form &$form)
    {
        $fields = $form->getFields();

        if (!$fields) {
            return null;
        }

        $result = array();

        foreach ($fields as $field) {
            if ($field instanceof CheckBox) {
                if (!$field->isDisabled()) {
                    $result[$field->getName()] = $field->isChecked() ? $field->getValue() : null;
                }
            } else {
                if ($field instanceof RadioButton) {
                    // if the field is not checked...
                    if (!$field->isChecked()) {
                        // there was no other field with the same name yet
                        if (!array_key_exists($field->getName(), $result)) {
                            // the field is not disabled...
                            if (!$field->isDisabled()) {
                                // then set the field with an empty value
                                $result[$field->getName()] = null;
                            }
                        }
                    } // the field is checked
                    else {
                        // the field is not disabled...
                        if (!$field->isDisabled()) {
                            $result[$field->getName()] = $field->getValue();
                        }
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
                    if (!$field->isDisabled()) {
                        $result[$field->getName()] = $value['name'];
                    }
                } elseif ($field instanceof SelectField) {
                    $value = $field->getValue();
                    if ($field->isMultiple()) {
                        if ($value instanceof \ArrayObject) {
                            $value = $value->getArrayCopy();
                        }
                        if (!$field->isDisabled()) {
                            $result[$field->getName()] = $value;
                        }
                    } else {
                        if (!$field->isDisabled()) {
                            $result[$field->getName()] = $value;
                        }
                    }
                } elseif ($field instanceof AbstractFormField) {
                    if (!$field->isDisabled()) {
                        $result[$field->getName()] = $field->getValue();
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Add query string params as hidden field to the given form.
     * This can be handy if you want to pass along the data in a $_POST way.
     * The values will be HTML escaped properly, so you don't have to bother about that.
     *
     * @param Form $form
     * @param array $whitelist
     * @param array $blacklist
     * @return void
     */
    public static function queryStringToForm(Form &$form, array $whitelist = null, array $blacklist = null)
    {
        if (!empty($_GET)) {
            foreach ($_GET as $key => $value) {
                if (is_array($whitelist) && !in_array($key, $whitelist)) {
                    continue;
                }
                if (is_array($blacklist) && in_array($key, $blacklist)) {
                    continue;
                }

                $form->hiddenField($key)->setValue($value);
            }
        }
    }
}
