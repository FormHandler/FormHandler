<?php
namespace FormHandler\Field;

use FormHandler\Form;

abstract class AbstractFormButton extends Element
{
    /**
     * The form object where this image button is located in.
     * @var Form
     */
    protected $form;

    /**
     * Is this button disabled?
     * @var bool
     */
    protected $disabled = false;

    /**
     * The name of the button
     * @var string
     */
    protected $name;

    /**
     * The size of the button
     * @var int
     */
    protected $size;

    /**
     * Return if this field is disabled
     *
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * Set if this field is disabled and return the ImageButton reference
     *
     * @param bool $disabled
     * @return $this
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * Return the name of the ImageButton
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name of the field and return the ImageButton reference
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * Set the size of the field and return the ImageButton reference
     *
     * @param int $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Return string representation of this button
     *
     * @return string
     */
    public function render()
    {
        return $this->getForm()->getRenderer()->render($this);
    }

    /**
     * Return the form instance of this field
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }
}
