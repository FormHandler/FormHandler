<?php
namespace FormHandler\Field;

use FormHandler\Form;

/**
 */
class PassField extends AbstractFormField
{

    protected $maxlength;

    protected $readonly;

    protected $size;

    protected $value;

    protected $placeholder;

    public function __construct(Form &$form, $name = '')
    {
        $this->form = $form;
        $this->form->addField($this);

        if (! empty($name)) {
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
     * Set the max length of this field and return the PassField reference
     *
     * @param int $maxlength
     * @return PassField
     */
    public function setMaxlength($maxlength)
    {
        $this->maxlength = (integer) $maxlength;
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
     * Return the readonly status of this field
     *
     * @return bool
     */
    public function isReadonly()
    {
        return $this->readonly;
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
     * Return the size of the field
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set the value for this field and return the PassField reference
     *
     * @param string $value
     * @return PassField
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Return the value for this field
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
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
     * Return string representation of this field
     *
     * @return string
     */
    public function render()
    {
        $str = '<input type="password"';

        if (! empty($this->name)) {
            $str .= ' name="' . $this->name . '"';
        }

        if (! empty($this->size)) {
            $str .= ' size="' . $this->size . '"';
        }

        if ($this->disabled !== null && $this->disabled) {
            $str .= ' disabled="disabled"';
        }

        if (! empty($this->maxlength)) {
            $str .= ' maxlength="' . $this->maxlength . '"';
        }

        if ($this->readonly !== null && $this->readonly) {
            $str .= ' readonly="readonly"';
        }

        if ($this->placeholder) {
            $str .= ' placeholder="' . htmlspecialchars($this->placeholder) . '"';
        }

        $str .= parent::render();
        $str .= ' />';

        return $str;
    }
}
