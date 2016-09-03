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
