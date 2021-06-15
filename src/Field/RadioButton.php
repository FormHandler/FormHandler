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
     *
     * @var bool
     */
    protected bool $checked = false;

    /**
     * The value of this radiobutton when it's selected.
     *
     * @var string
     */
    protected $value = '';

    /**
     * The label of this radiobutton.
     * Note: this is only useful (and used) if the formatter parses it. By default, this its thus unused!
     *
     * @var string
     */
    protected string $label = '';

    /**
     * RadioButton constructor.
     *
     * @param Form   $form
     * @param string $name
     * @param string $value
     */
    public function __construct(Form $form, string $name = '', string $value = '')
    {
        $this->form = $form;
        $this->form->addField($this);

        if (!empty($name)) {
            $this->setName($name);
        }

        if ($value !== null) {
            $this->setValue($value);
        }
    }

    /**
     * Set the value for this field and return the CheckBox reference
     *
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value): self
    {
        parent::setValue($value);
        $this->setChecked($this->form->getFieldValue($this->name) == $this->getValue());

        return $this;
    }

    /**
     * Set the name
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
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
    public function getLabel(): string
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
     *
     * @return $this
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Return if this input element should be preselected when the page loads
     *
     * @return bool
     */
    public function isChecked(): bool
    {
        return $this->checked;
    }

    /**
     * Specifies that an input element should be preselected when the page loads
     *
     * @param bool $checked
     *
     * @return RadioButton
     */
    public function setChecked(bool $checked = true): self
    {
        $this->clearCache();
        $this->checked = $checked;

        return $this;
    }
}
