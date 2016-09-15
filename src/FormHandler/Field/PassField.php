<?php
namespace FormHandler\Field;

use FormHandler\Form;

/**
 * Create a password input field.
 */
class PassField extends AbstractFormField
{
    /**
     * The max allowed length of the inpit
     * @var int
     */
    protected $maxlength;

    /**
     * Is this field readonly? By default not (false)
     * @var bool
     */
    protected $readonly = false;

    /**
     * The size of this field
     * @var int
     */
    protected $size;

    /**
     * The value of this field
     * @var string
     */
    protected $value;

    /**
     * Set the placeholder for this field for when it has no value
     * @var string
     */
    protected $placeholder;

    /**
     * Constructor.
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
     * @return PassField
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->setValue($this->form->getFieldValue($this->name));
        return $this;
    }

    /**
     * Return the max length of this field
     *
     * @return int
     */
    public function getMaxlength()
    {
        return $this->maxlength;
    }

    /**
     * Set the max length of this field and return the PassField reference
     *
     * @param int $maxlength
     * @return PassField
     */
    public function setMaxlength($maxlength)
    {
        $this->maxlength = (integer)$maxlength;
        return $this;
    }

    /**
     * Return the readonly status of this field
     *
     * @return bool
     */
    public function isReadonly()
    {
        return $this->readonly;
    }

    /**
     * Set if this field is readonly and return the PassField reference
     *
     * @param bool $readonly
     * @return PassField
     */
    public function setReadonly($readonly)
    {
        $this->readonly = $readonly;
        return $this;
    }

    /**
     * Return the size of the field
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set the size of the field and return the PassField reference
     *
     * @param int $size
     * @return PassField
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Get the value for placeholder
     *
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * Set the value for placeholder
     *
     * @param string $value
     * @return PassField
     */
    public function setPlaceholder($value)
    {
        $this->placeholder = $value;
        return $this;
    }
}
