<?php

namespace FormHandler\Field;

use FormHandler\Form;

/**
 * This class will create a submit button
 */
class SubmitButton extends AbstractFormButton
{
    /**
     * The value of this button (text which is displayed)
     *
     * @var string
     */
    protected string $value;

    /**
     * SubmitButton constructor.
     *
     * @param Form   $form
     * @param string $value
     */
    public function __construct(Form $form, string $value = '')
    {
        $this->form = $form;
        $this->form->addField($this);

        $this->setValue($value);
    }

    /**
     * Return the form instance of this field
     *
     * @return Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * Set if this field is disabled and return the TextField reference
     *
     * @param bool $disabled
     *
     * @return $this
     */
    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Return if this field is disabled
     *
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * Return the value for this field
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Set the value for this field and return the TextField reference
     *
     * @param string $value
     *
     * @return $this
     */
    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
