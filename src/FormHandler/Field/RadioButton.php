<?php
namespace FormHandler\Field;

use FormHandler\Form;

/**
 * Radio button class.
 */
class RadioButton extends AbstractFormField
{
    /**
     * Is this radiobutton is checked or not
     * @var bool
     */
    protected $checked = false;

    /**
     * The value of this radiobutton when it's selected.
     * @var string
     */
    protected $value;

    /**
     * The label of this radiobutton.
     * Note: this is only usefull (and used) if the formatter parses it. By default its thus unused!
     * @var string
     */
    protected $label;

    /**
     * RadioButton constructor.
     * @param Form $form
     * @param string $name
     * @param string $value
     */
    public function __construct(Form &$form, $name = '', $value = '')
    {
        $this->form = $form;
        $this->form->addField($this);

        if ($value !== null) {
            $this->setValue($value);
        }

        if (!empty($name)) {
            $this->setName($name);
        }
    }

    /**
     * Set the value for this field and return the CheckBox reference
     *
     * @param string $value
     * @return RadioButton
     */
    public function setValue($value)
    {
        parent::setValue($value);
        $this->setChecked($this->form->getFieldValue($this->name) == $this->getValue());
        return $this;
    }

    /**
     * Set the name
     *
     * @param string $name
     * @return RadioButton
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->setChecked($this->form->getFieldValue($this->name) == $this->getValue());
        return $this;
    }

    /**
     * Get the label for this field.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the label used for this field.
     * The label will NOT be HTML escaped, so please be aware to do this yourself!
     *
     * Please note: this is just a "container" for the label text.
     * The Formatter class will generate the label for us!
     *
     * @param string $label
     * @return RadioButton
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Return if this input element should be preselected when the page loads
     *
     * @return bool
     */
    public function isChecked()
    {
        return $this->checked;
    }

    /**
     * Specifies that an input element should be preselected when the page loads
     *
     * @param bool $checked
     * @return RadioButton
     */
    public function setChecked($checked = true)
    {
        $this->clearCache();
        $this->checked = (bool)$checked;
        return $this;
    }
}
