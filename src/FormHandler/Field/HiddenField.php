<?php
namespace FormHandler\Field;

/**
 */
class HiddenField extends AbstractFormField
{

    protected $value;

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
     * @return HiddenField
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->setValue($this->form->getFieldValue($this->name));
        return $this;
    }

    /**
     * Set the value for this field and return the TextField reference
     *
     * @param string $value
     * @return TextField
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
     * Return string representation of this field
     *
     * @return string
     */
    public function render()
    {
        $str = '<input type="hidden"';

        if (! empty($this->name)) {
            $str .= ' name="' . $this->name . '"';
        }

        if (! empty($this->value)) {
            $str .= ' value="' . htmlspecialchars($this->value) . '"';
        }

        if ($this->disabled !== null && $this->disabled) {
            $str .= ' disabled="disabled"';
        }

        $str .= parent::render();
        $str .= ' />';

        return $str;
    }
}
