<?php

namespace FormHandler\Field;

use FormHandler\Form;

/**
 * Create a text input field.
 *
 * Other HTML5 types are also allowed. See the ```TextField::TYPE_*``` constants
 * @package FormHandler\Field
 */
class TextField extends PassField
{
    const TYPE_COLOR = 'color';
    const TYPE_DATE = 'date';
    const TYPE_DATETIME = 'datetime';
    const TYPE_DATETIME_LOCAL = 'datetime-local';
    const TYPE_EMAIL = 'email';
    const TYPE_MONTH = 'month';

    // common used (HTML5) types
    const TYPE_NUMBER = 'number';
    const TYPE_RANGE = 'range';
    const TYPE_SEARCH = 'search';
    const TYPE_TEL = 'tel';
    const TYPE_TEXT = 'text';
    const TYPE_TIME = 'time';
    const TYPE_URL = 'url';
    const TYPE_WEEK = 'week';

    /**
     * The type of the field. Default is text, but HTML5 types
     * are also allowed. See the ```TextField::TYPE_*``` constants
     * @var string
     */
    protected $type = self::TYPE_TEXT;

    /**
     * TextField constructor.
     * @param Form $form
     * @param string $name
     */
    public function __construct(Form &$form, $name = '')
    {
        $this->form = $form;
        $this->form->addField($this);

        if (!empty($name)) {
            $this->setName($name);
        }
    }

    /**
     * Set the name
     *
     * @param string $name
     * @return TextField
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->setValue($this->form->getFieldValue($this->name));
        return $this;
    }

    /**
     * Get the value for type. Default text
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value for type. In HTML5 new types are allowed, for example "number", "email", etc.
     * Default is still "text".
     * @param $value
     * @return TextField
     */
    public function setType($value)
    {
        $this->type = $value;
        return $this;
    }
}
