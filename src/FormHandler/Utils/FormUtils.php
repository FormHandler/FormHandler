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
