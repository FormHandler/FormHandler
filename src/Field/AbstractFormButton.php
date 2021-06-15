<?php

namespace FormHandler\Field;

use FormHandler\Form;

abstract class AbstractFormButton extends Element
{
    /**
     * The form object where this image button is located in.
     *
     * @var Form
     */
    protected Form $form;

    /**
     * Is this button disabled?
     *
     * @var bool
     */
    protected bool $disabled = false;

    /**
     * The name of the button
     *
     * @var string
     */
    protected string $name = '';

    /**
     * The size of the button
     *
     * @var int|null
     */
    protected ?int $size = null;

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
     * Set if this field is disabled and return the ImageButton reference
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
     * Return the name of the ImageButton
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the name of the field and return the ImageButton reference
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Return the size of the field
     *
     * @return int|null
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * Set the size of the field and return the ImageButton reference
     *
     * @param int $size
     *
     * @return $this
     */
    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Return string representation of this button
     *
     * @return string
     */
    public function render(): string
    {
        return $this->getForm()->getRenderer()->render($this);
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
}
